<?php
class User {
	private $_db,
			$_sessionName = null,
			$_cookieName = null,
			$_data = array(),
			$_isLoggedIn = false;

	public function __construct($user = null) {
		$this->_db = DB::getInstance();

		$this->_sessionName = Config::get('session/session_name');
		$this->_cookieName = Config::get('remember/cookie_name');

		// Check if a session exists and set user if so.
		if(Session::exists($this->_sessionName) && !$user) {
			$user = Session::get($this->_sessionName);

			if($this->find($user)) {
				$this->_isLoggedIn = true;
			} else {
				$this->logout();
			}
		} else {
			$this->find($user);
		}
	}

	public function exists() {
		return (!empty($this->_data)) ? true : false;
	}

	public function find($user = null) {
		// Check if user_id specified and grab details
		if($user) {
			$field = (is_numeric($user)) ? 'id' : 'email';
			$data = $this->_db->get('users', array($field, '=', $user));

			// Check if data is valid (not false) before calling count()
			if($data && $data->count()) {
				$this->_data = $data->first();
				return true;
			}
		}
		return false;
	}

	public function create($fields = array()) {
		if(!$this->_db->insert('users', $fields)) {
			throw new Exception('There was a problem creating an account.');
		}
	}

	public function update($fields = array(), $id = null) {
		if(!$id && $this->isLoggedIn()) {
			$id = $this->data()->id;
		}

		if(!$this->_db->update('users', $id, $fields)) {
			throw new Exception('There was a problem updating.');
		} else {
			return true;
		}
	}

	public function login($username = null, $password = null, $remember = false) {

		if(!$username && !$password && $this->exists()) {
			Session::put($this->_sessionName, $this->data()->id);
		} else {
			$user = $this->find($username);

			if($user) {
				$storedPassword = $this->data()->password;
				$passwordValid = false;

				// Check if this is a new bcrypt hash (starts with $2y$)
				if (strpos($storedPassword, '$2y$') === 0) {
					// New password format - use password_verify
					$passwordValid = Hash::verify($password, $storedPassword);
				} else {
					// Legacy SHA-256 format - verify and upgrade
					$passwordValid = Hash::verifyLegacy($password, $storedPassword, $this->data()->salt);

					// If valid, upgrade to new hash format
					if ($passwordValid) {
						$this->upgradePassword($password);
					}
				}

				if($passwordValid) {
					Session::put($this->_sessionName, $this->data()->id);

					if($remember) {
						$hash = Hash::unique();
						$hashCheck = $this->_db->get('users_session', array('user_id', '=', $this->data()->id));

						// Check if hashCheck is valid (not false) before calling count()
						if(!$hashCheck || !$hashCheck->count()) {
							$this->_db->insert('users_session', array(
								'user_id' => $this->data()->id,
								'hash' => $hash
							));
						} else {
							$hash = $hashCheck->first()->hash;
						}

						Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
					}

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Upgrade a legacy password to the new bcrypt format
	 * @param string $password The plain text password
	 */
	private function upgradePassword($password) {
		$newHash = Hash::make($password);
		$this->_db->update('users', $this->data()->id, array(
			'password' => $newHash,
			'salt' => '' // Clear the salt as it's no longer needed
		));
	}

	public function hasPermission($key) {
		$group = $this->_db->query("SELECT * FROM rl_groups WHERE id = ?", array($this->data()->group));

		// Check if group query was successful before calling count()
		if($group && $group->count()) {
			$permissions = json_decode($group->first()->permissions, true);

			if(isset($permissions[$key]) && $permissions[$key] === 1) {
				return true;
			}
		}

		return false;
	}

	public function isLoggedIn() {
		return $this->_isLoggedIn;
	}

	public function data() {
		return $this->_data;
	}

	public function getProfilePicture() {
		return ($this->_data->image != '') ? 'files/'.$this->_data->image : 'http://placehold.it/170x170';
	}

	public function logout() {
		$this->_db->delete('users_session', array('user_id', '=', $this->data()->id));

		Cookie::delete($this->_cookieName);
		Session::delete($this->_sessionName);
	}
}

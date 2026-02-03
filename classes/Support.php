<?php

/**
 * Description of Support
 * Handles all support operations including:
 *  - Creating new cases
 *  - Updating cases
 *  - Closing, re-opening cases
 *  - Ownership handling
 *  - Email notification on support updates
 *
 * @author Alexander
 */
class Support {
	private $_db,
			$_data = array(),
			$_caseData = array(),
			$_isLoggedIn = false;

	public function __construct($caseID = null) {
		$this->_db = DB::getInstance();
		
		$user = new User();
		$this->_isLoggedIn = $user->isLoggedIn();
		
		if (isset($caseID)) {
			$this->find($caseID);
		}
	}

	public function exists() {
		return (!empty($this->_data)) ? true : false;
	}

	public function find($caseID = null) {
		if($caseID) {
			$field = (is_numeric($caseID)) ? 'id' : 'email';
			$data = $this->_db->get('cases', array($field, '=', $caseID));
			$caseData = $this->_db->get('cases_convos', array($field, '=', $caseData));

			if($data->count()) {
				$this->_data = $data->first();
				return true;
			}
		}
		return false;
	}

	public function create($fields = array()) {
		if(!$this->_db->insert('cases_convos', $fields)) {
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

	public function data() {
		return $this->_data;
	}
}

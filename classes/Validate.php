<?php

class Validate {

	private $_passed = false,
			$_errors = array(),
			$_db = null;

	public function __construct() {
		$this->_db = DB::getInstance();
	}

	public function check($source, $items = array()) {
		foreach ($items as $item => $rules) {
			foreach ($rules as $rule => $rule_value) {

				$value = trim($source[$item]);

				if ($rule === 'required' && $rule_value === true && empty($value)) {
					$this->addError("{$item} er påkrevd.");
				} else if (!empty($value)) {

					switch ($rule) {
						case 'min':
							if (strlen($value) < $rule_value) {
								$this->addError("{$item} må inneholde minst {$rule_value} tegn.");
							}
							break;
						case 'max':
							if (strlen($value) > $rule_value) {
								$this->addError("{$item} kan inneholde maks {$rule_value} tegn.");
							}
							break;
						case 'matches':
							if ($value != $source[$rule_value]) {
								$this->addError("{$rule_value} må være lik {$item}.");
							}
							break;
						case 'unique':
							$check = $this->_db->get('rl_users', array($item, '=', $value));
							if ($check->count()) {
								$this->addError("{$item} er allerede tatt i bruk.");
							}
							break;
						case 'numeric':
							if (!is_numeric($value)) {
								$this->addError("{$item} må være et tall.");
							}
							break;
						case 'email':
							if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
								$this->addError("{$item} er ikke en gyldig e-postadresse.");
							}
							break;
					}
				}
			}
		}

		if (empty($this->_errors)) {
			$this->_passed = true;
		}

		return $this;
	}

	protected function addError($error) {
		$this->_errors[] = $error;
	}

	public function passed() {
		return $this->_passed;
	}

	public function errors() {
		return $this->_errors;
	}

}

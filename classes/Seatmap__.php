<?php

class Seatmap {

	private $_db,
			$_data = array();

	public function __construct($seatID = null) {
		$this->_db = DB::getInstance();
		if (!is_null($seatID)) {
			$this->find($seatID);
		}
	}

	public function exists() {
		return (!empty($this->_data)) ? true : false;
	}

	public function find($seatID = null) {
		// Check if user_id specified and grab details
		if ($seatID) {
			$field = 'id';
			$data = $this->_db->get('seats', array($field, '=', $seatID));

			if ($data->count()) {
				$this->_data = $data->first();
				return true;
			}
		}
		return false;
	}

	public function create($fields = array()) {
//		if (!$this->_db->insert('seats', $fields)) {
//			throw new Exception('There was a problem creating an account.');
//		}
	}

	public function update($fields = array(), $id = null) {
		if (!$id && $this->isLoggedIn()) {
			$id = $this->data()->id;
		}

		if (!$this->_db->update('seats', $id, $fields)) {
			throw new Exception('There was a problem updating.');
		} else {
			return true;
		}
	}

	public function data() {
		return $this->_data;
	}

	public function outputSeats($from, $to, $type, $direction) {
		echo ($direction == 'horizontal') ? '<tr>' : '';
		for ($i = $from; $i <= $to; $i++) {
			$this->find($i);
			$occupied_by_id = $this->data()->occupied_by;
			echo ($direction == 'vertical') ? '<tr>' : '';
			if ($this->data()->type == 2) {
				$seat_status = $occupied_by_id;
				if ($seat_status == 0) {
					echo "<td class='seat seat-crew' onclick='viewSeat({$i});' data-toggle='tooltip' data-placement='top' data-container='body' title='Ledig!'>{$i}</td>";
				} else {
					$userObj = new User($occupied_by_id);
					$occupied_by_name = $userObj->data()->name;
					echo "<td class='seat seat-crew' onclick='viewSeat({$i});' data-toggle='tooltip' data-placement='top' data-container='body' title='{$occupied_by_name}'>{$i}</td>";
				}
			} else if ($this->data()->type == 3) {
				echo "<td class='seat seat-other'>&nbsp;</td>";
			} else {
				$seat_status = $occupied_by_id;
				//echo "<td class='seat '></td>";
				if ($seat_status == 0) {
					echo "<td class='seat seat-free' onclick='viewSeat({$i});' data-toggle='tooltip' data-placement='top' data-container='body' title='Ledig!'>{$i}</td>";
				} else {
					$userObj = new User($occupied_by_id);
					$occupied_by_name = $userObj->data()->name;
					echo "<td class='seat seat-occupied' onclick='viewSeat({$i});' data-toggle='tooltip' data-placement='top' data-container='body' title='{$occupied_by_name}'>{$i}</td>";
				}
				//echo ($seat_status == 0) ? "<td class='seat seat-free' onclick='viewSeat({$i});'>{$i}</td>" : "<td class='seat seat-occupied' onclick='viewSeat({$i});'>{$i}</td>";
			}
			echo ($direction == 'vertical') ? '</tr>' : '';
		}
		echo ($direction == 'horizontal') ? '</tr>' : '';
	}

}

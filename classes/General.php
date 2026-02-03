<?php

/**
 * Description of General
 *
 * @author Alexander
 */
class General {

	public static function nameCasing($string) {
		$word_splitters = array(' ', '-', "O'", "L'", "D'", 'St.', 'Mc');
		$lowercase_exceptions = array('the', 'van', 'den', 'von', 'und', 'der', 'de', 'da', 'of', 'and', "l'", "d'");
		$uppercase_exceptions = array('III', 'IV', 'VI', 'VII', 'VIII', 'IX');

		$string = strtolower($string);
		foreach ($word_splitters as $delimiter) {
			$words = explode($delimiter, $string);
			$newwords = array();
			foreach ($words as $word) {
				if (in_array(strtoupper($word), $uppercase_exceptions))
					$word = strtoupper($word);
				else
				if (!in_array($word, $lowercase_exceptions))
					$word = ucfirst($word);

				$newwords[] = $word;
			}

			if (in_array(strtolower($delimiter), $lowercase_exceptions))
				$delimiter = strtolower($delimiter);

			$string = join($delimiter, $newwords);
		}
		return $string;
	}

	public static function getAge($birthdayDate) {
		$dob = strtotime($birthdayDate);
		$tdate = time();
		return date('Y', $tdate) - date('Y', $dob);
	}
	
	public static function formatCaseID($input) {
		return str_pad($input, 8, "CAS-0000", STR_PAD_LEFT);
	}

}
<?php

/**
 * Handles file operations like upload, move, validation and deletion
 *
 * @author Alexander
 */
class Filelogo {

	private $_error = null,
			$_data = array();

	public function upload($file, $allowed_ext, $max_filesize = 20971520) {
		$filename = $file['name'];
		$filesize = $file['size'];
		$filetmpname = $file['tmp_name'];

		$file_ext = strtolower(end(explode('.', $filename)));

		$allowed_extensions = explode(',', $allowed_ext);

		if (!$this->valid_ext($file_ext, $allowed_extensions)) {
			$this->_error = 'Filtypen er ikke gyldig. Velg en annen filtype og prøv på nytt.';
			return false;
		}

		if (!$this->valid_size($filetmpname, $max_filesize)) {
			$this->_error = 'Filen er for stor. Du kan ikke laste opp filer som er større en ' . $this->formatFileSize($max_filesize, 0);
			return false;
		}

		$uploaded_filename = Hash::salt() . '.' . $file_ext;

		if (!move_uploaded_file($filetmpname, '../images/logo/' . $uploaded_filename)) {
			$this->_error = 'Det oppstod en uventet feil ved opplasting av filen';
			return false;
		}

		$this->_data = (object) array(
					'filename' => $filename,
					'filesize' => $filesize,
					'tmpname' => $filetmpname,
					'file_ext' => $file_ext,
					'uploaded_filename' => $uploaded_filename
		);
		return $uploaded_filename;
	}

	public function image_resize($width, $height, $filename) {
		/* Get original image x y */
		list($w, $h) = getimagesize('../images/logo/' . $filename);

		/* calculate new image size with ratio */
		$ratio = max($width / $w, $height / $h);
		$h = ceil($height / $ratio);
		$x = ($w - $width / $ratio) / 2;
		$w = ceil($width / $ratio);

		/* new file name */
		$path = 'files/' . $filename;

		/* read binary data from image file */
		$imgString = file_get_contents('../images/logo/' . $filename);

		/* create image from string */
		$image = imagecreatefromstring($imgString);
		$tmp = imagecreatetruecolor($width, $height);
		imagecopyresampled($tmp, $image, 0, 0, $x, 0, $width, $height, $w, $h);

		/* Save image */
		switch ($this->get_extension($filename)) {
			case 'jpg':
				imagejpeg($tmp, $path, 100);
				break;
			case 'jpeg':
				imagejpeg($tmp, $path, 100);
				break;
			case 'png':
				imagepng($tmp, $path, 0);
				break;
			case 'gif':
				imagegif($tmp, $path);
				break;
			default:
				exit;
				break;
		}

		/* cleanup memory */
		imagedestroy($image);
		imagedestroy($tmp);

		return $path;
	}
	
	public function get_extension($filename) {
		return strtolower(end(explode('.', $filename)));
	}

	public function valid_ext($file_ext, $allowed_ext) {
		return in_array($file_ext, $allowed_ext);
	}

	public function valid_size($file, $max_size) {
		return (filesize($file) >= $max_size) ? false : true;
	}

	public function formatFileSize($size, $decimals) {
		$base = log($size) / log(1024);
		$suffixes = array('b', 'kb', 'Mb', 'Gb', 'Tb');

		return round(pow(1024, $base - floor($base)), $decimals) . $suffixes[floor($base)];
	}

	public static function isPosted($name) {
		return ($_FILES[$name]['name'] != '') ? true : false;
	}

	public static function delete($name) {
		if (!file_exists($name)) {
			return false;
		}

		return unlink($name);
	}
	
	public static function exists($file) {
		return file_exists($file);
	}
	
	public function error() {
		return ($this->_error == null) ? false : $this->_error;
	}

	public function data() {
		return $this->_data;
	}

}

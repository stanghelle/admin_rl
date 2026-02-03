<?php
class Template {
	
	public static function output($file) {
		require_once "template/" . $file . ".php";
	}
	
}
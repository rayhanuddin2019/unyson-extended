<?php

abstract class Attr_Backups_Task {
	/**
	 * @return string Unique type
	 */
	abstract public function get_type();

	abstract public function get_title();

	abstract public function execute(array $args, array $state = array());
}
<?php


namespace sys\jordan\meetup;


use sys\jordan\core\form\SimpleForm;

class MeetupSettingsForm extends SimpleForm {

	public function __construct() {
		parent::__construct("Meetup Settings", "", []);
	}

}
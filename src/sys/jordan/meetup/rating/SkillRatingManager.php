<?php


namespace sys\jordan\meetup\rating;


use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\utils\MeetupBaseTrait;

class SkillRatingManager {
	use MeetupBaseTrait;

	public function __construct(MeetupBase $plugin) {
		$this->setPlugin($plugin);
	}

	public function load(string $uuid, callable $onLoad): void {

	}

}
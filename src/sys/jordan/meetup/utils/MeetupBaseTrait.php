<?php


namespace sys\jordan\meetup\utils;


use sys\jordan\meetup\MeetupBase;

trait MeetupBaseTrait {

	protected MeetupBase $plugin;

	public function getPlugin(): MeetupBase {
		return $this->plugin;
	}

	public function setPlugin(MeetupBase $plugin): void {
		$this->plugin = $plugin;
	}

}
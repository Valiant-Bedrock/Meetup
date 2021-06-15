<?php


namespace sys\jordan\meetup\scenario\defaults;


use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\scenario\Scenario;

class Bowless extends Scenario {

	public function __construct() {
		parent::__construct("Bowless", "If enabled, no bows will be able to be used during the game");
	}

	public function onAdd(Game $game): void {
		// TODO: Implement onAdd() method.
	}

	public function onRemove(Game $game): void {
		// TODO: Implement onRemove() method.
	}
}
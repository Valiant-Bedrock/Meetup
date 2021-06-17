<?php


namespace sys\jordan\meetup\scenario\defaults;


use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\scenario\Scenario;

class NoClean extends Scenario {

	public function __construct() {
		parent::__construct("No-Clean", "Players are given 15 seconds of invulnerability. This can be nullified by hitting other players.");
	}

	public function onAdd(Game $game): void {}

	public function onRemove(Game $game): void {}
}
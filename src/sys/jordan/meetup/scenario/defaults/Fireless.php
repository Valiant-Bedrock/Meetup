<?php


namespace sys\jordan\meetup\scenario\defaults;


use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\scenario\Scenario;

class Fireless extends Scenario {

	public function __construct() {
		parent::__construct("Fireless", "Fire damage will be disabled. This includes lava, flint and steel, and flame/fire aspect enchantments.");
	}

	public function onAdd(Game $game): void {}

	public function onRemove(Game $game): void {}
}
<?php


namespace sys\jordan\meetup\scenario\defaults;


use JetBrains\PhpStorm\Pure;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\scenario\Scenario;

class Timebomb extends Scenario {

	private array $tasks = [];

	#[Pure]
	public function __construct() {
		parent::__construct("Timebomb", "Upon death, a player's items will be dropped into a chest along with a golden head");
	}

	public function onAdd(Game $game): void {}

	public function onRemove(Game $game): void {}
}
<?php


namespace sys\jordan\meetup\scenario\defaults;


use JetBrains\PhpStorm\Pure;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\scenario\Scenario;

class Switcheroo extends Scenario {

	#[Pure]
	public function __construct() {
		parent::__construct("Switcheroo", "Hitting a player with a projectile (snowballs, arrows, etc.) will cause the thrower to switch positions with the player hit");
	}

	public function onAdd(Game $game): void {}

	public function onRemove(Game $game): void {}
}
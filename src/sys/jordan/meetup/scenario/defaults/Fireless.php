<?php


namespace sys\jordan\meetup\scenario\defaults;


use JetBrains\PhpStorm\Pure;
use pocketmine\event\entity\EntityDamageEvent;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\scenario\Scenario;

class Fireless extends Scenario {

	#[Pure]
	public function __construct() {
		parent::__construct("Fireless", "Fire damage will be disabled. This includes lava, flint and steel, and flame/fire aspect enchantments.");
	}

	public function onAdd(Game $game): void {}

	public function onRemove(Game $game): void {}

	public function handleDamage(EntityDamageEvent $event): void {
		/** @var MeetupPlayer $victim */
		$victim = $event->getEntity();
		if($event->getCause() === EntityDamageEvent::CAUSE_FIRE || $event->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK) {
			$event->cancel();
			$victim->extinguish();
		}
	}
}
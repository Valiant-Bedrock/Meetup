<?php


namespace sys\jordan\meetup\scenario\defaults;


use JetBrains\PhpStorm\Pure;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\utils\TextFormat;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\scenario\Scenario;

class Switcheroo extends Scenario {

	#[Pure]
	public function __construct() {
		parent::__construct("Switcheroo", "Hitting a player with a bow will cause the thrower to switch positions with the player hit");
	}

	public function onAdd(Game $game): void {}

	public function onRemove(Game $game): void {}

	public function handleProjectileHit(ProjectileHitEvent $event): void {
		if($event->getEntity() instanceof Arrow && $event instanceof ProjectileHitEntityEvent) {
			/** @var MeetupPlayer $victim */
			if(($victim = $event->getEntityHit()) instanceof MeetupPlayer) {
				/** @var MeetupPlayer $owner */
				$owner = $event->getEntity()->getOwningEntity();
				$victimLocation = $victim->getLocation();
				$ownerLocation = $owner->getLocation();
				$owner->teleport($victimLocation);
				$victim->teleport($ownerLocation);
				$owner->notify(TextFormat::YELLOW . "You have switched places with {$victim->getName()}!", TextFormat::YELLOW);
				$victim->notify(TextFormat::YELLOW . "You have switched places with {$owner->getName()}!", TextFormat::YELLOW);
			}
		}
	}

}
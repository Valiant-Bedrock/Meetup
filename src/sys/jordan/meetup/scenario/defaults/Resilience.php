<?php


namespace sys\jordan\meetup\scenario\defaults;


use JetBrains\PhpStorm\Pure;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\player\MeetupPlayerDeathEvent;
use sys\jordan\meetup\scenario\Scenario;

class Resilience extends Scenario {

	/** The effect amplifier (2 hearts per amplifier) */
	public const AMPLIFIER = 1;
	/** The length of the effect */
	public const DURATION = 30;

	#[Pure]
	public function __construct() {
		parent::__construct("Resilience", "Upon killing a player, the killer will receive 4 hearts of absorption for 30 seconds.");
	}

	public function onAdd(Game $game): void {}

	public function onRemove(Game $game): void {}

	public function handleMeetupDeath(MeetupPlayerDeathEvent $event): void {
		/** @var EntityDamageByEntityEvent $lastDamageEvent */
		if(($lastDamageEvent = $event->getLastDamageEvent()) instanceof EntityDamageByEntityEvent) {
			/** @var MeetupPlayer $damager */
			if(($damager = $lastDamageEvent->getDamager()) instanceof MeetupPlayer) {
				if($damager->inGame() && $damager->getGame()->getPlayerManager()->isPlayer($damager)) {
					$damager->getEffects()->add(new EffectInstance(VanillaEffects::ABSORPTION(), self::DURATION, self::AMPLIFIER));
				}
			}
		}
	}

}
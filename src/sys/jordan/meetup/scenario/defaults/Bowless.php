<?php


namespace sys\jordan\meetup\scenario\defaults;


use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\utils\TextFormat;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\scenario\Scenario;

class Bowless extends Scenario {

	public function __construct() {
		parent::__construct("Bowless", "If enabled, no bows will be able to be used during the game");
	}

	public function onAdd(Game $game): void {}

	public function onRemove(Game $game): void {}

	public function handleShootBow(EntityShootBowEvent $event): void {
		/** @var MeetupPlayer $player */
		if(($player = $event->getEntity()) instanceof MeetupPlayer) {
			$event->cancel();
			$player->notify(TextFormat::RED . "You can't use a bow in a bowless game!", TextFormat::RED);
		}
	}
}
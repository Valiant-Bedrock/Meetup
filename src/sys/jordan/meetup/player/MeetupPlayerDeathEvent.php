<?php


namespace sys\jordan\meetup\player;


use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use sys\jordan\meetup\MeetupPlayer;

class MeetupPlayerDeathEvent extends Event {

	public function __construct(protected MeetupPlayer $player, protected ?EntityDamageEvent $lastDamageEvent = null, protected bool $dropItems = true) {}

	public function getPlayer(): MeetupPlayer {
		return $this->player;
	}

	public function getLastDamageEvent(): ?EntityDamageEvent {
		return $this->lastDamageEvent;
	}

	public function canDropItems(): bool {
		return $this->dropItems;
	}

	public function setCanDropItems(bool $dropItems = true): void {
		$this->dropItems = $dropItems;
	}
}
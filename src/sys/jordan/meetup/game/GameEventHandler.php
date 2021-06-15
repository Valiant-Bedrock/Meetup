<?php

declare(strict_types=1);

namespace sys\jordan\meetup\game;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerQuitEvent;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\GameTrait;

class GameEventHandler {

	use GameTrait;

	/**
	 * GameEventHandler constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	public function handleQuit(PlayerQuitEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
	}

	public function handleBreak(BlockBreakEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();

	}

	public function handleDamage(EntityDamageEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getEntity();

	}

	public function handleExhaust(PlayerExhaustEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();

	}

	public function handleRegainHealth(EntityRegainHealthEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getEntity();

	}

}
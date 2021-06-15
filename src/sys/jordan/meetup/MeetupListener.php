<?php


namespace sys\jordan\meetup;

use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\Plugin;
use sys\jordan\core\base\BaseListener;

class MeetupListener extends BaseListener {

	/**
	 * MeetupListener constructor.
	 * @param MeetupBase $plugin
	 */
	public function __construct(MeetupBase $plugin) {
		parent::__construct($plugin);
	}

	public function getPlugin(): Plugin|MeetupBase {
		return $this->plugin;
	}

	/**
	 * @param PlayerCreationEvent $event
	 * @priority HIGHEST
	 */
	public function handleCreation(PlayerCreationEvent $event): void {
		$event->setPlayerClass(MeetupPlayer::class);
	}

	/**
	 * @param PlayerJoinEvent $event
	 * @priority HIGHEST
	 */
	public function handleJoin(PlayerJoinEvent $event): void {
		$event->setJoinMessage(null);
		$event->getPlayer()->teleport($this->getPlugin()->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
	}

	public function handleQuit(PlayerQuitEvent $event): void {
		$event->setQuitMessage(null);
	}

	public function handleMove(PlayerMoveEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($event->getTo()->getFloorY() <= 0) {
			$player->teleport($this->getPlugin()->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
		}

	}

}
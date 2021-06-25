<?php


namespace sys\jordan\meetup;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\PluginBase;
use sys\jordan\core\base\BaseListener;

class MeetupListener extends BaseListener {

	/**
	 * MeetupListener constructor.
	 * @param MeetupBase $plugin
	 */
	public function __construct(MeetupBase $plugin) {
		parent::__construct($plugin);
	}

	public function getPlugin(): PluginBase|MeetupBase {
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
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		$this->getPlugin()->setupLobbyPlayer($player);
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event) {
		/** @var MeetupPlayer $player */
		if(($player = $event->getEntity()) instanceof MeetupPlayer && !$player->inGame()) {
			$event->cancel();
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event) {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if(!$player->inGame() && !$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
			$event->cancel();
		}
	}

	/**
	 * @param PlayerChatEvent $event
	 */
	public function handleChat(PlayerChatEvent $event) {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if(!$player->inGame()) {
			$event->setRecipients($this->getPlugin()->getLobbyPlayers());
		}
	}

	/**
	 * @param PlayerDropItemEvent $event
	 */
	public function handleDropItem(PlayerDropItemEvent $event) {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if(!$player->inGame() && !$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
			$event->cancel();
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function handlePlace(BlockPlaceEvent $event) {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if(!$player->inGame() && !$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
			$event->cancel();
		}
	}

	/**
	 * @param PlayerItemUseEvent $event
	 */
	public function handleItemUse(PlayerItemUseEvent $event) {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if(!$player->inGame()) {
			$this->getPlugin()->getMenu()->check($player, $player->getInventory()->getHeldItemIndex());
		}
	}

	public function handleQuit(PlayerQuitEvent $event): void {
		$event->setQuitMessage(null);
	}

	public function handleMove(PlayerMoveEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if(!$player->inGame() && $event->getTo()->getFloorY() <= 0) {
			$player->teleport($this->getPlugin()->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
		}
	}

}
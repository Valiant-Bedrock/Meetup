<?php


namespace sys\jordan\meetup;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\GameMode;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
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
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		$player->setGamemode(GameMode::SURVIVAL());
		$player->setNameTag($player->getName() . TextFormat::YELLOW . "[{$player->getOSString()}/{$player->getInputString()}");
		$player->feed();
		$player->fullHeal();
		$player->getHungerManager()->setEnabled(false);
		$player->teleport($this->getPlugin()->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
		$player->setShowCoordinates(true);
		$this->getPlugin()->getMenu()->give($player);
		$this->getPlugin()->sendScoreboard($player);
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event) {
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
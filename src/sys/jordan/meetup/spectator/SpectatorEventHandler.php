<?php


namespace sys\jordan\meetup\spectator;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerQuitEvent;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\GameTrait;

class SpectatorEventHandler {
	use GameTrait;

	public function __construct(Game $game) {
		$this->setGame($game);
	}

	public function handleChat(PlayerChatEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		$this->getGame()->chat($player, $event, true);
	}

	public function handleQuit(PlayerQuitEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		$this->getGame()->getSpectatorManager()->quit($player);
	}

	public function handleBreak(BlockBreakEvent $event): void {
		$event->cancel();
	}

	public function handleDamage(EntityDamageEvent $event): void {

	}

	public function handleExhaust(PlayerExhaustEvent $event): void {
		$event->cancel();
	}

	public function handleDropItem(PlayerDropItemEvent $event): void {
		$event->cancel();
	}

	public function handleRegainHealth(EntityRegainHealthEvent $event): void {

	}

	public function handleItemUse(PlayerItemUseEvent $event): void {
		//TODO: Remove this in favor of using a hotbar menu for spectators
		$event->cancel();
	}
}
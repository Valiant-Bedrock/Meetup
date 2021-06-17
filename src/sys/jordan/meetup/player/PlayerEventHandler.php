<?php

declare(strict_types=1);

namespace sys\jordan\meetup\player;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerQuitEvent;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\game\GameState;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\GameTrait;

class PlayerEventHandler {
	use GameTrait;

	/**
	 * PlayerEventManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	public function handleChat(PlayerChatEvent $event): void {

	}

	public function handleQuit(PlayerQuitEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		$this->game->getPlayerManager()->quit($player);
	}

	public function handleBreak(BlockBreakEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if(!$this->game->hasStarted()) {
			$event->cancel();
		}
	}

	public function handleDamage(EntityDamageEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getEntity();
		if(!$this->game->hasStarted()) {
			$event->cancel();
		} else {
			if($event->getFinalDamage() >= $player->getHealth()) {
				$this->getGame()->getPlayerManager()->death($player, $event);
				$event->cancel();
			}
		}
	}

	public function handleExhaust(PlayerExhaustEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if(!$this->game->hasStarted()) {
			$event->cancel();
		}
	}

	public function handleRegainHealth(EntityRegainHealthEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getEntity();
		if($event->getRegainReason() !== EntityRegainHealthEvent::CAUSE_MAGIC || $event->getRegainReason() !== EntityRegainHealthEvent::CAUSE_CUSTOM) {
			$event->cancel();
		}
	}

	public function handleItemUse(PlayerItemUseEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->getState() === GameState::VOTING()) {
			$this->getGame()->getVoteManager()->getMenu()->check($player, $player->getInventory()->getHeldItemIndex());
		}
	}

}
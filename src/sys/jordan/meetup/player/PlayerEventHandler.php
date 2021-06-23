<?php

declare(strict_types=1);

namespace sys\jordan\meetup\player;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\TextFormat;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\game\GameState;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\GameTrait;
use sys\jordan\meetup\utils\MeetupUtilities;

class PlayerEventHandler {
	use GameTrait;

	/**
	 * PlayerEventManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	public function handleCraftItem(CraftItemEvent $event) {
		foreach($event->getOutputs() as $output) {
			if(!$output->equals(MeetupUtilities::GOLDEN_HEAD(), true, false)) {
				$event->cancel();
			}
		}
	}

	public function handleChat(PlayerChatEvent $event): void {
		$this->getGame()->chat($event);
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
		} else {
			if($event->getBlock()->getFullId() === $this->getGame()->getBorder()->getFullBlockId()) {
				$event->cancel();
			}
		}
	}

	public function handlePlace(BlockPlaceEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		$position = $event->getBlock()->getPos();
		if($this->getGame()->getBorder()->exceedsHeightLimit($position)) {
			$event->cancel();
			$player->notify(TextFormat::RED . "Sky-basing is not allowed!", TextFormat::RED);
		}
	}

	public function handleShootBow(EntityShootBowEvent $event): void {
		if(!$this->game->hasStarted() || $this->game->getState()->equals(GameState::POSTGAME())) {
			$event->cancel();
		}
	}

	public function handleDamage(EntityDamageEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getEntity();
		if(!$this->game->hasStarted() || $this->game->getState()->equals(GameState::POSTGAME())) {
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

	public function handleDropItem(PlayerDropItemEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if(!$this->game->hasStarted()) {
			$event->cancel();
		}
	}

	public function handleRegainHealth(EntityRegainHealthEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getEntity();
		if($event->getRegainReason() !== EntityRegainHealthEvent::CAUSE_MAGIC && $event->getRegainReason() !== EntityRegainHealthEvent::CAUSE_CUSTOM) {
			$event->cancel();
		}
		if(!$event->isCancelled()) {
			$player->setScoreTag($player->getHealthString());
		}
	}

	public function handleProjectileHit(ProjectileHitEvent $event): void {
		if($event instanceof ProjectileHitEntityEvent) {
			/** @var MeetupPlayer $damager */
			/** @var MeetupPlayer $victim */
			if(($damager = $event->getEntity()->getOwningEntity()) instanceof MeetupPlayer && ($victim = $event->getEntityHit()) instanceof MeetupPlayer) {
				if($this->getGame()->getPlayerManager()->isPlayer($damager) && $this->getGame()->getPlayerManager()->isPlayer($victim)) {
					$damager->notify(TextFormat::YELLOW . "{$victim->getName()}'s health: {$victim->getHealthString()}" , TextFormat::YELLOW);
				}
			}
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
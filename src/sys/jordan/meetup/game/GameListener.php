<?php

declare(strict_types=1);

namespace sys\jordan\meetup\game;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\Plugin;
use sys\jordan\core\base\BaseListener;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\GameTrait;

/**
 * Class GameListener
 * @package sys\jordan\meetup\game
 *
 * TODO: Get rid of OOP hell (i.e getGame()->getPlayerManager()->getHandler()->handleChat())
 */
class GameListener extends BaseListener {

	use GameTrait;

	/**
	 * GameListener constructor.
	 * @param MeetupBase $plugin
	 * @param Game $game
	 */
	public function __construct(MeetupBase $plugin, Game $game) {
		parent::__construct($plugin);
		$this->setGame($game);
	}

	public function getPlugin(): Plugin|MeetupBase {
		return $this->plugin;
	}

	public function handleChat(PlayerChatEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->getPlayerManager()->isPlayer($player)) {
			$this->getGame()->getPlayerManager()->getHandler()->handleChat($event);
		} elseif($this->getGame()->getSpectatorManager()->isSpectator($player)) {
			$this->getGame()->getSpectatorManager()->getHandler()->handleChat($event);
		}
	}

	public function handleProjectileHit(ProjectileHitEvent $event) {
		$player = $event->getEntity()->getOwningEntity();
		if($player instanceof MeetupPlayer && $this->getGame()->getPlayerManager()->isPlayer($player)) {
			$this->getGame()->getPlayerManager()->getHandler()->handleProjectileHit($event);
		}
	}

	public function handleQuit(PlayerQuitEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->getPlayerManager()->isPlayer($player)) {
			$this->getGame()->getPlayerManager()->getHandler()->handleQuit($event);
		} elseif($this->getGame()->getSpectatorManager()->isSpectator($player)) {
			$this->getGame()->getSpectatorManager()->getHandler()->handleQuit($event);
		}
	}

	public function handleBreak(BlockBreakEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->getPlayerManager()->isPlayer($player)) {
			$this->getGame()->getPlayerManager()->getHandler()->handleBreak($event);
		} elseif($this->getGame()->getSpectatorManager()->isSpectator($player)) {
			$this->getGame()->getSpectatorManager()->getHandler()->handleBreak($event);
		}
	}

	public function handleDamage(EntityDamageEvent $event): void {
		/** @var MeetupPlayer $player */
		if(($player = $event->getEntity()) instanceof MeetupPlayer) {
			if($this->getGame()->getPlayerManager()->isPlayer($player)) {
				$this->getGame()->getPlayerManager()->getHandler()->handleDamage($event);
			} elseif($this->getGame()->getSpectatorManager()->isSpectator($player)) {
				$this->getGame()->getSpectatorManager()->getHandler()->handleDamage($event);

			}
		}
	}

	public function handleExhaust(PlayerExhaustEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->getPlayerManager()->isPlayer($player)) {
			$this->getGame()->getPlayerManager()->getHandler()->handleExhaust($event);
		} elseif($this->getGame()->getSpectatorManager()->isSpectator($player)) {
			$this->getGame()->getSpectatorManager()->getHandler()->handleExhaust($event);
		}
	}

	public function handleDropItem(PlayerDropItemEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->getPlayerManager()->isPlayer($player)) {
			$this->getGame()->getPlayerManager()->getHandler()->handleDropItem($event);
		} elseif($this->getGame()->getSpectatorManager()->isSpectator($player)) {
			$this->getGame()->getSpectatorManager()->getHandler()->handleDropItem($event);
		}
	}

	public function handleRegainHealth(EntityRegainHealthEvent $event): void {
		/** @var MeetupPlayer $player */
		if(($player = $event->getEntity()) instanceof MeetupPlayer) {
			if($this->getGame()->getPlayerManager()->isPlayer($player)) {
				$this->getGame()->getPlayerManager()->getHandler()->handleRegainHealth($event);
			} elseif($this->getGame()->getSpectatorManager()->isSpectator($player)) {
				$this->getGame()->getSpectatorManager()->getHandler()->handleRegainHealth($event);
			}
		}
	}

	public function handleItemUse(PlayerItemUseEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->getPlayerManager()->isPlayer($player)) {
			$this->getGame()->getPlayerManager()->getHandler()->handleItemUse($event);
		} elseif($this->getGame()->getSpectatorManager()->isSpectator($player)) {
			$this->getGame()->getSpectatorManager()->getHandler()->handleItemUse($event);
		}
	}

}
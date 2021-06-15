<?php

declare(strict_types=1);

namespace sys\jordan\meetup\game;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\Plugin;
use sys\jordan\core\base\BaseListener;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\GameTrait;

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

	public function handleQuit(PlayerQuitEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->getPlayerManager()->isPlayer($player)) {
			$this->getGame()->getHandler()->handleQuit($event);
		}
	}

	public function handleBreak(BlockBreakEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->getPlayerManager()->isPlayer($player)) {
			$this->getGame()->getHandler()->handleBreak($event);
		}
	}

	public function handleDamage(EntityDamageEvent $event): void {
		/** @var MeetupPlayer $player */
		if(($player = $event->getEntity()) && $this->getGame()->getPlayerManager()->isPlayer($player)) {
			$this->getGame()->getHandler()->handleDamage($event);
		}
	}

	public function handleExhaust(PlayerExhaustEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->getPlayerManager()->isPlayer($player)) {
			$this->getGame()->getHandler()->handleExhaust($event);
		}
	}

	public function handleRegainHealth(EntityRegainHealthEvent $event): void {
		/** @var MeetupPlayer $player */
		if(($player = $event->getEntity()) instanceof MeetupPlayer && $this->getGame()->getPlayerManager()->isPlayer($player)) {
			$this->getGame()->getHandler()->handleRegainHealth($event);
		}
	}

}
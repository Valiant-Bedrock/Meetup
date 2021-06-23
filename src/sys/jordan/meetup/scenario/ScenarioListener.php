<?php


namespace sys\jordan\meetup\scenario;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use sys\jordan\core\base\BaseListener;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\player\MeetupPlayerDeathEvent;

class ScenarioListener extends BaseListener {

	public function __construct(protected ScenarioManager $manager) {
		parent::__construct($manager->getGame()->getPlugin());
	}

	public function check(MeetupPlayer $player): bool {
		return $player->inGame() && $this->manager->getGame()->getPlayerManager()->isPlayer($player);
	}

	public function handleBreak(BlockBreakEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->check($player)) {
			$this->manager->handleBreak($event);
		}
	}

	public function handlePlace(BlockPlaceEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->check($player)) {
			$this->manager->handlePlace($event);
		}
	}

	public function handleProjectileHit(ProjectileHitEvent $event): void {
		/** @var MeetupPlayer $player */
		if(($player = $event->getEntity()->getOwningEntity()) instanceof MeetupPlayer && $this->check($player)) {
			$this->manager->handleProjectileHit($event);
		}
	}

	public function handleConsume(PlayerItemConsumeEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->check($player)) {
			$this->manager->handleConsume($event);
		}
	}

	public function handleCraft(CraftItemEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->check($player)) {
			$this->manager->handleCraft($event);
		}
	}

	public function handleShootBow(EntityShootBowEvent $event): void {
		/** @var MeetupPlayer $player */
		if(($player = $event->getEntity()) instanceof MeetupPlayer && $this->check($player)) {
			$this->manager->handleShootBow($event);
		}
	}

	public function handleDamage(EntityDamageEvent $event): void {
		/** @var MeetupPlayer $player */
		if(($player = $event->getEntity()) instanceof MeetupPlayer && $this->check($player)) {
			$this->manager->handleDamage($event);
		}
	}
	public function handleEntityDeath(EntityDeathEvent $event): void {
		if($event->getEntity()->getWorld() === $this->manager->getGame()->getWorld()) {
			$this->manager->handleEntityDeath($event);
		}
	}

	public function handleMeetupDeath(MeetupPlayerDeathEvent $event): void {
		if($this->check($event->getPlayer())) {
			$this->manager->handleMeetupDeath($event);
		}
	}

	public function handleInteract(PlayerInteractEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->check($player)) {
			$this->manager->handleInteract($event);
		}
	}

	public function handleJoin(PlayerJoinEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->check($player)) {
			$this->manager->handleJoin($event);
		}
	}

	public function handleQuit(PlayerQuitEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		if($this->check($player)) {
			$this->manager->handleQuit($event);
		}
	}
}
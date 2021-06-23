<?php


namespace sys\jordan\meetup\scenario;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\player\MeetupPlayerDeathEvent;

abstract class Scenario implements ScenarioInterface {

	public function __construct(protected string $name, protected string $description, protected string $imageUrl = "") {}

	public function getName(): string {
		return $this->name;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function getImageUrl(): string {
		return $this->imageUrl;
	}

	public function hasImage(): bool {
		return strlen($this->imageUrl) > 0;
	}

	abstract public function onAdd(Game $game): void;

	abstract public function onRemove(Game $game): void;

	public function handleBreak(BlockBreakEvent $event): void {}

	public function handlePlace(BlockPlaceEvent $event): void {}

	public function handleProjectileHit(ProjectileHitEvent $event): void {}

	public function handleShootBow(EntityShootBowEvent $event): void {}

	public function handleCommand(PlayerCommandPreprocessEvent $event): void {}

	public function handleConsume(PlayerItemConsumeEvent $event): void {}

	public function handleCraft(CraftItemEvent $event): void {}

	public function handleDamage(EntityDamageEvent $event): void {}

	public function handleMeetupDeath(MeetupPlayerDeathEvent $event): void {}

	public function handleEntityDeath(EntityDeathEvent $event): void {}

	public function handleInteract(PlayerInteractEvent $event): void {}

	public function handleJoin(PlayerJoinEvent $event): void {}

	public function handleQuit(PlayerQuitEvent $event): void {}

}
<?php


namespace sys\jordan\meetup\scenario;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;

interface ScenarioInterface {

	public function handleBreak(BlockBreakEvent $event): void;

	public function handlePlace(BlockPlaceEvent $event): void;

	public function handleCommand(PlayerCommandPreprocessEvent $event): void;

	public function handleConsume(PlayerItemConsumeEvent $event): void;

	public function handleCraft(CraftItemEvent $event): void;

	public function handleTransaction(InventoryTransactionEvent $event): void;

	public function handleSmelt(FurnaceSmeltEvent $event): void;

	public function handleDamage(EntityDamageEvent $event): void;

	public function handleDeath(PlayerDeathEvent $event): void;

	public function handleEntityDeath(EntityDeathEvent $event): void;

	public function handleInteract(PlayerInteractEvent $event): void;

	public function handleJump(PlayerJumpEvent $event): void;

	public function handleMove(PlayerMoveEvent $event): void;

	public function handleToggleFlight(PlayerToggleFlightEvent $event): void;

	public function handleJoin(PlayerJoinEvent $event): void;

	public function handleQuit(PlayerQuitEvent $event): void;

}
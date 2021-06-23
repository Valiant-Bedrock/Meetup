<?php


namespace sys\jordan\meetup\scenario;


use JetBrains\PhpStorm\Pure;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\Event;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\TextFormat;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\player\MeetupPlayerDeathEvent;
use sys\jordan\meetup\utils\GameTrait;

class ScenarioManager {
	use GameTrait;

	/** @var Scenario[] */
	private array $scenarios = [];

	protected ScenarioListener $listener;

	/**
	 * ScenarioManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
		$this->listener = new ScenarioListener($this);
		$this->listener->register();
	}

	/**
	 * @return Scenario[]
	 */
	public function getScenarios(): array {
		return $this->scenarios;
	}


	public function add(Scenario $scenario): void {
		$this->scenarios[$scenario->getName()] = clone $scenario;
		$scenario->onAdd($this->getGame());
	}

	public function remove(Scenario $scenario): void {
		if($this->exists($scenario)) {
			$instance = $this->scenarios[$scenario->getName()];
			$instance->onRemove($this->getGame());
		}
	}

	#[Pure]
	public function exists(Scenario $scenario): bool {
		return isset($this->scenarios[$scenario->getName()]);
	}

	private function call(string $methodName, Event $event): void {
		if($this->getGame()->hasStarted()) {
			foreach($this->getScenarios() as $scenario) {
				$scenario->$methodName($event);
			}
		}
	}

	public function handleBreak(BlockBreakEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handlePlace(BlockPlaceEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handleProjectileHit(ProjectileHitEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handleConsume(PlayerItemConsumeEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handleCraft(CraftItemEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handleDamage(EntityDamageEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handleShootBow(EntityShootBowEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handleMeetupDeath(MeetupPlayerDeathEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handleEntityDeath(EntityDeathEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handleInteract(PlayerInteractEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handleJoin(PlayerJoinEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handleQuit(PlayerQuitEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function end(): void {
		foreach($this->scenarios as $scenario) {
			$scenario->onRemove($this->game);
			unset($this->scenarios[$scenario->getName()]);
		}
		$this->game->getLogger()->info(TextFormat::YELLOW . "Unregistering scenario listener...");
		$this->listener->unregister();
		$this->game->getLogger()->info(TextFormat::YELLOW . "Cleaning scenarios up...");
		unset($this->scenarios, $this->listener);
	}

}
<?php

declare(strict_types=1);

namespace sys\jordan\meetup\game;

use pocketmine\block\utils\SkullType;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\lang\TranslationContainer;
use pocketmine\math\Facing;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use sys\jordan\core\utils\TickEnum;
use sys\jordan\meetup\border\Border;
use sys\jordan\meetup\eliminations\EliminationManager;
use sys\jordan\meetup\kit\Kit;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\player\PlayerManager;
use sys\jordan\meetup\scenario\Scenario;
use sys\jordan\meetup\scenario\ScenarioManager;
use sys\jordan\meetup\spectator\SpectatorManager;
use sys\jordan\meetup\utils\MeetupBaseTrait;
use sys\jordan\meetup\vote\VoteManager;
use sys\jordan\meetup\world\WorldManager;

class Game {
	use MeetupBaseTrait;

	/** @var int */
	public const MAX_PLAYER_COUNT = 100;

	protected int $votingTime = 45;
	protected int $countdown = 45;
	protected int $time = 0;
	protected int $postgame = 15;

	protected Border $border;

	protected PlayerManager $playerManager;
	protected SpectatorManager $spectatorManager;
	protected EliminationManager $eliminationManager;
	protected ScenarioManager $scenarioManager;
	protected VoteManager $voteManager;

	protected ClosureTask $heartbeat;
	protected ClosureTask $scoreboardHeartbeat;

	protected GameListener $listener;
	protected GameLogger $logger;
	protected GameScoreboard $scoreboard;
	protected GameState $state;



	/**
	 * Game constructor.
	 * @param MeetupBase $plugin
	 * @param int $id
	 * @param World $world
	 * @param Kit $kit
	 */
	public function __construct(MeetupBase $plugin, protected int $id, protected World $world, protected Kit $kit) {
		$this->setPlugin($plugin);
		$this->setState(GameState::WAITING());
		$this->border = new Border($world);

		$this->heartbeat = new ClosureTask(function (): void { $this->update(); });
		$this->scoreboardHeartbeat = new ClosureTask(function (): void { $this->updateUI(); });

		$this->listener = new GameListener($plugin, $this);
		$this->listener->register();
		$this->logger = new GameLogger($this);

		$this->scoreboard = new GameScoreboard($this);

		$this->playerManager = new PlayerManager($this);
		$this->spectatorManager = new SpectatorManager($this);

		$this->eliminationManager = new EliminationManager($this);
		$this->scenarioManager = new ScenarioManager($this);
		$this->voteManager = new VoteManager($this);

		$plugin->getScheduler()->scheduleRepeatingTask($this->heartbeat, TickEnum::SECOND);
		$plugin->getScheduler()->scheduleRepeatingTask($this->scoreboardHeartbeat, 1);
	}

	public function getId(): int {
		return $this->id;
	}

	public function getBorder(): Border {
		return $this->border;
	}

	public function getKit(): Kit {
		return $this->kit;
	}

	public function giveKits(): void {
		foreach($this->getPlayerManager()->getPlayers() as $player) {
			$kit = $this->getKit()->pull();
			$player->getInventory()->setContents($kit->getItemContents());
			$player->getArmorInventory()->setContents($kit->getArmorContents());
		}
	}

	public function getWorld(): World {
		return $this->world;
	}

	public function getPlayerManager(): PlayerManager {
		return $this->playerManager;
	}

	public function getSpectatorManager(): SpectatorManager {
		return $this->spectatorManager;
	}

	/**
	 * @return MeetupPlayer[]
	 */
	public function getAll(): array {
		return array_filter(
			$this->playerManager->getPlayers() + $this->spectatorManager->getSpectators(),
			fn (MeetupPlayer $player): bool => $player->isConnected()
		);
	}

	public function getEliminationManager(): EliminationManager {
		return $this->eliminationManager;
	}

	public function getScenarioManager(): ScenarioManager {
		return $this->scenarioManager;
	}

	public function getVoteManager(): VoteManager {
		return $this->voteManager;
	}

	public function getListener(): GameListener {
		return $this->listener;
	}

	public function getScoreboard(): GameScoreboard {
		return $this->scoreboard;
	}

	public function getLogger(): GameLogger {
		return $this->logger;
	}

	public function getState(): GameState {
		return $this->state;
	}

	public function setState(GameState $state): void {
		$this->state = $state;
	}

	public function hasStarted(): bool {
		return $this->state === GameState::PLAYING() || $this->state === GameState::POSTGAME();
	}

	public function getFormattedTime(): string {
		return gmdate(($this->time >= TickEnum::HOUR ? "H:" : "") . "i:s", $this->time);
	}

	/**
	 * Main function to transition Meetup from
	 * WAITING -> VOTING
	 */
	public function start(): void {
		$this->notify(TextFormat::GREEN . "The player threshold has been met! Voting will now commence!", TextFormat::GREEN);
		$this->setState(GameState::VOTING());
		$this->getPlayerManager()->start();
		$this->getVoteManager()->giveItems();
	}

	/**
	 * Main function to start the Meetup
	 * COUNTDOWN -> PLAYING
	 */
	public function play(): void {
		foreach($this->playerManager->getPlayers() as $player) {
			$player->fullHeal();
			$player->feed();
			$player->setImmobile(false);
			$player->getHungerManager()->setEnabled(true);
		}
		$this->setState(GameState::PLAYING());
		$this->broadcastMessage(TextFormat::GREEN . "The meetup has now started! Good luck!", true);
	}

	public function end(): void {
		foreach($this->getAll() as $player) {
			$player->teleport($this->getPlugin()->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
		}
		$this->getPlayerManager()->end();
		$this->getSpectatorManager()->end();
		$this->getEliminationManager()->end();

	}

	public function update(): void {
		switch($this->getState()->id()) {
			case GameState::WAITING()->id():
				$this->handleWaiting();
				break;
			case GameState::VOTING()->id():
				$this->handleVoting();
				break;
			case GameState::COUNTDOWN()->id():
				$this->handleCountdown();
				break;
			case GameState::PLAYING()->id():
				$this->handlePlaying();
				break;
			case GameState::POSTGAME()->id():
				$this->handlePostgame();
				break;
		}
	}

	public function handleWaiting(): void {
		$this->broadcastActionBar(TextFormat::YELLOW . "Waiting for players...");
		if($this->playerManager->canStart()) {
			$this->start();
		}
	}

	public function handleVoting(): void {
		$this->broadcastActionBar(TextFormat::YELLOW . "Voting will end in $this->votingTime...");
		if($this->votingTime-- <= 0) {
			$scenarios = []; // $this->getVoteManager()->check();
//			foreach($scenarios as $scenario) {
//				$this->getScenarioManager()->add($scenario);
//			}
			if(count($scenarios) > 0) {
				$message = TextFormat::GREEN . "The scenarios for this game are: [" . implode(", ",  array_map(fn(Scenario $scenario): string => TextFormat::YELLOW . $scenario->getName() . TextFormat::GREEN, $scenarios)) . TextFormat::GREEN . "]!";
			} else {
				$message = TextFormat::GREEN . "There are no scenarios enabled for this game!";
			}
			$this->broadcastMessage($message, true);
			$this->setState(GameState::COUNTDOWN());
			$this->giveKits();
		}
	}

	public function handleCountdown(): void {
		$this->broadcastActionBar(TextFormat::YELLOW . "The game will commence in $this->countdown...");
		if($this->countdown-- <= 0) {
			$this->play();
		}
	}

	public function handlePlaying(): void {
		$this->time++;
		$this->getPlayerManager()->check();
	}

	public function handlePostgame(): void {
		$this->broadcastActionBar(TextFormat::YELLOW . "Game ending in $this->postgame...");
		if($this->postgame-- <= 0) {
			$this->end();
			$this->delete();
		}
	}

	public function updateUI(): void {
		foreach($this->getAll() as $player) {
			$this->getScoreboard()->sendData($player);
			$player->setScoreTag($player->getHealthString());
			$this->broadcastTip(
				TextFormat::WHITE . "CPS: " . TextFormat::YELLOW . $player->getClicksPerSecond() .
				TextFormat::WHITE . " | Ping: " . TextFormat::YELLOW . $player->getNetworkSession()->getPing()
			);
		}
	}

	public function broadcastTip(string $message): void {
		foreach($this->getAll() as $player) {
			$player->sendTip($message);
		}
	}

	public function broadcastPopup(string $message): void {
		foreach($this->getAll() as $player) {
			$player->sendPopup($message);
		}
	}

	public function broadcastMessage(TranslationContainer|string $message, bool $addPrefix = false): void {
		if($addPrefix && is_string($message)) $message = TextFormat::RED . "Game" . TextFormat::WHITE . " » $message";
		foreach($this->getAll() as $player) {
			$player->sendMessage($message);
		}
	}

	public function broadcastTitle(string $title, string $subtitle = ""): void {
		foreach($this->getAll() as $player) {
			$player->sendTitle($title, $subtitle, 0, -1, 0);
		}
	}

	public function broadcastActionBar(string $message): void {
		foreach($this->getAll() as $player) {
			$player->sendActionBarMessage($message);
		}
	}

	public function notify(string $message, string $color = TextFormat::WHITE): void {
		foreach($this->getAll() as $player) {
			$player->notify($message, $color);
		}
	}

	public function summonLightning(MeetupPlayer $player): void {
		$location = $player->getLocation();
		$vector = $location->asVector3();

		$actorPkt = new AddActorPacket;
		$actorPkt->type = EntityIds::LIGHTNING_BOLT;
		$actorPkt->entityRuntimeId = Entity::nextRuntimeId();
		$actorPkt->position = $vector;

		$soundPkt = new PlaySoundPacket;
		$soundPkt->soundName = "ambient.weather.thunder";
		$soundPkt->x = $vector->getX();
		$soundPkt->y = $vector->getY();
		$soundPkt->z = $vector->getZ();
		$soundPkt->volume = 1;
		$soundPkt->pitch = 1;

		$player->getServer()->broadcastPackets($player->getWorld()->getPlayers(), [$actorPkt, $soundPkt]);
	}

	public function createPole(MeetupPlayer $player): void {
		$vector = $player->getPosition()->asVector3();
		while ($this->getBorder()->isPassable($this->getWorld()->getBlock($new = $vector->down())) && $vector->y > 1) {
			$vector = $new;
		}
		$this->getWorld()->setBlock($vector, VanillaBlocks::NETHER_BRICK_FENCE());
		$this->getWorld()->setBlock(
			$vector->up(),
			VanillaBlocks::MOB_HEAD()
				->setSkullType(SkullType::PLAYER())
				->setFacing(Facing::UP) // ensure that the skull is ground based, rather than wall based
				->setRotation((floor($player->getLocation()->getYaw() * 16 / 360) + 8) & 0x0f) // map the yaw to [0 - 15] & add 8 to properly orient the head
		);
	}

	public function delete(): void {
		$this->heartbeat->getHandler()?->cancel();
		$this->scoreboardHeartbeat->getHandler()?->cancel();
		$this->listener->unregister();
		$this->world->getServer()->getWorldManager()->unloadWorld($this->world);
		$this->border->handleWorld();
		$this->getPlugin()->getGameManager()->remove($this);
		foreach($this as $key => $value) {
			unset($this->$key);
		}
	}
}
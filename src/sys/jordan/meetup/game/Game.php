<?php

declare(strict_types=1);

namespace sys\jordan\meetup\game;

use JetBrains\PhpStorm\Pure;
use pocketmine\block\utils\SkullType;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\lang\TranslationContainer;
use pocketmine\math\Facing;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;
use pocketmine\world\sound\Sound;
use pocketmine\world\World;
use sys\jordan\core\utils\TickEnum;
use sys\jordan\meetup\border\Border;
use sys\jordan\meetup\border\BorderValues;
use sys\jordan\meetup\eliminations\EliminationManager;
use sys\jordan\meetup\kit\Kit;
use sys\jordan\meetup\kit\KitManager;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\player\PlayerManager;
use sys\jordan\meetup\scenario\Scenario;
use sys\jordan\meetup\scenario\ScenarioManager;
use sys\jordan\meetup\spectator\SpectatorManager;
use sys\jordan\meetup\utils\MeetupBaseTrait;
use sys\jordan\meetup\vote\VoteManager;

class Game {
	use MeetupBaseTrait;

	public const PREFIX = TextFormat::RED . "Game" . TextFormat::WHITE . " » ";
	/** @var int */
	public const MAX_PLAYER_COUNT = 50;

	protected int $voting = 45;
	protected int $countdown = 30;
	protected int $time = 0;
	protected int $postgame = 15;

	protected Border $border;

	protected PlayerManager $playerManager;
	protected SpectatorManager $spectatorManager;

	protected EliminationManager $eliminationManager;
	protected KitManager $kitManager;

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
	public function __construct(MeetupBase $plugin, protected int $id, protected World $world, Kit $kit) {
		$this->setPlugin($plugin);
		$this->logger = new GameLogger($this);

		$this->setState(GameState::WAITING());
		$this->border = new Border($this, $world, BorderValues::$INFO);

		$world->setTime(World::TIME_FULL);
		$world->stopTime();

		$this->heartbeat = new ClosureTask(function (): void { $this->update(); });
		$this->scoreboardHeartbeat = new ClosureTask(function (): void { $this->updateUI(); });

		$this->listener = new GameListener($plugin, $this);
		$this->listener->register();

		$this->scoreboard = new GameScoreboard($this);

		$this->playerManager = new PlayerManager($this);
		$this->spectatorManager = new SpectatorManager($this);

		$this->eliminationManager = new EliminationManager($this);
		$this->kitManager = new KitManager($this, $kit);

		$this->scenarioManager = new ScenarioManager($this);
		$this->voteManager = new VoteManager($this);

		$plugin->getScheduler()->scheduleRepeatingTask($this->heartbeat, TickEnum::SECOND);
		$plugin->getScheduler()->scheduleRepeatingTask($this->scoreboardHeartbeat, TickEnum::SECOND);
	}

	public function getId(): int {
		return $this->id;
	}

	public function getBorder(): Border {
		return $this->border;
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
	#[Pure]
	public function getAll(): array {
		return ($this->playerManager->getPlayers() + $this->spectatorManager->getSpectators());
	}

	public function getEliminationManager(): EliminationManager {
		return $this->eliminationManager;
	}

	public function getKitManager(): KitManager {
		return $this->kitManager;
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

	public function start(): void {
		$this->notify(TextFormat::GREEN . "The player threshold has been met!", TextFormat::GREEN);
		$this->setState(GameState::VOTING());
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
		$this->getPlayerManager()->play();
		$this->broadcastMessage(TextFormat::GREEN . "The meetup has now started! Good luck!", true);
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
		$this->broadcastActionBar(TextFormat::YELLOW . "Voting will end in $this->voting...");
		if($this->voting-- <= 0) {
			$scenarios = $this->getVoteManager()->check();
			foreach($scenarios as $scenario) {
				$this->getScenarioManager()->add($scenario);
			}
			if(count($scenarios) > 0) {
				$message = TextFormat::GREEN . "The scenarios for this game are: [" . implode(", ",  array_map(fn(Scenario $scenario): string => TextFormat::YELLOW . $scenario->getName() . TextFormat::GREEN, $scenarios)) . TextFormat::GREEN . "]!";
			} else {
				$message = TextFormat::GREEN . "There are no scenarios enabled for this game!";
			}
			$this->broadcastMessage($message, true);
			$this->setState(GameState::COUNTDOWN());
			$this->getKitManager()->giveAll();
		}
	}

	public function handleCountdown(): void {
		$this->countdown--;
		$this->broadcastActionBar(TextFormat::YELLOW . "The game will commence in $this->countdown...");
		if($this->countdown <= 5) {
			if($this->countdown === 0) {
				$this->play();
				$sound = new NoteSound(NoteInstrument::PIANO(), 127);
			} else {
				$sound = new ClickSound(3);
			}
			$this->broadcastSound($sound);
		}

	}

	public function handlePlaying(): void {
		$this->time++;
		$this->getPlayerManager()->check();
		$this->getBorder()->update();
	}

	public function handlePostgame(): void {
		$this->broadcastActionBar(TextFormat::YELLOW . "Game ending in $this->postgame...");
		if($this->postgame-- <= 0) {
			$this->end();
		}
	}

	public function updateUI(): void {
		foreach($this->getAll() as $player) {
			$this->getScoreboard()->sendData($player);
			$player->setScoreTag($player->getHealthString());
//			$this->broadcastTip(
//				TextFormat::WHITE . "CPS: " . TextFormat::YELLOW . $player->getClicksPerSecond() .
//				TextFormat::WHITE . " | Ping: " . TextFormat::YELLOW . $player->getNetworkSession()->getPing()
//			);
		}
	}

	public function chat(PlayerChatEvent $event, bool $isSpectator = false): void {
		$recipients = $isSpectator && !$this->getState()->equals(GameState::POSTGAME()) ? $this->getSpectatorManager()->getSpectators() : $this->getAll();
		if($isSpectator) {
			$event->setMessage(TextFormat::DARK_GRAY . "[Spectator] {$event->getMessage()}");
		}
		$event->setRecipients($recipients);
		$this->getLogger()->info($event->getMessage());
	}

	public function broadcastTip(string $message): void {
		foreach($this->getAll() as $player) {
			$player->sendTip($message);
		}
	}

	public function broadcastMessage(TranslationContainer|string $message, bool $addPrefix = false): void {
		if($addPrefix && is_string($message)) $message = self::PREFIX . $message;
		foreach($this->getAll() as $player) {
			$player->sendMessage($message);
		}
		$this->getLogger()->info("[Message Broadcast] " . ($message instanceof TranslationContainer ? $this->plugin->getServer()->getLanguage()->translate($message) : $message));
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
		$this->getLogger()->info("[Game Notification] $message");
	}

	public function broadcastSound(Sound $sound): void {
		foreach($this->getAll() as $player) {
			$pkt = $sound->encode($player->getLocation());
			if(count($pkt) > 0) {
				foreach($pkt as $soundPkt) {
					$player->getNetworkSession()->sendDataPacket($soundPkt);
				}
			}
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

	public function end(): void {
		$this->getPlayerManager()->end();
		$this->getSpectatorManager()->end();
		$this->getScenarioManager()->end();

		$this->getEliminationManager()->end();
		$this->getKitManager()->end();

		$this->getVoteManager()->end();
		$this->delete();
	}

	public function delete(): void {
		$this->getLogger()->info(TextFormat::YELLOW . "Cancelling tasks...");
		$this->heartbeat->getHandler()?->cancel();
		$this->scoreboardHeartbeat->getHandler()?->cancel();
		$this->getLogger()->info(TextFormat::YELLOW . "Unregistering listener...");
		$this->listener->unregister();
		$this->getLogger()->info(TextFormat::YELLOW . "Handling world...");
		$this->world->getServer()->getWorldManager()->unloadWorld($this->world);
		$this->border->end();
		$this->getLogger()->info(TextFormat::YELLOW . "Removing from game manager...");
		$this->getPlugin()->getGameManager()->remove($this);
		// clean up
		foreach($this as $key => $value) {
			unset($this->$key);
		}
	}

	#[Pure]
	public function __toString(): string {
		return "Game(id=#$this->id,world=[\"{$this->world?->getDisplayName()}\"/\"{$this->world?->getFolderName()}\"])";
	}
}
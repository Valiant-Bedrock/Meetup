<?php


namespace sys\jordan\meetup;

use JetBrains\PhpStorm\Pure;
use pocketmine\block\BlockFactory;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use sys\jordan\core\command\OverloadPatcher;
use sys\jordan\core\CoreBase;
use sys\jordan\core\utils\Scoreboard;
use sys\jordan\core\utils\TickEnum;
use sys\jordan\meetup\border\BorderValues;
use sys\jordan\meetup\command\ForceStartCommand;
use sys\jordan\meetup\command\KillTopCommand;
use sys\jordan\meetup\command\LobbyCommand;
use sys\jordan\meetup\command\SpectateCommand;
use sys\jordan\meetup\game\GameManager;
use sys\jordan\meetup\kit\KitFactory;
use sys\jordan\meetup\scenario\ScenarioFactory;
use sys\jordan\meetup\utils\GoldenHead;
use sys\jordan\meetup\utils\MeetupPermissions;
use sys\jordan\meetup\utils\MeetupUtilities;
use sys\jordan\meetup\world\WorldManager;

class MeetupBase extends PluginBase {

	/** @var string */
	public const PREFIX = TextFormat::RED . "Valiant" . TextFormat::RESET;
	/** @var string */
	public const GAMEMODE = "Meetup";


	private static MeetupBase $instance;
	protected MeetupHotbarMenu $menu;

	protected KitFactory $kitFactory;
	protected ScenarioFactory $scenarioFactory;

	protected GameManager $gameManager;
	protected WorldManager $worldManager;

	protected ClosureTask $scoreboardUpdateTask;

	public function onLoad(): void {
		self::$instance = $this;
		$this->menu = new MeetupHotbarMenu();
		MeetupPermissions::register();
		$this->registerCommands();
		$this->registerRecipes();
		$this->registerConfiguration();
		$this->scoreboardUpdateTask = new ClosureTask(function (): void {
			foreach($this->getLobbyPlayers() as $player) {
				$player->getScoreboard()->setLineArray($this->getScoreboardData($player));
			}
		});
	}

	public function onEnable(): void {
		$this->registerManagers();
		$this->registerLobby();
		(new MeetupListener($this))->register();
		$this->getScheduler()->scheduleRepeatingTask($this->scoreboardUpdateTask, TickEnum::SECOND * 5);
		$this->getLogger()->info(TextFormat::GREEN . "{$this->getDescription()->getFullName()} has been enabled!");
	}

	public function onDisable(): void {
		/** @var MeetupPlayer $player */
		foreach($this->getServer()->getOnlinePlayers() as $player) {
			// we'll implement something into the core later to fix this, but this works for now
			$this->redirect($player, "valiantnetwork.xyz");
		}
		$this->getLogger()->info(TextFormat::RED . "{$this->getDescription()->getFullName()} has been disabled!");
	}

	public function registerCommands(): void {
		$this->getServer()->getCommandMap()->registerAll("meetup", [
			new ForceStartCommand($this),
			new KillTopCommand($this),
			new LobbyCommand($this),
			new SpectateCommand($this)
		]);
		OverloadPatcher::load($this);
	}

	public function registerConfiguration(): void {
		$this->saveDefaultConfig();
		BorderValues::load($this->getConfig());
	}

	public function registerLobby(): void {
		$world = $this->getServer()->getWorldManager()->getDefaultWorld();
		/** Clear default world of randomly ticked blocks */
		foreach($world->getRandomTickedBlocks() as $fullId => $boolean) {
			$world->removeRandomTickedBlock(BlockFactory::getInstance()->fromFullBlock($fullId));
		}
		$world->setTime(World::TIME_MIDNIGHT);
		$world->stopTime();
	}

	public function registerManagers(): void {
		$this->kitFactory = new KitFactory($this);
		$this->scenarioFactory = new ScenarioFactory();
		$this->worldManager = new WorldManager($this);
		/** initiate GameManager last as it relies on KitFactory and WorldManager */
		$this->gameManager = new GameManager($this);
	}

	public function registerRecipes(): void {
		ItemFactory::getInstance()->register(new GoldenHead(), true);
		$this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
			["ggg", "ghg", "ggg"],
			["g" => VanillaItems::GOLD_INGOT(), "h" => VanillaItems::PLAYER_HEAD()],
			[MeetupUtilities::GOLDEN_HEAD()->setCustomName(TextFormat::GOLD . "Golden Head")]
		));
	}

	public static function getInstance(): self {
		return self::$instance;
	}

	public function getMenu(): MeetupHotbarMenu {
		return $this->menu;
	}

	public function getGameManager(): GameManager {
		return $this->gameManager;
	}

	public function getWorldManager(): WorldManager {
		return $this->worldManager;
	}

	/**
	 * @return MeetupPlayer[]
	 */
	public function getLobbyPlayers(): array {
		return array_filter($this->getServer()->getOnlinePlayers(), fn(MeetupPlayer $current): bool => !$current->inGame());
	}

	public function setupLobbyPlayer(MeetupPlayer $player): void {
		$player->setGame();
		$player->setGamemode(GameMode::SURVIVAL());
		$player->setNameTag($player->getName() . TextFormat::YELLOW . " [{$player->getOS()->getDisplayName()} / {$player->getInputMode()->getDisplayName()}]");
		$player->setScoreTag("");
		$player->getEffects()->clear();
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->feed();
		$player->fullHeal();
		$player->setImmobile(false);
		$player->getHungerManager()->setEnabled(false);
		$player->setRegeneration(true);
		$player->setShowCoordinates(true);
		$player->teleport($this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
		$this->getMenu()->give($player);
		$this->sendScoreboard($player);
	}

	public function sendScoreboard(MeetupPlayer $player): void {
		$player->getScoreboard()->send(self::PREFIX . TextFormat::WHITE . " - " . CoreBase::SECONDARY_COLOR . self::GAMEMODE, Scoreboard::SLOT_SIDEBAR, Scoreboard::SORT_ASCENDING);
		$player->getScoreboard()->setLineArray($this->getScoreboardData($player));
	}

	/**
	 * @return string[]
	 */
	#[Pure]
	public function getScoreboardData(MeetupPlayer $player): array {
		$padding = str_repeat(" ", 3);
		return [
			($line = str_repeat("-", 18)),
			TextFormat::WHITE . "Name: " . TextFormat::YELLOW . $player->getName() . $padding,
			TextFormat::WHITE . "Rating: " . "{$player->getRating()->getColor()}{$player->getRating()->getRank()}",
			"",
			TextFormat::WHITE . "Playing: " . TextFormat::YELLOW . $this->getGameManager()->getPlaying() . $padding,
			TextFormat::WHITE . "Online: " . TextFormat::YELLOW . count($this->getServer()->getOnlinePlayers()) . $padding,
			$line
		];
	}

	public function redirect(MeetupPlayer $player, string $address, int $port = 19132): void {
		$player->transfer($address, $port, "Server closed. Transferring to $address:$port");
	}

}
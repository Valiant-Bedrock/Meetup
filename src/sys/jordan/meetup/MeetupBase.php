<?php


namespace sys\jordan\meetup;

use pocketmine\block\BlockFactory;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use sys\jordan\meetup\command\ForceStartCommand;
use sys\jordan\meetup\command\KillTopCommand;
use sys\jordan\meetup\command\SpectateCommand;
use sys\jordan\meetup\game\GameManager;
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

	protected ScenarioFactory $scenarioFactory;

	protected GameManager $gameManager;
	protected WorldManager $worldManager;

	public function onLoad(): void {
		self::$instance = $this;
		MeetupPermissions::register();
		$this->registerCommands();
		$this->registerManagers();
		$this->registerRecipes();
	}

	public function onEnable(): void {
		$this->registerLobby();
		(new MeetupListener($this))->register();
		$this->getLogger()->info(TextFormat::GREEN . "{$this->getDescription()->getFullName()} has been enabled!");
	}

	public function onDisable(): void {
		$this->getLogger()->info(TextFormat::RED . "{$this->getDescription()->getFullName()} has been disabled!");
	}

	public function registerCommands(): void {
		$this->getServer()->getCommandMap()->registerAll("meetup", [
			new ForceStartCommand($this),
			new KillTopCommand($this),
			new SpectateCommand($this)
		]);
	}

	public function registerLobby(): void {
		$world = $this->getServer()->getWorldManager()->getDefaultWorld();
		/** Clear default world of randomly ticked blocks */
		foreach($world->getRandomTickedBlocks() as $fullId => $boolean) {
			$world->removeRandomTickedBlock(BlockFactory::getInstance()->fromFullBlock($fullId));
		}

	}

	public function registerManagers(): void {
		$this->scenarioFactory = new ScenarioFactory();
		$this->gameManager = new GameManager($this);
		$this->worldManager = new WorldManager($this);
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

	/**
	 * @return GameManager
	 */
	public function getGameManager(): GameManager {
		return $this->gameManager;
	}

	/**
	 * @return WorldManager
	 */
	public function getWorldManager(): WorldManager {
		return $this->worldManager;
	}

}
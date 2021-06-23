<?php


namespace sys\jordan\meetup\scenario\defaults;


use JetBrains\PhpStorm\Pure;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\VanillaBlocks;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat;
use sys\jordan\core\utils\TickEnum;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\player\MeetupPlayerDeathEvent;
use sys\jordan\meetup\scenario\defaults\module\timebomb\TimebombTask;
use sys\jordan\meetup\scenario\Scenario;
use sys\jordan\meetup\utils\MeetupUtilities;

class Timebomb extends Scenario {

	/** @var TimebombTask[] */
	protected array $tasks = [];

	public const COUNTDOWN_LENGTH = 30;
	public const EXPLOSION_SIZE = 5;

	#[Pure]
	public function __construct() {
		parent::__construct("Timebomb", "Upon death, a player's items will be dropped into a chest along with a golden head");
	}

	public function onAdd(Game $game): void {

	}

	public function onRemove(Game $game): void {
		foreach($this->tasks as $uuid => $task) {
			$this->removeTask($uuid);
		}
	}

	public function handleMeetupDeath(MeetupPlayerDeathEvent $event): void {
		$event->setCanDropItems(false);
		$this->placeChests($event->getPlayer());
		$this->addTask($event->getPlayer());
	}


	public function addTask(MeetupPlayer $player): void {
		$task = new TimebombTask($player, $this);
		$this->tasks[$player->getUniqueId()->toString()] = $task;
		$task->schedule(TickEnum::SECOND);
	}

	public function removeTask(string $uuid): void {
		if(isset($this->tasks[$uuid])) {
			$task = $this->tasks[$uuid];
			if($task->getHandler() instanceof TaskHandler) {
				$task->cancel();
			}
			unset($this->tasks[$uuid]);
		}
	}

	public function placeChests(MeetupPlayer $player): void {
		$contents = [...$player->getArmorInventory()->getContents(), ...$player->getInventory()->getContents(), MeetupUtilities::GOLDEN_HEAD()];

		$world = $player->getWorld();
		$position = $player->getPosition();
		$otherPosition = $position->east();

		$world->setBlock($position, VanillaBlocks::CHEST());
		$world->setBlock($otherPosition, VanillaBlocks::CHEST());
		$chest = $world->getTile($position);
		$otherChest = $world->getTile($otherPosition);
		if($chest instanceof TileChest && $otherChest instanceof TileChest) {
			$chest->pairWith($otherChest);
			$chest->getInventory()->setContents($contents);
			$chest->setName(TextFormat::RESET . TextFormat::GREEN . $player->getName() . "'s " . TextFormat::YELLOW . "Corpse");
		}

	}

}
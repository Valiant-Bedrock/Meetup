<?php


namespace sys\jordan\meetup;


use JetBrains\PhpStorm\Pure;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\SkullType;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\player\PlayerInfo;
use pocketmine\Server;
use sys\jordan\core\CorePlayer;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\game\task\NoCleanTask;
use sys\jordan\meetup\utils\GameTrait;
use sys\jordan\meetup\utils\ScoreboardExtraData;

class MeetupPlayer extends CorePlayer {

	use GameTrait;


	protected ?NoCleanTask $noCleanTask = null;
	protected ScoreboardExtraData $scoreboardExtraData;

	public function __construct(Server $server, NetworkSession $session, PlayerInfo $playerInfo, bool $authenticated, Location $spawnLocation, ?CompoundTag $namedtag) {
		parent::__construct($server, $session, $playerInfo, $authenticated, $spawnLocation, $namedtag);
		$this->scoreboardExtraData = new ScoreboardExtraData();
	}

	#[Pure]
	public function inGame(): bool {
		return $this->game instanceof Game;
	}

	public function hasNoClean(): bool {
		return $this->noCleanTask instanceof NoCleanTask;
	}

	public function removeNoClean(): void {
		if($this->noCleanTask instanceof NoCleanTask) {
			$this->noCleanTask->cancel();
		}
		$this->noCleanTask = null;
	}

	public function addNoClean(): void {
		$this->removeNoClean();
		$this->noCleanTask = new NoCleanTask($this);
	}

	public function getScoreboardExtraData(): ScoreboardExtraData {
		return $this->scoreboardExtraData;
	}

	public function processMostRecentMovements(): void {
		if($this->inGame()) {
			$border = $this->getGame()->getBorder();
			if(!$border->inside($this)) {
				$border->teleport($this);
			}
		}
		parent::processMostRecentMovements();
	}

}
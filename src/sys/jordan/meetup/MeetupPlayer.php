<?php


namespace sys\jordan\meetup;


use JetBrains\PhpStorm\Pure;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\player\PlayerInfo;
use pocketmine\Server;
use sys\jordan\core\CorePlayer;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\rating\SkillRating;
use sys\jordan\meetup\utils\GameTrait;
use sys\jordan\meetup\utils\ScoreboardExtraData;

class MeetupPlayer extends CorePlayer {

	use GameTrait;

	protected ScoreboardExtraData $scoreboardExtraData;
	protected SkillRating $rating;

	public function __construct(Server $server, NetworkSession $session, PlayerInfo $playerInfo, bool $authenticated, Location $spawnLocation, ?CompoundTag $namedtag) {
		parent::__construct($server, $session, $playerInfo, $authenticated, $spawnLocation, $namedtag);
		$this->scoreboardExtraData = new ScoreboardExtraData;
		$this->rating = new SkillRating; //TODO: Load from data
	}

	#[Pure]
	public function inGame(): bool {
		return $this->game instanceof Game;
	}

	public function getScoreboardExtraData(): ScoreboardExtraData {
		return $this->scoreboardExtraData;
	}

	public function getRating(): SkillRating {
		return $this->rating;
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
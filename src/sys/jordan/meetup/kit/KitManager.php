<?php


namespace sys\jordan\meetup\kit;


use pocketmine\utils\TextFormat;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\GameTrait;

class KitManager {
	use GameTrait;

	/** @var KitPullResult[] */
	protected array $cachedResults = [];

	public function __construct(Game $game, protected Kit $kit) {
		$this->setGame($game);
	}

	public function getKit(): Kit {
		return $this->kit;
	}

	/**
	 * TODO: Add re-roll functionality
	 */
	public function give(MeetupPlayer $player): void {
		if(!isset($this->cachedResults[$player->getUniqueId()->toString()])) {
			$kit = $this->getKit()->pull();
			$this->cachedResults[$player->getUniqueId()->toString()] = $kit;
		} else {
			$kit = $this->cachedResults[$player->getUniqueId()->toString()];
		}
		$player->getInventory()->setContents($kit->getItemContents());
		$player->getArmorInventory()->setContents($kit->getArmorContents());
	}

	public function giveAll(): void {
		foreach($this->game->getPlayerManager()->getPlayers() as $player) {
			$this->give($player);
		}
	}

	public function clear(): void {
		$this->cachedResults = [];
		unset($this->kit);
	}

	public function end(): void {
		$this->game->getLogger()->info(TextFormat::YELLOW . "Cleaning up kit manager...");
		$this->clear();
	}

}
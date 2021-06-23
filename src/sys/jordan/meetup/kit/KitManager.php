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
	/** (uuid => bool) */
	protected array $usedRerolls = [];

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
		$kit = ($this->cachedResults[$player->getUniqueId()->toString()] ??= $this->getKit()->pull());
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
		$this->usedRerolls = [];
		unset($this->kit);
	}

	public function hasReroll(MeetupPlayer $player): bool {
		return !isset($this->usedRerolls[$player->getUniqueId()->toString()]);
	}

	public function useReroll(MeetupPlayer $player): void {
		$this->usedRerolls[$player->getUniqueId()->toString()] = true;
	}

	public function end(): void {
		$this->game->getLogger()->info(TextFormat::YELLOW . "Cleaning up kit manager...");
		$this->clear();
	}

}
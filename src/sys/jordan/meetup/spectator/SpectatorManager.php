<?php


namespace sys\jordan\meetup\spectator;


use pocketmine\utils\TextFormat;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\GameTrait;

class SpectatorManager {

	use GameTrait;

	/** @var MeetupPlayer[] */
	private array $spectators = [];

	/**
	 * SpectatorManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	/**
	 * @return MeetupPlayer[]
	 */
	public function getSpectators(): array {
		return $this->spectators;
	}

	/**
	 * @param MeetupPlayer $player
	 * @param bool $setup
	 */
	public function add(MeetupPlayer $player, bool $setup = true): void {
		if(!$this->isSpectator($player)) {
			$this->spectators[$player->getUniqueId()->toString()] = $player;
			if($setup) $this->setup($player);
		}
	}

	/**
	 * @param MeetupPlayer $player
	 */
	public function setup(MeetupPlayer $player): void {

	}

	/**
	 * @param MeetupPlayer $player
	 */
	public function remove(MeetupPlayer $player): void {
		if($this->isSpectator($player)) {
			unset($this->spectators[$player->getUniqueId()->toString()]);
		}
	}

	/**
	 * @param MeetupPlayer $player
	 * @return bool
	 */
	public function isSpectator(MeetupPlayer $player): bool {
		return isset($this->spectators[$player->getUniqueId()->toString()]);
	}

	public function clear(): void {
		$spawn = MeetupBase::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn();
		foreach($this->getSpectators() as $key => $spectator) {
			if($spectator->isOnline()) {
				$spectator->teleport($spawn);
			}
			unset($this->spectators[$key]);
		}
	}

	public function end(): void {
		$this->getGame()->getLogger()->info(TextFormat::YELLOW . "Clearing spectators...");
		$this->clear();
	}

}
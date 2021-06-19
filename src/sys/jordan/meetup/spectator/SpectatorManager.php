<?php


namespace sys\jordan\meetup\spectator;


use pocketmine\player\GameMode;
use pocketmine\utils\TextFormat;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\GameTrait;

class SpectatorManager {
	use GameTrait;

	protected SpectatorEventHandler $handler;

	/** @var MeetupPlayer[] */
	private array $spectators = [];

	/**
	 * SpectatorManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
		$this->handler = new SpectatorEventHandler($game);
	}

	/**
	 * @return MeetupPlayer[]
	 */
	public function getSpectators(): array {
		return $this->spectators;
	}

	public function getHandler(): SpectatorEventHandler {
		return $this->handler;
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
		$player->setGamemode(GameMode::SPECTATOR());
		$player->getScoreboard()->clearLines();
		$player->setGame($this->game);
	}

	/**
	 * @param MeetupPlayer $player
	 */
	public function remove(MeetupPlayer $player): void {
		if($this->isSpectator($player)) {
			unset($this->spectators[$player->getUniqueId()->toString()]);
		}
	}

	public function join(MeetupPlayer $player): void {
		$this->add($player);
	}

	public function quit(MeetupPlayer $player): void {
		$this->remove($player);
		$this->getGame()->getPlugin()->setupLobbyPlayer($player);
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
				$spectator->setGame();
				$this->getGame()->getPlugin()->setupLobbyPlayer($spectator);
			}
			unset($this->spectators[$key]);
		}
	}

	public function end(): void {
		$this->game->getLogger()->info(TextFormat::YELLOW . "Cleaning up spectator manager...");
		$this->clear();
	}

}
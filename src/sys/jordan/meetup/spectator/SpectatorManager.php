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
	 */
	public function add(MeetupPlayer $player, bool $teleport = true): void {
		if(!$this->isSpectator($player)) {
			$this->spectators[$player->getUniqueId()->toString()] = $player;
			$this->setup($player, $teleport);
		}
	}

	/**
	 * @param MeetupPlayer $player
	 * @param bool $teleport
	 */
	public function setup(MeetupPlayer $player, bool $teleport = true): void {
		$player->getEffects()->clear();
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();
		$player->setGamemode(GameMode::SPECTATOR());
		$player->getScoreboard()->clearLines();
		if(!$player->inGame() || $player->getGame() !== $this->game) {
			$player->setGame($this->game);
		}
		if($teleport) {
			$player->teleport($this->game->getWorld()->getSpawnLocation());
		}
	}

	/**
	 * @param MeetupPlayer $player
	 */
	public function remove(MeetupPlayer $player): void {
		if($this->isSpectator($player)) {
			unset($this->spectators[$player->getUniqueId()->toString()]);
		}
	}

	public function join(MeetupPlayer $player, bool $teleport = true): void {
		$this->add($player, $teleport);
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
<?php


namespace sys\jordan\meetup\player;


use pocketmine\player\GameMode;
use pocketmine\utils\TextFormat;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\game\GameState;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\GameTrait;

use function array_key_first, count;

class PlayerManager {

	use GameTrait;

	/** @var MeetupPlayer[] */
	private array $players = [];

	/**
	 * PlayerManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	/**
	 * @return MeetupPlayer[]
	 */
	public function getPlayers(): array {
		return $this->players;
	}

	public function getCount(): int {
		return count($this->players);
	}

	public function add(MeetupPlayer $player): void {
		if($this->isPlayer($player)) {
			// this should never happen
			return;
		}
		$this->players[$player->getUniqueId()->toString()] = $player;
		$this->setup($player);
	}

	public function remove(MeetupPlayer $player): void {
		if($this->isPlayer($player)) {
			unset($this->players[$player->getUniqueId()->toString()]);
		} else {
			$this->getGame()->getLogger()->error(TextFormat::RED . "PlayerManager::remove() called on a player not in the game!");
		}
	}

	public function isPlayer(MeetupPlayer $player): bool {
		return isset($this->players[$player->getUniqueId()->toString()]);
	}

	/**
	 * Checks the player count & sets to postgame if <= 1
	 */
	public function check(): void {
		if($count = count($this->players) <= 1) {
			if($count > 0) {
				$player = $this->players[array_key_first($this->getPlayers())];
				$this->game->broadcastTitle(TextFormat::GREEN . $player->getDisplayName() . " won the game!");
			} else {
				$this->game->broadcastTitle(TextFormat::YELLOW . "The game is a draw!");
			}
			$this->game->setState(GameState::POSTGAME());
		}
	}

	public function setup(MeetupPlayer $player): void {
		$this->scatter($player);
		$player->setGamemode(GameMode::SURVIVAL());
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();
		$player->getEffects()->clear();
		$player->fullHeal();
		$player->feed();
		$player->sendMessage(TextFormat::YELLOW . "You have been randomly scattered across the map!");
		$player->setImmobile();
		if($this->game->getState() === GameState::COUNTDOWN()) {

		}
	}

	public function clear(): void {
		$spawn = MeetupBase::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn();
		foreach($this->players as $key => $player) {
			if($player->isOnline()) {
				$player->teleport($spawn);
			}
			unset($this->players[$key]);
		}
	}

	public function end(): void {
		$this->game->getLogger()->info(TextFormat::YELLOW . "Clearing players...");
		$this->clear();
	}

	public function scatter(MeetupPlayer $player): void {
		$this->game->getBorder()->randomTeleport($player);
	}

	public function death(MeetupPlayer $player): void {

	}

}
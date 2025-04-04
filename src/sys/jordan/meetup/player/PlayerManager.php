<?php


namespace sys\jordan\meetup\player;


use JetBrains\PhpStorm\Pure;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\VanillaItems;
use pocketmine\lang\TranslationContainer;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\game\GameState;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\scenario\DefaultScenarios;
use sys\jordan\meetup\utils\GameTrait;

use sys\jordan\meetup\vote\form\VoteSelectForm;
use function array_key_first, count;

class PlayerManager {
	use GameTrait;

	/** If enabled, the game won't auto-end with 1 player */
	public const DEBUG = false;
	/** The amount of players needed to start the game */
	public const THRESHOLD = 10;

	protected PlayerEventHandler $handler;

	/** @var MeetupPlayer[] */
	private array $players = [];
	protected int $startingCount = -1;

	/**
	 * PlayerManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
		$this->handler = new PlayerEventHandler($game);
	}

	/**
	 * @return MeetupPlayer[]
	 */
	public function getPlayers(): array {
		return $this->players;
	}

	public function getHandler(): PlayerEventHandler {
		return $this->handler;
	}

	public function getCount(): int {
		return count($this->players);
	}

	#[Pure]
	public function getStartingCount(): int {
		return $this->startingCount <= 0 ? $this->getCount() : $this->startingCount;
	}

	#[Pure]
	public function canStart(): bool {
		return $this->getCount() >= self::THRESHOLD;
	}

	public function play(): void {
		$this->startingCount = count($this->players);
	}

	public function add(MeetupPlayer $player): void {
		if($this->isPlayer($player)) {
			// this should never happen
			return;
		}
		$this->players[$player->getUniqueId()->toString()] = $player;
		$this->setup($player);
	}

	public function join(MeetupPlayer $player): void {
		$this->add($player);
		$player->notify(TextFormat::GREEN . "You have successfully joined the game!", TextFormat::GREEN);
		$this->game->broadcastMessage(TextFormat::YELLOW . "{$player->getName()} has joined the game!", true);
	}

	public function quit(MeetupPlayer $player): void {
		if($this->game->hasStarted()) {
			$this->death($player);
		}
		$this->remove($player);
		$this->getGame()->getPlugin()->setupLobbyPlayer($player);
		$player->notify(TextFormat::YELLOW . "You have successfully left the game!", TextFormat::YELLOW);
		$this->game->broadcastMessage(TextFormat::YELLOW . "{$player->getName()} has left the game!", true);
		if(count($this->players) <= 0 && !$this->game->getState()->equals(GameState::WAITING())) {
			$this->game->end();
		}
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
		if($count = count($this->players) <= 1 && !self::DEBUG) {
			if($count > 0) {
				$player = $this->players[array_key_first($this->getPlayers())];
				$this->game->broadcastTitle(TextFormat::GREEN . "{$player->getName()} won the game!");
			} else {
				$this->game->broadcastTitle(TextFormat::YELLOW . "The game is a draw!");
			}
			$this->game->setState(GameState::POSTGAME());
		}
	}

	public function setup(MeetupPlayer $player): void {
		$this->scatter($player);
		$player->getScoreboard()->clearLines();
		$player->getHungerManager()->setEnabled(true);
		$player->setRegeneration(false);
		$player->setGamemode(GameMode::SURVIVAL());
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();
		$player->getEffects()->clear();
		$player->fullHeal();
		$player->feed();
		$player->setImmobile();
		$player->setGame($this->game);
		switch($this->game->getState()->id()) {
			case GameState::VOTING()->id():
				$this->getGame()->getVoteManager()->getMenu()->give($player);
				$player->sendForm(new VoteSelectForm($this->game->getVoteManager()));
				break;
			case GameState::COUNTDOWN()->id():
				$this->getGame()->getKitManager()->give($player);

		}
	}

	public function clear(): void {
		$spawn = MeetupBase::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn();
		foreach($this->players as $key => $player) {
			if($player instanceof MeetupPlayer) {
				$player->setGame();
				$player->teleport($spawn);
				$this->getGame()->getPlugin()->setupLobbyPlayer($player);
			}
			unset($this->players[$key]);
		}
	}

	public function end(): void {
		$this->game->getLogger()->info(TextFormat::YELLOW . "Cleaning up player manager...");
		$this->clear();
	}

	public function scatter(MeetupPlayer $player): void {
		$this->game->getBorder()->randomTeleport($player);
	}

	public function death(MeetupPlayer $player, ?EntityDamageEvent $event = null): void {
		$deathEvent = new MeetupPlayerDeathEvent($player, $event);
		$deathEvent->call();
		if(!$this->game->getScenarioManager()->exists(DefaultScenarios::TIMEBOMB())) {
			$this->game->createPole($player);
		}
		$position = $player->getPosition();

		if($deathEvent->canDropItems()) {
			foreach([...$player->getInventory()->getContents(), ...$player->getArmorInventory()->getContents(), VanillaItems::GOLD_INGOT()->setCount(8)] as $item){
				$this->getGame()->getWorld()->dropItem($position, $item);
			}
		}
		$this->getGame()->getWorld()->dropExperience($position, $player->getXpDropAmount());

		$this->game->summonLightning($player);

		if($player->isOnline()) {
			$this->getGame()->getPlayerManager()->remove($player);

			$this->getGame()->getSpectatorManager()->add($player, false);
			$player->sendTitle(TextFormat::RED . "You died!", TextFormat::YELLOW . "Use /lobby to leave this match and enter a new one!");
		}
		if($event instanceof EntityDamageByEntityEvent && ($damager = $event->getDamager()) instanceof MeetupPlayer) {
			$this->getGame()->getEliminationManager()->addElimination($damager);
		}
		$this->game->broadcastMessage($this->createDeathMessage($player, $event));
	}

	public function createDeathMessage(MeetupPlayer $player, ?EntityDamageEvent $event = null): TranslationContainer {
		$server = Server::getInstance();
		$deathMessage = PlayerDeathEvent::deriveMessage($player->getName(), $event);
		$parameters = [];
		foreach($deathMessage->getParameters() as $i => $name) {
			/** @var MeetupPlayer $currentPlayer */
			if(($currentPlayer = $server->getPlayerExact($name)) instanceof MeetupPlayer) {
				$playerEliminations = $this->game->getEliminationManager()->getEliminations($currentPlayer);
				$name = TextFormat::YELLOW . $name . TextFormat::WHITE . "[" . TextFormat::RED . $playerEliminations . TextFormat::WHITE . "]";
			}
			$parameters[$i] = $name;
		}
		return new TranslationContainer($deathMessage->getText(), $parameters);
	}

}
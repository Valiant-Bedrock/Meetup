<?php

declare(strict_types=1);

namespace sys\jordan\meetup\game;


use JetBrains\PhpStorm\Pure;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use sys\jordan\meetup\kit\Kit;
use sys\jordan\meetup\kit\KitFactory;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\utils\MeetupBaseTrait;
use sys\jordan\meetup\world\MeetupWorldData;

class GameManager {

	use MeetupBaseTrait;

	/** @var int */
	public const DEFAULT_GAME_COUNT = 5;

	/** @var Game[] */
	private array $games = [];

	/**
	 * GameManager constructor.
	 * @param MeetupBase $plugin
	 */
	public function __construct(MeetupBase $plugin) {
		$this->setPlugin($plugin);
		$this->setup();
	}

	public function setup(): void {
		for($i = 0; $i < self::DEFAULT_GAME_COUNT; $i++) {
			$this->setupRandom();
		}
	}

	/**
	 * @return Game[]
	 */
	public function getAll(): array {
		return $this->games;
	}

	#[Pure]
	public function getPlaying(): int {
		$count = 0;
		foreach($this->getAll() as $game) {
			$count += $game->getPlayerManager()->getCount();
		}
		return $count;
	}

	public function add(Game $game): void {
		$this->games[$game->getId()] = $game;
		$this->getPlugin()->getLogger()->info(TextFormat::YELLOW . "Game created: {$game->__toString()}");
	}

	public function remove(Game $game, bool $replace = true): void {
		if(isset($this->games[$game->getId()])) {
			unset($this->games[$game->getId()]);
			$this->getPlugin()->getLogger()->info(TextFormat::YELLOW . "Game removed: {$game->__toString()}");
			if($replace) {
				$this->setupRandom();
			}
		}
	}

	public function create(MeetupWorldData $data, Kit $kit): ?Game {
		$world = $this->getPlugin()->getWorldManager()->create($data);
		if($world instanceof World) {
			return new Game($this->getPlugin(), spl_object_id($world), $world, $kit);
		}
		return null;
	}

	public function setupRandom(): void {
		$data = $this->getPlugin()->getWorldManager()->getRandom();
		$kit = KitFactory::getInstance()->getRandom();
		if($data instanceof MeetupWorldData && $kit instanceof Kit) {
			if(($game = $this->create($data, $kit)) instanceof Game) {
				$this->add($game);
			} else {
				$this->getPlugin()->getLogger()->warning("Unable to create random game!");
			}
		}
	}

}
<?php

declare(strict_types=1);

namespace sys\jordan\meetup\game;


use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\utils\MeetupBaseTrait;

class GameManager {

	use MeetupBaseTrait;

	/** @var int */
	public const DEFAULT_GAME_COUNT = 5;
	/** @var string */
	public const TARGET_DIRECTORY = "temp";

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
	}

	/**
	 * @return Game[]
	 */
	public function getAll(): array {
		return $this->games;
	}

	public function add(Game $game): void {
		$this->games[$game->getId()] = $game;
	}

	public function remove(Game $game): void {
		if(isset($this->games[$game->getId()])) {
			unset($this->games[$game->getId()]);
		}
	}

	public function create(): void {

	}

}
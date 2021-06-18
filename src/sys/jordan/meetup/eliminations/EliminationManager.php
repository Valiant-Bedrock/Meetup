<?php

declare(strict_types=1);

namespace sys\jordan\meetup\eliminations;


use pocketmine\utils\TextFormat;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\GameTrait;

use function array_filter, count, arsort, reset, min, key, current, next;

class EliminationManager {

	use GameTrait;

	/** @var int[] */
	private array $eliminations = [];

	/**
	 * EliminationManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	public function addElimination(MeetupPlayer $player): void {
		if(!isset($this->eliminations[$player->getName()])) {
			$this->eliminations[$player->getName()] = 0;
		}
		$this->eliminations[$player->getName()] = $this->getEliminations($player) + 1;
	}

	public function setEliminations(MeetupPlayer $player, int $count = 0): void {
		$this->eliminations[$player->getName()] = $count;
	}

	public function getEliminations(MeetupPlayer $player): int {
		return $this->eliminations[$player->getName()] ??= 0;
	}

	/**
	 * @param int $count
	 * @return int[]
	 */
	public function getKillTop(int $count = 5): array {
		$filtered = array_filter($this->eliminations, static fn($kills): bool => $kills > 0);
		$output = [];
		if(count($filtered) > 0) {
			arsort($filtered);
			reset($filtered);
			for($i = 0; $i < min($count, count($filtered)); $i++) {
				$output[key($filtered)] = current($filtered);
				next($filtered);
			}
		}
		return $output;
	}

	public function clear(): void {
		$this->eliminations = [];
	}

	public function end(): void {
		$this->getGame()->getLogger()->info(TextFormat::YELLOW . "Clearing eliminations...");
		$this->clear();
	}

}
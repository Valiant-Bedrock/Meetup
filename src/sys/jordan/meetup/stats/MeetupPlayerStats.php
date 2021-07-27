<?php


namespace sys\jordan\meetup\stats;


use JetBrains\PhpStorm\Pure;

class MeetupPlayerStats {

	protected float $timePlayed = 0.0;


	protected float $damageDone = 0.0;
	protected float $damageDealt = 0.0;

	protected float $healthRestored = 0.0;

	protected int $wins = 0;
	protected int $losses = 0;

	protected int $kills = 0;
	protected int $deaths = 0;


	protected int $goldenApplesEaten = 0;
	protected int $goldenHeadsEaten = 0;


	#[Pure]
	public static function create(\stdClass $data): self {
		$stats = new MeetupPlayerStats;

		$stats->timePlayed = $data->timePlayed ?? 0.0;

		$stats->damageDone = $data->damageDone ?? 0.0;
		$stats->damageDealt = $data->damageDealt ?? 0.0;

		$stats->healthRestored = $data->healthRestored ?? 0.0;

		$stats->wins = $data->wins ?? 0;
		$stats->losses = $data->losses ?? 0;

		$stats->kills = $data->kills ?? 0;
		$stats->deaths = $data->deaths ?? 0;

		$stats->goldenApplesEaten = $data->goldenApplesEaten ?? 0;
		$stats->goldenHeadsEaten = $data->goldenHeadsEaten ?? 0;

		return $stats;
	}

	public function getDamageDone(): float {
		return $this->damageDone;
	}

	public function getDamageDealt(): float {
		return $this->damageDealt;
	}

	public function getWins(): int {
		return $this->wins;
	}

	public function getLosses(): int {
		return $this->losses;
	}

	public function getDeaths(): int {
		return $this->deaths;
	}

	public function getGoldenApplesEaten(): int {
		return $this->goldenApplesEaten;
	}

	public function getGoldenHeadsEaten(): int {
		return $this->goldenHeadsEaten;
	}
}
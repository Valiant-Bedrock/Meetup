<?php


namespace sys\jordan\meetup\rating;


use JetBrains\PhpStorm\Pure;
use pocketmine\utils\TextFormat;

class PlayerRating {


	public function __construct(protected int $value = -1) {}

	public function getValue(): int {
		return $this->value;
	}

	public function set(int $value): void {
		$this->value = $value;
	}

	public function add(int $addition): void {
		$this->value += $addition;
	}

	public function subtract(int $subtraction): void {
		$this->value -= $subtraction;
	}

	public function getRank(): string {
		return match (true) {
			$this->value > 0 && $this->value < 1500 => "Bronze",
			$this->value >= 1500 && $this->value < 2000 => "Silver",
			$this->value >= 2000 && $this->value < 2500 => "Gold",
			$this->value >= 2500 && $this->value < 3000 => "Platinum",
			$this->value >= 3000 && $this->value < 3500 => "Diamond",
			$this->value >= 3500 && $this->value < 4000 => "Master",
			$this->value >= 4000 && $this->value < 4500 => "Grandmaster",
			default => "Unranked",
		};
	}

	#[Pure]
	public function getColor(): string {
		return match ($this->getRank()) {
			"Bronze" => TextFormat::DARK_GREEN,
			"Silver" => TextFormat::GRAY,
			"Gold" => TextFormat::GOLD,
			"Platinum" => TextFormat::WHITE,
			"Diamond" => TextFormat::AQUA,
			"Master" => TextFormat::YELLOW,
			"Grandmaster" => TextFormat::RED,
			default => TextFormat::DARK_GRAY,
		};
	}

	public function getTier(): string {
		return ""; //TODO: Figure out tier calculations (I, II, III)
	}

}
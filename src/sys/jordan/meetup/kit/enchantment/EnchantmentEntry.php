<?php


namespace sys\jordan\meetup\kit\enchantment;


use JetBrains\PhpStorm\Pure;

class EnchantmentEntry {

	private float $accumulation = 0.0;

	#[Pure]
	public function __construct(protected int $value, protected float $weight) {}

	public function getValue(): int {
		return $this->value;
	}

	public function getWeight(): float {
		return $this->weight;
	}

	public function setAccumulation(float $accumulation): void {
		$this->accumulation = $accumulation;
	}

	public function getAccumulation(): float {
		return $this->accumulation;
	}
}
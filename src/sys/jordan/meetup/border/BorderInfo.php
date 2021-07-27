<?php


namespace sys\jordan\meetup\border;

use JetBrains\PhpStorm\Pure;

class BorderInfo {

	/**
	 * BorderInfo constructor.
	 * @param int $initialSize
	 * @param int[] $borders
	 * @param int $shrinkInterval (interval in seconds)
	 */
	public function __construct(private int $initialSize, private array $borders = [], protected int $shrinkInterval = 60 * 3) {}

	public function getInitialSize(): int {
		return $this->initialSize;
	}

	public function getBorders(): array {
		return $this->borders;
	}

	public function getShrinkInterval(): int {
		return $this->shrinkInterval;
	}

	public function getSize(int $index): int {
		return $this->borders[$index] ?? -1;
	}

	#[Pure]
	public static function parse(array $data): self {
		return new BorderInfo($data["initial-size"], $data["borders"], $data["shrink-interval"]);
	}

}
<?php


namespace sys\jordan\meetup\kit;


use pocketmine\item\Item;

class KitItem {

	protected bool $randomized = false;

	public function __construct(protected Item $item, protected int $min = -1, protected int $max = -1) {
		$this->randomized = $min > 0 && $max > 0;
	}

	public function getItem(): Item {
		return $this->item;
	}

	public function isRandomized(): bool {
		return $this->randomized;
	}

	public function getMin(): int {
		return $this->min;
	}

	public function getMax(): int {
		return $this->max;
	}

	public function getCount(): int {
		return $this->randomized ? max(1, mt_rand($this->getMin(), $this->getMax())) : $this->getItem()->getCount();
	}

	public function pull(): Item {
		$cloned = clone $this->item;
		return $cloned->setCount($this->getCount());
	}

}
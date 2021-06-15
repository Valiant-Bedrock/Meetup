<?php


namespace sys\jordan\meetup\kit;


use pocketmine\item\Item;

class KitItem {

	protected bool $randomized = false;

	public function __construct(protected Item $item, protected int $min = -1, protected int $max = -1) {
		$this->randomized = $min > 0 && $max > 0;
	}

	/**
	 * @return Item
	 */
	public function getItem(): Item {
		return $this->item;
	}

	/**
	 * @return bool
	 */
	public function isRandomized(): bool {
		return $this->randomized;
	}

	/**
	 * @return int
	 */
	public function getMin(): int {
		return $this->min;
	}

	/**
	 * @return int
	 */
	public function getMax(): int {
		return $this->max;
	}

	public function getCount(): int {
		return $this->randomized ? max(1, mt_rand($this->getMin(), $this->getMax())) : 1;
	}

	public function pull(): Item {
		$cloned = clone $this->item;
		return $cloned->setCount($this->getCount());
	}

}
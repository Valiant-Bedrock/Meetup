<?php


namespace sys\jordan\meetup\kit;


use pocketmine\item\Item;
use sys\jordan\meetup\kit\enchantment\KitEnchantment;

class KitItem {

	protected bool $randomized = false;

	/**
	 * KitItem constructor.
	 * @param Item $item
	 * @param int $min
	 * @param int $max
	 * @param KitEnchantment[] $enchantments
	 */
	public function __construct(protected Item $item, protected int $min = -1, protected int $max = -1, protected array $enchantments = []) {
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

	/**
	 * @param KitEnchantment[] $enchantments
	 */
	public function setEnchantments(array $enchantments): self {
		$this->enchantments = $enchantments;
		return $this;
	}

	public function pull(): Item {
		$cloned = clone $this->item;
		foreach($this->enchantments as $enchantment) {
			$cloned->addEnchantment($enchantment->pull());
		}
		return $cloned->setCount($this->getCount());
	}

}
<?php


namespace sys\jordan\meetup\kit\pool;


use pocketmine\item\Item;
use sys\jordan\meetup\kit\KitItem;

class ItemEntry {

	private float $accumulation = 0.0;
	protected KitItem $item;

	public function __construct(KitItem|Item $item, protected float $weight) {
		if($item instanceof Item) $item = new KitItem($item);
		$this->item = $item;
	}

	public function getItem(): KitItem {
		return $this->item;
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
<?php


namespace sys\jordan\meetup\kit;


use pocketmine\item\Item;
use sys\jordan\meetup\kit\pool\ItemPool;

class Kit {
	/**
	 * Kit constructor.
	 * @param string $name
	 * @param ItemPool[]|KitItem[] $armor
	 * @param ItemPool[]|KitItem[] $items
	 */
	public function __construct(protected string $name, protected array $armor, protected array $items) {}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	public function getArmor(): array {
		return $this->armor;
	}

	public function getItems(): array {
		return $this->items;
	}

	public function pull(): KitPullResult {
		return new KitPullResult(
			array_map(static fn(ItemPool|KitItem $item): Item => $item->pull(), $this->getArmor()),
			array_map(static fn(ItemPool|KitItem $item): Item => $item->pull(), $this->getItems()),
		);
	}

}
<?php


namespace sys\jordan\meetup\kit\pool;

use pocketmine\item\Item;

use function getrandmax;
use function rand;

class ItemPool {

	/** @var ItemEntry[] */
	private array $entries = [];
	private float $accumulatedWeight = 0.0;

	/**
	 * ItemPool constructor.
	 * @param ItemEntry[]
	 */
	public function __construct(ItemEntry ...$entries) {
		foreach($entries as $entry) {
			$this->addEntry($entry);
		}
	}

	/**
	 * @return ItemEntry[]
	 */
	public function getEntries(): array {
		return $this->entries;
	}

	public function addEntry(ItemEntry $entry): void {
		$this->accumulatedWeight += $entry->getWeight();
		$entry->setAccumulation($this->accumulatedWeight);
		$this->entries[] = $entry;
	}

	/**
	 * @param int $count
	 * @return Item|Item[]
	 */
	public function pull(int $count = 1): array|Item {
		$items = [];
		$rand = $this->random() * $this->accumulatedWeight;
		foreach($this->getEntries() as $entry) {
			if($entry->getAccumulation() >= $rand) {
				if($count === 1) {
					return $entry->getItem()->pull();
				}
				$items[] = $entry->getItem()->pull();
				$count--;
				if($count <= 0) {
					break;
				}
			}
		}
		return $items;
	}

	public function random(): float {
		return (float) rand() / (float) getrandmax();
	}
}
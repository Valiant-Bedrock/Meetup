<?php


namespace sys\jordan\meetup\kit\enchantment;



class EnchantmentPool {

	/** @var EnchantmentEntry[] */
	private array $entries = [];
	private float $accumulatedWeight = 0.0;

	/**
	 * ItemPool constructor.
	 * @param EnchantmentEntry[]
	 */
	public function __construct(EnchantmentEntry ...$entries) {
		foreach($entries as $entry) {
			$this->addEntry($entry);
		}
	}

	/**
	 * @return EnchantmentEntry[]
	 */
	public function getEntries(): array {
		return $this->entries;
	}

	public function addEntry(EnchantmentEntry $entry): void {
		$this->accumulatedWeight += $entry->getWeight();
		$entry->setAccumulation($this->accumulatedWeight);
		$this->entries[] = $entry;
	}

	public function pull(): int {
		$rand = $this->random() * $this->accumulatedWeight;
		foreach($this->getEntries() as $entry) {
			if($entry->getAccumulation() >= $rand) {
				return $entry->getValue();
			}
		}
		return 0; // it should never reach this, but if it does... return 0
	}

	public function random(): float {
		return (float) rand() / (float) getrandmax();
	}
}
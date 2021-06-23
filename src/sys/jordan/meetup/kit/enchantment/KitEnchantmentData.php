<?php


namespace sys\jordan\meetup\kit\enchantment;


use JetBrains\PhpStorm\Pure;

class KitEnchantmentData {

	protected int $level = 1;

	protected int $min = -1;
	protected int $max = -1;

	protected ?EnchantmentPool $pool = null;

	/**
	 * @throws \Exception
	 */
	public static function fromData(int|array $levelData): self {
		if(is_array($levelData)) {
			if(isset($levelData["min"]) && isset($levelData["max"])) {
				return self::createRandomized($levelData["min"], $levelData["max"]);
			} elseif(isset($levelData["entries"])) {
				return self::createWeighted($levelData["entries"]);
			} else {
				throw new \Exception("You must use min/max or entries when specifying a kit's enchantment level data");
			}
		}
		return self::createLevel($levelData);
	}

	#[Pure]
	public static function createLevel(int $level): self {
		$data = new KitEnchantmentData;
		$data->level = $level;
		return $data;
	}

	#[Pure]
	public static function createRandomized(int $min, int $max): self {
		$data = new KitEnchantmentData;
		$data->min = $min;
		$data->max = $max;
		return $data;
	}

	public static function createWeighted(array $levelEntriesData): self {
		$data = new KitEnchantmentData;
		$data->pool = new EnchantmentPool(...array_map(
			fn(array $entryData): EnchantmentEntry => new EnchantmentEntry($entryData["value"], $entryData["weight"]),
			$levelEntriesData
		));
		return $data;
	}

	public function getLevel(): int {
		if($this->min > 0 && $this->max > 0) {
			return mt_rand($this->min, $this->max);
		} elseif($this->pool instanceof EnchantmentPool) {
			return $this->pool->pull();
		}
		return $this->level;
	}
}
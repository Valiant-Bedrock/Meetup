<?php


namespace sys\jordan\meetup\kit;


use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use sys\jordan\meetup\kit\enchantment\KitEnchantment;
use sys\jordan\meetup\kit\enchantment\KitEnchantmentData;
use sys\jordan\meetup\kit\pool\ItemEntry;
use sys\jordan\meetup\kit\pool\ItemPool;

class Kit {
	/**
	 * Kit constructor.
	 * @param string $name
	 * @param ItemPool[]|KitItem[] $armor
	 * @param ItemPool[]|KitItem[] $items
	 */
	public function __construct(protected string $name, protected array $armor, protected array $items) {}

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

	public static function load(string $name, array $data): self {
		return new Kit($name, Kit::parseArmor($data["armor"] ?? []), Kit::parseItems($data["items"] ?? []));
	}

	/**
	 * @throws \Exception
	 */
	public static function parseArmor(array $armorData): array {
		$output = [];
		foreach($armorData as $type => $piece) {
			$index = match(mb_strtolower($type)) {
				"helmet" => 0,
				"chestplate" => 1,
				"leggings" => 2,
				"boots" => 3,
				default => throw new \Exception("Invalid armor type given ")
			};
			$output[$index] = self::parseKitEntry($piece);
		}
		return $output;
	}

	public static function parseItems(array $itemData): array {
		return array_map(static fn(array $kitData): ItemPool|KitItem => self::parseKitEntry($kitData), $itemData);
	}

	public static function parseKitEntry(array $entryData): ItemPool|KitItem {
		if(isset($entryData["entries"])) {
			return new ItemPool(...array_map(
				static fn (array $poolEntry): ItemEntry =>
					new ItemEntry(self::parseItem($poolEntry, $entryData["enchantments"] ?? []), $poolEntry["weight"]),
				$entryData["entries"]
			));
		} else {
			return self::parseItem($entryData, $entryData["enchantments"] ?? []);
		}
	}

	public static function parseItem(array $itemData, array $enchantmentData = []): KitItem {
		$damage = $itemData["damage"] ?? 0;
		$count = $itemData["count"] ?? 1;

		$enchantments = array_map(
			static fn(array $enchantData): KitEnchantment =>
				new KitEnchantment(VanillaEnchantments::fromString($enchantData["name"]), KitEnchantmentData::fromData($enchantData["level"])),
			$enchantmentData
		);
		// we have the fromString method, but for better item handling (like custom names, custom tags, etc.), we should migrate to a better method
		if(is_array($count)) {
			$item = new KitItem(self::fromString($itemData["name"], $damage), $count["min"], $count["max"], $enchantments);
		} else {
			$item = (new KitItem(self::fromString($itemData["name"], $damage, $count)))->setEnchantments($enchantments);
		}
		if(isset($itemData["customName"])) {
			$item->getItem()->setCustomName($itemData["customName"]);
		}
		return $item;
	}

	/**
	 * To ensure that we get the most up to date items
	 * @throws \Exception
	 */
	public static function fromString(string $name, int $damage = 0, int $count = 1): Item {
		// a little hacky, but that's okay :)
		return LegacyStringToItemParser::getInstance()->parse("$name:$damage")->setCount($count);
	}
}
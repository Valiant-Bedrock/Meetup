<?php


namespace sys\jordan\meetup\kit;


use pocketmine\item\Item;

class KitPullResult {

	/**
	 * KitPullResult constructor.
	 * @param Item[] $armorContents
	 * @param Item[] $itemContents
	 */
	public function __construct(protected array $armorContents, protected array $itemContents) {}

	/**
	 * @return Item[]
	 */
	public function getArmorContents(): array {
		return $this->armorContents;
	}

	/**
	 * @return Item[]
	 */
	public function getItemContents(): array {
		return $this->itemContents;
	}

}
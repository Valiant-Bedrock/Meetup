<?php


namespace sys\jordan\meetup\kit\enchantment;


use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

class KitEnchantment {

	public function __construct(protected Enchantment $enchantment, protected KitEnchantmentData $data) {}

	public function pull(): EnchantmentInstance {
		return new EnchantmentInstance($this->enchantment, $this->data->getLevel());
	}

}
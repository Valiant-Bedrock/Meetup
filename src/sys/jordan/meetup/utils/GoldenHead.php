<?php
/**
 * File created by Matt(@yaboimattj)
 * Unauthorized access of this file will
 * result in legal punishment.
 */

namespace sys\jordan\meetup\utils;


use pocketmine\block\utils\SkullType;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\GoldenApple;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\utils\TextFormat;

class GoldenHead extends GoldenApple {

	public function __construct(){
		parent::__construct(new ItemIdentifier(ItemIds::GOLDEN_APPLE, SkullType::PLAYER()->getMagicNumber()));
		if($this->getMeta() === SkullType::PLAYER()->getMagicNumber()) {
			$this->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Golden Head");
		}
	}

	public function getAdditionalEffects() : array{
		return $this->getMeta() === SkullType::PLAYER()->getMagicNumber() ? [
			new EffectInstance(VanillaEffects::REGENERATION(), 20 * 9, 1),
			new EffectInstance(VanillaEffects::ABSORPTION(), 2400)
		] : parent::getAdditionalEffects();
	}

}
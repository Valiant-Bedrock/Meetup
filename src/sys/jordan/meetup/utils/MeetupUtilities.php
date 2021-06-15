<?php


namespace sys\jordan\meetup\utils;


use pocketmine\block\utils\SkullType;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\utils\TextFormat;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use function closedir;
use function copy;
use function is_dir;
use function mkdir;
use function opendir;
use function readdir;
use function rmdir;

class MeetupUtilities {

	/** @var string[] */
	private static array $colors = [];

	public static function getRandomColor(): string {
		if(count(self::$colors) === 0) {
			$disallowed = [
				"EOL" => true, "ESCAPE" => true,
				"OBFUSCATED" => true, "BOLD" => true,
				"STRIKETHROUGH" => true, "UNDERLINE" => true,
				"ITALIC" => true, "RESET" => true,
				"BLACK" => true, "WHITE" => true,
				"GRAY" => true, "DARK_BLUE" => true,
				"DARK_RED" => true
			];
			/** @var string[] $colors */
			$colors = array_filter((new ReflectionClass(TextFormat::class))->getConstants(),
				fn (string $name): bool =>  isset($disallowed[mb_strtoupper($name)]), ARRAY_FILTER_USE_KEY
			);
		} else {
			$colors = self::$colors;
		}
		return ($colors[array_rand($colors)]);
	}


	public static function GOLDEN_HEAD(): Item {
		return ItemFactory::getInstance()->get(ItemIds::GOLDEN_APPLE, SkullType::PLAYER()->getMagicNumber())->setCustomName(TextFormat::GOLD . "Golden Head");
	}

}
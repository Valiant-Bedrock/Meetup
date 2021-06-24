<?php


namespace sys\jordan\meetup\rating;


use pocketmine\utils\Config;

final class RatingValues {

	/** How many matches they play before being placed into a skill rating */
	public static int $PLACEMENT_COUNT = 5;

	public static function load(Config $config): void {
		self::$PLACEMENT_COUNT = $config->getNested("rating.placement-count", 5);
	}
}
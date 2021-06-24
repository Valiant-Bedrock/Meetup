<?php


namespace sys\jordan\meetup\border;


use pocketmine\utils\Config;

final class BorderValues {

	public static BorderInfo $INFO;
	public static int $SKYBASING_THRESHOLD = 5;
	public static float $BORDER_ANIMATION_TIME = 2;

	public static function load(Config $config) {
		self::$INFO = BorderInfo::parse($config->getNested("border.default-info", []));
		self::$SKYBASING_THRESHOLD = $config->getNested("border.skybasing-threshold", 5);
		self::$BORDER_ANIMATION_TIME = $config->getNested("border.animation-time", 2);
	}

}
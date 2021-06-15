<?php


namespace sys\jordan\meetup\game;


use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see \pocketmine\utils\RegistryUtils::_generateMethodAnnotations()
 *
 * @method static GameState COUNTDOWN()
 * @method static GameState PLAYING()
 * @method static GameState POSTGAME()
 * @method static GameState VOTING()
 * @method static GameState WAITING()
 */
final class GameState {
	use EnumTrait;

	protected static function setup(): void {
		self::registerAll(
			new self("waiting"),
			new self("voting"),
			new self("countdown"),
			new self("playing"),
			new self("postgame")
		);
	}
}
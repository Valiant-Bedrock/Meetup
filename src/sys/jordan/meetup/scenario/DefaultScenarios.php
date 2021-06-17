<?php


namespace sys\jordan\meetup\scenario;


use pocketmine\utils\CloningRegistryTrait;

use sys\jordan\meetup\scenario\defaults\{
	Bowless,
	Fireless,
	NoClean,
	Switcheroo,
	Timebomb
};
use pocketmine\utils\RegistryUtils;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see \pocketmine\utils\RegistryUtils::_generateMethodAnnotations()
 *
 * @method static Bowless BOWLESS()
 * @method static Fireless FIRELESS()
 * @method static NoClean NO_CLEAN()
 * @method static Switcheroo SWITCHEROO()
 * @method static Timebomb TIMEBOMB()
 */
final class DefaultScenarios {
	use CloningRegistryTrait;

	private function __construct() {}

	private static function register(string $name, Scenario $scenario): void {
		self::_registryRegister($name, $scenario);
	}

	/**
	 * @return Scenario[]
	 */
	public static function getAll(): array {
		return self::_registryGetAll();
	}

	protected static function setup(): void {
		$factory = ScenarioFactory::getInstance();
		self::register("bowless", $factory->get("bowless"));
		self::register("fireless", $factory->get("fireless"));
		self::register("no_clean", $factory->get("no-clean"));
		self::register("switcheroo", $factory->get("switcheroo"));
		self::register("timebomb", $factory->get("timebomb"));

		//echo RegistryUtils::_generateMethodAnnotations(self::class, self::getAll()) . "\n";
	}
}
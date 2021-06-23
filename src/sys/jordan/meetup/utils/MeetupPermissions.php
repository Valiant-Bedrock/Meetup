<?php


namespace sys\jordan\meetup\utils;


use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use ReflectionClass;
use ReflectionClassConstant;

class MeetupPermissions {

	/** @var string */
	private const PREFIX = "valiant.permission.";

	public const CUSTOM_GAME = self::PREFIX . "customgame";
	public const FORCE_START = self::PREFIX . "forcestart";


	public static function register(): void {
		$reflected = new ReflectionClass(self::class);
		$operatorRoot = PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_OPERATOR);
		foreach($reflected->getConstants(ReflectionClassConstant::IS_PUBLIC) as $permission) {
			DefaultPermissions::registerPermission(new Permission($permission), [$operatorRoot]);
		}
	}
}
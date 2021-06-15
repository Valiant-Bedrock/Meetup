<?php

declare(strict_types=1);

namespace sys\jordan\meetup\command;


use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\MeetupPermissions;

class ForceStartCommand extends BaseUserCommand {

	/**
	 * ForceStartCommand constructor.
	 * @param Plugin $main
	 */
	public function __construct(Plugin $main) {
		parent::__construct($main, "forcestart", "Force-start the meetup!", "/forcestart", ["fs"], MeetupPermissions::FORCE_START);
	}

	/**
	 * @param CommandSender|MeetupPlayer $sender
	 * @param string[] $args
	 * @return string
	 */
	public function onExecute(CommandSender|MeetupPlayer $sender, array $args): string {
		if($sender->inGame()) {
			if(!$sender->getGame()->hasStarted()) {
				$sender->getGame()->start();
				return TextFormat::GREEN . "Game successfully started!";
			}
			return TextFormat::RED . "Game is already started!";
		}
		return TextFormat::RED . "You must be in a game to use this command!";
	}
}
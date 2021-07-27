<?php


namespace sys\jordan\meetup\command;


use paroxity\portal\Portal;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\meetup\MeetupPlayer;

class HubCommand extends BaseUserCommand {

	/**
	 * ForceStartCommand constructor.
	 * @param Plugin $main
	 */
	public function __construct(Plugin $main) {
		parent::__construct($main, "hub", "Redirect back to the hub server", "/hub");
	}

	/**
	 * @param CommandSender|MeetupPlayer $sender
	 * @param string[] $args
	 * @return string
	 */
	public function onExecute(CommandSender|MeetupPlayer $sender, array $args): string {
		Portal::getInstance()->transferPlayer($sender, "Hub", "Hub-1", static function (): void {});
		return TextFormat::YELLOW . "Attempting to transfer you to the hub...";
	}
}
<?php

declare(strict_types=1);

namespace sys\jordan\meetup\command;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\meetup\MeetupPlayer;

use function count;

class KillTopCommand extends BaseUserCommand {

	/**
	 * ForceStartCommand constructor.
	 * @param Plugin $main
	 */
	public function __construct(Plugin $main) {
		parent::__construct($main, "kt", "Show top kills for the meetup", "/kt");
	}

	/**
	 * @param CommandSender|MeetupPlayer $sender
	 * @param array $args
	 * @return string
	 */
	public function onExecute(CommandSender|MeetupPlayer $sender, array $args): string {
		if($sender->inGame()) {
			$top = $sender->getGame()->getEliminationManager()->getKillTop();
			if(count($top) > 0) {
				$sender->sendMessage(TextFormat::WHITE . "-----" . TextFormat::RED . " Top Kills " . TextFormat::WHITE . "-----");
				foreach($top as $name => $killCount) {
					$sender->sendMessage(TextFormat::RED . $name . TextFormat::WHITE . " - " . $killCount);
				}
				return TextFormat::WHITE . "------------------";
			} else {
				return TextFormat::YELLOW . "There are no kills yet. Change that?";
			}
		}
		return TextFormat::RED . "You must be in a game to use this command!";
	}

}
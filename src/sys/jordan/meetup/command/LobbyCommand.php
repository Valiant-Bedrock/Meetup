<?php


namespace sys\jordan\meetup\command;


use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\meetup\MeetupPlayer;

class LobbyCommand extends BaseUserCommand {

	public function __construct(Plugin $main) {
		parent::__construct($main, "lobby", "Leave the current game you are in and return to the lobby", "/lobby", []);
	}

	public function onExecute(CommandSender|MeetupPlayer $sender, array $args): string {
		if($sender->inGame()) {
			$game = $sender->getGame();
			if($game->getPlayerManager()->isPlayer($sender)) {
				$game->getPlayerManager()->quit($sender);
			} else {
				$game->getSpectatorManager()->quit($sender);
			}
			return "";
		}
		return TextFormat::RED . "You must be in a game to use this command!";
	}
}
<?php


namespace sys\jordan\meetup\command;


use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\meetup\game\GameState;
use sys\jordan\meetup\MeetupPlayer;

class RerollCommand extends BaseUserCommand {

	/**
	 * ForceStartCommand constructor.
	 * @param Plugin $main
	 */
	public function __construct(Plugin $main) {
		parent::__construct($main, "reroll", "Reroll your current kit (Can only use once per game)", "/reroll");
	}

	/**
	 * @param CommandSender|MeetupPlayer $sender
	 * @param array $args
	 * @return string
	 */
	public function onExecute(CommandSender|MeetupPlayer $sender, array $args): string {
		if($sender->inGame()) {
			if(($game = $sender->getGame())->getPlayerManager()->isPlayer($sender)) {
				if($game->getState() === GameState::VOTING() || $game->getState() === GameState::COUNTDOWN()) {

				}
				return TextFormat::RED . "You can only use this command during voting/countdown!";
			}
			return TextFormat::RED . "You must be an active player to use this command!";
		}
		return TextFormat::RED . "You must be in a game to use this command!";
	}

}
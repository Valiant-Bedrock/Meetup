<?php

declare(strict_types=1);

namespace sys\jordan\meetup\command;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;

class SpectateCommand extends BaseUserCommand {

	/**
	 * SpectateCommand constructor.
	 * @param MeetupBase $main
	 */
	public function __construct(MeetupBase $main) {
		parent::__construct($main, "spectate", "Spectate players in the UHC!", "/spectate", []);
	}

	/**
	 * @param CommandSender|MeetupPlayer $sender
	 * @param array $args
	 *
	 * @return string
	 */
	public function onExecute(CommandSender|MeetupPlayer $sender, array $args): string {
		if($sender->inGame()) {
			if($sender instanceof MeetupPlayer && $sender->getGame()->getSpectatorManager()->isSpectator($sender)) {
				if(isset($args[0])) {
					$player = $sender->getServer()->getPlayerByPrefix($args[0]);
					if($player instanceof MeetupPlayer) {
						if($player->inGame()) {
							if($player->getGame()->getPlayerManager()->isPlayer($player)) {
								$sender->teleport($player->getLocation());
								return TextFormat::GREEN . "Now spectating: " . TextFormat::GOLD . $player->getName();
							}
							return TextFormat::RED . "That player is not an active player in the game!";
						}
						return TextFormat::RED . "That player is not in a game!";
					}
					return TextFormat::RED . "Player not found!";
				}
				return TextFormat::RED . "You must specify a player to spectate!";
			}
			return TextFormat::RED . "You must be a spectator to use this command!";
		}
		return TextFormat::RED . "You must be in a game to use this command!";
	}

}
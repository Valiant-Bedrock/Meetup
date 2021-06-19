<?php


namespace sys\jordan\meetup\spectator\form;


use pocketmine\utils\TextFormat;
use sys\jordan\core\form\elements\Button;
use sys\jordan\core\form\SimpleForm;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupPlayer;

class SpectateSelectForm extends SimpleForm {

	public function __construct(Game $game) {
		parent::__construct("Spectate", "",
			array_map(fn(MeetupPlayer $player): Button =>
				new Button(TextFormat::YELLOW . $player->getName(), function (MeetupPlayer $spectator) use($player): void {
					$spectator->teleport($player->getLocation());
					$spectator->sendMessage(TextFormat::GREEN . "Now spectating: " . TextFormat::YELLOW . $player->getName());
				}),
				$game->getPlayerManager()->getPlayers()
			)
		);
	}

}
<?php


namespace sys\jordan\meetup\vote;


use pocketmine\item\VanillaItems;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use sys\jordan\core\hotbar\HotbarMenu;
use sys\jordan\core\hotbar\ItemCallback;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\vote\form\VoteSelectForm;

class VotingHotbarMenu extends HotbarMenu {

	public function __construct() {
		parent::__construct([
			0 => new ItemCallback(
				VanillaItems::BOOK()->setCustomName(TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Vote" . TextFormat::DARK_GRAY . " [Use]"),
				static function (MeetupPlayer $player): void {
					if(!$player->inGame()) {
						throw new AssumptionFailedError("Player received a hotbar item while not in game!");
					}
					$player->sendForm(new VoteSelectForm($player->getGame()->getVoteManager()));
				}
			)
		]);
	}
}
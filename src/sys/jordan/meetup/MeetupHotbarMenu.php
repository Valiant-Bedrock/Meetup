<?php


namespace sys\jordan\meetup;


use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use sys\jordan\core\hotbar\HotbarMenu;
use sys\jordan\core\hotbar\ItemCallback;
use sys\jordan\meetup\game\form\GameSelectForm;

class MeetupHotbarMenu extends HotbarMenu {

	public function __construct() {
		parent::__construct([
			0 => new ItemCallback(
				VanillaItems::PAPER()->setCustomName(TextFormat::GREEN . "Game Selector"),
				static function(MeetupPlayer $player): void {
					if(!$player->inGame()) {
						$player->sendForm(new GameSelectForm(MeetupBase::getInstance()->getGameManager()));
					}
				}),
			1 => new ItemCallback(
				VanillaItems::DIAMOND_SWORD()->setCustomName(TextFormat::GREEN . "Game Selector " . TextFormat::YELLOW . "[Ranked]"),
				static function(MeetupPlayer $player): void {
					$player->sendMessage(TextFormat::RED . "Ranked games are disabled right now! Please try again later!");
				}
			),
			8 => new ItemCallback(
				VanillaItems::BOOK()->setCustomName(TextFormat::YELLOW . "Settings"),
				static function(MeetupPlayer $player): void {
					$player->sendForm(new MeetupSettingsForm);
				})
		]);
	}
}
<?php


namespace sys\jordan\meetup\game\form;


use pocketmine\utils\TextFormat;
use sys\jordan\core\form\elements\Button;
use sys\jordan\core\form\SimpleForm;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\game\GameManager;
use sys\jordan\meetup\game\GameState;
use sys\jordan\meetup\MeetupPlayer;

class GameSelectForm extends SimpleForm {

	public function __construct(GameManager $manager) {
		parent::__construct("Game Select", "", $this->create($manager));
	}

	/**
	 * TODO: Instead of saying the game isn't joinable, we could add them as spectators(?)
	 *
	 * @param GameManager $manager
	 * @return Button[]
	 */
	public function create(GameManager $manager): array {
		return array_map(static function (Game $game): Button {
			$name = mb_strtoupper($game->getState()->name());
			$joinable = $game->getState() === GameState::WAITING();
			return new Button(
				TextFormat::YELLOW . "Game (" . ($joinable ? TextFormat::GREEN : TextFormat::RED) . $name . TextFormat::YELLOW . ") " .
				TextFormat::WHITE . "[" . TextFormat::YELLOW . $game->getPlayerManager()->getCount() . TextFormat::WHITE . "/" . TextFormat::YELLOW . Game::MAX_PLAYER_COUNT . TextFormat::WHITE . "]\n" .
				TextFormat::WHITE . "Map: " . TextFormat::YELLOW . $game->getWorld()->getDisplayName() . TextFormat::WHITE . " | Kit: " . TextFormat::YELLOW . $game->getKit()->getName(),
				static function(MeetupPlayer $player) use($game, $joinable): void {
					if($joinable) {
						$game->getPlayerManager()->join($player);
					} else {
						$player->notify(TextFormat::RED . "This game has already started! Please try another game!", TextFormat::RED);
					}
				}
			);
		}, $manager->getAll());
	}

}
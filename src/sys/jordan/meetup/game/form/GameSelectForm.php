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
		parent::__construct("Game Select", "", self::create($manager));
	}

	/**
	 * TODO: Instead of saying the game isn't joinable, we could add them as spectators(?)
	 *
	 * @param GameManager $manager
	 * @return Button[]
	 */
	public static function create(GameManager $manager): array {
		return array_map(static fn (Game $game): Button => self::createButton($game), $manager->getAll());
	}

	public static function createButton(Game $game): Button {
		$color = match($game->getState()->id()) {
			GameState::WAITING()->id() => TextFormat::GREEN,
			GameState::VOTING()->id(), GameState::COUNTDOWN()->id() => TextFormat::LIGHT_PURPLE,
			default => TextFormat::RED
		};
		return new Button(
			// could  inline the variable into the string, but it's more clear if it's separated
			TextFormat::YELLOW . "Game (" . ($color . mb_strtoupper($game->getState()->name())) . TextFormat::YELLOW . ") " .
			TextFormat::WHITE . "[" . TextFormat::YELLOW . $game->getPlayerManager()->getCount() . TextFormat::WHITE . "/" . TextFormat::YELLOW . Game::MAX_PLAYER_COUNT . TextFormat::WHITE . "]\n" .
			TextFormat::WHITE . "Map: " . TextFormat::YELLOW . $game->getWorld()->getDisplayName() . TextFormat::WHITE . " | Kit: " . TextFormat::YELLOW . $game->getKitManager()->getKit()->getName(),
			static function(MeetupPlayer $player) use($game): void {
				if($game->getState() === GameState::WAITING() || $game->getState() === GameState::VOTING() || $game->getState() === GameState::COUNTDOWN()) {
					$game->getPlayerManager()->join($player);
				} else {
					$game->getSpectatorManager()->join($player);
				}
			}
		);
	}

}
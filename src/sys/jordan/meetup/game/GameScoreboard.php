<?php

declare(strict_types=1);

namespace sys\jordan\meetup\game;


use pocketmine\utils\TextFormat;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\GameTrait;

class GameScoreboard {

	use GameTrait;

	private static string $SCOREBOARD_LINE;
	private static string $PADDING;

	/**
	 * GameScoreboard constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
		self::$SCOREBOARD_LINE = TextFormat::WHITE . str_repeat("-", 18);
		self::$PADDING = str_repeat(" ", 7);

	}

	public function sendData(MeetupPlayer $player): void {
		$player->getScoreboard()->setLineArray($this->getData($player));
	}

	/**
	 * @param MeetupPlayer $player
	 * @return string[]
	 */
	public function getData(MeetupPlayer $player): array {
		$data =  [
			self::$SCOREBOARD_LINE,
			TextFormat::WHITE . "Time: " . TextFormat::YELLOW . $this->game->getFormattedTime() . self::$PADDING,
			TextFormat::WHITE . "Players: " . TextFormat::YELLOW . count($this->game->getPlayerManager()->getPlayers()) . self::$PADDING,
			TextFormat::WHITE . "Kill Count: " . TextFormat::YELLOW . $this->game->getEliminationManager()->getEliminations($player). self::$PADDING,
			TextFormat::WHITE . "Border: " . TextFormat::YELLOW . $this->game->getBorder()->getSize() . self::$PADDING,
			TextFormat::WHITE . "CPS: " . TextFormat::YELLOW . $player->getClicksPerSecond()  . self::$PADDING,
			TextFormat::WHITE . "Ping: " . TextFormat::YELLOW . $player->getNetworkSession()->getPing()  . self::$PADDING,
		];
		if($player->getScoreboardExtradata()->hasData()) {
			array_push($data, ...$player->getScoreboardExtraData()->getData());
		}
		$data[] = self::$SCOREBOARD_LINE;
		return $data;
	}

}
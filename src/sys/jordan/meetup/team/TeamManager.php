<?php


namespace sys\jordan\meetup\team;

use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\utils\GameTrait;

class TeamManager {

	use GameTrait;

	/** @var Team[] */
	private array $teams = [];

	/**
	 * TeamManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);

	}
}
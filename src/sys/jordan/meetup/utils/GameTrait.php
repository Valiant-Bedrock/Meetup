<?php


namespace sys\jordan\meetup\utils;


use sys\jordan\meetup\game\Game;

trait GameTrait {
	protected ?Game $game = null;

	public function getGame(): ?Game {
		return $this->game;
	}

	public function setGame(?Game $game = null): void {
		$this->game = $game;
	}

}
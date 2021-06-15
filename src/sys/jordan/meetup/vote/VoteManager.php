<?php


namespace sys\jordan\meetup\vote;


use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\scenario\DefaultScenarios;
use sys\jordan\meetup\scenario\Scenario;
use sys\jordan\meetup\utils\GameTrait;

class VoteManager {

	use GameTrait;

	private static VoteOption $NONE;
	/** @var VoteOption[] */
	private array $options;

	/**
	 * VoteManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
		$this->options = [
			...array_map(static fn(Scenario $scenario): VoteOption => new VoteOption($scenario->getName(), $scenario), DefaultScenarios::getAll()),
			(self::$NONE = new VoteOption("None"))
		];
	}

	/**
	 * @return VoteOption[]
	 */
	public function getOptions(): array {
		return $this->options;
	}

	public function hasVoted(MeetupPlayer $player): bool {
		return $this->getVote($player) instanceof VoteOption;
	}

	public function getVote(MeetupPlayer $player): ?VoteOption {
		foreach($this->getOptions() as $option) {
			if($option->hasVote($player)) return $option;
		}
		return null;
	}

	/**
	 * @param int $count
	 * @return Scenario[]
	 */
	public function check(int $count = 1): array {
		usort($this->options, static fn (VoteOption $first, VoteOption $second): int => $first->getVotes() <=> $second->getVotes());
		if(($this->options[array_key_first($this->options)]) === self::$NONE) {
			return [];
		}
		return array_map(
			static fn(VoteOption $option): Scenario => $option->getScenario(),
			array_slice(array_filter($this->options, static fn(VoteOption $option): bool => $option === self::$NONE), 0, $count)
		);
	}

}
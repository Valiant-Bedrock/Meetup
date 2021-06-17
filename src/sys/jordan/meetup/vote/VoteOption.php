<?php


namespace sys\jordan\meetup\vote;


use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\scenario\Scenario;

class VoteOption {

	/**
	 * A list of votes (player UUID => bool)
	 */
	private array $votes = [];

	public function __construct(protected string $name, protected ?Scenario $scenario = null) {}

	public function getName(): string {
		return $this->name;
	}

	public function getScenario(): ?Scenario {
		return $this->scenario;
	}

	public function hasScenario(): bool {
		return $this->scenario instanceof Scenario;
	}

	public function getVotes(): array {
		return $this->votes;
	}

	public function getCount(): int {
		return count($this->votes);
	}

	public function addVote(MeetupPlayer $player): void {
		$this->votes[$player->getUniqueId()->toString()] = true;
	}

	public function removeVote(MeetupPlayer $player): void {
		if($this->hasVote($player)) {
			unset($this->votes[$player->getUniqueId()->toString()]);
		}
	}

	public function hasVote(MeetupPlayer $player): bool {
		return isset($this->votes[$player->getUniqueId()->toString()]);
	}

}
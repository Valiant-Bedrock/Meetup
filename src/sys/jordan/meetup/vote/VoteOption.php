<?php


namespace sys\jordan\meetup\vote;


use JetBrains\PhpStorm\Pure;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\scenario\Scenario;

class VoteOption {

	/**
	 * A list of votes (player UUID => bool)
	 */
	private array $votes = [];
	protected string $description = "";

	#[Pure]
	public function __construct(protected string $name, protected ?Scenario $scenario = null) {
		if($this->scenario instanceof Scenario) {
			$this->description = $scenario->getDescription();
		}
	}

	public function getName(): string {
		return $this->name;
	}

	public function getDescription(): string {
		return $this->description;
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

	public function clear(): void {
		$this->votes = [];
	}

	#[Pure]
	public static function VANILLA(): self {
		$option = new VoteOption("Vanilla");
		$option->description = "If selected, no gamemodes will be enabled for the game";
		return $option;
	}

}
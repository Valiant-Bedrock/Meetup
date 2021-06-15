<?php


namespace sys\jordan\meetup\team;


use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\utils\MeetupUtilities;

class Team {

	private int $id;
	private string $format;
	/** @var MeetupPlayer[] */
	private array $members;

	/**
	 * Team constructor.
	 * @param int $id
	 * @param array $members
	 */
	public function __construct(int $id, array $members = []) {
		$this->id = $id;
		$this->format = MeetupUtilities::getRandomColor() . "[Team $id]";
		$this->members = $members;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getFormat(): string {
		return $this->format;
	}

	/**
	 * @return MeetupPlayer[]
	 */
	public function getMembers(): array {
		return $this->members;
	}

}
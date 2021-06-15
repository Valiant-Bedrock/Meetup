<?php


namespace sys\jordan\meetup\world;


class MeetupWorldData {

	public function __construct(protected string $name, protected string $path) {}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}

	public function generateUniqueId(): string {
		return uniqid("$name-", true);
	}

}
<?php


namespace sys\jordan\meetup\utils;


class ScoreboardExtraData {

	/** @var string[] */
	private array $data = [];

	public function hasData(): bool {
		return count($this->data) > 0;
	}

	public function setData(string $key, string $value): void {
		$this->data[$key] = $value;
	}

	public function removeData(string $key): void {
		if(isset($this->data[$key])) {
			unset($this->data[$key]);
		}
	}

	/**
	 * @return string[]
	 */
	public function getData(): array {
		return $this->data;
	}

}
<?php


namespace sys\jordan\meetup\scenario;


use JetBrains\PhpStorm\Pure;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\utils\GameTrait;

class ScenarioManager {
	use GameTrait;

	/** @var Scenario[] */
	private array $scenarios = [];

	/**
	 * ScenarioManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	/**
	 * @return Scenario[]
	 */
	public function getScenarios(): array {
		return $this->scenarios;
	}


	public function add(Scenario $scenario): void {
		$this->scenarios[$scenario->getName()] = clone $scenario;
		$scenario->onAdd($this->getGame());
	}

	public function remove(Scenario $scenario): void {
		if($this->exists($scenario)) {
			$instance = $this->scenarios[$scenario->getName()];
			$instance->onRemove($this->getGame());
		}
	}

	#[Pure]
	public function exists(Scenario $scenario): bool {
		return isset($this->scenarios[$scenario->getName()]);
	}

}
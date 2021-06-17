<?php


namespace sys\jordan\meetup\scenario;


use sys\jordan\meetup\scenario\defaults\Bowless;
use sys\jordan\meetup\scenario\defaults\Fireless;
use sys\jordan\meetup\scenario\defaults\NoClean;
use sys\jordan\meetup\scenario\defaults\Switcheroo;
use sys\jordan\meetup\scenario\defaults\Timebomb;

class ScenarioFactory {

	protected static ScenarioFactory $instance;
	/** @var Scenario[] */
	protected array $scenarios = [];

	public function __construct() {
		$this->load();
		self::$instance = $this;
	}

	public function load(): void {
		$this->register(new Bowless);
		$this->register(new Fireless);
		$this->register(new NoClean);
		$this->register(new Switcheroo);
		$this->register(new Timebomb);
	}

	public function get(string $name): ?Scenario {
		return $this->scenarios[mb_strtolower($name)] ?? null;
	}

	public function register(Scenario $scenario): void {
		if(!$this->exists($scenario)) {
			$this->scenarios[mb_strtolower($scenario->getName())] = $scenario;
		}
	}

	public function exists(Scenario $scenario): bool {
		return isset($this->scenarios[mb_strtolower($scenario->getName())]);
	}

	public static function getInstance(): self {
		return self::$instance;
	}

	/**
	 * @return Scenario[]
	 */
	public function getAll(): array {
		return $this->scenarios;
	}

}
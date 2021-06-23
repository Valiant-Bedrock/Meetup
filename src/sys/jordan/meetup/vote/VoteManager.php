<?php


namespace sys\jordan\meetup\vote;


use pocketmine\utils\TextFormat;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\scenario\DefaultScenarios;
use sys\jordan\meetup\scenario\Scenario;
use sys\jordan\meetup\utils\GameTrait;

class VoteManager {

	use GameTrait;

	private static VoteOption $VANILLA;
	/** @var VoteOption[] */
	private array $options;
	protected VotingHotbarMenu $menu;

	/**
	 * VoteManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
		$this->options = [
			...array_values(array_map(static fn(Scenario $scenario): VoteOption => new VoteOption($scenario->getName(), $scenario), DefaultScenarios::getAll())),
			(self::$VANILLA = VoteOption::VANILLA())
		];
		$this->menu = new VotingHotbarMenu;
	}

	/**
	 * @return VotingHotbarMenu
	 */
	public function getMenu(): VotingHotbarMenu {
		return $this->menu;
	}

	/**
	 * @return VoteOption[]
	 */
	public function getOptions(): array {
		return $this->options;
	}

	public function giveItems(): void {
		foreach($this->getGame()->getPlayerManager()->getPlayers() as $player) {
			$this->getMenu()->give($player);
		}
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
		$options = $this->options;
		usort($options, static fn (VoteOption $first, VoteOption $second): int => count($second->getVotes()) <=> count($first->getVotes()));
		if(($options[array_key_first($options)]) === self::$VANILLA) {
			return [];
		}
		return array_map(
			static fn(VoteOption $option): Scenario => $option->getScenario(),
			array_slice(array_filter($options, static fn(VoteOption $option): bool => $option !== self::$VANILLA && $option->getScenario() instanceof Scenario), 0, $count)
		);
	}

	public function clear(): void {
		foreach($this as $key => $value) {
			unset($this->$key);
		}
	}

	public function end(): void {
		$this->game->getLogger()->info(TextFormat::YELLOW . "Cleaning up vote manager...");
		$this->clear();
	}

}
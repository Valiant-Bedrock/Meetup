<?php


namespace sys\jordan\meetup\scenario\defaults;


use JetBrains\PhpStorm\Pure;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\TextFormat;
use sys\jordan\core\utils\TickEnum;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\player\MeetupPlayerDeathEvent;
use sys\jordan\meetup\scenario\defaults\module\noclean\NoCleanTask;
use sys\jordan\meetup\scenario\Scenario;

class NoClean extends Scenario {

	public const TASK_LENGTH = 15;

	protected array $tasks = [];

	#[Pure]
	public function __construct() {
		parent::__construct("No-Clean", "Players are given 15 seconds of invulnerability. This can be nullified by hitting other players.");
	}

	public function onAdd(Game $game): void {}

	public function onRemove(Game $game): void {
		$this->tasks = [];
	}

	public function handleDamage(EntityDamageEvent $event): void {
		if($event instanceof EntityDamageByEntityEvent) {
			/** @var MeetupPlayer $player */
			$player = $event->getEntity();
			$damager = $event->getDamager();
			if($damager instanceof MeetupPlayer && $this->hasInvulnerability($damager)) {
				$this->removeInvulnerability($damager->getUniqueId()->toString());
				$damager->notify(TextFormat::RED . "Your invulnerability has been canceled by hitting a player!", TextFormat::RED);
			}
			if($this->hasInvulnerability($player)) {
				$event->cancel();
			}
		}
	}

	public function handleMeetupDeath(MeetupPlayerDeathEvent $event): void {
		/** @var EntityDamageByEntityEvent $lastDamageEvent */
		if(($lastDamageEvent = $event->getLastDamageEvent()) instanceof EntityDamageByEntityEvent) {
			/** @var MeetupPlayer $damager */
			if(($damager = $lastDamageEvent->getDamager()) instanceof MeetupPlayer) {
				if($damager->inGame() && $damager->getGame()->getPlayerManager()->isPlayer($damager)) {
					$this->giveInvulnerability($damager);
					$damager->notify(TextFormat::GREEN . "You have been given invulnerability for " . self::TASK_LENGTH . " seconds!", TextFormat::GREEN);
				}
			}
		}
	}

	public function handleQuit(PlayerQuitEvent $event): void {
		/** @var MeetupPlayer $player */
		$player = $event->getPlayer();
		$this->removeInvulnerability($player);
	}

	public function giveInvulnerability(MeetupPlayer $player): void {
		if($this->hasInvulnerability($player)) {
			$this->removeInvulnerability($player->getUniqueId()->toString());
		}
		$task = new NoCleanTask($this, $player);
		$this->tasks[$player->getUniqueId()->toString()] = $task;

		$task->schedule(TickEnum::SECOND);
	}

	public function removeInvulnerability(MeetupPlayer|string $player): void {
		if($player instanceof MeetupPlayer) {
			$player->getScoreboardExtraData()->removeData($this->getName());
			$player = $player->getUniqueId()->toString();
		}
		if(isset($this->tasks[$player])) {
			unset($this->tasks[$player]);
		}
	}

	public function hasInvulnerability(MeetupPlayer $player): bool {
		return isset($this->tasks[$player->getUniqueId()->toString()]);
	}

}
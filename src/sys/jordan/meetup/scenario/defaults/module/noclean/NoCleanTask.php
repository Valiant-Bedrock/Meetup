<?php


namespace sys\jordan\meetup\scenario\defaults\module\noclean;

use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseTask;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\scenario\defaults\NoClean;

class NoCleanTask extends BaseTask {

	protected int $countdown = NoClean::TASK_LENGTH;
	protected string $uuid;

	public function __construct(protected NoClean $instance, protected MeetupPlayer $player) {
		parent::__construct(MeetupBase::getInstance());
		$this->uuid = $player->getUniqueId()->toString();
		$player->getScoreboardExtraData()->setData($instance->getName(), $instance->getName() . ":" . TextFormat::YELLOW . $this->countdown);
	}

	public function onRun(): void {
		$this->player->getScoreboardExtraData()->setData($this->instance->getName(), $this->instance->getName() . ":" . TextFormat::YELLOW . $this->countdown);
		if($this->countdown-- <= 0) {
			$this->cancel();
			if($this->player->isOnline()) {
				$this->player->notify(TextFormat::YELLOW . "Your invulnerability has worn off!", TextFormat::YELLOW);
				$this->instance->removeInvulnerability($this->player);
			} else {
				$this->instance->removeInvulnerability($this->uuid);
			}
			unset($this->countdown, $this->instance, $this->player, $this->uuid);
		}
	}

}
<?php


namespace sys\jordan\meetup\game\task;


use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseTask;
use sys\jordan\core\CoreBase;
use sys\jordan\core\utils\TickEnum;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;

class NoCleanTask extends BaseTask {

	/** @var string */
	public const PREFIX = CoreBase::PRIMARY_COLOR . "[NoClean]";
	/** @var int */
	public const TASK_LENGTH = 15;

	/** @var int */
	private int $time = self::TASK_LENGTH;

	/**
	 * NoCleanTask constructor.
	 * @param MeetupPlayer $player
	 */
	public function __construct(protected MeetupPlayer $player) {
		parent::__construct(MeetupBase::getInstance());
		$player->sendMessage(self::PREFIX . TextFormat::GREEN . " You have been given invulnerability for " . self::TASK_LENGTH . " seconds!");
		$this->schedule(TickEnum::SECOND);
	}

	public function onRun(): void {
		$this->time--;
		$this->player->getScoreboard()->setLine(7, TextFormat::GRAY . "NoClean: " . TextFormat::GOLD . $this->time);
		if($this->time <= 0) {
			$this->cancel();
		}
	}

	public function cancel(): void {
		$this->player->sendMessage(self::PREFIX . TextFormat::YELLOW . " Your invulnerability has worn off!");
		$this->player->removeNoClean();
		$this->player->getScoreboard()->removeLine(7, true);
		parent::cancel();
	}
}
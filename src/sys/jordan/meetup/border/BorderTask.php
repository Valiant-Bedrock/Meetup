<?php

declare(strict_types=1);

namespace sys\jordan\meetup\border;


use sys\jordan\core\base\BaseTask;
use sys\jordan\meetup\MeetupBase;

class BorderTask extends BaseTask {

	/** @var int[][] */
	public const WALL_MAPPING = [
		[-1, 1, 1, 1],
		[-1, 1, -1, -1],
		[-1, -1, -1, 1],
		[1, 1, -1, 1]
	];
	private Border $border;
	private int $iteration = 0;

	public function __construct(Border $border) {
		parent::__construct(MeetupBase::getInstance());
		$this->border = $border;
		$this->schedule(5);
	}

	public function getBorder(): Border {
		return $this->border;
	}

	public function onRun(): void {
		$size = $this->getBorder()->getSize();
		$wall = self::WALL_MAPPING[$this->iteration];
		$this->getBorder()->createWall($size * $wall[0], $size * $wall[1], $size * $wall[2], $size * $wall[3]);
		if(++$this->iteration > 3) {
			$this->cancel();
		}
	}
}
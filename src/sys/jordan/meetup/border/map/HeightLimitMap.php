<?php


namespace sys\jordan\meetup\border\map;


use pocketmine\math\Vector3;
use pocketmine\world\World;
use sys\jordan\meetup\border\BorderValues;

class HeightLimitMap {

	protected string $name;
	protected array $heightLimits = [];

	public function __construct(World $world, int $size) {
		$this->name = $world->getDisplayName();
		$this->generate($world, $size);
	}

	public function generate(World $world, int $size): void {
		$threshold = BorderValues::$SKYBASING_THRESHOLD;
		for($x = -$size; $x <= $size; $x++) {
			for($z = -$size; $z <= $size; $z++) {
				$chunk = $world->loadChunk($x >> 4, $z >> 4);
				$this->addHeightLimitAt($x, $z, $chunk->getHighestBlockAt($x & 0x0f, $z & 0x0f) + $threshold);
			}
		}
	}

	public function addHeightLimitAt(int $x, int $z, int $level): void {
		$this->heightLimits["$x:$z"] = $level;
	}

	public function getHeightLimitAt(int $x, int $z): int {
		return $this->heightLimits["$x:$z"] ?? 256;
	}

	public function exceedsHeightLimit(Vector3 $vector): bool {
		return $vector->getY() > $this->getHeightLimitAt($vector->getX(), $vector->getZ());
	}

}
<?php

declare(strict_types=1);

namespace sys\jordan\meetup\border;

use JetBrains\PhpStorm\Pure;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\utils\SubChunkExplorer;
use pocketmine\world\World;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;

use function floor, abs;
use function mt_rand;

class Border {

	/** @var int */
	public const WALL_HEIGHT = 3;

	/** @var string */
	public const PREFIX = TextFormat::RED . "[Border]";

	private World $world;

	/** The full block ID of the block to place */
	private int $fullBlockId;

	/**
	 * A list of all of the passable blocks
	 * that the border can go through
	 */
	private array $passable = [
		BlockLegacyIds::AIR => true,
		BlockLegacyIds::DOUBLE_PLANT => true,
		BlockLegacyIds::LEAVES => true,
		BlockLegacyIds::LEAVES2 => true,
		BlockLegacyIds::LILY_PAD => true,
		BlockLegacyIds::LOG => true,
		BlockLegacyIds::LOG2 => true,
		BlockLegacyIds::RED_FLOWER => true,
		BlockLegacyIds::SNOW_LAYER => true,
		BlockLegacyIds::TALL_GRASS => true,
		BlockLegacyIds::VINES => true,
		BlockLegacyIds::YELLOW_FLOWER => true
	];

	/**
	 * A list of all of the blocks that the player
	 * is disallowed from being teleported on
	 */
	private array $disallowedGround = [
		BlockLegacyIds::WATER => true,
		BlockLegacyIds::LAVA => true,
		BlockLegacyIds::FLOWING_WATER => true,
		BlockLegacyIds::FLOWING_LAVA => true
	];

	/** The current size of the border */
	private int $size;
	/** The array index of the current border size */
	private int $borderIndex = 0;

	/**
	 * @var int[]
	 */
	private array $borders = [
		200,
		100,
		50,
		25,
		10,
		5
	];

	/**
	 * Border constructor.
	 * @param World $world
	 */
	public function __construct(World $world) {
		$this->world = $world;
		$this->size = $this->borders[$this->borderIndex];
		$this->fullBlockId = VanillaBlocks::BEDROCK()->getFullId();
		$this->create();
	}

	public function getWorld(): World {
		return $this->world;
	}

	public function getSize(): int {
		return $this->size;
	}

	public function getTeleportDistance(): float {
		return 3.5;
	}

	#[Pure]
	public function getTeleportBounds(): int {
		return (int) floor($this->getSize() * 0.98);
	}

	public function isPassable(Block|int $block): bool {
		return isset($this->passable[$block instanceof Block ? $block->getId() : $block]);
	}

	public function isDisallowed(Block|int $block): bool {
		return isset($this->disallowedGround[$block instanceof Block ? $block->getId() : $block]);
	}

	public function sendMessage(MeetupPlayer $player, string $message): void {
		$player->sendMessage(self::PREFIX . " $message");
	}

	public function inside(MeetupPlayer $player): bool {
		return abs($player->getPosition()->getX()) <= $this->getSize() && abs($player->getPosition()->getZ()) <= $this->getSize();
	}

	public function shrink(): void {
		$this->borderIndex++;
		$this->size = $this->borders[$this->borderIndex];
		$this->create();
	}

	public function create(): void {
		new BorderTask($this);
	}

	public function createLayer(int $x1, int $x2, int $z1, int $z2): void {
		$minX = min($x1, $x2);
		$maxX = max($x1, $x2);
		$minZ = min($z1, $z2);
		$maxZ = max($z1, $z2);
		$iterator = new SubChunkExplorer($this->world);
		$iterator->moveTo($minX, 128, $minZ);
		for($x = $minX; $x <= $maxX; $x++) {
			for($z = $minZ; $z <= $maxZ; $z++) {
				$y = $iterator->currentChunk->getHighestBlockAt($x & 0x0f, $z & 0x0f);
				$iterator->moveTo($x, $y, $z);
				while($this->isPassable($iterator->currentChunk->getFullBlock($x & 0x0f, $y, $z & 0x0f)) && $y > 1) {
					$y -= 1;
				}
				$y += 1;
				$iterator->moveTo($x, $y, $z);
				$iterator->currentSubChunk->setFullBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $this->fullBlockId);
			}
		}
	}

	public function createWall(int $x1, int $x2, int $z1, int $z2): void {
		for($y = 0; $y < self::WALL_HEIGHT; $y++) {
			MeetupBase::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use($x1, $x2, $z1, $z2): void {
				$this->createLayer($x1, $x2, $z1, $z2);
			}), (int) floor(self::WALL_HEIGHT * 2));
		}
	}

	public function randomTeleport(MeetupPlayer $player): void {
		$bounds = $this->getTeleportBounds();
		$x = mt_rand(-$bounds, $bounds);
		$z = mt_rand(-$bounds, $bounds);
		$y = $this->getWorld()->getHighestBlockAt($x, $z) + 1;
		if($this->isDisallowed($this->getWorld()->getBlockAt($x, $y - 1, $z, false, false)->getId()) || $y <= 0) {
			$this->randomTeleport($player);
			return;
		}
		$player->teleport(new Position($x + 0.5, $y, $z + 0.5, $this->getWorld()));
	}

	public function teleport(MeetupPlayer $player): void {
		$outsideX = abs($player->getPosition()->getFloorX()) >= $this->getSize();
		$outsideZ = abs($player->getPosition()->getFloorZ()) >= $this->getSize();
		$teleportDistance = $this->getTeleportDistance();
		$teleportDistance = $teleportDistance > $this->getSize() ? 0.1 : $teleportDistance;
		$location = $player->getLocation()->asLocation();
		$location->x = $outsideX ? (($location->getFloorX() <=> 0) * ($this->getSize() - $teleportDistance)) : $location->x;
		$location->z = $outsideZ ? (($location->getFloorZ() <=> 0) * ($this->getSize() - $teleportDistance)) : $location->z;
		$location->y = $this->getWorld()->getHighestBlockAt($location->getFloorX(), $location->getFloorZ()) + 1;
		$player->teleport($location);
		$this->sendMessage($player, TextFormat::YELLOW . "You have been teleported inside the border!");
	}

}
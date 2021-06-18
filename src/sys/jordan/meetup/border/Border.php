<?php

declare(strict_types=1);

namespace sys\jordan\meetup\border;

use JetBrains\PhpStorm\Pure;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\utils\SubChunkExplorer;
use pocketmine\world\World;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;

use sys\jordan\meetup\utils\GameTrait;
use sys\jordan\meetup\world\WorldManager;
use function floor, abs;
use function mt_rand;

class Border {
	use GameTrait;

	/** @var int */
	public const WALL_HEIGHT = 4;
	/** @var string */
	public const PREFIX = TextFormat::RED . "Border" . TextFormat::WHITE . " » ";

	protected Block $block;
	/** The full block ID of the block to place */
	protected int $fullBlockId;

	/**
	 * A list of all of the passable blocks
	 * that the border can go through
	 */
	protected array $passable = [
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
	protected array $disallowedGround = [
		BlockLegacyIds::WATER => true,
		BlockLegacyIds::LAVA => true,
		BlockLegacyIds::FLOWING_WATER => true,
		BlockLegacyIds::FLOWING_LAVA => true
	];

	/** The time (in seconds) until the next border shrink */
	protected int $nextShrinkTime = -1;
	/** The current size of the border */
	protected int $size;
	/** The array index of the current border size */
	protected int $borderIndex = 0;
	/** Whether or not the border can shrink further */
	protected bool $canShrink = true;

	/**
	 * Border constructor.
	 * @param Game $game
	 * @param World $world
	 * @param BorderInfo $info
	 */
	public function __construct(Game $game, protected World $world, protected BorderInfo $info) {
		$this->setGame($game);
		$this->size = $info->getInitialSize();
		$this->nextShrinkTime = $info->getShrinkInterval();
		$this->block = VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::RED());
		$this->fullBlockId = $this->block->getFullId();
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

	public function canShrink(): bool {
		return $this->canShrink;
	}

	public function getNextShrinkTime(): int {
		return $this->nextShrinkTime;
	}

	public function getFullBlockId(): int {
		return $this->fullBlockId;
	}

	#[Pure]
	public function getScoreboardTime(): string {
		$min = round($this->nextShrinkTime / 60, 1);
		$showSeconds = $min < 1;
		return $this->canShrink ?
			TextFormat::WHITE . "(" . TextFormat::RED .
				($showSeconds ? $this->nextShrinkTime : $min) .
				($showSeconds ?  "s" : "m") .
			TextFormat::WHITE . ")" : "";
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

	public function update(): void {
		if($this->canShrink && $this->nextShrinkTime-- <= 0) {
			$this->shrink();
		}
	}

	public function shrink(): void {
		if($this->canShrink && ($newSize = $this->info->getSize($this->borderIndex++)) > -1) {
			$this->size = $newSize;
			$this->nextShrinkTime = $this->info->getShrinkInterval();
			$this->create();
			$this->getGame()->broadcastMessage(self::PREFIX . "The border has shrank to {$this->size}x{$this->size}!");
			$this->canShrink = $this->info->getSize($this->borderIndex + 1) !== null;
			if(!$this->canShrink) $this->getGame()->broadcastMessage(self::PREFIX . "The border has finished shrinking! Good luck!");
		}

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
		for($x = $minX; $x <= $maxX; $x++) {
			for($z = $minZ; $z <= $maxZ; $z++) {
				$iterator->moveTo($x, 128, $z);
				if(!$iterator->currentChunk instanceof Chunk) {
					$iterator->currentChunk = $this->world->loadChunk($x >> 4, $z >> 4);
					if(!$iterator->currentChunk === null) {
						// ouch... these should be pre-generated
						continue;
					}
				}
				$this->setBorderBlock($x, $z, $iterator);
			}
		}
	}

	public function setBorderBlock(int $x, int $z, SubChunkExplorer $iterator): void {
		$y = $iterator->currentChunk->getHighestBlockAt($x & 0x0f, $z & 0x0f);
		while($this->isPassable($iterator->currentChunk->getFullBlock($x & 0x0f, $y, $z & 0x0f) >> Block::INTERNAL_METADATA_BITS) && $y > 1) {
			$y -= 1;
		}
		$y += 1;
		$this->world->setBlockAt($x, $y, $z, $this->block);
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
		$this->getWorld()->orderChunkPopulation($x >> 4, $z >> 4, null)->onCompletion(function () use($x, $z, $player): void {
			$y = $this->getWorld()->getHighestBlockAt($x, $z) + 1;
			if($this->isDisallowed($this->getWorld()->getBlockAt($x, $y - 1, $z, false, false)->getId()) || $y <= 0) {
				$this->randomTeleport($player);
				return;
			}
			$player->teleport(new Position($x + 0.5, $y, $z + 0.5, $this->getWorld()));
			$player->notify(TextFormat::YELLOW . "You have been randomly scattered across the map!", TextFormat::YELLOW);
		}, static function (): void {});

	}

	public function teleport(MeetupPlayer $player): void {
		$outsideX = abs($player->getPosition()->getFloorX()) >= $this->getSize();
		$outsideZ = abs($player->getPosition()->getFloorZ()) >= $this->getSize();
		$teleportDistance = $this->getTeleportDistance();
		$teleportDistance = $teleportDistance > $this->getSize() ? 0.1 : $teleportDistance;
		$location = $player->getLocation()->asLocation();
		$location->x = $outsideX ? (($location->getFloorX() <=> 0) * ($this->getSize() - $teleportDistance)) : $location->x;
		$location->z = $outsideZ ? (($location->getFloorZ() <=> 0) * ($this->getSize() - $teleportDistance)) : $location->z;
		if($this->getWorld()->isChunkLoaded($location->getFloorX() >> 4, $location->getFloorZ() >> 4)) {
			if(!$this->getWorld()->loadChunk($location->getFloorX() >> 4, $location->getFloorX() >> 4) instanceof Chunk) {
				// ouch... this should never happen
				return;
			}
		}
		$location->y = $this->getWorld()->getHighestBlockAt($location->getFloorX(), $location->getFloorZ()) + 1;
		$player->teleport($location);
		$this->sendMessage($player, TextFormat::YELLOW . "You have been teleported inside the border!");
	}

	public function handleWorld(): void {
		WorldManager::delete(WorldManager::$TARGET_DIRECTORY . DIRECTORY_SEPARATOR . explode(DIRECTORY_SEPARATOR, $this->world->getFolderName())[1]);
	}

}
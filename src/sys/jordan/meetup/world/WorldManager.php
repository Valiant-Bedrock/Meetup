<?php


namespace sys\jordan\meetup\world;


use pocketmine\world\World;
use RecursiveDirectoryIterator,
	RecursiveIteratorIterator,
	SplFileInfo;
use sys\jordan\meetup\Loadable;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\utils\MeetupBaseTrait;

class WorldManager implements Loadable {

	use MeetupBaseTrait;

	/** @var string */
	public const WORLD_DIRECTORY = "worlds";
	public static string $TARGET_DIRECTORY;

	/** @var MeetupWorldData[] */
	protected array $worlds = [];

	/**
	 * WorldManager constructor.
	 * @param MeetupBase $plugin
	 */
	public function __construct(MeetupBase $plugin) {
		$this->setPlugin($plugin);
		self::$TARGET_DIRECTORY = $plugin->getServer()->getDataPath() . self::WORLD_DIRECTORY . DIRECTORY_SEPARATOR . "temp";
		$this->load();
		$this->clear();
	}

	/**
	 * An array store of world folders that can be used (basename => real path)
	 *
	 * @return MeetupWorldData[]
	 */
	public function getAll(): array {
		return $this->worlds;
	}

	public function load(): void {
		/** @var SplFileInfo $directory */
		foreach(new RecursiveDirectoryIterator($this->getPlugin()->getDataFolder() . self::WORLD_DIRECTORY, RecursiveDirectoryIterator::SKIP_DOTS) as $directory) {
			if($directory->isDir()) {
				if(file_exists($directory->getRealPath() . DIRECTORY_SEPARATOR .  "level.dat")) {
					$this->worlds[strtolower($directory->getBasename())] = new MeetupWorldData($directory->getBasename(), $directory->getRealPath());
				} else {
					$this->getPlugin()->getLogger()->warning("Encountered directory '{$directory->getBasename()}' without level.dat. Skipping...");
				}
			}
		}
	}

	public function clear(): void {
		$iterator = new RecursiveDirectoryIterator(self::$TARGET_DIRECTORY, RecursiveDirectoryIterator::SKIP_DOTS);
		foreach($iterator as $directory) {
			self::delete($directory);
		}
	}

	public function create(string $basename): World|null {
		$worldData = $this->worlds[strtolower($basename)] ?? null;
		if($worldData instanceof MeetupWorldData) {
			$id = $worldData->generateUniqueId();
			self::copy($worldData->getPath(), self::$TARGET_DIRECTORY . $id);
			// the worlds should never have to be auto-upgraded, but just in case
			$this->getPlugin()->getServer()->getWorldManager()->loadWorld($id, true);
			return $this->getPlugin()->getServer()->getWorldManager()->getWorldByName($id);
		}
		return null;
	}

	public function remove(World $world): void {

	}

	public static function copy(string $source, string $destination): void {
		@mkdir($source, 0777, true);
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);
		/** @var SplFileInfo $item */
		foreach($iterator as $item) {
			$path = $destination . DIRECTORY_SEPARATOR . $item->getBasename();
			if($item->isDir()) {
				@mkdir($path, 0777, true);
			} else {
				copy($item, $path);
			}
		}
	}

	public static function delete(string $source): void {
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($files as $fileInfo) {
			$callable = ($fileInfo->isDir() ? "rmdir" : "unlink");
			$callable($fileInfo->getRealPath());
		}
		rmdir($source);
	}
}
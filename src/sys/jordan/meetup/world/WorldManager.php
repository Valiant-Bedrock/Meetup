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
	public const IN_USE_DIRECTORY = "temp";
	public static string $TARGET_DIRECTORY;

	/** @var MeetupWorldData[] */
	protected array $worlds = [];

	/**
	 * WorldManager constructor.
	 * @param MeetupBase $plugin
	 */
	public function __construct(MeetupBase $plugin) {
		$this->setPlugin($plugin);
		self::$TARGET_DIRECTORY = $plugin->getServer()->getDataPath() . self::WORLD_DIRECTORY . DIRECTORY_SEPARATOR . self::IN_USE_DIRECTORY . DIRECTORY_SEPARATOR;
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

	public function getRandom(): ?MeetupWorldData {
		if(count($this->worlds) <= 0) {
			return null;
		}
		return $this->worlds[array_rand($this->worlds)] ?? null;
	}

	public function load(): void {
		@mkdir(self::$TARGET_DIRECTORY);
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

	public function create(MeetupWorldData $data): ?World {
		$id = $data->generateUniqueId();
		self::copy($data->getPath(), self::$TARGET_DIRECTORY . $id);
		// the worlds should never have to be auto-upgraded, but just in case
		$name = self::IN_USE_DIRECTORY . DIRECTORY_SEPARATOR . $id;
		$this->getPlugin()->getServer()->getWorldManager()->loadWorld($name, true);
		return $this->getPlugin()->getServer()->getWorldManager()->getWorldByName($name);
	}

	public static function copy(string $source, string $destination): void {
		$dir = opendir($source);
		@mkdir($destination);
		while(($file = readdir($dir)) !== false) {
			if ($file !== "." && $file !== "..") {
				if (is_dir($source . DIRECTORY_SEPARATOR . $file)) {
					self::copy($source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file);
				} else {
					copy($source . DIRECTORY_SEPARATOR . $file,$destination . DIRECTORY_SEPARATOR . $file);
				}
			}
		}
		closedir($dir);
	}

	/**
	 * @param string $source
	 * @param string $destination
	 *
	 * TODO: Figure out how to get the subdirectory
	 */
	public static function legacyCopy(string $source, string $destination): void {
		@mkdir($destination, 0777, true);
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);
		/** @var SplFileInfo $item */
		foreach($iterator as $item) {
			$path = $destination . DIRECTORY_SEPARATOR . $item->getFilename();
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
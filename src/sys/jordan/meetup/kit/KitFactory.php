<?php


namespace sys\jordan\meetup\kit;


use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\utils\Config;
use sys\jordan\core\utils\FileTrait;
use sys\jordan\meetup\kit\pool\ItemEntry;
use sys\jordan\meetup\kit\pool\ItemPool;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\utils\MeetupBaseTrait;
use sys\jordan\meetup\utils\MeetupUtilities;

class KitFactory {
	use FileTrait, MeetupBaseTrait;

	public static KitFactory $instance;

	public static Kit $DEFAULT_KIT;
	/** @var Kit[] */
	private array $kits = [];

	/**
	 * KitFactory constructor.
	 * @param MeetupBase $plugin
	 */
	public function __construct(MeetupBase $plugin) {
		self::$instance = $this;
		$this->setPlugin($plugin);
		$plugin->saveResource("kits.json");
		$this->setFile(new Config($plugin->getDataFolder() . "kits.json"));
		$this->load();
	}

	public function load(): void {
		self::$DEFAULT_KIT = new Kit(
			"Meetup",
			[
				new ItemPool(new ItemEntry(VanillaItems::DIAMOND_HELMET(), 5), new ItemEntry(VanillaItems::IRON_HELMET(), 15)),
				new ItemPool(new ItemEntry(VanillaItems::DIAMOND_CHESTPLATE(), 3), new ItemEntry(VanillaItems::IRON_CHESTPLATE(), 17)),
				new ItemPool(new ItemEntry(VanillaItems::DIAMOND_LEGGINGS(), 5), new ItemEntry(VanillaItems::IRON_LEGGINGS(), 15)),
				new ItemPool(new ItemEntry(VanillaItems::DIAMOND_BOOTS(), 10), new ItemEntry(VanillaItems::IRON_BOOTS(), 10)),

			],
			[
				new ItemPool(new ItemEntry(VanillaItems::DIAMOND_SWORD(), 5), new ItemEntry(VanillaItems::IRON_SWORD(), 15)),
				new KitItem(VanillaItems::GOLDEN_APPLE(), 3, 6),
				new KitItem(MeetupUtilities::GOLDEN_HEAD(), 1, 3),
				new KitItem(VanillaItems::LAVA_BUCKET()),
				new KitItem(VanillaItems::WATER_BUCKET()),
				new KitItem(VanillaItems::BOW()),
				new KitItem(VanillaBlocks::COBBLESTONE()->asItem()->setCount(64)),
				new KitItem(VanillaBlocks::OAK_PLANKS()->asItem()->setCount(64)),
				new KitItem(VanillaItems::STEAK()->setCount(64)),
				new KitItem(VanillaItems::DIAMOND_AXE()),
				new KitItem(VanillaItems::DIAMOND_PICKAXE()),
				new KitItem(VanillaItems::ARROW()->setCount(64)),
			]
		);
//		foreach($this->getFile()->getAll() as $name => $data) {
//
//		}
		$this->addKit(self::$DEFAULT_KIT);
	}

	public static function getInstance(): KitFactory {
		return self::$instance;
	}

	/**
	 * @return Kit[]
	 */
	public function getKits(): array {
		return $this->kits;
	}

	public function getKit(string $name): ?Kit {
		return $this->kits[$name] ?? null;
	}

	public function getRandom(): ?Kit {
		return $this->kits[array_rand($this->kits)] ?? null;
	}

	public function addKit(Kit $kit): void {
		$this->kits[$kit->getName()] = $kit;
	}

}
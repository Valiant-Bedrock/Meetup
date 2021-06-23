<?php


namespace sys\jordan\meetup\scenario\defaults\module\timebomb;


use pocketmine\utils\TextFormat;
use pocketmine\world\Explosion;
use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\world\Position;
use sys\jordan\core\base\BaseTask;
use sys\jordan\meetup\MeetupBase;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\scenario\defaults\Timebomb;

class TimebombTask extends BaseTask {

	protected int $countdown = Timebomb::COUNTDOWN_LENGTH;

	protected string $name;
	protected string $uuid;
	protected Position $position;
	protected FloatingTextParticle $particle;

	public function __construct(MeetupPlayer $player, protected Timebomb $instance) {
		parent::__construct(MeetupBase::getInstance());
		$this->name = $player->getName();
		$this->uuid = $player->getUniqueId()->toString();
		$this->position = $player->getPosition();
		$this->particle = new FloatingTextParticle(TextFormat::GREEN . $this->countdown, TextFormat::YELLOW . "$this->name's corpse will explode in: ");
		$this->updateParticle();
	}

	public function getPosition(): Position {
		return $this->position;
	}

	public function calculateText(): void {
		$color = match(true) {
			$this->countdown > 15 => TextFormat::GREEN,
			$this->countdown > 5 && $this->countdown <= 15 => TextFormat::YELLOW,
			$this->countdown <= 5 => TextFormat::RED
		};
		$this->particle->setText($color . $this->countdown . TextFormat::YELLOW . "...");
	}

	public function onRun(): void {
		$this->calculateText();
		if($this->countdown-- <= 0) {
			$this->explode();
			$this->cancel();
			$this->instance->removeTask($this->uuid);
		}
		$this->updateParticle();

	}

	public function updateParticle(): void {
		// hate calculating it this way, but until we stop just getting the east side of the block, we're gonna use this
		if($this->position->isValid()) {
			$vector = $this->position->east()->add(0, 0.75, 0);
			$vector->x = $vector->getFloorX();
			$this->position->getWorld()->addParticle($vector, $this->particle);
		}

	}

	public function explode(): void {
		$explosion = new Explosion($this->position, Timebomb::EXPLOSION_SIZE);
		$explosion->explodeA();
		$explosion->explodeB();
	}

	public function onCancel(): void {
		$this->particle->setInvisible();
	}
}
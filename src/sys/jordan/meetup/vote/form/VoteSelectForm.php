<?php


namespace sys\jordan\meetup\vote\form;


use pocketmine\utils\TextFormat;
use sys\jordan\core\CoreBase;
use sys\jordan\core\form\elements\Button;
use sys\jordan\core\form\SimpleForm;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\vote\VoteManager;
use sys\jordan\meetup\vote\VoteOption;

class VoteSelectForm extends SimpleForm {

	public function __construct(VoteManager $manager) {
		parent::__construct("Vote Select", "", self::create($manager));
	}

	/**
	 * @return Button[]
	 */
	public static function create(VoteManager $manager): array {
		return array_map(static fn(VoteOption $option): Button => self::createButton($manager, $option), $manager->getOptions());
	}

	public static function createButton(VoteManager $manager, VoteOption $option): Button {
		$button = new Button(
			CoreBase::PRIMARY_COLOR . $option->getName() . TextFormat::GRAY . " (" . CoreBase::SECONDARY_COLOR . count($option->getVotes()) . TextFormat::GRAY . ")",
			static function (MeetupPlayer $player) use($option, $manager): void {
				$message = TextFormat::GREEN . "Successfully voted for {$option->getName()}!";
				if($manager->hasVoted($player)) {
					$selectedOption = $manager->getVote($player);
					$selectedOption->removeVote($player);
					$message = TextFormat::YELLOW . "Successfully changed vote from {$selectedOption->getName()} to {$option->getName()}!";
				}
				$option->addVote($player);
				$player->notify($message, TextFormat::GREEN);
			}
		);
		if($option->hasScenario() && ($scenario = $option->getScenario())->hasImage()) {
			$button->addImage(Button::IMAGE_TYPE_PATH, $scenario->getImageUrl());
		}
		return $button;
	}
}
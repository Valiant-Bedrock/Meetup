<?php


namespace sys\jordan\meetup\vote\form;


use pocketmine\utils\TextFormat;
use sys\jordan\core\CoreBase;
use sys\jordan\core\form\elements\Button;
use sys\jordan\core\form\ModalForm;
use sys\jordan\core\form\SimpleForm;
use sys\jordan\meetup\MeetupPlayer;
use sys\jordan\meetup\vote\VoteManager;
use sys\jordan\meetup\vote\VoteOption;

class VoteSelectForm extends SimpleForm {

	public function __construct(VoteManager $manager) {
		parent::__construct("Vote Select", "", self::createElements($manager));
	}

	/**
	 * @return Button[]
	 */
	public static function createElements(VoteManager $manager): array {
		return array_map(static fn(VoteOption $option): Button => self::createButton($manager, $option), $manager->getOptions());
	}

	public static function createButton(VoteManager $manager, VoteOption $option): Button {
		$button = new Button(
			CoreBase::PRIMARY_COLOR . $option->getName() . TextFormat::GRAY . " (" . CoreBase::SECONDARY_COLOR . count($option->getVotes()) . TextFormat::GRAY . ")",
			static function (MeetupPlayer $player) use($manager, $option): void { $player->sendForm(self::createVoteForm($manager, $option)); }
		);
		if($option->hasScenario() && ($scenario = $option->getScenario())->hasImage()) {
			$button->addImage(Button::IMAGE_TYPE_PATH, $scenario->getImageUrl());
		}
		return $button;
	}

	public static function createVoteForm(VoteManager $manager, VoteOption $option): ModalForm {
		return new ModalForm(
			TextFormat::GREEN . "Vote for " . TextFormat::YELLOW . $option->getName(),
			$option->getDescription(),
			TextFormat::GREEN . "Vote", TextFormat::YELLOW . "Back",
			static function (MeetupPlayer $player, bool $accept) use($option, $manager) {
				if($accept) {
					if($manager->hasVoted($player)) {
						$selectedOption = $manager->getVote($player);
						$selectedOption->removeVote($player);
						$message = TextFormat::YELLOW . "Successfully changed vote from {$selectedOption->getName()} to {$option->getName()}!";
					} else {
						$message = TextFormat::GREEN . "Successfully voted for {$option->getName()}!";
					}
					$option->addVote($player);
					$player->notify($message, TextFormat::GREEN);
				} else {
					$player->sendForm(new self($manager));
				}
			}
		);
	}
}
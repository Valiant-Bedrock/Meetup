<?php


namespace sys\jordan\meetup\vote\form;


use pocketmine\utils\TextFormat;
use sys\jordan\core\CoreBase;
use sys\jordan\core\CorePlayer;
use sys\jordan\core\form\elements\Button;
use sys\jordan\core\form\SimpleForm;
use sys\jordan\meetup\game\Game;
use sys\jordan\meetup\utils\GameTrait;
use sys\jordan\meetup\vote\VoteManager;
use sys\jordan\meetup\vote\VoteOption;

class VoteSelectForm extends SimpleForm {

	use GameTrait;

	public function __construct(VoteManager $voteManager) {
		parent::__construct("Vote Select", "", $this->create($voteManager));
	}

	/**
	 * @return Button[]
	 */
	public function create(VoteManager $voteManager): array {
		return array_map(
			fn(VoteOption $option): Button => $this->createButton($option),
			$voteManager->getOptions()
		);
	}

	public function createButton(VoteOption $option): Button {
		$button = new Button(
			CoreBase::SECONDARY_COLOR . $option->getName() . TextFormat::GRAY . "(" . TextFormat::YELLOW . $option->getVotes() . TextFormat::GRAY . ")",
			static function (CorePlayer $player, $data) use($option): void {

			}
		);
		if($option->hasScenario() && ($scenario = $option->getScenario())->hasImage()) {
			$button->addImage(Button::IMAGE_TYPE_PATH, $scenario->getImageUrl());
		}
	}
}
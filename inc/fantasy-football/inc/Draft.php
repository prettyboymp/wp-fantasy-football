<?php

namespace Prettyboymp\FantasyFootball;

class Draft {
	
	public $positionRequirements;

	public static function factory($teams, $numRounds, $positionRequirements) {
		$draftPicks = [];
		$pickNum = 1;
		for($round = 1; $round <= $numRounds; $round++) {
			foreach($teams as $team) {
				$draftPick = new DraftPick();
				$draftPick->team = $team;
				$draftPick->pickNumber = $pickNum;
			}
			
			$teams = array_reverse($teams);
		}
		
	}
	
	protected $draftPicks;
	
	public function getDraftPicks() {
		
	}
	
	
}



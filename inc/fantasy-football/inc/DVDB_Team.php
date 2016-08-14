<?php

namespace Prettyboymp\FantasyFootball;

use \DraftAPI;

class DVDB_Team extends Team {

	public function choose_player() {
		$pick_num = DraftAPI::get_pick_number();
		if ( $pick_num < 40 || 80 < $pick_num ) {
			return parent::choose_player();
		}
		$pick_options = $this->get_pick_options();
		$possible_picks = array();

		usort( $pick_options, function($option_a, $option_b) {
			return $option_a['value'] > $option_b['value'] ? -1 : 1;
		} );

		$value = 0;
		foreach ( $pick_options as $pick_option ) {
			if ( $pick_option['value'] >= $value ) {
				$possible_picks[] = $pick_option['players'][0]->player_id;
				$value = $pick_option['value'];
			} else {
				break;
			}
		}
		$player_id = false;
		if ( count( $possible_picks ) ) {
			$player_id = $possible_picks[rand( 0, count( $possible_picks ) - 1 )];
		}
		if ( !$player_id ) {
			return parent::choose_player();
		}
		return $player_id;
	}

}

function gauss() {
	$x = random_0_1();
	$y = random_0_1();

	$u = sqrt( -2 * log( $x ) ) * cos( 2 * pi() * $y );

	return $u;
}

function gauss_ms( $m = 0.0, $s = 1.0 ) { // N(m,s)
	return gauss() * $s + $m;
}

function random_0_1() { // auxiliary function
	return ( float ) rand() / ( float ) getrandmax();
}

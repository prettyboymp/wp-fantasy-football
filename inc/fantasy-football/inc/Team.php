<?php

namespace Prettyboymp\FantasyFootball;

use \DraftAPI;

class Team {

	public $team_key;
	public $pick;

	public static function get_position_draft_by() {
		$config = DraftAPI::get_draft_config();
		return $config['draft_by'];
	}

	/**
	 * Maps each position to the earliest round it can be selected.  This helps limit
	 * teams from picking the same position over and over again
	 * 
	 * @todo store randomized limits wit teams so all teams don't grab second QB's right away
	 * 
	 * @return array
	 */
	public static function get_positions_limits() {
		return array(
			'QB' => array( 0, 7 ),
			'RB' => array( 0, 0, 5, 5, 8, 8, 8 ),
			'WR' => array( 0, 0, 7, 10, 11, 12 ),
			'TE' => array( 0, 2, 14 ),
			'DST' => array( 8 ),
			'K' => array( 14 ),
		);
	}

	public function __construct( $team_key, $pick ) {
		$this->team_key = $team_key;
		$this->pick = $pick;
	}

	public function get_keepers() {
		$all_keepers = array(
			'mike' => array(
				//14881 => 6, //Russell Wilson
				//15848 => 1, //Eddie Lacy
				13934 => 3, //Antonio Brown
				16013 => 15, //Joseph Randle
			//10452 => 1 //Adrian Peterson
			//16777 => 13 //Carlos Hyde
			),
			'seth' => array(
				//Kept Manning and J. Reed
				//14005 => 1, //Demarco Murray
				13536 => 8, //Joique Bell
				//13982 => 2, //Julio Jones
				//12579 => 4, //Jeremy Maclin
				13295 => 8, //Emannuelle Sanders
			//11295 => 6, //Martellus Bennet
			//9761 => 9, //Delanie Walker
			//15893 => 3, //Andre Ellington
			),
			'richie' => array(
				//Kept Gronkowski and Rodgers
				//8479 => 2, //Frank Gore
				//8544 => 10, //Darren Sproles
				//13215 => 1, //Dez Bryant
				//5536 => 9, //Ben Roeth
				//13213 => 15, //Lagarrette Blount
			),
			'weston' => array(
				//Kept Stafford and McCoy
				//16728 => 15, //Teddy Bridgewater
				11467 => 15, //Justin Forsett
				//16803 => 15, //Jeremy Hill
			//13983 => 1, //AJ Green
			10447 => 1, //Calvin Johnson
			//14901 => 15, //Dwayne Allen
			//15835 => 7, //Zach Ertz
			),
			'james' => array(
			//Kept Alson Jeffery and RGIII
			//2580 => 1, //Drew Brees
			//12497 => 1, //Arian Foster
			12601 => 9, //Mike Wallace
			),
			'richard' => array(
			),
			'beau' => array(
				//Kept Reggie Bush and Percy Harvin //ha ha ha
				//13981 => 15, //Mark Ingram
				16040 => 15, //CJ Anderson
				16733 => 15, //Odel Beckham Jr.
			//16763 => 15, //Jordan Mathews
			),
			'ryan' => array(
				//Kept Demarius Thomas and Zac Stacy
				//11237 => 6, //Matt Ryan
				//11278 => 1, //Matt Forte
				15825 => 2, //LeVeon Bell
				//14053 => 2, //Randall Cobb
				16737 => 9, //Mike Evans
				//15795 => 12, //DeAndre Hopkins
				//15920 => 10, //Latavius Murray
			),
			'grant' => [
				14874 => 1, //Andrew Luck
				//14886 => 15, //Lamar Miller
				//16944 => 15, //Devonta Freeman
				//16730 => 14, //Kelvin Benjamin
			//14876 => 11, //Ryan Tannehill
			],
			'matt' => [
				//10456 => 1, //Marshawn Lynch
				//15009 => 1, //Alfred Morris
				//11270 => 2, //Jordy Nelson
				14924 => 5, //TY Hilton
			//4461 => 3, //Andre Johnson
			//12482 => 15, //Mark Sanchez
			]
		);



		if ( false && $this->team_key == 'mike' ) {
			if ( $this->pick > 0 ) {
				$_keepers = [
					13934 => 3, //Antonio Brown
					16013 => 15, //Joseph Randle
				];
			} else {
				$_keepers = [
					16013 => 15, //Joseph Randle
					16777 => 13 //Carlos Hyde
				];
			}
			/*
			  'mike' => array(
			  //14881 => 6, //Russell Wilson
			  //15848 => 1, //Eddie Lacy
			  13934 => 3, //Antonio Brown
			  16013 => 15, //Joseph Randle
			  //10452 => 1 //Adrian Peterson
			  //16777 => 13 //Carlos Hyde
			  );
			 * 
			 */
		} else {
			if ( !isset( $all_keepers[$this->team_key] ) ) {
				return array();
			}
			$_keepers = $all_keepers[$this->team_key];
		}
		
		$keepers = [ ];
		foreach ( $_keepers as $player_id => $round ) {
			$keepers[] = ['player_id' => $player_id, 'round' => $round ];
		}

		shuffle( $keepers );

		$keepers = array_slice( $keepers, 0, 2 );
		if ( count( $keepers ) == 2 && $keepers[0]['round'] == $keepers[1]['round'] ) {
			$keepers[1]['round'] ++;
		}


		for ( $i = 0; $i < count( $keepers ); $i++ ) {
			if ( $keepers[$i]['round'] % 2 ) {
				$keepers[$i]['pick_num'] = (($keepers[$i]['round'] - 1) * 10) + $this->pick;
			} else {
				$keepers[$i]['pick_num'] = (($keepers[$i]['round'] - 1) * 10) + (10 + 1 - $this->pick);
			}
		}
		return $keepers;
	}

	public function strategize_draft() {
		global $wpdb;

		if ( $this->team_key == 'mike' ) {
			//don't erase my rankings
			return;
		}

		//clear current ranks for this team
		$wpdb->query( $wpdb->prepare( 'DELETE from ' . DraftAPI::$player_rankings_table . ' WHERE ranker_key = %s', $this->team_key ) );

		//get players and adp
		$players = DraftAPI::get_players( array( 'meta_key' => 'adp_' . date( 'Y' ) . '_espn' ) );

		$player_ranks = array();
		//calculate order based on distribution
		foreach ( $players as $player ) {
			if ( !is_a( $player, 'Player' ) ) {
				$player = new Player( $player->player_id );
			}
			$adp = $player->getMeta( 'adp_' . date( 'Y' ) . '_espn' );
			if ( $adp >= 170 || $adp == 0 ) {
				//the top is 170, so lets increase it so these pollute the draft less
				$adp = 185;
			}
			$s = floatval( $player->getMeta( 'stddev_' . date( 'Y' ) . '_ffcalculator' ) / 10 );
			if ( !$s ) {
				$s = $adp / (2 * sqrt( $adp ) );
			}
			if ( false || $player->player_position == 'QB' ) {
				//QB's in our league tend to go much earlier than adp, so have them get drafted a bit earlier
				$adp = $adp - (sqrt( $adp ));
			}
			$player_ranks[] = array(
				'player_id' => $player->player_id,
				'ranking' => gauss_ms( $adp, $s )
			);
		}

		//reorder by new order
		usort( $player_ranks, function($rank_a, $rank_b) {
			if ( $rank_a['ranking'] == $rank_b['ranking'] ) {
				return 0;
			}
			return ($rank_a['ranking'] < $rank_b['ranking'] ) ? -1 : 1;
		} );


		//rank players according to adp
		$i = 1;
		foreach ( $player_ranks as $player_rank ) {
			$wpdb->insert( DraftAPI::$player_rankings_table, array( 'ranker_key' => $this->team_key, 'player_order' => $i, 'player_id' => $player_rank['player_id'] ) );
			$i++;
		}
	}

	public function get_draft_order() {
		return DraftAPI::get_players( array( 'ranker_key' => $this->team_key ) );
	}

	public function get_roster_html() {
		$roster = $this->get_drafted_players();
		$html = '';
		foreach ( $roster as $roster_spot => $player ) {
			$html .= '<li><em>' . $roster_spot . '</em>';
			if ( $player ) {
				if ( !is_a( $player, '\Prettyboymp\\Fantasyfootball\\Player' ) ) {
					$player = new Player( $player->player_id );
				}

				$html .= "<strong>{$player->player_name}</strong> &ndash; {$player->player_position} &ndash; {$player->team_name}";
			}
			$html .= '</li>';
		}
		return $html;
	}

	public function choose_player() {
		global $wpdb;

		$num_teams = 10;

		$roster = $this->get_drafted_players();

		$position_limits = self::get_positions_limits();

		$round = DraftAPI::get_round();

		//remove all used position limits
		foreach ( $roster as $position => $player ) {
			if ( !is_null( $player ) ) {
				array_shift( $position_limits[$player->player_position] );
			}
		}

		$selectable_positions = array();
		foreach ( $position_limits as $position => $limits ) {
			if ( is_array( $limits ) && count( $limits ) && $limits[0] <= $round ) {
				$selectable_positions[] = $position;
			}
		}

		$required_positions = array();
		$positions = DraftAPI::get_positions();
		foreach ( self::get_position_draft_by() as $position => $pick_by ) {
			if ( $pick_by <= $round && is_null( $roster[$position] ) ) {
				$required_positions = array_merge( $required_positions, $positions[$position] );
			}
		}
		if ( count( $required_positions ) > 0 ) {
			$selectable_positions = $required_positions;
		}

		$sql = $wpdb->prepare( "SELECT P.player_id FROM {$wpdb->prefix}players P " .
			"JOIN " . DraftAPI::$player_rankings_table . " PR ON PR.player_id = P.player_id " .
			"LEFT JOIN " . DraftAPI::$draft_picks_table . " DP ON DP.player_id = P.player_id AND DP.draft_id = %d " .
			"WHERE P.player_position IN ('" . join( "','", $selectable_positions ) . "') " .
			"AND DP.player_id is null " .
			"AND PR.ranker_key = '" . $this->team_key . "' " .
			"ORDER BY PR.player_order LIMIT 1", DraftAPI::get_draft_id() );

		$player_id = $wpdb->get_var( $sql );

		return $player_id;

		/**
		 * strategies:
		 * -Best available:  next player in rank that fills an open position  (works well for initial rounds)
		 * 
		 * -Value based:  uses vbd to select next player (works well until starting roster is nearly full)
		 * 
		 * 
		 * 
		 * -Need strategy that works better with end draft
		 */
	}

	public function get_drafted_players() {
		global $wpdb;

		$cache_key = "{$this->team_key}_drafted_players";
		$roster = wp_cache_get( $cache_key );
		if ( empty( $roster ) ) {
			$sql = $wpdb->prepare( "SELECT P.*, DP.pick_num from " . DraftAPI::$players_table . " P JOIN " . DraftAPI::$draft_picks_table . " DP ON DP.player_id = P.player_id " .
				"WHERE DP.team_key = %s and DP.draft_id = %d ORDER BY pick_id asc", $this->team_key, DraftAPI::get_draft_id() );
			$team_players = $wpdb->get_results( $sql );
			$team_positions = DraftAPI::get_positions();
			$roster = array();
			foreach ( $team_positions as $roster_key => $positions ) {
				$roster[$roster_key] = null;
				for ( $i = 0; $i < count( $team_players ); $i++ ) {
					if ( in_array( $team_players[$i]->player_position, $positions ) ) {
						$roster[$roster_key] = new Player( array_shift( array_splice( $team_players, $i, 1 ) )->player_id );
						//unset($team_players[$i]);
						break;
					}
				}
			}
			wp_cache_set( $cache_key, $roster );
		}

		return $roster;
	}

	public function get_pick_options() {
		global $wpdb;

		$position_weights = $this->get_position_weights();
		$drafted_players = $this->get_drafted_players();

		foreach ( $drafted_players as $player ) {
			if ( !is_null( $player ) ) {
				$position_weights[$player->player_position]-= 1;
			}
		}

		$continue_vbd = false;
		foreach ( $position_weights as $position => $weight ) {
			if ( $weight >= .75 ) {
				$continue_vbd = true;
			}
		}
		if ( !$continue_vbd ) {
			return array();
		}

		$num_teams = 10;
		$pick_num = DraftAPI::get_pick_number();
		$round = DraftAPI::get_round();
		$draft_id = DraftAPI::get_draft_id();
		$pick_options = array();
		$keepers = $this->get_keepers();
		$year = date( 'Y' );
		foreach ( $position_weights as $position => $weight ) {
			//if we have a keeper in the weighted rounds, we don't get to pick those rounds
			//so we need to expand the coverage
			$endRound = floor( $weight ) + $round;
			foreach ( $keepers as $keeper ) {
				if ( $keeper['round'] > $round && $keeper['round'] <= $endRound ) {
					$weight++;
				}
			}
			$round_weight = $weight;
			$pick_cnt = floor( $weight / 2 ) * 2 * $num_teams;
			$weight = $weight - (floor( $weight / 2 ) * 2);

			if ( $round % 2 ) {
				//odd round
				if ( $weight >= 1 ) {
					$pick_cnt += (2 * ($num_teams - $this->pick));
					$weight -= 1;
				}
				$pick_cnt += 2 * $this->pick * $weight;
			} else {
				if ( $weight >= 1 ) {
					$pick_cnt += 2 * $this->pick;
					$weight -= 1;
				}
				$pick_cnt += 2 * ($num_teams - $this->pick) * $weight;
			}
			$next_base_pick = $pick_cnt + $pick_num;
			if ( $position == 'QB' ) {
				$next_base_pick = (($next_base_pick / 10) * 9) + 12;
			}

			$picked_by_next = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) " .
					"FROM " . DraftAPI::$players_table . " P " .
					"JOIN " . DraftAPI::$player_rankings_table . " R ON P.player_id = R.player_id and R.ranker_key = %s " .
					"WHERE R.player_order <= %d AND player_position = %s", $this->team_key, $next_base_pick, $position ) );

			$picked_already = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) " .
					"FROM " . DraftAPI::$draft_picks_table . " DP " .
					"JOIN " . DraftAPI::$players_table . " P ON DP.player_id = P.player_id " .
					"WHERE DP.draft_id = %d and P.player_position = %s", $draft_id, $position ) );

			if ( $picked_by_next - $picked_already > 0 ) {
				$players = $wpdb->get_results( "SELECT AVG(PM.value) as value, PM.player_id " .
					"FROM " . DraftAPI::$players_table . " P " .
					"JOIN " . DraftAPI::$player_meta_table . " PM ON PM.player_id = P.player_id AND PM.meta_key like 'projection_" . date( 'Y' ) . "_%' " .
					"JOIN " . DraftAPI::$player_rankings_table . " R ON P.player_id = R.player_id and R.ranker_key = '{$this->team_key}' " .
					$wpdb->prepare( "LEFT JOIN " . DraftAPI::$draft_picks_table . " DP ON DP.player_id = P.player_id AND DP.draft_id = %d " .
						"WHERE DP.player_id is null AND P.player_position = %s " .
						"GROUP BY P.player_id " .
						"ORDER BY R.player_order " .
						"LIMIT %d", $draft_id, $position, $picked_by_next - $picked_already + 1 ) );

				if ( count( $players ) ) {
					$next_value = $players[0]->value;
					$base_line_value = ($players[count( $players ) - 1]->value + $players[count( $players ) - 2]->value) / 2;
					$pick_options[] = array( 'position' => $position, 'players' => $players, 'value' => $next_value - $base_line_value, 'weight' => $round_weight );
				}
			}
		}
		return $pick_options;
	}

	public function get_position_weights() {
		$config = DraftAPI::get_draft_config();
		return $config['position_weights'];
	}

	public function get_vbd_html() {
		ob_start();
		foreach ( $this->get_pick_options() as $position_set ) :
			?>
			<?php
			$baseline = ($position_set['players'][count( $position_set['players'] ) - 1]->value + $position_set['players'][count( $position_set['players'] ) - 2]->value) / 2;
			?>
			<ul>
				<li>Position: <?php echo $position_set['position'] ?></li>
				<li>Baseline:  <?php echo round( $baseline, 1 ); ?></li>
				<li>Beta: <?php echo $position_set['weight'] ?>, Players: <?php echo count( $position_set['players'] ) ?>
				<li><strong>Next 5 Players by ADP:</strong></li>
				<?php for ( $i = 0; $i < 5 && $i < count( $position_set['players'] ); $i++ ) : $player = DraftAPI::get_player( $position_set['players'][$i]->player_id ) ?>
					<li><?php echo $player->player_name ?>, &nbsp; Proj: <?php echo round( $position_set['players'][$i]->value, 1 ) ?>, &nbsp; Value: <?php echo round( $position_set['players'][$i]->value - $baseline, 1 ) ?></li>
				<?php endfor; ?>
				<li><strong>Baseline Players:</strong></li>
				<?php for ( $i = max( array( count( $position_set['players'] ) - 3, 0 ) ); $i < count( $position_set['players'] ); $i++ ) : $player = DraftAPI::get_player( $position_set['players'][$i]->player_id ) ?>
					<li><?php echo $player->player_name ?>, &nbsp; Proj: <?php echo round( $position_set['players'][$i]->value, 1 ) ?></li>
				<?php endfor; ?>
			</ul>
			<?php
		endforeach;
		return ob_get_clean();



		/**
		 * Determine value of a player
		 * 
		 * - the value of a player is the projection of the player - the projection of the baseline player
		 * - the baseline player is the replacement player at the same position you would get if you waited X rounds to fill the last starter slot at that position.
		 * -- example, if I have 5 starters to fill, the baseline RB is the RB I would get 4 rounds from this pick.
		 * 
		 * - on average, at a given pick, the owner will select the next player based on ADP at a position which they have not filled their full draft quota.
		 * -- is a team's need for a position covered by ADP?  Or does that need to be calculated?
		 * 
		 * 
		 * - how many rounds set the baseline for a position?
		 * -- is it how many of the position you plan to draft + 1?
		 * -- is it how many you start + 1?
		 * -- is it how many picks are required for all remaining starting position?
		 * 
		 *
		 */
	}

}

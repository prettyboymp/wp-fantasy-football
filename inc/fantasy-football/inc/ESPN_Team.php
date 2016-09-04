<?php

namespace Prettyboymp\FantasyFootball;

use \DraftAPI;

class ESPN_Team extends Team {


	protected $pick_order = [ ];

	public function __construct( $team_key, $pick ) {
		parent::__construct( $team_key, $pick );

		$this->pick_order = isset( $this->config[ 'pick_order' ] ) ? $this->config[ 'pick_order' ] : [ ];
	}

	public function choose_player() {
		global $wpdb;
		$round = DraftAPI::get_round();

		if ( ! isset( $this->pick_order[ $round ] ) ) {
			return parent::choose_player();
		}

		$position = $this->pick_order[ $round ];

		$player_id = $wpdb->get_var( $wpdb->prepare( 'SELECT PR.player_id from ' . DraftAPI::$player_rankings_table . ' as PR ' .
			'JOIN ' . DraftAPI::$players_table . ' as P ON (P.player_id = PR.player_id) ' .
			'LEFT JOIN ' . DraftAPI::$draft_picks_table . ' as DP ON (DP.player_id = PR.player_id and DP.draft_id = %d) ' .
			'WHERE PR.ranker_key = %s AND P.player_position = %s AND DP.player_id is null ORDER BY player_order ASC LIMIT 1',
			DraftAPI::get_draft_id(), $this->team_key, $position ) );

		return $player_id;
	}

	public function strategize_draft() {
		global $wpdb;

		$num_teams = DraftAPI::get_num_teams();

		if ( $this->team_key == 'mike' ) {
			//don't erase my rankings
			return;
		}

		//clear current ranks for this team
		$wpdb->query( $wpdb->prepare( 'DELETE from ' . DraftAPI::$player_rankings_table . ' WHERE ranker_key = %s', $this->team_key ) );

		$player_ids = $wpdb->get_col( "select player_id from wp_player_rankings where ranker_key = 'top300' order by player_order asc" );


		//rank players according to adp
		$i = 1;
		foreach ( $player_ids as $player_id ) {
			$wpdb->insert( DraftAPI::$player_rankings_table, array( 'ranker_key' => $this->team_key, 'player_order' => $i, 'player_id' => $player_id ) );
			$i++;
		}
	}

}
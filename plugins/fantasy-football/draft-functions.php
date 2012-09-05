<?php
add_action('init', array('DraftAPI', 'init'), 5);
class DraftAPI {
	
	const DB_VER = '0.1';

	public static $draft_table;
	public static $draft_teams_table;
	public static $draft_order_table;
	public static $draft_picks_table;
	public static $players_table;
	public static $player_teams_table;
	public static $player_rankings_table;
	public static $teams;

	public static function init() {
		global $wpdb;

		self::$draft_table = $wpdb->prefix . 'drafts';
		self::$draft_teams_table = $wpdb->prefix . 'draft_teams';
		self::$draft_order_table = $wpdb->prefix . 'draft_order';
		self::$draft_picks_table = $wpdb->prefix . 'draft_picks';
		self::$players_table = $wpdb->prefix . 'players';
		self::$player_teams_table = $wpdb->prefix . 'player_teams';
		self::$player_rankings_table = $wpdb->prefix . 'player_rankings';
		
		
		$current_db_version = get_option('ff_db_version', '0.0');
		if(self::DB_VER > $current_db_version) {
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
			
			$charset_collate = '';

			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
			
			$queries = array();
			
			//draft table
			//
			$queries[] = "CREATE TABLE `".self::$draft_table ."` (
				`draft_id` int(10) unsigned NOT NULL,
				`draft_order` tinyint(3) unsigned NOT NULL,
				`team_key` varchar(20) NOT NULL,
				PRIMARY KEY (`draft_id`,`draft_order`)
			) $charset_collate";
			
			
			$queries[] = "CREATE TABLE `".self::$draft_teams_table."` (
				`team_key` varchar(20) NOT NULL,
				`team_name` varchar(20) NOT NULL,
				PRIMARY KEY (`team_key`)
			) $charset_collate";
			
			$queries[] = "CREATE TABLE `".self::$draft_order_table."` (
				`draft_id` int(10) unsigned NOT NULL,
				`draft_order` tinyint(3) unsigned NOT NULL,
				`team_key` varchar(20) NOT NULL,
				PRIMARY KEY (`draft_id`,`draft_order`)
			) $charset_collate";
			
			$queries[] = "CREATE TABLE `".self::$draft_picks_table."` (
				`pick_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`player_id` bigint(20) unsigned NOT NULL,
				`draft_id` int(11) unsigned NOT NULL,
				`team_key` varchar(20) NOT NULL,
				PRIMARY KEY (`pick_id`),
				UNIQUE KEY `draft_player` (`draft_id`,`player_id`)
			) $charset_collate";
			
			$queries[] = "CREATE TABLE `".self::$players_table."` (
				  `player_id` bigint(20) unsigned NOT NULL,
					`player_name` varchar(100) NOT NULL,
					`player_position` varchar(5) NOT NULL,
					`note` text,
					`upside` varchar(20) NOT NULL DEFAULT '',
					`adp` float(9,1) DEFAULT NULL,
					`team_name` varchar(3) DEFAULT '',
					PRIMARY KEY (`player_id`)
			) $charset_collate";
			
			$queries[] = "CREATE TABLE `".self::$player_teams_table."` (
				`player_id` bigint(20) unsigned NOT NULL,
				`year` int(11) unsigned NOT NULL DEFAULT '2010',
				`team_key` varchar(3) NOT NULL,
				PRIMARY KEY (`player_id`,`year`)
			) $charset_collate";
			
			$queries[] = "CREATE TABLE `".self::$player_rankings_table."` (
				`ranker_key` varchar(25) NOT NULL,
				`player_order` int(10) unsigned NOT NULL,
				`player_id` bigint(20) unsigned NOT NULL,
				PRIMARY KEY (`ranker_key`,`player_id`)
			) $charset_collate";
			
			dbDelta($queries);
			
			update_option('ff_db_version', self::DB_VER);
			
		}
	}

	public static function get_positions() {
		return array(
			'QB' => array( 'QB' ),
			'RB1' => array( 'RB' ),
			'RB2' => array( 'RB' ),
			'WR1' => array( 'WR' ),
			'WR2' => array( 'WR' ),
			'TE' => array( 'TE' ),
			'RB/WR' => array( 'RB', 'WR', 'TE' ),
			'DST' => array( 'DST' ),
			'K' => array( 'K' ),
			'BE1' => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
			'BE2' => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
			'BE3' => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
			'BE4' => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
			'BE5' => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
			'BE6' => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
			'BE7' => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
		);
	}

	public static function start_new_draft() {
		global $wpdb;

		wp_cache_flush();

		$wpdb->query( "UPDATE ". self::$draft_table." SET draft_status = 'closed'" );

		$wpdb->insert( self::$draft_table, array( 'draft_status' => 'open' ) );
		$draft_id = ( int ) $wpdb->insert_id;

		//set draft order
		$teams = $wpdb->get_col( "SELECT team_key FROM " . self::$draft_teams_table . " ORDER BY RAND()" );

		$teams = array(
			'mike',
			'matt',
			'emily',
			'ryan',
			'james',
			'tyler',
			'seth',
			'beau',
			'steve',
			'derrick',
		);
		
		$teams = array_map( function($team_key) {
				return new FF_Team( $team_key );
			}, $teams );
			
		wp_cache_set( 'draft_teams', $teams );
		
		for ( $i = 0; $i < count( $teams ); $i++ ) {
			$wpdb->insert( self::$draft_order_table, array( 'draft_id' => $draft_id, 'draft_order' => $i, 'team_key' => $teams[$i]->team_key ) );

			$teams[$i]->strategize_draft();
		}

		return $draft_id;
	}

	public static function mock_draft() {
		while(self::get_pick_number() <= 160 ) {
			self::auto_draft_player();
		}
	}
	
	public static function get_pick_number() {
		global $wpdb;
		$pick_number = wp_cache_get( 'pick_number' );
		if ( !$pick_number ) {
			$pick_number = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . self::$draft_picks_table . " WHERE draft_id = %d", self::get_draft_id() ) ) + 1;
			wp_cache_set( 'pick_number', $pick_number );
		}
		return $pick_number;
	}

	public static function get_draft_id() {
		global $wpdb;

		$draft_id = wp_cache_get( 'draft_id' );
		if ( !$draft_id ) {
			$draft_id = $wpdb->get_var( "SELECT draft_id from " . self::$draft_table . " WHERE draft_status = 'open'" );
			if ( !$draft_id ) {
				$draft_id = self::start_new_draft();
			}
			wp_cache_set( 'draft_id', $draft_id );
		}
		return $draft_id;
	}

	public static function get_players( $args = array( ) ) {
		global $wpdb;
		
		$args = wp_parse_args($args, array(
			'ranker_key' => 'mike'
		));
		
		$select = "SELECT P.*, DP.team_key ";
		$from = "FROM {$wpdb->prefix}players P ";
		$join = " JOIN {$wpdb->prefix}player_rankings R ON R.player_id = P.player_id AND R.ranker_key = '".$args['ranker_key']."'" .
			" LEFT JOIN " . self::$draft_picks_table . " DP ON DP.player_id = P.player_id AND DP.draft_id = " . self::get_draft_id() . " ";

		$orderby = "ORDER BY R.player_order ASC";
		$limit = "LIMIT 300";

		$sql = "$select $from $join $orderby $limit";
		$players = $wpdb->get_results( $sql );
		return $players;
	}

	public static function get_draft_teams() {
		global $wpdb;
		$teams = wp_cache_get( 'draft_teams' );
		if ( empty( $teams ) ) {
			$sql = $wpdb->prepare( "SELECT team_key FROM " . self::$draft_order_table . " WHERE draft_id = %d ORDER BY draft_order ASC", self::get_draft_id() );
			$teams = $wpdb->get_col( $sql );
			$teams = array_map( function($team_key) {
					return new FF_Team( $team_key );
				}, $teams );
			wp_cache_set( 'draft_teams', $teams );
		}
		return $teams;
	}

	/**
	 *
	 * @return FF_Team
	 */
	public static function get_active_draft_team() {
		$pick_num = self::get_pick_number();
		$teams = self::get_draft_teams();

		$num_teams = count( $teams );

		$round = ceil( $pick_num / $num_teams );

		$team_offset = ($round % 2) ? (($pick_num - 1) % $num_teams) : (($num_teams - ($pick_num % $num_teams)) % $num_teams);

		return $teams[$team_offset];
	}

	public static function draft_player( $player_id ) {
		global $wpdb;
		$team_key = self::get_active_draft_team()->team_key;
		$success = ( bool ) $wpdb->insert( self::$draft_picks_table, array( 'team_key' => $team_key, 'player_id' => $player_id, 'draft_id' => self::get_draft_id() ) );
		$pick_number = self::get_pick_number();
		wp_cache_set( 'pick_number', ++$pick_number );
		wp_cache_delete( "{$team_key}_drafted_players" );
		return $success;
	}

	public static function undo() {
		global $wpdb;
		$pick_id = $wpdb->get_var( $wpdb->prepare( "SELECT max(pick_id) FROM " . self::$draft_picks_table . " WHERE draft_id = %d", self::get_draft_id() ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . self::$draft_picks_table . " WHERE pick_id = %d", $pick_id ) );

		wp_cache_delete( 'pick_number' );
	}

	public static function reorder_players( $new_order ) {
		global $wpdb;

		$limit = count( $new_order );
		$current_order = $wpdb->get_results( "SELECT player_id, player_order FROM {$wpdb->prefix}player_rankings WHERE ranker_key = 'mike' ORDER BY player_order ASC LIMIT $limit", OBJECT_K );

		for ( $i = 0; $i < count( $new_order ); $i++ ) {
			$player_id = substr( $new_order[$i], 7 );
			$new_position = $i + 1;
			if ( $new_position != $current_order[$player_id]->player_order ) {
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}player_rankings SET player_order = %d WHERE player_id = %d AND ranker_key = 'mike'", $new_position, $player_id ) );
			}
		}
		return false;
	}
	
	public static function get_round() {
		$pick_num = self::get_pick_number();
		$teams = self::get_draft_teams();

		$num_teams = count( $teams );

		return ceil( $pick_num / $num_teams );
	}

	public static function auto_draft_player() {
		global $wpdb;

		$pick_num = self::get_pick_number();
		$teams = self::get_draft_teams();

		$num_teams = count( $teams );

		$round = ceil( $pick_num / $num_teams );
		$team = self::get_active_draft_team();

		$player_id = $team->choose_player();
		
		self::draft_player( $player_id );
		
		return $player_id;
	}

	public static function update_note( $player_id, $note ) {
		global $wpdb;
		$sql = $wpdb->prepare( "UPDATE " . DraftAPI::$players_table . " SET note = %s where player_id = %d", $note, $player_id );
		return $wpdb->query( $sql );
	}

}

class FF_Team {

	public $team_key;
	
	public static function get_position_draft_by() {
		return array(
			'QB' => 8,
			'RB1' => 5,
			'RB2' => 11,
			'WR1' => 5,
			'WR2' => 11,
			'TE' => 12,
			'RB/WR' => 11,
			'DST' => 15,
			'K' => 16
		);
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
			'QB' => array( 0 ),
			'RB' => array( 0, 0, 5, 5, 8, 8, 8 ),
			'WR' => array( 0, 0, 7, 10, 11, 12 ),
			'TE' => array( 0, 2, 11 ),
			'DST' => array( 10 ),
			'K' => array( 14 ),
		);
	}

	
	public function __construct($team_key) {
		$this->team_key = $team_key;
	}

	public function strategize_draft() {
		global $wpdb;
		
		if($this->team_key == 'mike') {
			//don't erase my rankings
			return;
		}
		
		//clear current ranks for this team
		$wpdb->query($wpdb->prepare('DELETE from ' . DraftAPI::$player_rankings_table . ' WHERE ranker_key = %s', $this->team_key));
		
		//get players and adp
		$players = DraftAPI::get_players();
		
		$player_ranks = array();
		//calculate order based on distribution
		foreach($players as $player) {
			if($player->adp >= 170) {
				//the top is 170, so lets increase it so these pollute the draft less
				$player->adp = 300;
			}
			$player_ranks[] = array(
				'player_id' => $player->player_id,
				'ranking' => gauss_ms($player->adp, (4 * $player->adp) / (4 * sqrt( $player->adp) ))
			);
		}
		
		//reorder by new order
		usort($player_ranks, function($rank_a, $rank_b) {
			if ($rank_a['ranking'] == $rank_b['ranking'] ) {
        return 0;
			}
			return ($rank_a['ranking']  < $rank_b['ranking'] ) ? -1 : 1;
		});
		
		
		//rank players according to adp
		$i = 1;
		foreach($player_ranks as $player_rank) {
			$wpdb->insert(DraftAPI::$player_rankings_table, array('ranker_key' => $this->team_key, 'player_order' => $i, 'player_id' => $player_rank['player_id']));
			$i++;
		}
		
	}
	
	public function get_draft_order() {
		return DraftAPI::get_players(array('ranker_key' => $this->team_key));
	}
	
	public function get_roster_html() {
		$roster = $this->get_drafted_players();
		$html = '';
		foreach ( $roster as $roster_spot => $player ) {
			$html .= '<li><em>' . $roster_spot . '</em>';
			if ( $player ) {
				$html .= "<strong>{$player->player_name}</strong> &ndash; {$player->player_position} &ndash; {$player->team_name} &ndash; ($player->adp}";
			}
			$html .= '</li>';
		}
		return $html;
	}

	public function choose_player() {
		global $wpdb;
		$roster = $this->get_drafted_players();

		$position_limits = self::get_positions_limits();

		$round = DraftAPI::get_round();
		if($round > 10) {
			$foo = 'bar';
		}
		//remove all used position limits
		foreach ( $roster as $position => $player ) {
			if ( !is_null( $player ) ) {
				array_shift( $position_limits[$player->player_position] );
			}
		}

		$selectable_positions = array( );
		foreach ( $position_limits as $position => $limits ) {
			if ( is_array( $limits ) && count( $limits ) && $limits[0] <= $round ) {
				$selectable_positions[] = $position;
			}
		}

		$required_positions = array( );
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
			"JOIN " . DraftAPI::$player_rankings_table . " PR ON PR.player_id = P.player_id ".
			"LEFT JOIN " . DraftAPI::$draft_picks_table . " DP ON DP.player_id = P.player_id AND DP.draft_id = %d " .
			"WHERE P.player_position IN ('" . join( "','", $selectable_positions ) . "') " .
			"AND DP.player_id is null " .
			"AND PR.ranker_key = '".$this->team_key . "' ".
			"ORDER BY PR.player_order LIMIT 1", DraftAPI::get_draft_id());
		
		$player_id = $wpdb->get_var( $sql );

		return $player_id;
	}

	public function get_drafted_players() {
		global $wpdb;

		$cache_key = "{$this->team_key}_drafted_players";
		$roster = wp_cache_get( $cache_key );
		if ( empty( $roster ) ) {
			$sql = $wpdb->prepare( "SELECT P.* from " . DraftAPI::$players_table . " P JOIN " . DraftAPI::$draft_picks_table . " DP ON DP.player_id = P.player_id " .
				"WHERE DP.team_key = %s and DP.draft_id = %d ORDER BY pick_id asc", $this->team_key, DraftAPI::get_draft_id() );
			$team_players = $wpdb->get_results( $sql );
			$team_positions = DraftAPI::get_positions();
			$roster = array( );
			foreach ( $team_positions as $roster_key => $positions ) {
				$roster[$roster_key] = null;
				for ( $i = 0; $i < count( $team_players ); $i++ ) {
					if ( in_array( $team_players[$i]->player_position, $positions ) ) {
						$roster[$roster_key] = array_shift( array_splice( $team_players, $i, 1 ) );
						//unset($team_players[$i]);
						break;
					}
				}
			}
			wp_cache_set( $cache_key, $roster );
		}

		return $roster;
	}

}

function gauss() {
	$x = random_0_1();
	$y = random_0_1();

	$u = sqrt( -2 * log( $x ) ) * cos( 2 * pi() * $y );

	return $u;
}

function gauss_ms( $m = 0.0, $s = 1.0 ) {	// N(m,s)
	return gauss() * $s + $m;
}

function random_0_1() {	// auxiliary function
	return ( float ) rand() / ( float ) getrandmax();
}
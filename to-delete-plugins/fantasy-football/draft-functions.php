<?php
add_action( 'init', array( 'DraftAPI', 'init' ), 5 );

class DraftAPI {

	const DB_VER = '0.5';

	public static $draft_table;
	public static $dradft_teams_table;
	public static $draft_order_table;
	public static $draft_picks_table;
	public static $players_table;
	public static $player_teams_table;
	public static $player_rankings_table;
	public static $player_meta_table;
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
		self::$player_meta_table = $wpdb->prefix . 'player_meta';


		$current_db_version = get_option( 'ff_db_version', '0.0' );
		if ( self::DB_VER > $current_db_version ) {
			require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

			$charset_collate = '';

			if ( ! empty( $wpdb->charset ) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty( $wpdb->collate ) )
				$charset_collate .= " COLLATE $wpdb->collate";

			$queries = array();

			//draft table
			//
			$queries[] = "CREATE TABLE `" . self::$draft_table . "` (
				`draft_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`draft_order` tinyint(3) unsigned NOT NULL,
				PRIMARY KEY (`draft_id`)
			) $charset_collate";


			$queries[] = "CREATE TABLE `" . self::$draft_teams_table . "` (
				`team_key` varchar(20) NOT NULL,
				`team_name` varchar(20) NOT NULL,
				PRIMARY KEY (`team_key`)
			) $charset_collate";

			$queries[] = "CREATE TABLE `" . self::$draft_order_table . "` (
				`draft_id` int(10) unsigned NOT NULL,
				`draft_order` tinyint(3) unsigned NOT NULL,
				`team_key` varchar(20) NOT NULL,
				PRIMARY KEY (`draft_id`,`draft_order`)
			) $charset_collate";

			$queries[] = "CREATE TABLE `" . self::$draft_picks_table . "` (
				`pick_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`player_id` bigint(20) unsigned NOT NULL,
				`draft_id` int(11) unsigned NOT NULL,
				`pick_num` int(11) unsigned NOT NULL,
				`team_key` varchar(20) NOT NULL,
				PRIMARY KEY (`pick_id`),
				UNIQUE KEY `draft_player` (`draft_id`,`player_id`)
			) $charset_collate";

			$queries[] = "CREATE TABLE `" . self::$players_table . "` (
				  `player_id` bigint(20) unsigned NOT NULL,
					`player_name` varchar(100) NOT NULL,
					`player_position` varchar(5) NOT NULL,
					`note` text,
					`upside` varchar(20) NOT NULL DEFAULT '',
					`team_name` varchar(3) DEFAULT '',
					PRIMARY KEY (`player_id`)
			) $charset_collate";

			$queries[] = "CREATE TABLE `" . self::$player_meta_table . "` (
				  `player_id` bigint(20) unsigned NOT NULL,
					`meta_key` varchar(100) NOT NULL DEFAULT '',
					`value` text,
					PRIMARY KEY (`player_id`, `meta_key`)
			) $charset_collate";

			$queries[] = "CREATE TABLE `" . self::$player_teams_table . "` (
				`player_id` bigint(20) unsigned NOT NULL,
				`year` int(11) unsigned NOT NULL DEFAULT '2010',
				`team_key` varchar(3) NOT NULL,
				PRIMARY KEY (`player_id`,`year`)
			) $charset_collate";

			$queries[] = "CREATE TABLE `" . self::$player_rankings_table . "` (
				`ranker_key` varchar(25) NOT NULL,
				`player_order` int(10) unsigned NOT NULL,
				`player_id` bigint(20) unsigned NOT NULL,
				PRIMARY KEY (`ranker_key`,`player_id`)
			) $charset_collate";

			dbDelta( $queries );

			update_option( 'ff_db_version', self::DB_VER );
		}
	}

	public static function get_positions() {
		return array(
			'QB'    => array( 'QB' ),
			'RB1'   => array( 'RB' ),
			'RB2'   => array( 'RB' ),
			'WR1'   => array( 'WR' ),
			'WR2'   => array( 'WR' ),
			'TE'    => array( 'TE' ),
			'RB/WR' => array( 'RB', 'WR', 'TE' ),
			'DST'   => array( 'DST' ),
			'K'     => array( 'K' ),
			'BE1'   => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
			'BE2'   => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
			'BE3'   => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
			'BE4'   => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
			'BE5'   => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
			'BE6'   => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
			'BE7'   => array( 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ),
		);
	}

	public static function start_new_draft() {
		global $wpdb;

		wp_cache_flush();

		$wpdb->query( "UPDATE " . self::$draft_table . " SET draft_status = 'closed'" );

		$wpdb->insert( self::$draft_table, array( 'draft_status' => 'open' ) );
		$draft_id = ( int ) $wpdb->insert_id;

		//set draft order
		//$team_keys = $wpdb->get_col( "SELECT team_key FROM " . self::$draft_teams_table . " ORDER BY RAND()" );
		$team_keys = array(
			'james',
			'tyler',
			'emily',
			'weston',
			'mike',
			'beau',
			'ryan',
			'seth'
		);
		$i = 1;
		$teams = array();
		for ( $i = 0; $i < count( $team_keys ); $i++ ) {
			if ( $team_keys[ $i ] == 'mike' || true ) {
				$teams[] = new DVDB_Team( $team_keys[ $i ], $i + 1 );
			} else {
				$teams[] = new FF_Team( $team_keys[ $i ], $i + 1 );
			};
		}

		wp_cache_set( 'draft_teams', $teams );

		for ( $i = 0; $i < count( $teams ); $i++ ) {
			$wpdb->insert( self::$draft_order_table, array( 'draft_id' => $draft_id, 'draft_order' => $i, 'team_key' => $teams[ $i ]->team_key ) );

			$teams[ $i ]->strategize_draft();

			$keepers = $teams[ $i ]->get_keepers();
			foreach ( $keepers as $keeper ) {
				self::draft_player( $keeper[ 'player_id' ], $keeper[ 'pick_num' ], $teams[ $i ]->team_key );
			}
		}

		return $draft_id;
	}

	public static function mock_draft() {
		while ( self::get_pick_number() <= 160 ) {
			self::auto_draft_player();
		}
	}

	public static function get_pick_number() {
		global $wpdb;
		$pick_number = wp_cache_get( 'pick_number' );
		if ( ! $pick_number ) {
			$draft_id = self::get_draft_id();
			//pick num is based off of first pick, so check that pick 1 exists;
			if ( 1 == $wpdb->get_var( $wpdb->prepare( "SELECT MIN(pick_num) from " . self::$draft_picks_table . " WHERE draft_id = %d", $draft_id ) ) ) {
				$query = $wpdb->prepare( "SELECT P1.pick_num + 1 as next_pick " .
					"FROM " . self::$draft_picks_table . " as P1 " .
					"LEFT JOIN " . self::$draft_picks_table . " as P2 ON P1.pick_num+1 = P2.pick_num AND P2.draft_id = %d " .
					"WHERE P2.pick_num is null " .
					"AND P1.draft_id = %d " .
					"ORDER BY P1.pick_num LIMIT 1", $draft_id, $draft_id );
				$pick_number = $wpdb->get_var( $query );
			}
			if ( ! $pick_number ) {
				$pick_number = 1;
			}
			wp_cache_set( 'pick_number', $pick_number );
		}
		return $pick_number;
	}

	public static function get_draft_id() {
		global $wpdb;

		$draft_id = wp_cache_get( 'draft_id' );
		if ( ! $draft_id ) {
			$draft_id = $wpdb->get_var( "SELECT draft_id from " . self::$draft_table . " WHERE draft_status = 'open'" );
			if ( ! $draft_id ) {
				$draft_id = self::start_new_draft();
			}
			wp_cache_set( 'draft_id', $draft_id );
		}
		return $draft_id;
	}

	public static function get_players( $args = array() ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'limit' => 300
		) );

		$select = "SELECT P.*, DP.team_key ";
		$from = "FROM " . self::$players_table . " P ";
		$join = " LEFT JOIN " . self::$draft_picks_table . " DP ON DP.player_id = P.player_id AND DP.draft_id = " . self::get_draft_id() . " ";

		$order = array();

		if ( ! empty( $args[ 'ranker_key' ] ) ) {
			$join .= $wpdb->prepare( " JOIN " . self::$player_rankings_table . " R ON R.player_id = P.player_id AND R.ranker_key = %s ", $args[ 'ranker_key' ] );
			$order[] = "R.player_order ASC";
		}

		if ( ! empty( $args[ 'meta_key' ] ) ) {
			$join .= $wpdb->prepare( " JOIN " . self::$player_meta_table . " M ON M.player_id = P.player_id AND M.meta_key = %s ", $args[ 'meta_key' ] );
			$order[] = "CAST(M.value as UNSIGNED) ASC";
		}

		if ( $order ) {
			$orderby = "ORDER BY " . implode( ', ', $order );
		} else {
			$orderby = '';
		}
		$limit = "LIMIT " . intval( $args[ 'limit' ] );

		$sql = "$select $from $join $orderby $limit";

		$players = $wpdb->get_results( $sql );
		if ( ! count( $players ) ) {
			var_dump( $sql );
		}
		return $players;
	}

	public static function get_player( $id ) {
		return new FF_Player( $id );
	}

	public static function get_draft_teams() {
		global $wpdb;
		$teams = wp_cache_get( 'draft_teams' );
		if ( empty( $teams ) ) {
			$sql = $wpdb->prepare( "SELECT team_key, draft_order FROM " . self::$draft_order_table . " WHERE draft_id = %d ORDER BY draft_order ASC", self::get_draft_id() );
			$teams = $wpdb->get_results( $sql );
			$teams = array_map( function( $team ) {
				return new DVDB_Team( $team->team_key, $team->draft_order );
			}, $teams );
			wp_cache_set( 'draft_teams', $teams );
		}
		return $teams;
	}

	public static function get_upcoming_picks_html() {
		$picks = array();
		$pick_num = self::get_pick_number();
		$teams = self::get_draft_teams();
		$num_teams = count( $teams );

		while ( count( $picks ) < 60 ) {
			$round = ceil( $pick_num / $num_teams );
			$team_offset = ( $round % 2 ) ? ( ( $pick_num - 1 ) % $num_teams ) : ( ( $num_teams - ( $pick_num % $num_teams ) ) % $num_teams );
			$picks[ $pick_num ] = array( 'team' => $teams[ $team_offset ]->team_key, 'pick' => $pick_num );
			$pick_num++;
		}

		foreach($teams as $team) {
			$team_picks = wp_list_pluck($team->get_drafted_players(), 'pick_num');
			$_team_picks = array_flip($team_picks);
			$picks = array_diff_key($picks, $_team_picks);
		}
		die(var_dump($picks));

		$html = '<ol>';
		foreach ( $picks as $pick ) {
			$html .= '<li class="' . $pick[ 'team' ] . '">' . $pick[ 'pick' ] . ') ' . $pick[ 'team' ] . '</li>';
		}
		$html .= '</ol>';
		return $html;
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

		$team_offset = ( $round % 2 ) ? ( ( $pick_num - 1 ) % $num_teams ) : ( ( $num_teams - ( $pick_num % $num_teams ) ) % $num_teams );

		return $teams[ $team_offset ];
	}

	public static function draft_player( $player_id, $pick_num = null, $team_key = null ) {
		global $wpdb;

		if ( ! $pick_num ) {
			$pick_num = self::get_pick_number();
		}

		if ( ! $team_key ) {
			$team_key = self::get_active_draft_team()->team_key;
		}
		$success = ( bool ) $wpdb->insert( self::$draft_picks_table, array( 'team_key' => $team_key, 'player_id' => $player_id, 'draft_id' => self::get_draft_id(), 'pick_num' => $pick_num ) );

		wp_cache_delete( 'pick_number' );
		wp_cache_delete( "{$team_key}_drafted_players" );
		return $success;
	}

	public static function undo() {
		global $wpdb;
		$pick_id = $wpdb->get_var( $wpdb->prepare( "SELECT max(pick_id) FROM " . self::$draft_picks_table . " WHERE draft_id = %d", self::get_draft_id() ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . self::$draft_picks_table . " WHERE pick_id = %d", $pick_id ) );
		wp_cache_flush();
	}

	public static function reorder_players( $new_order ) {
		global $wpdb;

		$limit = count( $new_order );
		$current_order = $wpdb->get_results( "SELECT player_id, player_order FROM {$wpdb->prefix}player_rankings WHERE ranker_key = 'mike' ORDER BY player_order ASC LIMIT $limit", OBJECT_K );

		for ( $i = 0; $i < count( $new_order ); $i++ ) {
			$player_id = substr( $new_order[ $i ], 7 );
			$new_position = $i + 1;
			if ( $new_position != $current_order[ $player_id ]->player_order ) {
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

		self::draft_player( $player_id, $pick_num );

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
	public $pick;

	public static function get_position_draft_by() {
		return array(
			'QB'    => 8,
			'RB1'   => 5,
			'RB2'   => 11,
			'WR1'   => 5,
			'WR2'   => 11,
			'TE'    => 12,
			'RB/WR' => 11,
			'DST'   => 15,
			'K'     => 16
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
		$limits = array(
			'seth' => array(
				array( //2012
					'QB'  => array( 0, 7 ),
					'RB'  => array( 0, 0, 5, 5, 8, 8, 8 ),
					'WR'  => array( 0, 0, 6, 9, 11, 12 ),
					'TE'  => array( 0, 5 ),
					'DST' => array( 10 ),
					'K'   => array( 15 ),
				),
				array(
					'QB'  => array( 0, 8 ),
					'RB'  => array( 0, 0, 5, 5, 8, 8, 8 ),
					'WR'  => array( 0, 0, 7, 10, 11, 12 ),
					'TE'  => array( 0, 2, 11 ),
					'DST' => array( 10 ),
					'K'   => array( 14 ),
				)
			),
			'seth' => array(
				array(
					'QB'  => array( 0, 8 ),
					'RB'  => array( 0, 0, 5, 5, 8, 8, 8 ),
					'WR'  => array( 0, 0, 7, 10, 11, 12 ),
					'TE'  => array( 0, 2, 11 ),
					'DST' => array( 10 ),
					'K'   => array( 14 ),
				),
				array(
					'QB'  => array( 0, 8 ),
					'RB'  => array( 0, 0, 5, 5, 8, 8, 8 ),
					'WR'  => array( 0, 0, 7, 10, 11, 12 ),
					'TE'  => array( 0, 2, 11 ),
					'DST' => array( 10 ),
					'K'   => array( 14 ),
				)
			),
			'seth' => array(
				array(
					'QB'  => array( 0, 8 ),
					'RB'  => array( 0, 0, 5, 5, 8, 8, 8 ),
					'WR'  => array( 0, 0, 7, 10, 11, 12 ),
					'TE'  => array( 0, 2, 11 ),
					'DST' => array( 10 ),
					'K'   => array( 14 ),
				),
				array(
					'QB'  => array( 0, 8 ),
					'RB'  => array( 0, 0, 5, 5, 8, 8, 8 ),
					'WR'  => array( 0, 0, 7, 10, 11, 12 ),
					'TE'  => array( 0, 2, 11 ),
					'DST' => array( 10 ),
					'K'   => array( 14 ),
				)
			),
			'seth' => array(
				array(
					'QB'  => array( 0, 8 ),
					'RB'  => array( 0, 0, 5, 5, 8, 8, 8 ),
					'WR'  => array( 0, 0, 7, 10, 11, 12 ),
					'TE'  => array( 0, 2, 11 ),
					'DST' => array( 10 ),
					'K'   => array( 14 ),
				),
				array(
					'QB'  => array( 0, 8 ),
					'RB'  => array( 0, 0, 5, 5, 8, 8, 8 ),
					'WR'  => array( 0, 0, 7, 10, 11, 12 ),
					'TE'  => array( 0, 2, 11 ),
					'DST' => array( 10 ),
					'K'   => array( 14 ),
				)
			)
		);

		return array(
			'QB'  => array( 0, 8 ),
			'RB'  => array( 0, 0, 5, 5, 8, 8, 8 ),
			'WR'  => array( 0, 0, 7, 10, 11, 12 ),
			'TE'  => array( 0, 2, 11 ),
			'DST' => array( 10 ),
			'K'   => array( 14 ),
		);
	}

	public function __construct( $team_key, $pick ) {
		$this->team_key = $team_key;
		$this->pick = $pick;
	}

	public function get_keepers() {
		$all_keepers = array(
			'mike'   => array(
				array( 'player_id' => 14885, 'round' => 5 ), //Doug Martin
				array( 'player_id' => 11307, 'round' => 2 ), //Jamaal Charles
			),
			'seth'   => array(
				array( 'player_id' => 15009, 'round' => 15 ), //Alfred Morris
				//array( 'player_id' => 13983, 'round' => 2 ), //AJ Green
				//array( 'player_id' => 9588, 'round' => 4 ), //Reggie Bush
				//array( 'player_id' => 12483, 'round' => 1 ), //Matt Stafford
			),
			'emily'  => array(
				//array( 'player_id' => 2330, 'round' => 1 ), //Tom Brady
				array( 'player_id' => 11252, 'round' => 8 ), //Joe Flacco
				//array( 'player_id' => 11283, 'round' => 2 ), //1 -- Steven Jackson
			),
			'weston' => array(),
			'james'  => array(
				//array( 'player_id' => 12514, 'round' => 1 ), //LeSean McCoy
				//array( 'player_id' => 11278, 'round' => 2 ), //Matt Forte
				array( 'player_id' => 14053, 'round' => 15 ), //Randall Cobb
				//array( 'player_id' => 9705, 'round' => 3 ), //Brandon Marshall
			),
			'tyler'  => array(
				array( 'player_id' => 13203, 'round' => 12 ), //CJ Spiller
				array( 'player_id' => 14028, 'round' => 9 ), //Stevan Ridley
			),
			'beau'   => array(
				array( 'player_id' => 10452, 'round' => 1 ), //Adrian Peterson
				array( 'player_id' => 10447, 'round' => 2 ), //1 Calvin Johnson
			),
			'ryan'   => array(
				array( 'player_id' => 11289, 'round' => 1 ), //Ray Rice
				//array( 'player_id' => 13271, 'round' => 8 ), //Eric Decker
				//array('player_id' => 13489, 'round' => 15), //Mike WIlliams
				array( 'player_id' => 14881, 'round' => 15 ), //Russell Wilson
			),
		);

		$keepers = ( array ) $all_keepers[ $this->team_key ];
		for ( $i = 0; $i < count( $keepers ); $i++ ) {
			if ( $keepers[ $i ][ 'round' ] % 2 ) {
				$keepers[ $i ][ 'pick_num' ] = ( ( $keepers[ $i ][ 'round' ] - 1 ) * 8 ) + $this->pick;
			} else {
				$keepers[ $i ][ 'pick_num' ] = ( ( $keepers[ $i ][ 'round' ] - 1 ) * 8 ) + ( 8 + 1 - $this->pick );
			}
		}
		return $keepers;
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

		//get players and adp
		$players = DraftAPI::get_players( array( 'meta_key' => 'adp_' . date( 'Y' ) . '_espn' ) );

		$player_ranks = array();
		//calculate order based on distribution
		foreach ( $players as $player ) {
			if ( ! is_a( $player, 'FF_Player' ) ) {
				$player = new FF_Player( $player->player_id );
			}
			$adp = $player->getMeta( 'adp_' . date( 'Y' ) . '_ffcalculator' );
			if ( $adp >= 170 || $adp == 0 ) {
				//the top is 170, so lets increase it so these pollute the draft less
				$adp = 300;
			}
			$s = floatval( $player->getMeta( 'stddev_2013_ffcalculator' ) / 10 );
			if ( ! $s ) {
				$s = $adp / ( 2 * sqrt( $adp ) );
			}
			if ( $player->player_position == 'QB' ) {
				//QB's in our league tend to go much earlier than adp, so have them get drafted a bit earlier
				$adp = ( ( 8 * $adp ) / 9 ) - 12;
			}
			$player_ranks[] = array(
				'player_id' => $player->player_id,
				'ranking'   => gauss_ms( $adp, $s )
			);
		}

		//reorder by new order
		usort( $player_ranks, function( $rank_a, $rank_b ) {
			if ( $rank_a[ 'ranking' ] == $rank_b[ 'ranking' ] ) {
				return 0;
			}
			return ( $rank_a[ 'ranking' ] < $rank_b[ 'ranking' ] ) ? -1 : 1;
		} );


		//rank players according to adp
		$i = 1;
		foreach ( $player_ranks as $player_rank ) {
			$wpdb->insert( DraftAPI::$player_rankings_table, array( 'ranker_key' => $this->team_key, 'player_order' => $i, 'player_id' => $player_rank[ 'player_id' ] ) );
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
				if ( ! is_a( $player, 'FF_Player' ) ) {
					$player = new FF_Player( $player->player_id );
				}

				$html .= "<strong>{$player->player_name}</strong> &ndash; {$player->player_position} &ndash; {$player->team_name} &ndash; " . round( $player->getMeta( 'adp_' . date( 'Y' ) . '_espn' ), 1 );
			}
			$html .= '</li>';
		}
		return $html;
	}

	public function choose_player() {
		global $wpdb;

		$this->pick;

		$num_teams = 8;

		$roster = $this->get_drafted_players();

		$position_limits = self::get_positions_limits();

		$round = DraftAPI::get_round();

		//remove all used position limits
		foreach ( $roster as $position => $player ) {
			if ( ! is_null( $player ) ) {
				array_shift( $position_limits[ $player->player_position ] );
			}
		}

		$selectable_positions = array();
		foreach ( $position_limits as $position => $limits ) {
			if ( is_array( $limits ) && count( $limits ) && $limits[ 0 ] <= $round ) {
				$selectable_positions[] = $position;
			}
		}

		$required_positions = array();
		$positions = DraftAPI::get_positions();
		foreach ( self::get_position_draft_by() as $position => $pick_by ) {
			if ( $pick_by <= $round && is_null( $roster[ $position ] ) ) {
				$required_positions = array_merge( $required_positions, $positions[ $position ] );
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
				$roster[ $roster_key ] = null;
				for ( $i = 0; $i < count( $team_players ); $i++ ) {
					if ( in_array( $team_players[ $i ]->player_position, $positions ) ) {
						$roster[ $roster_key ] = new FF_Player( array_shift( array_splice( $team_players, $i, 1 ) )->player_id );
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

class DVDB_Team extends FF_Team {

	public function get_pick_options() {
		global $wpdb;

		$position_weights = $this->get_position_weights();
		$drafted_players = $this->get_drafted_players();

		foreach ( $drafted_players as $player ) {
			if ( ! is_null( $player ) ) {
				$position_weights[ $player->player_position ] -= 1;
			}
		}

		$continue_vbd = false;
		foreach ( $position_weights as $position => $weight ) {
			if ( $weight >= .75 ) {
				$continue_vbd = true;
			}
		}
		if ( ! $continue_vbd ) {
			return array();
		}

		$num_teams = 8;
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
				if ( $keeper[ 'round' ] > $round && $keeper[ 'round' ] <= $endRound ) {
					$weight++;
				}
			}
			$round_weight = $weight;
			$pick_cnt = floor( $weight / 2 ) * 2 * $num_teams;
			$weight = $weight - ( floor( $weight / 2 ) * 2 );

			if ( $round % 2 ) {
				//odd round
				if ( $weight >= 1 ) {
					$pick_cnt += ( 2 * ( $num_teams - $this->pick ) );
					$weight -= 1;
				}
				$pick_cnt += 2 * $this->pick * $weight;
			} else {
				if ( $weight >= 1 ) {
					$pick_cnt += 2 * $this->pick;
					$weight -= 1;
				}
				$pick_cnt += 2 * ( $num_teams - $this->pick ) * $weight;
			}
			$next_base_pick = $pick_cnt + $pick_num;
			if ( $position == 'QB' ) {
				$next_base_pick = ( ( $next_base_pick / 8 ) * 9 ) + 12;
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
					$next_value = $players[ 0 ]->value;
					$base_line_value = ( $players[ count( $players ) - 1 ]->value + $players[ count( $players ) - 2 ]->value ) / 2;
					$pick_options[] = array( 'position' => $position, 'players' => $players, 'value' => $next_value - $base_line_value, 'weight' => $round_weight );
				}
			}
		}
		return $pick_options;
	}

	public function get_vbd_html() {
		ob_start();
		foreach ( $this->get_pick_options() as $position_set ) :
			?>
			<?php
			$baseline = ( $position_set[ 'players' ][ count( $position_set[ 'players' ] ) - 1 ]->value + $position_set[ 'players' ][ count( $position_set[ 'players' ] ) - 2 ]->value ) / 2;
			?>
			<ul>
				<li>Position: <?php echo $position_set[ 'position' ] ?></li>
				<li>Baseline: <?php echo round( $baseline, 1 ); ?></li>
				<li>Beta: <?php echo $position_set[ 'weight' ] ?>,
					Players: <?php echo count( $position_set[ 'players' ] ) ?>
				<li><strong>Next 5 Players by ADP:</strong></li>
				<?php for ( $i = 0; $i < 5 && $i < count( $position_set[ 'players' ] ); $i++ ) : $player = DraftAPI::get_player( $position_set[ 'players' ][ $i ]->player_id ) ?>
					<li><?php echo $player->player_name ?>, &nbsp;
						Proj: <?php echo round( $position_set[ 'players' ][ $i ]->value, 1 ) ?>, &nbsp;
						Value: <?php echo round( $position_set[ 'players' ][ $i ]->value - $baseline, 1 ) ?></li>
				<?php endfor; ?>
				<li><strong>Baseline Players:</strong></li>
				<?php for ( $i = max( array( count( $position_set[ 'players' ] ) - 3, 0 ) ); $i < count( $position_set[ 'players' ] ); $i++ ) : $player = DraftAPI::get_player( $position_set[ 'players' ][ $i ]->player_id ) ?>
					<li><?php echo $player->player_name ?>, &nbsp;
						Proj: <?php echo round( $position_set[ 'players' ][ $i ]->value, 1 ) ?></li>
				<?php endfor; ?>
			</ul>
			<?php
		endforeach;
		return ob_get_clean();
	}

	public function get_position_weights() {
		return array(
			'QB'  => 1.5,
			'RB'  => 3.75,
			'WR'  => 3.75,
			'TE'  => 1.5,
			'DST' => 1.5,
			'K'   => 0,
		);
	}

	public function choose_player() {

		$pick_options = $this->get_pick_options();
		$possible_picks = array();

		usort( $pick_options, function( $option_a, $option_b ) {
			return $option_a[ 'value' ] > $option_b[ 'value' ] ? -1 : 1;
		} );

		$value = 0;
		foreach ( $pick_options as $pick_option ) {
			if ( $pick_option[ 'value' ] >= $value ) {
				$possible_picks[] = $pick_option[ 'players' ][ 0 ]->player_id;
				$value = $pick_option[ 'value' ];
			} else {
				break;
			}
		}
		$player_id = false;
		if ( count( $possible_picks ) ) {
			$player_id = $possible_picks[ rand( 0, count( $possible_picks ) - 1 ) ];
		}
		if ( ! $player_id ) {
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

class FF_Player {

	public $player_id;

	public function __construct( $player_id ) {
		$this->player_id = $player_id;
	}

	public function __get( $field ) {
		global $wpdb;
		$player_data = wp_cache_get( 'player_data_' . $this->player_id );
		if ( ! is_array( $player_data ) ) {
			$player_data = $wpdb->get_row( $wpdb->prepare( "SELECT * from " . DraftAPI::$players_table
				. " WHERE player_id = %d", $this->player_id ) );
			wp_cache_set( 'player_data_' . $this->player_id, $player_data );
		}
		return isset( $player_data->$field ) ? $player_data->$field : null;
	}

	public function getMeta( $key ) {
		global $wpdb;
		$player_meta = wp_cache_get( 'player_meta_' . $this->player_id );
		if ( ! is_array( $player_meta ) ) {
			$player_meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, value from " . DraftAPI::$player_meta_table
				. " WHERE player_id = %d", $this->player_id ), OBJECT_K );
			wp_cache_set( 'player_meta_' . $this->player_id, $player_meta );
		}
		return isset( $player_meta[ $key ] ) ? $player_meta[ $key ]->value : null;
	}

	public function getProjection() {
		$meta = wp_cache_get( 'player_meta_' . $this->player_id );
		$proj = 0;
		$count = 0;
		foreach ( $meta as $meta_key => $data ) {
			if ( strpos( $meta_key, 'projection_' . date( 'Y' ) ) === 0 ) {
				$count++;
				$proj += $data->value;
			}
		}
		if ( $count )
			return round( $proj / $count, 1 );
		return 0;
	}

}
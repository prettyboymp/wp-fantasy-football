<?php

add_action( 'init', array( 'DraftAPI', 'init' ), 5 );

use Prettyboymp\FantasyFootball\Player;
use Prettyboymp\FantasyFootball\Team;
use Prettyboymp\FantasyFootball\DVDB_Team;

class DraftAPI {

	const DB_VER = '0.6';

	public static $draft_table;
	public static $draft_teams_table;
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
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

			$charset_collate = '';

			if ( !empty( $wpdb->charset ) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( !empty( $wpdb->collate ) )
				$charset_collate .= " COLLATE $wpdb->collate";

			$queries = array();

			//draft table
			//
			$queries[] = "CREATE TABLE `" . self::$draft_table . "` (
				`draft_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`draft_status` varchar(20) NOT NULL,
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

	public static function get_draft_config() {
		$config = include __DIR__ . '/draft-config.php';

		//comment out to randomize draft order
		if ( $config['shuffle'] ) {
			$draft_order = &$config['draft_order'];
			shuffle( $draft_order );
		}


		return $config;
	}

	public static function get_positions() {
		$config = self::get_draft_config();
		return $config['positions'];
	}

	public static function start_new_draft() {
		global $wpdb;

		wp_cache_flush();

		$wpdb->query( "UPDATE " . self::$draft_table . " SET draft_status = 'closed'" );

		$wpdb->insert( self::$draft_table, array( 'draft_status' => 'open' ) );
		$draft_id = ( int ) $wpdb->insert_id;

		$config = self::get_draft_config();
		if ( isset( $config['draft_order'] ) ) {
			$team_keys = $config['draft_order'];
		} else {
			//set draft order
			$team_keys = $wpdb->get_col( "SELECT team_key FROM " . self::$draft_teams_table . " ORDER BY RAND()" );
		}
		$i = 1;
		$teams = array();
		for ( $i = 0; $i < count( $team_keys ); $i++ ) {
			if ( $team_keys[$i] == 'mike' ) {
				$teams[] = new DVDB_Team( $team_keys[$i], $i + 1 );
			} else {
				$teams[] = new Team( $team_keys[$i], $i + 1 );
			};
		}

		wp_cache_set( 'draft_teams', $teams );

		for ( $i = 0; $i < count( $teams ); $i++ ) {
			$wpdb->insert( self::$draft_order_table, array( 'draft_id' => $draft_id, 'draft_order' => $i, 'team_key' => $teams[$i]->team_key ) );

			$teams[$i]->strategize_draft();

			$keepers = $teams[$i]->get_keepers();
			foreach ( $keepers as $keeper ) {
				self::draft_player( $keeper['player_id'], $keeper['pick_num'], $teams[$i]->team_key );
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
		if ( !$pick_number ) {
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
			if ( !$pick_number ) {
				$pick_number = 1;
			}
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

	public static function get_players( $args = array() ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'limit' => 500
			) );

		$select = "SELECT P.*, DP.team_key ";
		$from = "FROM " . self::$players_table . " P ";
		$join = " LEFT JOIN " . self::$draft_picks_table . " DP ON DP.player_id = P.player_id AND DP.draft_id = " . self::get_draft_id() . " ";

		$order = array();

		if ( !empty( $args['ranker_key'] ) ) {
			$join .= $wpdb->prepare( " JOIN " . self::$player_rankings_table . " R ON R.player_id = P.player_id AND R.ranker_key = %s ", $args['ranker_key'] );
			$order[] = "R.player_order ASC";
		}

		if ( !empty( $args['meta_key'] ) ) {
			$join .= $wpdb->prepare( " JOIN " . self::$player_meta_table . " M ON M.player_id = P.player_id AND M.meta_key = %s ", $args['meta_key'] );
			$order[] = "CAST(M.value as UNSIGNED) ASC";
		}

		if ( $order ) {
			$orderby = "ORDER BY " . implode( ', ', $order );
		} else {
			$orderby = '';
		}
		$limit = "LIMIT " . intval( $args['limit'] );

		$sql = "$select $from $join $orderby $limit";

		$players = $wpdb->get_results( $sql );
		if ( !count( $players ) ) {
			var_dump( $sql );
		}
		return $players;
	}

	public static function get_draft_picks() {
		global $wpdb;
		$picks = wp_cache_get( 'ff_draft_picks' );
		if ( !$picks ) {
			$picks = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . self::$draft_picks_table . " where draft_id = %d", self::get_draft_id() ) );
			wp_cache_set( 'ff_draft_picks', $picks );
			
		}
		return $picks;
	}

	public static function get_player( $id ) {
		return new Player( $id );
	}

	public static function get_draft_teams() {
		global $wpdb;
		$teams = wp_cache_get( 'draft_teams' );
		if ( empty( $teams ) ) {
			$sql = $wpdb->prepare( "SELECT team_key, draft_order FROM " . self::$draft_order_table . " WHERE draft_id = %d ORDER BY draft_order ASC", self::get_draft_id() );
			$teams = $wpdb->get_results( $sql );
			$teams = array_map( function($team) {
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
			$team_offset = ($round % 2) ? (($pick_num - 1) % $num_teams) : (($num_teams - ($pick_num % $num_teams)) % $num_teams);
			$keepers = $teams[$team_offset]->get_keepers();
			$has_keeper = false;
			foreach ( $keepers as $keeper ) {
				if ( $keeper['round'] == $round ) {
					$has_keeper = true;
					break;
				}
			}
			if ( !$has_keeper ) {
				$picks[] = array( 'team' => $teams[$team_offset]->team_key, 'pick' => $pick_num );
			}
			$pick_num++;
		}

		$html = '<ol>';
		foreach ( $picks as $pick ) {
			$html .= '<li class="' . $pick['team'] . '">' . $pick['pick'] . ') ' . $pick['team'] . '</li>';
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

		$team_offset = ($round % 2) ? (($pick_num - 1) % $num_teams) : (($num_teams - ($pick_num % $num_teams)) % $num_teams);

		return $teams[$team_offset];
	}

	public static function draft_player( $player_id, $pick_num = null, $team_key = null ) {
		global $wpdb;

		if ( !$pick_num ) {
			$pick_num = self::get_pick_number();
		}

		if ( !$team_key ) {
			$team_key = self::get_active_draft_team()->team_key;
		}
		$success = ( bool ) $wpdb->insert( self::$draft_picks_table, array( 'team_key' => $team_key, 'player_id' => $player_id, 'draft_id' => self::get_draft_id(), 'pick_num' => $pick_num ) );

		wp_cache_delete( 'pick_number' );
		wp_cache_delete( "{$team_key}_drafted_players" );
		wp_cache_delete('ff_draft_picks');
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

		self::draft_player( $player_id, $pick_num );

		return $player_id;
	}

	public static function update_note( $player_id, $note ) {
		global $wpdb;
		$sql = $wpdb->prepare( "UPDATE " . DraftAPI::$players_table . " SET note = %s where player_id = %d", $note, $player_id );
		return $wpdb->query( $sql );
	}

}

<?php
/*
  Plugin Name: Fantasy Football
  Plugin URI: http://vocecommunications.com/#
  Description: A fantasy football plugin
  Author: Michael Pretty (prettyboymp)
  Version: 0.1
  Author URI: http://prettyboymp.wordpress.com
 */

require_once(dirname( __FILE__ ) . '/draft-functions.php');

class FF_Plugin {

	private static $instance;

	public static function GetInstance() {
		if ( !isset( self::$instance ) ) {
			self::$instance = new FF_Plugin();
		}
		return self::$instance;
	}

	public function initialize() {
		$this->handle_ajax();

		add_action( 'admin_menu', array( $this, 'add_menu_items' ) );
	}

	public function add_menu_items() {
		add_menu_page( 'Fantasy Fooball', 'Fant. Football', 'edit_posts', 'fantasy-football-options', array( $this, 'admin_options_page' ) );
		$hook = add_submenu_page( 'fantasy-football-options', 'Options', 'Options', 'edit_posts', 'fantasy-football-options', array( $this, 'admin_options_page' ) );
		add_action( 'load-' . $hook, array( $this, 'admin_load_options_page' ) );
		$hook = add_submenu_page( 'fantasy-football-options', 'Importers', 'Importers', 'edit_posts', 'fantasy-football-importers', array( $this, 'admin_importers_page' ) );
		add_action( 'load-' . $hook, array( $this, 'admin_load_importers_page' ) );
	}

	public function admin_load_options_page() {
		
	}

	public function admin_options_page() {
		?>
		<div class="wrap">
			<h2>HELLO</h2>
		</div>
		<?php
	}

	public function admin_load_importers_page() {
		if ( isset( $_REQUEST['importer'] ) ) {
			$importer = false;
			switch ( $_REQUEST['importer'] ) {
				case 'espn_player':
					$importer = new ESPN_Player_Importer();
					break;
				case 'espn_analyst':
					$importer = new ESPN_Analyst_Importer();
					break;
				case 'espn_adp':
					$importer = new ESPN_ADP_Importer();
					break;
				case 'espn_projections':
					$importer = new ESPN_Projection_Importer();
					break;
				case 'default_rank':
					$importer = new My_Default_Rank_Importer();
					break;
				case 'fftoday_projections':
					$importer = new FFToday_Projection_Importer();
					break;
				case 'fantasypros_projections':
					$importer = new FantasyPros_Projection_Importer();
					break;
				case 'fantasypros_adp':
					$importer = new FantasyPros_ADP_Importer();
					break;
				case 'fantasysharks_projections':
					$importer = new FantasySharks_Projection_Importer();
					break;
				case 'ffcalculator_adp':
					$importer = new FFCalculator_ADP_Importer();
					break;
				case 'fix_my_ranks':
					$importer = new Fix_My_Ranks_Importer();
					break;
			}
			if ( $importer ) {
				$importer->run( $_REQUEST );
				wp_cache_flush();
			}
		}
	}

	public function admin_importers_page() {
		?>
		<div class="wrap">
			<h2>HELLO</h2>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=espn_player' ); ?>">Run ESPN Player Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=espn_analyst&analyst=2013preseasonFFLranks250' ); ?>">Run ESPN Default Ranking Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=espn_analyst&analyst=NFLDK2K13_Harris_200' ); ?>">Run ESPN Christopher Harris Ranking Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=espn_analyst&analyst=NFLDK2K13_Berry_200' ); ?>">Run ESPN Mathew Berry Ranking Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=espn_analyst&analyst=NFLDK2K13_Karabell_200' ); ?>">Run ESPN Eric Karabell Ranking Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=espn_adp' ); ?>">Run ESPN ADP Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=default_rank&ranker_key=mike' ); ?>">Run ESPN ADP Importer As My Ranks</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=fix_my_ranks' ); ?>">Add/Remove Major Movers from my Ranks</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=espn_projections&year=2013' ); ?>">Run 2013 ESPN Projection Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=espn_projections&year=2012' ); ?>">Run 2012 ESPN Projection Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=fftoday_projections' ); ?>">Run FFToday Projection Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=fantasypros_projections' ); ?>">Run FantasyPros Projection Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=fantasypros_adp' ); ?>">Run FantasyPros ADP Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=fantasysharks_projections' ); ?>">Run FantasySharks Projection Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=fantasy-football-importers&importer=ffcalculator_adp' ); ?>">Run FFCalculator ADP Importer</a>
			</p>
		</div>
		<?php
	}

	public function handle_ajax() {
		if ( isset( $_REQUEST['draft_action'] ) ) {
			switch ( $action = $_REQUEST['draft_action'] ) {
				case 'draft_player':
					$player_id = $_REQUEST['player_id'];
					$data = array( );
					$draft_team = DraftAPI::get_active_draft_team();
					$data['draft_team'] = $draft_team->team_key;
					if ( DraftAPI::draft_player( $player_id ) ) {
						$active_team = DraftAPI::get_active_draft_team();
						$data['success'] = true;
						$data['draft_team_html'] = $draft_team->get_roster_html();
						$data['new_active_team'] = $active_team->team_key;
						$data['player_id'] = $player_id;
						$data['pick_num'] = DraftAPI::get_pick_number();
						$data['vbd_html'] = $active_team->get_vbd_html();
						$data['upcoming_html'] = DraftAPI::get_upcoming_picks_html();
					} else {
						$data['success'] = false;
					}
					echo json_encode( $data );
					die();
					break;
				case 'auto_draft_player':
					$data = array( );
					$draft_team = DraftAPI::get_active_draft_team();
					$data['draft_team'] = $draft_team->team_key;
					if ( $player_id = DraftAPI::auto_draft_player() ) {
						$active_team = DraftAPI::get_active_draft_team();
						$data['success'] = true;
						$data['draft_team_html'] = $draft_team->get_roster_html();
						$data['new_active_team'] = $active_team->team_key;
						$data['player_id'] = $player_id;
						$data['pick_num'] = DraftAPI::get_pick_number();
						$data['vbd_html'] = $active_team->get_vbd_html();
						$data['upcoming_html'] = DraftAPI::get_upcoming_picks_html();
					} else {
						$data['success'] = false;
					}
					echo json_encode( $data );
					die();
					break;
				case 'reorder_players':
					$new_order = $_REQUEST['new_order'];
					$data = array( 'success' => DraftAPI::reorder_players( $new_order ) );
					echo json_encode( $data );
					die();
					break;
				case 'restart':
					DraftAPI::start_new_draft();
					wp_redirect( remove_query_arg( 'draft_action' ) );
					die();
					break;
				case 'mock_draft':
					DraftAPI::mock_draft();
					wp_redirect( remove_query_arg( 'draft_action' ) );
					die();
					break;
					break;
				case 'undo':
					DraftAPI::undo();
					wp_redirect( remove_query_arg( 'draft_action' ) );
					die();
					break;
				case 'update_note':
					$player_id = intval( $_REQUEST['player_id'] );
					$note = $_REQUEST['note'];
					echo DraftAPI::update_note( $player_id, $note );
					die();
					break;
			}
		}
	}

}

add_action( 'init', array( FF_Plugin::GetInstance(), 'initialize' ) );

interface iFF_Importer {

	public function run( $args = array( ) );
}

abstract class aFF_Importer implements iFF_Importer {

	protected function find_player( $player_name, $args = array( ), $additionalWhere = '' ) {
		global $wpdb;

		foreach ( $args as $arg => $value ) {
			$additionalWhere .= $wpdb->prepare( "AND {$arg} = %s ", $value );
		}

		$query = $wpdb->prepare( "SELECT player_id " .
				"FROM " . DraftAPI::$players_table . " " .
				"WHERE player_name like %s ", $player_name ) .
			$additionalWhere .
			"LIMIT 1";

		$player_id = $wpdb->get_var( $query );
		if ( !$player_id )
			var_dump( $query );
		return $player_id;
	}

}

class ESPN_Player_Importer extends aFF_Importer {

	public function run( $args = array( ) ) {
		global $wpdb;
		$base_url = 'http://games.espn.go.com/ffl/tools/projections?&leagueId=93130';
		$wpdb->query( "DELETE FROM wp_player_rankings where ranker_key = 'espn'" );
		$max_players = 1045;
		$player_offset = 0;
		while ( $player_offset < $max_players ) {
			$response = wp_remote_get( add_query_arg( array( 'startIndex' => $player_offset ), $base_url ) );
			$content = wp_remote_retrieve_body( $response );
			$content = mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" );
			$dom = new DOMDocument();
			@$dom->loadHTML( $content );

			$player_table = $dom->getElementById( 'playertable_0' );
			if ( is_null( $player_table ) ) {
				break;
			}
			//$player_table = new DOMElement();
			$trs = $player_table->getElementsByTagName( 'tr' );
			foreach ( $trs as $tr ) {
				//$tr = new DOMElement();
				$class = $tr->getAttribute( 'class' );
				if ( false !== strpos( $class, 'pncPlayerRow' ) ) {
					$espn_id = str_replace( 'plyr', '', $tr->getAttribute( 'id' ) );
					$cells = $tr->getElementsByTagName( 'td' );

					$rank = $cells->item( 0 )->nodeValue;
					$playername = $cells->item( 1 )->nodeValue;
					$name_parts = explode( ' ', str_replace( array( '*', '&Acirc;&nbsp;' ), ' ', htmlentities( $playername ) ) );
					if ( in_array( $name_parts[count( $name_parts ) - 1], array( 'P', 'Q', 'O', 'D', 'IR', 'SSPD' ) ) ) {
						array_pop( $name_parts );
						array_pop( $name_parts );
					}
					$position = str_replace( 'D/ST', 'DST', array_pop( $name_parts ) );
					$team = array_pop( $name_parts );
					$playername = join( ' ', $name_parts );
					$playername = substr( $playername, 0, strlen( $playername ) - 1 );

					$wpdb->query( $wpdb->prepare(
								"INSERT INTO " . DraftAPI::$players_table .
								" (player_id, player_name, player_position, team_name) " .
								" VALUES (%d, %s, %s, %s)" .
								" ON DUPLICATE KEY UPDATE player_position=values(player_position), team_name=values(team_name)", $espn_id, $playername, $position, $team
						) );
					if ( $wpdb->last_error ) {
						var_dump( $wpdb->last_error );
					}

					if ( !$wpdb->insert( 'wp_player_rankings', array( 'ranker_key' => 'espn', 'player_order' => $rank, 'player_id' => $espn_id ) ) ) {
						var_dump( $wpdb->last_error );
					}

					if ( (++$player_offset) >= $max_players ) {
						break 2;
					}
				}
			}
			sleep( 2 );
		}
		die( "DONE!!" );
		return true;
	}

}

class ESPN_Analyst_Importer extends aFF_Importer {

	public function run( $args = array( ) ) {
		global $wpdb;
		$defaults = array(
			'analyst' => '2013preseasonFFLranks250'
		);
		$args = wp_parse_args( $args, $defaults );
		$ranker_key = $args['analyst'];
		$base_url = 'http://espn.go.com/fantasy/football/story/_/page/' . $ranker_key;
		$wpdb->query( $wpdb->prepare( "DELETE FROM wp_player_rankings where ranker_key = %s", $ranker_key ) );
		$rank = 1;
		$response = wp_remote_get( $base_url );
		$content = wp_remote_retrieve_body( $response );
		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" );

		register_post_type( $base_url );
		preg_match_all( '#http://espn.go.com/nfl/player/_/id/([^/]+)/([^"]*)"[^>]*#s', $content, $matches );
		foreach ( $matches[1] as $player_id ) {
			if ( !$wpdb->insert( 'wp_player_rankings', array( 'ranker_key' => $ranker_key, 'player_order' => $rank, 'player_id' => $player_id ) ) ) {
				var_dump( $wpdb->last_error );
			}
			$rank++;
		}
		die( "DONE!!" );
		return true;
	}

}

class ESPN_ADP_Importer extends aFF_Importer {

	public function run( $args = array( ) ) {
		global $wpdb;
		$args = wp_parse_args( $args, array( 'ranker_key' => 'adp' ) );
		$positions = array( 'QB', 'RB', 'WR', 'TE', 'D/ST', 'K' );

		$base_url = 'http://games.espn.go.com/ffl/livedraftresults?position=';
		$year = date( 'Y' );
		$adp_key = "adp_{$year}_espn";

		$wpdb->query( $wpdb->prepare( "DELETE FROM " . DraftAPI::$player_meta_table . " where meta_key = %s", $adp_key ) );

		foreach ( $positions as $position ) {
			$response = wp_remote_get( $base_url . $position );
			$content = wp_remote_retrieve_body( $response );
			$content = mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" );
			$table_start = '<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tableBody" bgcolor="#ffffff" >';

			$content = substr( $content, strpos( $content, $table_start ) + strlen( $table_start ) + 1 );
			$table_content = substr( $content, 0, strpos( $content, '</table>' ) );
			$rows = split( '</tr><tr', $table_content );
			array_shift( $rows );
			array_shift( $rows );
			array_shift( $rows );
			foreach ( $rows as $row ) {
				$row = substr( $row, strpos( $row, 'playerId="' ) + strlen( 'playerId="' ) );
				$player_id = substr( $row, 0, strpos( $row, '"' ) );

				$row = substr( $row, strpos( $row, ';">' ) + strlen( ';">' ) );
				$adp = floatval( substr( $row, 0, strpos( $row, '<' ) ) );
				if ( $adp == 0 )
					continue;
				$wpdb->insert( DraftAPI::$player_meta_table, array( 'player_id' => $player_id, 'meta_key' => $adp_key, 'value' => $adp ) );
			}
		}

		die( "DONE!!" );
		return true;
	}

}

class Fix_My_Ranks_Importer extends aFF_Importer {

	public function run( $args = array( ) ) {
		global $wpdb;

		$year = date( 'Y' );
		$adp_key = "adp_{$year}_espn";
		$ranker_key = 'mike';

		$sql = $wpdb->prepare( "SELECT M.player_id, M.value " .
			"FROM " . DraftAPI::$player_meta_table . " M " .
			"LEFT JOIN " . DraftAPI::$player_rankings_table . " R ON R.player_id = M.player_id AND R.ranker_key = %s " .
			"WHERE M.meta_key = %s AND R.player_id is null AND cast(M.value as unsigned) < 170 ", $ranker_key, $adp_key );

		$players = $wpdb->get_results( $sql );
		if ( count( $players ) ) {
			foreach ( $players as $player ) {
				$ffPlayer = DraftAPI::get_player( $player->player_id );
				var_dump( sprintf( "Adding %s at %d", $ffPlayer->player_name, $player->value ) );
				$wpdb->insert( DraftAPI::$player_rankings_table, array( 'ranker_key' => $ranker_key, 'player_id' => $player->player_id, 'player_order' => intval( $player->value ) ) );
			}
		} else {
			var_dump( "No players to add." );
		}

		$sql = $wpdb->prepare( "SELECT R.player_id, R.player_order " .
			"FROM " . DraftAPI::$player_rankings_table . " R " .
			"LEFT JOIN " . DraftAPI::$player_meta_table . " M ON R.player_id = M.player_id AND M.meta_key = %s " .
			"WHERE R.ranker_key = %s AND (M.player_id is null OR cast(M.value as unsigned) >= 170) ", $adp_key, $ranker_key );

		$players = $wpdb->get_results( $sql );
		if ( count( $players ) ) {
			foreach ( $players as $player ) {
				$ffPlayer = DraftAPI::get_player( $player->player_id );
				if ( $player->player_order < 170 ) {
					var_dump( sprintf( "Consider Removing %s at %d", $ffPlayer->player_name, $player->player_order ) );
				}
			}
		}
		die();
	}

}

class My_Default_Rank_Importer extends aFF_Importer {

	public function run( $args = array( ) ) {
		die( "THIS NEEDS TO BE CHANGED TO MATCH LATEST DB CONFIG" );
		global $wpdb;
		$args = wp_parse_args( $args, array( 'ranker_key' => 'mike' ) );
		$positions = array( 'QB', 'RB', 'WR', 'TE', 'D/ST', 'K' );

		$base_url = 'http://games.espn.go.com/ffl/livedraftresults?position=';
		$wpdb->query( "UPDATE wp_players set adp = 999" );

		foreach ( $positions as $position ) {
			$response = wp_remote_get( $base_url . $position );
			$content = wp_remote_retrieve_body( $response );
			$content = mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" );
			$table_start = '<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tableBody" bgcolor="#ffffff" >';

			$content = substr( $content, strpos( $content, $table_start ) + strlen( $table_start ) + 1 );
			$table_content = substr( $content, 0, strpos( $content, '</table>' ) );
			$rows = split( '</tr><tr', $table_content );
			array_shift( $rows );
			array_shift( $rows );
			array_shift( $rows );
			foreach ( $rows as $row ) {
				$row = substr( $row, strpos( $row, 'playerId="' ) + strlen( 'playerId="' ) );
				$player_id = substr( $row, 0, strpos( $row, '"' ) );

				$row = substr( $row, strpos( $row, ';">' ) + strlen( ';">' ) );
				$adp = floatval( substr( $row, 0, strpos( $row, '<' ) ) );
				$sql = $wpdb->prepare( "UPDATE wp_players set adp = '{$adp}' WHERE player_id = %d", $player_id );
				$wpdb->query( $sql );
			}
		}

		//now update the rankings for adp, this is separate so I can easily set my rankings based on adp
		//and adjust my ranks based on my value to adp
		$wpdb->query( $wpdb->prepare( "DELETE FROM wp_player_rankings where ranker_key = %s", $args['ranker_key'] ) );
		$rank = 1;
		$player_ids = $wpdb->get_col( 'SELECT player_id from wp_players order by adp LIMIT 300' );

		foreach ( $player_ids as $player_id ) {
			if ( !$wpdb->insert( 'wp_player_rankings', array( 'ranker_key' => $args['ranker_key'], 'player_order' => $rank, 'player_id' => $player_id ) ) ) {
				var_dump( $wpdb->last_error );
			}
			$rank++;
		}

		die( "DONE!!" );
		return true;
	}

}

class ESPN_Projection_Importer extends aFF_Importer {

	public function run( $args = array( ) ) {
		global $wpdb;
		$base_url = 'http://games.espn.go.com/ffl/tools/projections?display=alt&leagueId=93130&seasonId=%1$d';
		$args = wp_parse_args( $args, array(
			'year' => Date( 'Y' )
			) );
		$year = intval( $args['year'] );

		$points_key = "points_" . ($year - 1);
		$projection_key = "projection_{$year}_espn";

		$base_url = sprintf( $base_url, $year );

		$wpdb->query( $wpdb->prepare( "DELETE FROM " . DraftAPI::$player_meta_table . " where meta_key = %s", $points_key ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . DraftAPI::$player_meta_table . " where meta_key = %s", $projection_key ) );

		$max_players = 1045;
		$player_offset = 0;
		while ( $player_offset < $max_players ) {
			$response = wp_remote_get( add_query_arg( array( 'startIndex' => $player_offset ), $base_url ) );
			$content = wp_remote_retrieve_body( $response );
			$content = mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" );
			$dom = new DOMDocument();
			@$dom->loadHTML( $content );
			$player_tables = $dom->getElementsByTagName( 'table' );
			for ( $i = 0; $i < $player_tables->length; $i++ ) {
				$player_table = $player_tables->item( $i );
				if ( false !== strpos( $player_table->getAttribute( 'class' ), 'tableBody' ) ) {
					$trs = $player_table->getElementsByTagName( 'tr' );
					$links = $trs->item( 0 )->getElementsByTagName( 'a' );
					$player_id = false;
					for ( $j = 0; $j < $links->length; $j++ ) {
						if ( $player_id = $links->item( $j )->getAttribute( 'playerid' ) ) {
							break;
						}
					}
					if ( !$player_id ) {
						var_dump( $player_table->textContent );
						die( "NO PLAYER ID" );
					}
					;
					$tds = $trs->item( 1 )->getElementsByTagName( 'td' );
					$points = $tds->item( $tds->length - 1 )->nodeValue;

					$tds = $trs->item( 2 )->getElementsByTagName( 'td' );
					$projection = $tds->item( $tds->length - 1 )->nodeValue;

					$wpdb->insert( DraftAPI::$player_meta_table, array( 'player_id' => $player_id, 'meta_key' => $points_key, 'value' => $points ) );
					$wpdb->insert( DraftAPI::$player_meta_table, array( 'player_id' => $player_id, 'meta_key' => $projection_key, 'value' => $projection ) );

					if ( (++$player_offset) >= $max_players ) {
						break 2;
					}
				}
			}
			sleep( 2 );
		}
		die( "DONE!!" );
		return true;
	}

}

class FFToday_Projection_Importer extends aFF_Importer {

	public function run( $args = array( ) ) {
		global $wpdb;

		$positions = array(
			'QB' => '10',
			'RB' => '20',
			'WR' => '30',
			'TE' => '40',
			'DST' => '99',
			'K' => '80'
		);

		$base_url = 'http://www.fftoday.com/rankings/playerproj.php?LeagueID=26955';

		$year = Date( 'Y' );

		$projection_key = "projection_{$year}_fftoday";

		$base_url = sprintf( $base_url, $year );

		$wpdb->query( $wpdb->prepare( "DELETE FROM " . DraftAPI::$player_meta_table . " where meta_key = %s", $projection_key ) );

		foreach ( $positions as $position => $posID ) {
			$response = wp_remote_get( add_query_arg( array( 'PosID' => $posID ), $base_url ) );
			$content = wp_remote_retrieve_body( $response );
			$content = mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" );
			$dom = new DOMDocument();
			@$dom->loadHTML( $content );
			$projection_table = $dom->getElementsByTagName( 'table' )->item( 3 )->getElementsByTagName( 'table' )->item( 5 )->getElementsByTagName( 'table' )->item( 0 );
			$trs = $projection_table->getELementsByTagName( 'tr' );
			for ( $i = 2; $i < $trs->length; $i++ ) {
				$tds = $trs->item( $i )->getElementsByTagName( 'td' );
				$name = $tds->item( 1 )->getElementsByTagName( 'a' )->item( 0 )->textContent;
				if ( $name == 'Robert Housler' ) {
					$name = 'Rob Housler';
				} elseif ( $name == 'Steve Hauschka' ) {
					$name = 'Steven Hauschka';
				}
				$additionalWhere = '';
				if ( $position == 'DST' ) {
					$additionalWhere = $wpdb->prepare( ' AND %s', $name ) . ' like concat(\'%\', player_name, \'%\') ';
					$name = '%';
				}
				$player_id = $this->find_player( $name, array( 'player_position' => $position ), $additionalWhere );
				if ( !$player_id )
					var_dump( $name );

				$projection = intval( $tds->item( $tds->length - 1 )->nodeValue );
				$wpdb->insert( DraftAPI::$player_meta_table, array( 'player_id' => $player_id, 'meta_key' => $projection_key, 'value' => $projection ) );
			}

			sleep( 2 );
		}
		die( "DONE!!" );
		return true;
	}

}

class FantasyPros_Projection_Importer extends aFF_Importer {

	public function run( $args = array( ) ) {
		global $wpdb;

		$positions = array(
			'QB',
			'RB',
			'WR',
			'TE',
			'K'
		);

		$base_url = 'http://www.fantasypros.com/nfl/projections/%s.php';

		$year = Date( 'Y' );

		$projection_key = "projection_{$year}_fantasypros";

		$wpdb->query( $wpdb->prepare( "DELETE FROM " . DraftAPI::$player_meta_table . " where meta_key = %s", $projection_key ) );

		foreach ( $positions as $position ) {
			$response = wp_remote_get( sprintf( $base_url, strtolower( $position ) ) );
			$content = wp_remote_retrieve_body( $response );
			$content = mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" );
			$dom = new DOMDocument();
			@$dom->loadHTML( $content );
			$projection_table = $dom->getElementsByTagName( 'table' )->item( 1 );
			$trs = $projection_table->getELementsByTagName( 'tr' );
			for ( $i = 2; $i < $trs->length; $i++ ) {
				$tds = $trs->item( $i )->getElementsByTagName( 'td' );
				$name = $tds->item( 0 )->getElementsByTagName( 'a' )->item( 0 )->textContent;
				if ( $name == 'E.J. Manuel' ) {
					$name = 'EJ Manuel';
				} elseif ( $name == 'Christopher Ivory' ) {
					$name = 'Chris Ivory';
				} elseif ( $name == 'Ty Hilton' ) {
					$name = 'T.Y. Hilton';
				} elseif ( $name == 'Robert Housler' ) {
					$name = 'Rob Housler';
				}
				$projection = intval( $tds->item( $tds->length - 1 )->nodeValue );
				if ( $projection > 50 ) {
					$player_id = $this->find_player( $name, array( 'player_position' => $position ) );

					if ( !$player_id ) {
						var_dump( $name . ' ----- ' . $projection );
						continue;
					}
					$wpdb->insert( DraftAPI::$player_meta_table, array( 'player_id' => $player_id, 'meta_key' => $projection_key, 'value' => $projection ) );
				}


				//
			}

			sleep( 2 );
		}
		die( "DONE!!" );
		return true;
	}

}

class FantasySharks_Projection_Importer extends aFF_Importer {

	public function run( $args = array( ) ) {
		global $wpdb;

		$positions = array(
			'QB' => 'QB',
			'RB' => 'RB',
			'WR' => 'WR',
			'TE' => 'TE',
			'Def' => 'DST',
			'PK' => 'K'
		);

		$base_url = 'http://www.fantasysharks.com/apps/Projections/SeasonProjections.php?pos=ALL&l=12';

		$year = Date( 'Y' );

		$adp_key = "adp_{$year}_fantasysharks";
		$projection_key = "projection_{$year}_fantasysharks";

		$wpdb->query( $wpdb->prepare( "DELETE FROM " . DraftAPI::$player_meta_table . " where meta_key = %s", $projection_key ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . DraftAPI::$player_meta_table . " where meta_key = %s", $adp_key ) );

		$response = wp_remote_get( $base_url );
		$content = wp_remote_retrieve_body( $response );
		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" );
		$dom = new DOMDocument();
		@$dom->loadHTML( $content );

		$projection_table = $dom->getElementsByTagName( 'table' )->item( 2 )->getElementsByTagName( 'table' )->item( 2 );
		$rows = $projection_table->getElementsByTagName( 'tr' );
		for ( $i = 2; $i < $rows->length; $i++ ) {
			$tds = $rows->item( $i )->getElementsByTagName( 'td' );
			if ( $tds->length == 1 ) {
				continue;
			}
			$position = $positions[$tds->item( 2 )->nodeValue];
			$player_name = $tds->item( 3 )->nodeValue;
			$player_name = explode( ',', $player_name );
			$player_name = $player_name[1] . ' ' . $player_name[0];

			if ( $player_name == 'E.J. Manuel' ) {
				$player_name = 'EJ Manuel';
			} else if ( $player_name == 'Stevie Johnson' ) {
				$player_name = 'Steve Johnson';
			} else if ( $player_name == 'Robert Housler' ) {
				$player_name = 'Rob Housler';
			}

			$adp = intval( $tds->item( 1 )->nodeValue );
			if ( !$adp || $adp > 200 ) {
				continue;
			}

			$projection = intval( $tds->item( 18 )->nodeValue );

			$additionalWhere = false;
			if ( $position == 'DST' ) {
				$additionalWhere = $wpdb->prepare( ' AND %s', $player_name ) . ' like concat(\'%\', player_name, \'%\') ';
				$player_name = '%';
			}
			$player_id = $this->find_player( $player_name, array( 'player_position' => $position ), $additionalWhere );
			if ( $player_id ) {
				$wpdb->insert( DraftAPI::$player_meta_table, array( 'player_id' => $player_id, 'meta_key' => $projection_key, 'value' => $projection ) );
				$wpdb->insert( DraftAPI::$player_meta_table, array( 'player_id' => $player_id, 'meta_key' => $adp_key, 'value' => $adp ) );
			} else {
				var_dump( "Couldn't find Player: {$player_name} - adp: $adp - proj: $projection" );
				var_dump( $rows[$i]->textContent );
			}
		}

		die( "DONE!!" );
		return true;
	}

}

class FantasyPros_ADP_Importer extends aFF_Importer {

	public function run( $args = array( ) ) {
		global $wpdb;
		$args = wp_parse_args( $args, array( 'ranker_key' => 'adp' ) );
		$positions = array( 'QB', 'RB', 'WR', 'TE', 'DST', 'K' );

		$base_url = 'http://www.fantasypros.com/nfl/rankings/consensus-cheatsheets.php';
		$year = date( 'Y' );
		$adp_key = "adp_{$year}_fantasypros";
		$std_key = "stddev_{$year}_fantasypros";
		$ranker_key = 'FantasyProAVG_2013';

		$wpdb->query( $wpdb->prepare( "DELETE FROM " . DraftAPI::$player_meta_table . " where meta_key = %s", $adp_key ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . DraftAPI::$player_meta_table . " where meta_key = %s", $std_key ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . DraftAPI::$player_rankings_table . " where ranker_key = %s", $ranker_key ) );



		$response = wp_remote_get( $base_url );
		$content = wp_remote_retrieve_body( $response );
		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" );
		$dom = new DOMDocument();
		@$dom->loadHTML( $content );
		$projection_table = $dom->getElementsByTagName( 'table' )->item( 1 );
		$trs = $projection_table->getELementsByTagName( 'tr' );
		for ( $i = 1; $i < $trs->length; $i++ ) {
			$tds = $trs->item( $i )->getElementsByTagName( 'td' );
			$name = $tds->item( 1 )->getElementsByTagName( 'a' )->item( 0 )->textContent;
			if ( $name == 'E.J. Manuel' ) {
				$name = 'EJ Manuel';
			} elseif ( $name == 'Christopher Ivory' ) {
				$name = 'Chris Ivory';
			} elseif ( $name == 'Ty Hilton' ) {
				$name = 'T.Y. Hilton';
			} elseif ( $name == 'Robert Housler' ) {
				$name = 'Rob Housler';
			} elseif ( $name == 'Chris "Beanie" Wells' ) {
				$name = 'Beanie Wells';
			}
			$adp = intval( $tds->item( 7 )->nodeValue );
			$std = intval( 10 * floatval( $tds->item( 6 )->nodeValue ) );
			$order = intval( $tds->item( 5 )->nodeValue );
			$position = preg_replace( '/[^\\/\-a-z\s]/i', '', $tds->item( 2 )->nodeValue );

			if ( $adp < 300 ) {
				$additionalWhere = false;
				if ( $position == 'DST' ) {
					$additionalWhere = $wpdb->prepare( ' AND %s', $name ) . ' like concat(\'%\', player_name, \'%\') ';
					$name = '%';
				}
				$player_id = $this->find_player( $name, array( 'player_position' => $position ), $additionalWhere );

				if ( !$player_id ) {
					var_dump( $name . ' ----- ' . $adp );
					continue;
				}
				$wpdb->insert( DraftAPI::$player_meta_table, array( 'player_id' => $player_id, 'meta_key' => $adp_key, 'value' => $adp ) );
				$wpdb->insert( DraftAPI::$player_meta_table, array( 'player_id' => $player_id, 'meta_key' => $std_key, 'value' => $std ) );
				$wpdb->insert( DraftAPI::$player_rankings_table, array( 'player_id' => $player_id, 'ranker_key' => $ranker_key, 'player_order' => $order ) );
			}
		}

		die( "DONE!!" );
		return true;
	}

}

class FFCalculator_ADP_Importer extends aFF_Importer {

	public function run( $args = array( ) ) {
		global $wpdb;
		$args = wp_parse_args( $args, array( 'ranker_key' => 'adp' ) );
		$positions = array(
			'QB' => 'QB',
			'RB' => 'RB',
			'WR' => 'WR',
			'TE' => 'TE',
			'DEF' => 'DST',
			'PK' => 'K'
		);

		$defs = array(
			'Seattle Defense' => 'Seahawk',
			'San Francisco Defense' => '49er',
			'Houston Defense' => 'Texan',
			'Chicago Defense' => 'Bear',
			'Denver Defense' => 'Bronco',
			'Cincinnati Defense' => 'Bengal',
			'New England Defense' => 'Patriot',
			'Baltimore Defense' => 'Raven',
			'Arizona Defense' => 'Cardinal',
			'Pittsburgh Defense' => 'Steeler',
			'Tampa Bay Defense' => 'Buccaneer',
			'St. Louis Defense' => 'Ram',
		);

		$base_url = 'http://fantasyfootballcalculator.com/adp.php?teams=8';
		$year = date( 'Y' );
		$adp_key = "adp_{$year}_ffcalculator";
		$std_key = "stddev_{$year}_ffcalculator";

		$wpdb->query( $wpdb->prepare( "DELETE FROM " . DraftAPI::$player_meta_table . " where meta_key = %s", $adp_key ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . DraftAPI::$player_meta_table . " where meta_key = %s", $std_key ) );

		$xml = simplexml_load_file( 'http://fantasyfootballcalculator.com/adp_xml.php?teams=8' );
		$info = array_shift( $xml->xpath( 'adp_info' ) );
		$total_drafts = intval( $info->total_drafts );

		$response = wp_remote_get( $base_url );
		$content = wp_remote_retrieve_body( $response );
		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" );
		$dom = new DOMDocument();
		@$dom->loadHTML( $content );
		$projection_table = $dom->getElementsByTagName( 'table' )->item( 1 );

		$trs = $projection_table->getELementsByTagName( 'tr' );
		for ( $i = 1; $i < $trs->length; $i++ ) {
			$tds = $trs->item( $i )->getElementsByTagName( 'td' );
			$name = $tds->item( 2 )->textContent;
			if ( $name == 'E.J. Manuel' ) {
				$name = 'EJ Manuel';
			} elseif ( $name == 'Christopher Ivory' ) {
				$name = 'Chris Ivory';
			} elseif ( $name == 'Ty Hilton' ) {
				$name = 'T.Y. Hilton';
			} elseif ( $name == 'Robert Housler' ) {
				$name = 'Rob Housler';
			} elseif ( $name == 'Chris "Beanie" Wells' ) {
				$name = 'Beanie Wells';
			} elseif ( $name == 'LeVeon Bell' ) {
				$name = 'Le\'Veon Bell';
			} elseif ( $name == 'Stevie Johnson' ) {
				$name = 'Steve Johnson';
			}

			$adp = intval( $tds->item( 6 )->nodeValue );
			$std = intval( 10 * floatval( $tds->item( 7 )->nodeValue ) );
			$drafted = intval( $tds->item( 10 )->nodeValue );
			$perDrafted = $drafted / $total_drafts;
			//adjust for players not getting drafted much and have skewed adps
			if ( $perDrafted < 0.2 ) {
				$adp = ($adp * $perDrafted) + (160 * (1 - $perDrafted));
				$std = intval( $std * (2 - $perDrafted) );
			}
			$position = $positions[$tds->item( 3 )->nodeValue];

			if ( $position === 'DST' ) {
				$name = $defs[$name];
			}

			$player_id = $this->find_player( $name, array( 'player_position' => $position ) );

			if ( !$player_id ) {
				var_dump( $name . ' ----- ' . $adp );
				continue;
			}
			$wpdb->insert( DraftAPI::$player_meta_table, array( 'player_id' => $player_id, 'meta_key' => $adp_key, 'value' => $adp ) );
			$wpdb->insert( DraftAPI::$player_meta_table, array( 'player_id' => $player_id, 'meta_key' => $std_key, 'value' => $std ) );
		}

		die( "DONE!!" );
		return true;
	}

}
<?php

require_once __DIR__ . '/inc/fantasy-football/fantasy-football.php';

if ( !function_exists( 'fantasy_football_setup' ) ) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function fantasy_football_setup() {

		add_action( 'wp_enqueue_scripts', 'fantasy_football_scripts' );

		add_filter( 'show_admin_bar', '__return_false' );
	}

endif; // fantasy_football_setup
add_action( 'after_setup_theme', 'fantasy_football_setup' );

/**
 * Enqueue scripts and styles.
 */
function fantasy_football_scripts() {
	if ( !defined( 'DOING_FF_OLD' ) ) {
		$template_dir = get_template_directory_uri();
		wp_enqueue_style( 'fantasy-footballstyle', get_stylesheet_uri() );

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			wp_enqueue_script( 'bootstrap', $template_dir . '/js/libs/bootstrap.js', array( 'jquery' ), '3.0.0', true );
			wp_enqueue_script( 'fantasy_football', $template_dir . '/js/main.js', array( 'jquery', 'bootstrap' ), '0.1.0', true );
		} else {
			wp_enqueue_script( 'bootstrap', $template_dir . '/js/libs/bootstrap.min.js', array( 'jquery' ), '3.0.0', true );
			wp_enqueue_script( 'fantasy_football', $template_dir . '/js/main.min.js', array( 'jquery', 'bootstrap' ), '0.1.0', true );
		}
		wp_enqueue_script( 'modernizr', $template_dir . '/js/libs/modernizr.min.js', array(), false, false );

		wp_register_script( 'es5-shim', $template_dir . '/js/libs/es5-shim.min.js', array(), false, true );
		wp_register_script( 'es5-sham', $template_dir . '/js/libs/es5-sham.min.js', array(), false, true );
		wp_register_script( 'console-polyfill', $template_dir . '/js/libs/console-polyfill.js', array(), false, true );
		wp_register_script( 'reactjs', $template_dir . '/js/libs/react-with-addons.js', array( 'console-polyfill', 'es5-shim', 'es5-sham' ), false, true );
		wp_enqueue_script( 'react-components', $template_dir . '/js/components.js', array( 'reactjs', 'jquery', 'underscore', 'wp-util' ), false, true );
	}
}

add_action( 'template_redirect', function() {
	if ( isset( $_REQUEST['switch_view'] ) ) {
		switch ( $_REQUEST['switch_view'] ) {
			case 'draft_orders':
				include __DIR__ . '/old/draft-orders.php';
				die();
				break;
			case 'compare':
				include __DIR__ . '/old/rank-compare.php';
				die();
				break;
		}
	}
} );

function ajax_get_players() {
	$playerObjs = array_map( function($player) {
		return DraftAPI::get_player( $player->player_id );
	}, DraftAPI::get_players( array( 'ranker_key' => 'mike' ) ) );

	$players = [];
	$year = date('Y');
	foreach($playerObjs as $player) {
		$players[] = [
			'id' => $player->player_id,
			'name' => $player->player_name,
			'position'=> $player->player_position,
			'team' => $player->team_name,
			'adp_espn' => (float) ($player->getMeta('adp_'.$year.'_espn') ?: 300),
			'rank_espn' => (float) $player->getMeta('espn_rank_'.$year),
			'adp_fp' => (float) ($player->getMeta('adp_'.$year.'_fantasypros') ?: 300),
			'rank_fp' => (float) ($player->getMeta('avgrank_'.$year.'_fantasypros') ?: 300),
			'projection' => (float) $player->getProjection()
		];
	}
	wp_send_json_success(['players'=> $players]);
}

add_action( 'wp_ajax_nopriv_ff_get_players', 'ajax_get_players' );
add_action( 'wp_ajax_ff_get_players', 'ajax_get_players' );

function ajax_get_draft_picks() {
	$picks = DraftAPI::get_draft_picks();
	wp_send_json_success(['picks'=> $picks]);
}

add_action( 'wp_ajax_nopriv_ff_get_draft_picks', 'ajax_get_draft_picks' );
add_action( 'wp_ajax_ff_get_draft_picks', 'ajax_get_draft_picks' );

function ajax_get_teams() {
	$teams = DraftAPI::get_draft_teams();
	$teams = array_map(function($team){
		return [
			'name' => ucfirst($team->team_key),
			'key' => $team->team_key
		];
	}, $teams);
	wp_send_json_success(['teams' => $teams]);
}
add_action( 'wp_ajax_nopriv_ff_get_draft_teams', 'ajax_get_teams' );
add_action( 'wp_ajax_ff_get_draft_teams', 'ajax_get_teams' );
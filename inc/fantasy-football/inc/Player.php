<?php

namespace Prettyboymp\FantasyFootball;

use \DraftAPI;

class Player {

	public $player_id;

	public function __construct( $player_id ) {
		$this->player_id = $player_id;
	}

	public function __get( $field ) {
		global $wpdb;
		$player_data = wp_cache_get( 'player_data_' . $this->player_id );
		if ( !is_array( $player_data ) ) {
			$player_data = $wpdb->get_row( $wpdb->prepare( "SELECT * from " . DraftAPI::$players_table
					. " WHERE player_id = %d", $this->player_id ) );
			wp_cache_set( 'player_data_' . $this->player_id, $player_data );
		}
		return isset( $player_data->$field ) ? $player_data->$field : null;
	}

	public function getMeta( $key = null ) {
		global $wpdb;
		$player_meta = wp_cache_get( 'player_meta_' . $this->player_id );
		if ( !is_array( $player_meta ) ) {
			$player_meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, value from " . DraftAPI::$player_meta_table
					. " WHERE player_id = %d", $this->player_id ), OBJECT_K );
			wp_cache_set( 'player_meta_' . $this->player_id, $player_meta );
		}
		if ( is_null( $key ) ) {
			return $player_meta;
		} else {
			return isset( $player_meta[$key] ) ? $player_meta[$key]->value : null;
		}
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

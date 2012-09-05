<?php
/*
Plugin Name: Fantasy Football
Plugin URI: http://vocecommunications.com/#
Description: A fantasy football plugin
Author: Michael Pretty (prettyboymp)
Version: 0.1
Author URI: http://prettyboymp.wordpress.com
*/

require_once(dirname(__FILE__).'/draft-functions.php');

class FF_Plugin {
	private static $instance;

	public static function GetInstance() {
		if(!isset(self::$instance)) {
			self::$instance = new FF_Plugin();
		}
		return self::$instance;
	}

	public function initialize() {
		$this->handle_ajax();

		add_action('admin_menu', array($this, 'add_menu_items'));
	}

	public function add_menu_items() {
		add_menu_page('Fantasy Fooball', 'Fant. Football', 'edit_posts', 'fantasy-football-options', array($this, 'admin_options_page'));
		$hook = add_submenu_page('fantasy-football-options', 'Options', 'Options', 'edit_posts', 'fantasy-football-options', array($this, 'admin_options_page'));
		add_action('load-'.$hook, array($this, 'admin_load_options_page'));
		$hook = add_submenu_page('fantasy-football-options', 'Importers', 'Importers', 'edit_posts', 'fantasy-football-importers', array($this, 'admin_importers_page'));
		add_action('load-'.$hook, array($this, 'admin_load_importers_page'));
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
		if(isset($_REQUEST['importer'])) {
			$importer = false;
			switch ($_REQUEST['importer']) {
				case 'espn_player':
					$importer = new ESPN_Player_Importer();
					break;
				case 'espn_analyst':
					$importer = new ESPN_Analyst_Importer();
					break;
				case 'espn_adp':
					$importer = new ESPN_ADP_Importer();
					break;
			}
			if($importer) {
				$importer->run($_REQUEST);
				wp_cache_flush();
			}
		}
	}
	public function admin_importers_page() {
		?>
		<div class="wrap">
			<h2>HELLO</h2>
			<p>
				<a href="<?php echo admin_url('admin.php?page=fantasy-football-importers&importer=espn_player');?>">Run ESPN Player Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url('admin.php?page=fantasy-football-importers&importer=espn_analyst&analyst=NFLDK2K10rankstop200');?>">Run ESPN Default Ranking Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url('admin.php?page=fantasy-football-importers&importer=espn_analyst&analyst=NFLDK2K12ranksHarris200');?>">Run ESPN Christopher Harris Ranking Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url('admin.php?page=fantasy-football-importers&importer=espn_analyst&analyst=NFLDK2K12ranksBerry200');?>">Run ESPN Mathew Berry Ranking Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url('admin.php?page=fantasy-football-importers&importer=espn_analyst&analyst=NFLDK2K12ranksKarabell200');?>">Run ESPN Eric Karabell Ranking Importer</a>
			</p>
			<p>
				<a href="<?php echo admin_url('admin.php?page=fantasy-football-importers&importer=espn_adp');?>">Run ESPN ADP Importer</a>
			</p>
		</div>
		<?php
	}

	public function handle_ajax() {
		if(isset($_REQUEST['draft_action'])) {
			switch ($action = $_REQUEST['draft_action']) {
				case 'draft_player':
					$player_id = $_REQUEST['player_id'];
					$data = array();
					$draft_team = DraftAPI::get_active_draft_team();
					$data['draft_team'] = $draft_team->team_key;
					if(DraftAPI::draft_player($player_id)) {
						$data['success'] = true;
						$data['draft_team_html'] = $draft_team->get_roster_html();
						$data['new_active_team'] = DraftAPI::get_active_draft_team()->team_key;
						$data['player_id'] = $player_id;
						$data['pick_num'] = DraftAPI::get_pick_number();
					} else {
						$data['success'] = false;
					}
					echo json_encode($data);
					die();
					break;
				case 'auto_draft_player':
					$data = array();
					$draft_team = DraftAPI::get_active_draft_team();
					$data['draft_team'] = $draft_team->team_key;
					if($player_id = DraftAPI::auto_draft_player()) {
						$data['success'] = true;
						$data['draft_team_html'] = $draft_team->get_roster_html();
						$data['new_active_team'] = DraftAPI::get_active_draft_team();
						$data['player_id'] = $player_id;
						$data['pick_num'] = DraftAPI::get_pick_number();
					} else {
						$data['success'] = false;
					}
					echo json_encode($data);
					die();
					break;
				case 'reorder_players':
					$new_order = $_REQUEST['new_order'];
					$data = array('success'=> DraftAPI::reorder_players($new_order));
					echo json_encode($data);
					die();
					break;
				case 'restart':
					DraftAPI::start_new_draft();
					wp_redirect(remove_query_arg('draft_action'));
					die();
					break;
				case 'mock_draft': 
					DraftAPI::mock_draft();
					wp_redirect(remove_query_arg('draft_action'));
					die();
					break;
					break;
				case 'undo':
					DraftAPI::undo();
					wp_redirect(remove_query_arg('draft_action'));
					die();
					break;
				case 'update_note':
					$player_id = intval($_REQUEST['player_id']);
					$note = $_REQUEST['note'];
					echo DraftAPI::update_note($player_id, $note);
					die();
					break;
			}
		}
	}
}
add_action('init', array(FF_Plugin::GetInstance(), 'initialize'));

interface iFF_Importer {
	public function run($args = array());
}

abstract class aFF_Importer implements iFF_Importer {

}

class ESPN_Player_Importer extends aFF_Importer {

	public function run($args = array()) {
		global $wpdb;
		$base_url = 'http://games.espn.go.com/ffl/tools/projections?&leagueId=93130';
		$wpdb->query("DELETE FROM wp_player_rankings where ranker_key = 'espn'");
		$max_players = 1045;
		$player_offset = 0;
		while($player_offset < $max_players) {
			$response = wp_remote_get(add_query_arg(array('startIndex' => $player_offset), $base_url));
			$content = wp_remote_retrieve_body($response);
			$content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
			$dom = new DOMDocument();
			@$dom->loadHTML($content);

			$player_table = $dom->getElementById('playertable_0');
			if(is_null($player_table)) {
				break;
			}
			//$player_table = new DOMElement();
			$trs = $player_table->getElementsByTagName('tr');
			foreach($trs as $tr) {
				//$tr = new DOMElement();
				$class = $tr->getAttribute('class');
				if(false !== strpos($class, 'pncPlayerRow')) {
					$espn_id = str_replace('plyr', '', $tr->getAttribute('id'));
					$cells = $tr->getElementsByTagName('td');

					$rank = $cells->item(0)->nodeValue;
					$playername = $cells->item(1)->nodeValue;
					$name_parts = explode(' ', str_replace(array('*', '&Acirc;&nbsp;'), ' ', htmlentities($playername)));
					if(in_array($name_parts[count($name_parts) -1], array('P', 'Q', 'O', 'D', 'IR'))) {
						array_pop($name_parts);
						array_pop($name_parts);

					}
					$position = str_replace('D/ST', 'DST', array_pop($name_parts));
					$team = array_pop($name_parts);
					$playername = join(' ', $name_parts);
					$playername = substr($playername, 0, strlen($playername) -1);

					if(!$wpdb->replace('wp_players', array('player_id'=>$espn_id, 'player_name'=>$playername, 'player_position'=>$position, 'team_name' => $team))) {
						var_dump($wpdb->last_error);
					}

					if(!$wpdb->replace('wp_player_rankings', array('ranker_key'=>'espn', 'player_order'=>$rank, 'player_id' => $espn_id))) {
						var_dump($wpdb->last_error);
					}
				
					if((++$player_offset) >= $max_players) {
						break 2;
					}
				}
			}
			sleep(2);
		}
		die("DONE!!");
		return true;
	}
}

class ESPN_Analyst_Importer extends aFF_Importer {

	public function run($args = array()) {
		global $wpdb;
		$defaults = array(
			'analyst'=> 'NFLDK2K12ranksTop300'
		);
		$args = wp_parse_args($args, $defaults);
		$ranker_key = $args['analyst'];
		$base_url = 'http://sports.espn.go.com/fantasy/football/ffl/story?page='.$ranker_key;
		$wpdb->query($wpdb->prepare("DELETE FROM wp_player_rankings where ranker_key = %s", $ranker_key));
		$rank = 1;
		$response = wp_remote_get($base_url);
		$content = wp_remote_retrieve_body($response);
		$content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
		
		register_post_type($base_url);
		preg_match_all('#http://espn.go.com/nfl/player/_/id/([^/]+)/([^"]*)"[^>]*#s', $content, $matches);
		foreach($matches[1] as $player_id) {
			$player_id = substr($player_id, 0, strlen($player_id) - 1);
			if(!$wpdb->replace('wp_player_rankings', array('ranker_key'=>$ranker_key, 'player_order'=>$rank, 'player_id' => $player_id))) {
				var_dump($wpdb->last_error);
			}
			$rank++;
		}
		die("DONE!!");
		return true;
	}
}

class ESPN_ADP_Importer extends aFF_Importer {

	public function run($args = array()) {
		global $wpdb;

		$positions = array('QB', 'RB', 'WR', 'TE', 'D/ST', 'K');

		$base_url = 'http://games.espn.go.com/ffl/livedraftresults?position=';
		$wpdb->query("UPDATE wp_players set adp = 999");
		
		foreach($positions as $position) {
			$response = wp_remote_get($base_url.$position);
			$content = wp_remote_retrieve_body($response);
			$content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
			$table_start = '<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tableBody" bgcolor="#ffffff" >';

			$content = substr($content, strpos($content, $table_start) + strlen($table_start) + 1);
			$table_content = substr($content, 0, strpos($content, '</table>'));
			$rows = split('</tr><tr', $table_content);
			array_shift($rows);
			array_shift($rows);
			array_shift($rows);
			foreach($rows as $row) {
				$row = substr($row, strpos($row, 'playerId="') + strlen('playerId="'));
				$player_id = substr($row, 0, strpos($row, '"'));

				$row = substr($row, strpos($row, ';">') + strlen(';">'));
				$adp = floatval(substr($row, 0, strpos($row, '<')));
				$sql = $wpdb->prepare("UPDATE wp_players set adp = '{$adp}' WHERE player_id = %d", $player_id);
				$wpdb->query($sql);
			}
		}
		
		//now update the rankings for adp, this is separate so I can easily set my rankings based on adp
		//and adjust my ranks based on my value to adp
		$ranker_key = 'adp';
		$wpdb->query($wpdb->prepare("DELETE FROM wp_player_rankings where ranker_key = %s", $ranker_key));
		$rank = 1;
		$player_ids = $wpdb->get_col('SELECT player_id from wp_players order by adp LIMIT 300');
		
		foreach($player_ids as $player_id) {
			if(!$wpdb->replace('wp_player_rankings', array('ranker_key'=>$ranker_key, 'player_order'=>$rank, 'player_id' => $player_id))) {
				var_dump($wpdb->last_error);
			}
			$rank++;
		}
		
		die("DONE!!");
		return true;
	}
}



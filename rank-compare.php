<?php get_header(); ?>
<?php get_template_part( 'draft-actions' ); ?>
<div id="ranking2" style="padding-top: 20px;">
	<div style="float: left; width: 200px;">
			<h2>My Ranks</h2>
			<ol class="players" id="players">
				<?php foreach ( DraftAPI::get_players( array( 'ranker_key' => 'mike' ) ) as $player ): $ffPlayer = DraftAPI::get_player( $player->player_id ); ?>
					<li id="player-<?php echo $player->player_id ?>" class="<?php echo $player->player_position ?><?php echo is_null( $player->team_key ) ? '' : ' drafted' ?>">
						<div>
							<span><a href="javascript:newWin('http://sports.espn.go.com/nfl/players/fantasy?playerId=<?php echo $player->player_id ?>)"><?php echo $player->player_name ?></a></span>
							<br />
							<?php echo "{$player->player_position} &ndash;  {$player->team_name} &ndash;" ?>
							<?php echo $ffPlayer->getMeta( 'adp_' . date( 'Y' ) . '_espn' ); ?>

							<?php if ( is_null( $player->team_key ) ) : ?>
								<a href="#" class="draft-player" id="draft-<?php echo $player->player_id ?>">draft</a>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php
	$rank_keys = array(
		'adp_2013_ffcalculator' => array( 'name' => 'FFCalculator ADP', 'key' => 'meta_key' ),
		'2013preseasonFFLranks250' => array( 'name' => 'ESPN', 'key' => 'ranker_key' ),
		'NFLDK2K13_Berry_200' => array( 'name' => 'Mathew Berry', 'key' => 'ranker_key' ),
		'NFLDK2K13_Harris_200' => array( 'name' => 'Chris Harris', 'key' => 'ranker_key' ),
		'NFLDK2K13_Karabell_200' => array( 'name' => 'Eric Karabell', 'key' => 'ranker_key' ),
		'FantasyProAVG_2013' => array( 'name' => 'Fantasy Pros AVG', 'key' => 'ranker_key' ),
		'adp_2013_espn' => array( 'name' => 'ESPN ADP', 'key' => 'meta_key' ),
		'adp_2013_fantasysharks' => array( 'name' => 'F Sharks ADP', 'key' => 'meta_key' ),
		//'adp_2013_fantasypros' => array( 'name' => 'Fantasy Pros ADP', 'key' => 'meta_key' ),
	);
	?>
	<?php foreach ( $rank_keys as $rank_key => $info ) : ?>
		<div style="float: left; width: 200px;">
			<h2><?php echo $info['name']; ?></h2>
			<ol class="players">
				<?php foreach ( DraftAPI::get_players( array( $info['key'] => $rank_key ) ) as $player ): $ffPlayer = DraftAPI::get_player( $player->player_id ); ?>
					<li id="player-<?php echo $player->player_id ?>" class="<?php echo $player->player_position ?><?php echo is_null( $player->team_key ) ? '' : ' drafted' ?>"">
						<div>
							<span><a href="javascript:newWin('http://sports.espn.go.com/nfl/players/fantasy?playerId=<?php echo $player->player_id ?>)"><?php echo $player->player_name ?></a></span>
							<br />
							<?php echo "{$player->player_position} &ndash;  {$player->team_name} &ndash;" ?>
							<?php echo $ffPlayer->getMeta( 'adp_' . date( 'Y' ) . '_espn' ); ?>

							<?php if ( is_null( $player->team_key ) ) : ?>
								<a href="#" class="draft-player" id="draft-<?php echo $player->player_id ?>">draft</a>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endforeach; ?>
</div>
<?php
get_footer();
<?php get_header(); ?>
<?php get_template_part( 'draft-actions' ); ?>
<div id="ranking">
	<ol id="players" class="players">
		<?php $players = DraftAPI::get_players( array( 'ranker_key' => 'mike' ) ); ?>
		<?php foreach ( $players as $player ): $ffPlayer = DraftAPI::get_player( $player->player_id ); ?>
			<li id="player-<?php echo $player->player_id ?>" class="player <?php echo $player->player_position ?><?php echo is_null( $player->team_key ) ? '' : ' drafted' ?>"
					data-adp="<?php echo $ffPlayer->getMeta( 'adp_' . date( 'Y' ) . '_ffcalculator' ); ?>" data-std="<?php echo $ffPlayer->getMeta( 'stddev_' . date( 'Y' ) . '_ffcalculator' ); ?>">
				<div>
					<span><a href="javascript:newWin('http://espn.go.com/nfl/player/_/id/<?php echo $player->player_id ?>/')"
									 instance="_ppc" playerid="<?php echo $player->player_id ?>" playeridtype="sportsId" gameroot="ffl" content="tabs#ppc" tab="null" fpopwidth="490px" fpopheight="357px" cache="true"
									 ><?php echo $player->player_name ?></a></span>
						<?php if ( is_null( $player->team_key ) ) : ?>
						&nbsp; &nbsp;
						<a href="#" class="draft-player" id="draft-<?php echo $player->player_id ?>">draft</a>
					<?php endif; ?>
					<br />
					<span class="meta">
						<?php echo "{$player->player_position} &ndash;  {$player->team_name}" ?>
						&nbsp; | &nbsp; ADP: <?php echo round($ffPlayer->getMeta( 'adp_' . date( 'Y' ) . '_ffcalculator' ), 1); ?>
						&nbsp; | &nbsp; LST: <?php echo $ffPlayer->getMeta( 'points_2012' ) ?>
						&nbsp; | &nbsp; PROJ: <?php echo round( $ffPlayer->getProjection(), 1 ) ?>
					</span>

				</div>
				<textarea class="note" id="note-<?php echo $player->player_id ?>" cols="54" rows="2"><?php echo esc_html( $player->note ) ?></textarea>
				<div class="meter">
					<span class="bar" style="width: 75%"></span>
					<div class="per-taken"></div>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
</div>

<div id="teams">
	<?php $teams = DraftAPI::get_draft_teams(); ?>
	<ul>
		<?php foreach ( $teams as $team ) : ?>
			<li>
				<span class="team_name"><?php echo $team->team_key ?></span>
				<ul id="roster-<?php echo $team->team_key ?>">
					<?php echo $team->get_roster_html(); ?>
				</ul>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php
$team = DraftAPI::get_active_draft_team();
?>
<div id="vbd">
	<?php echo $team->get_vbd_html(); ?>
</div>
<div id="upcoming">
	<?php echo DraftAPI::get_upcoming_picks_html(); ?>
</div>
<?php
get_footer();
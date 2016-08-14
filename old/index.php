<?php get_template_part( 'old/header' ); ?>
<?php get_template_part( 'old/draft-actions' ); ?>
<div id="ranking">
	<ol id="players" class="players">
		<?php $players = DraftAPI::get_players( array( 'ranker_key' => 'mike' ) ); ?>
		<?php foreach ( $players as $player ): $ffPlayer = DraftAPI::get_player( $player->player_id ); ?>
			<li id="player-<?php echo $player->player_id ?>" class="player <?php echo $player->player_position ?><?php echo is_null( $player->team_key ) ? '' : ' drafted' ?>"
					data-adp="<?php echo $ffPlayer->getMeta( 'adp_' . date( 'Y' ) . '_espn' ); ?>" data-std="<?php echo $ffPlayer->getMeta( 'stddev_' . date( 'Y' ) . '_ffcalculator' ); ?>">
				<div>
					<div class="playername">
						<span><a href="#" class="flexpop fpop_open_ppc" content="tabs#ppc" instance="_ppc" fpopheight="357px" fpopwidth="490px" tab="null" leagueid="93130" playerid="<?php echo $player->player_id ?>" teamid="1" seasonid="2015" cache="true"><?php echo $player->player_name ?></a></span>
							<?php if ( is_null( $player->team_key ) ) : ?>
							&nbsp; &nbsp;
							<a href="#" class="draft-player" id="draft-<?php echo $player->player_id ?>">draft</a>
						<?php endif; ?>
							<br />
							<?php echo "{$player->player_position} &ndash;  {$player->team_name}" ?>
					</div>
					<div class="meta">
						<span>
							ESPN ADP
							<span class="value"><?php echo round( $ffPlayer->getMeta( 'adp_' . date( 'Y' ) . '_espn' ), 1 ); ?></span>
						</span>
						
						<span>
							Avg. ADP
							<span class="value"><?php echo round( $ffPlayer->getMeta( 'adp_' . date( 'Y' ) . '_fantasypros' ), 1 ); ?></span>
						</span>
						&nbsp; &nbsp; &nbsp; &nbsp; 
						<span>
							ESPN Rank
							<span class="value"><?php echo round( $ffPlayer->getMeta( 'espn_rank_' . date( 'Y' ) ), 1 ); ?></span>
						</span>
						
						<span>
							Avg. Rank
							<span class="value"><?php echo round( $ffPlayer->getMeta( 'avgrank_' . date( 'Y' ) . '_fantasypros' ), 1 ); ?></span>
						</span>
						&nbsp; &nbsp; &nbsp; &nbsp; 
						<span>
							Avg Proj
							<span class="value"><?php echo round( $ffPlayer->getProjection(), 1 ); ?></span>
						</span>
						
					</div>

				</div>
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
get_template_part( 'old/footer' );

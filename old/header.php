<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<title><?php wp_title( '|', true, 'right' ); ?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<link rel="stylesheet" type="text/css" href="http://g.espncdn.com/ffl/static/css/main?v=19138102711">
		<?php
		define( 'DOING_FF_OLD', true );
		wp_enqueue_script( 'main', get_bloginfo( 'template_directory' ) . '/old/main.js', array( 'jquery-ui-sortable', 'jquery' ) );
		wp_enqueue_style( 'main', get_bloginfo( 'template_directory' ) . '/old/style.css' );
		wp_head();
		?>
		<script type="text/javascript" src="http://g.espncdn.com/lm-static/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
		<script type="text/javascript" charset="iso-8859-1" src="http://a.espncdn.com/combiner/c/?js=plugins/json2.r3.js,plugins/teacrypt.js,plugins/jquery.metadata.js,plugins/jquery.bgiframe.r3.js,plugins/jquery.easing.1.3.js,plugins/jquery.hoverIntent.js,plugins/jquery.jcarousel.js,plugins/jquery.tinysort.r4.js,plugins/jquery.pubsub.r5.js,plugins/ba-debug-0.4.js,espn.l10n.r12.js,flashObjWrapper.r7.js,espn.core.duo.r54.js,stub.search.r9.js,espn.nav.mega.r33.js,espn.storage.r6.js,espn.p13n.r15.js,espn.video.r67.js,registration/staticLogin.r12.06102014.js,espn.universal.overlay.r21.js,espn.insider.r5.js,espn.espn360.stub.r9.js,espn.myHeadlines.stub.r13.js,espn.gallery.0628.js,espn.geo.r1.js,avatar-uploader.js,plugins/jquery.colorbox.1.3.14.js,"></script>
		<script src="http://g.espncdn.com/lm-static/libs/prototype/1.6.0.2/prototype.js"></script>
		<script type="text/javascript">

      com = {espn: {
          env: {
            av: navigator.appVersion,
            ua: navigator.userAgent,
            an: navigator.appName,
            platform: navigator.platform,
            IE: "Microsoft Internet Explorer",
            NS: "Netscape",
            MAC: "MacPPC",
            host: "games.espn.go.com",
            context: "freeagency"
          },
          listeners: {},
          games: {
            gameRoot: "ffl",
            leagueId: 93130,
            seasonId: 2015,
            toTeamId: 1,
            fromTeamId: 1,
            scoringPeriodId: 1,
            currentScoringPeriodId: 1,
            leagueType: 0
          }
        }};

      com.espn.games.isLeagueManager = true;
      com.espn.games.isLeagueCreator = true;
      com.espn.games.isLeagueMember = true;
      com.espn.games.userLoggedIn = false;
      com.espn.games.username = 'Mhsouthpaw';
      var GAMEROOT = "ffl";
      var host = "games.espn.go.com";
      var gameRoot = "ffl";
      var leagueId = 93130;
      var teamId = 1;</script><script type="text/javascript" src="http://g.espncdn.com/ffl/static/js/libraries?v=317525"></script>
		<script type="text/javascript">


      function loadPage(redir, leagueId, teamId, pool, recentInput, slotCat) {
//	alert(leagueId + "-" + teamId + "-" + pool + "-" + recentInput + "-" + slotCat);
        if (redir == -1) {
          redir = "http://games.espn.go.com/ffl/freeagency?leagueId=" + leagueId + "&teamId=" + teamId + "&pool=" + pool + "&recent=" + recentInput + "&pos=" + slotCat;
//		alert(redir);
        }
        gotosite(redir);
      }</script>
		<script type="text/javascript">
      var ajax_url = '<?php echo site_url(); ?>';
		</script>
	</head>
	<body <?php body_class(); ?>>
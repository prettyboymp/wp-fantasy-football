<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php	wp_head();?>

<link rel="stylesheet" href="http://g.espncdn.com/fsr/static/css/popPlayerCard" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" href="http://a.espncdn.com/prod/styles/legacy.min.200811061403.css" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" href="http://a.espncdn.com/prod/styles/playerpopup1.css" type="text/css" media="screen" charset="utf-8" />

<script src="http://a.espncdn.com/combiner/c?js=json2.r3.js,plugins/teacrypt.js,plugins/jquery.metadata.js,plugins/jquery.bgiframe.r3.js,plugins/jquery.easing.1.3.js,plugins/jquery.hoverIntent.js,plugins/jquery.jcarousel.js,plugins/jquery.tinysort.r4.js,plugins/jquery.pubsub.r5.js,ui/1.8.16/jquery.ui.core.js,ui/1.8.16/jquery.ui.widget.js,ui/1.8.16/jquery.ui.tabs.js,ui/1.8.16/jquery.ui.accordion.js,plugins/ba-debug-0.4.js,espn.l10n.r12.js,swfobject/2.2/swfobject.js,flashObjWrapper.r7.js,plugins/jquery.colorbox.1.3.14.js,plugins/jquery.ba-postmessage.js,espn.core.duo.r55.js,stub.search.r9.js,espn.nav.mega.r33.js,espn.storage.r6.js,espn.p13n.r16.js,espn.video.r65.js,registration/staticLogin.r10-28.js,espn.universal.overlay.r14.js,insider/espn.insider.2012062703.js,espn.espn360.stub.r9.js,espn.myHeadlines.stub.r13.js,espn.myfaves.stub.r3.js,tsscoreboard.20090612.js,%2Fforesee_v3%2Fforesee-alive.js,espn.gallery.0908a.js" type="text/javascript" charset="utf-8"></script>
<script src="http://a.espncdn.com/prod/scripts/espn.loader.200901201835.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
ESPN.include([
	'http://g.espncdn.com/lm/static/js/popPlayerCard',
	'http://widgets.outbrain.com/outbrain.js'
]);
</script>


<script type="text/javascript">
	var ajax_url = '<?php echo site_url();?>';
</script>
</head>
<body <?php body_class(); ?>>
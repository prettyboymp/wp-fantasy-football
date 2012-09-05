<?php
add_filter('show_admin_bar', '__return_false');

add_action('template_redirect', function() {
	wp_enqueue_script('main', get_bloginfo('template_directory').'/js/main.js', array('jquery-ui-sortable', 'jquery'));
	
	if(isset($_REQUEST['switch_view'])) {
		switch ($_REQUEST['switch_view']) {
			case 'draft_orders':
				include __DIR__ . '/draft-orders.php';
				die();
				break;
		}
	}
});

require_once( __DIR__ . '/plugins/fantasy-football/fantasy-football.php');

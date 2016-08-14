<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php wp_title( '|', true, 'right' ); ?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11">
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
		<?php wp_head(); ?>
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Work Sans">
		<style>
      body {
        font-family: 'Work Sans', serif;
      }
    </style>
	</head>

	<body <?php body_class(); ?>>
		<?php do_action( 'before' ); ?>

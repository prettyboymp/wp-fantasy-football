<?php

return [
	'positions'        => [
		'QB'  => [ 'QB' ],
		'RB1' => [ 'RB' ],
		'RB2' => [ 'RB' ],
		//'RB3' => [ 'RB'],
		'WR1' => [ 'WR' ],
		'WR2' => [ 'WR' ],
		'WR3' => [ 'WR' ],
		'TE'  => [ 'TE' ],
		//'TE2' => [ 'TE' ],
		'FLX' => [ 'RB', 'WR', 'TE' ],
		'DST' => [ 'DST' ],
		'K'   => [ 'K' ],
		'BE1' => [ 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ],
		'BE2' => [ 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ],
		'BE3' => [ 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ],
		'BE4' => [ 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ],
		'BE5' => [ 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ],
		'BE6' => [ 'QB', 'RB', 'WR', 'TE', 'K', 'DST' ],
	],
	'draft_by'         => [
		'QB'  => 10,
		'RB1' => 8,
		'RB2' => 11,
		'WR1' => 9,
		'WR2' => 12,
		'TE'  => 14,
		'WR3' => 13,
		'FLX' => 13,
		'DST' => 15,
		'K'   => 16
	],
	'position_weights' => [
		'QB'  => 2,
		'RB'  => 5,
		'WR'  => 5,
		'TE'  => 1.5,
		'DST' => 1,
		'K'   => 1
	],
	'draft_order'      => [
		'seth',
		'james',
		'weston',
		'matt',
		'david',
		'ryan',
		'richard',
		'mattl',
		'beau',
		'richie',
		'mike',
		'grant'
	],
	'shuffle'          => false
];
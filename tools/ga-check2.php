<?php
global $wpdb; $t = $wpdb->prefix . 'snippets';
if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$t'" ) ) { echo "no snippets table\n"; return; }
$cols = $wpdb->get_col( "SHOW COLUMNS FROM $t" ); echo "cols: ", implode( ',', $cols ), "\n";
foreach ( $wpdb->get_results( "SELECT * FROM $t ORDER BY id", ARRAY_A ) as $r ) {
	preg_match_all( '/(G-[A-Z0-9]{8,12}|GTM-[A-Z0-9]{5,8}|UA-\d{5,}-\d+|clarity|gtag|googletagmanager)/', $r['code'], $m );
	echo "#{$r['id']} active={$r['active']} scope=", $r['scope'] ?? '', " modified=", $r['modified'] ?? '', " «{$r['name']}» ids=", implode( ',', array_unique( $m[1] ) ), "\n";
	if ( $m[1] ) echo "   code: ", substr( preg_replace( '/\s+/', ' ', $r['code'] ), 0, 400 ), "\n";
}
// trash/revisions of snippets
echo "rank math analytics: ", wp_json_encode( get_option( 'rank_math_google_analytic_options' ) ), "\n";
echo "rank math modules: ", wp_json_encode( get_option( 'rank_math_modules' ) ), "\n";

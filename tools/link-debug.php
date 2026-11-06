<?php
ini_set( 'pcre.backtrack_limit', '50000000' ); ini_set( 'pcre.jit', '0' );
$u = get_permalink( (int) $args[0] ); $home = wp_parse_url( home_url(), PHP_URL_HOST );
$ch = curl_init( add_query_arg( 'la', 1, $u ) ); curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => 1, CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_SSL_VERIFYHOST => 0 ) ); $b = curl_exec( $ch );
foreach ( array( 'script', 'style', 'header', 'footer', 'nav', 'aside' ) as $tg ) { $x = preg_replace( '#<' . $tg . '\b.*?</' . $tg . '>#si', '', $b ); if ( null !== $x ) $b = $x; }
echo 'home=' . $home . ' len=' . strlen( $b ) . ' a=' . preg_match_all( '#<a\b#i', $b ) . "\n";
$n = preg_match_all( '#<a\b([^>]*)href="([^"#]+)[^"]*"([^>]*)>(.*?)</a>#si', $b, $m, PREG_SET_ORDER ); echo "matches $n err " . preg_last_error() . "\n";
foreach ( array_slice( $m, 0, 12 ) as $a ) echo '  ' . $a[2] . ' host=' . wp_parse_url( html_entity_decode( $a[2] ), PHP_URL_HOST ) . ' | ' . mb_substr( trim( wp_strip_all_tags( $a[4] ) ), 0, 40 ) . "\n";

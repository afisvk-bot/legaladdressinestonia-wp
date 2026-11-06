<?php
$u = get_permalink( (int) $args[0] );
$ch = curl_init( add_query_arg( 'la', 1, $u ) ); curl_setopt_array( $ch, array( CURLOPT_RETURNTRANSFER => 1, CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_SSL_VERIFYHOST => 0 ) ); $b = curl_exec( $ch );
echo "len " . strlen( $b ) . ' a=' . preg_match_all( '#<a\b#i', $b ) . "\n";
foreach ( array( 'header', 'footer', 'nav', 'script', 'style' ) as $t ) { $b2 = preg_replace( '#<(' . $t . ')\b.*?</\1>#si', '', $b ); echo "$t: len " . strlen( (string) $b2 ) . ' a=' . preg_match_all( '#<a\b#i', (string) $b2 ) . ' err=' . preg_last_error() . "\n"; }
preg_match_all( '#<(header|nav|footer)\b[^>]*>#i', $b, $m ); echo implode( "\n", array_slice( $m[0], 0, 10 ) ), "\n";
preg_match_all( '#<a\b[^>]*href="(https?://[^"]+)"[^>]*class="[^"]*lai-autolink#', $b, $x ); echo 'autolinks ' . count( $x[0] ) . "\n";

<?php
/** wp eval-file tools/check-disclaimer.php <path> — где и в каком виде выводится дисклеймер. */
do_action( 'litespeed_purge_all' );
$path = $args[0] ?? '/target/estonska-kompaniya-dlya-ne-rezydentiv-yes/';
foreach ( array( '', 'disc=note', 'disc=details', 'disc=card&disc_pos=top' ) as $q ) {
	$url  = home_url( $path ) . ( $q ? '?' . $q : '' ) . ( $q ? '&' : '?' ) . 'nc=' . microtime( true );
	$res  = wp_remote_get( $url, array( 'timeout' => 30, 'sslverify' => false ) );
	$body = wp_remote_retrieve_body( $res );
	$at   = strpos( $body, 'class="row lai-disc' );
	$ctx  = function ( $needle ) use ( $body, $at ) {
		$p = strpos( $body, $needle );
		return false === $p ? '-' : ( $p < $at ? 'before' : 'after' );
	};
	preg_match( '#class="row lai-disc[^"]*"#', $body, $m ); $m[1] = $m[0] ?? '';
	echo str_pad( $q ?: 'default', 24 ), ' http=', wp_remote_retrieve_response_code( $res ), ' found=', false === $at ? 'NO' : 'yes', ' ', $m[1] ?? '', ' | faq:', $ctx( 'service-faq' ), ' final-cta:', $ctx( 'service-final-cta' ), ' css:', strpos( $body, 'lai-disc__card{' ) || strpos( $body, 'litespeed/css' ) ? 'yes' : 'no', "\n";
}
preg_match( '#<p class="lai-disc__title">([^<]+)#', wp_remote_retrieve_body( wp_remote_get( home_url( $path ) . '?nc=' . time(), array( 'sslverify' => false ) ) ), $t );
echo 'title on page: ', $t[1] ?? '-', "\n";

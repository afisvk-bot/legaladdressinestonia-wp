<?php
foreach ( get_posts( array( 'post_type' => 'wpcf7_contact_form', 'numberposts' => -1, 'lang' => '' ) ) as $f ) {
	$h = substr( md5( $f->ID ), 0, 7 );
	echo "== #{$f->ID} «{$f->post_title}» hash=", get_post_meta( $f->ID, '_hash', true ) ? substr( get_post_meta( $f->ID, '_hash', true ), 0, 7 ) : '-', "\n";
	if ( 'form_EN' === $f->post_title ) {
		echo "FORM:\n", get_post_meta( $f->ID, '_form', true ), "\n";
		$m = get_post_meta( $f->ID, '_mail', true );
		echo "MAIL to=", preg_replace( '/[^@\s,]+@/', '***@', $m['recipient'] ?? '' ), " subject=", $m['subject'] ?? '', " additional=", str_replace( "\n", ' / ', preg_replace( '/[^@\s,]+@/', '***@', $m['additional_headers'] ?? '' ) ), "\n";
		echo "MAIL2 active=", var_export( ( get_post_meta( $f->ID, '_mail_2', true ) )['active'] ?? null, true ), "\n";
		echo "additional_settings: ", get_post_meta( $f->ID, '_additional_settings', true ), "\n";
		foreach ( get_post_meta( $f->ID ) as $k => $v ) if ( ! in_array( $k, array( '_form', '_mail', '_mail_2', '_messages' ), true ) ) echo "  meta $k = ", mb_substr( $v[0], 0, 200 ), "\n";
	}
}
echo "active plugins with pipedrive/cf7: ", implode( ', ', preg_grep( '/pipedrive|cf7|contact|recaptcha|entries/i', (array) get_option( 'active_plugins' ) ) ), "\n";
foreach ( array( 'cf7_pipedrive', 'cf7_pipedrive_pro', 'vxcf_pipedrive', 'crmperks' ) as $p ) { global $wpdb; $r = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 5", '%' . $p . '%' ) ); if ( $r ) echo "options $p: ", implode( ',', $r ), "\n"; }

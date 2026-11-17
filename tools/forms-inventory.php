<?php
global $wpdb;
$entries_tbl = $wpdb->prefix . 'cf7_entries'; $has_e = (bool) $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}vxcf_leads'" );
echo "== CF7 forms\n";
foreach ( get_posts( array( 'post_type' => 'wpcf7_contact_form', 'numberposts' => -1, 'lang' => '', 'post_status' => 'any', 'orderby' => 'ID', 'order' => 'ASC' ) ) as $f ) {
	$form  = (string) get_post_meta( $f->ID, '_form', true );
	preg_match_all( '/\[([a-z_*]+)\s+([a-z0-9_-]+)/i', $form, $m, PREG_SET_ORDER );
	$fields = array(); foreach ( $m as $x ) if ( ! in_array( rtrim( $x[1], '*' ), array( 'submit' ), true ) ) $fields[] = $x[1] . ':' . $x[2];
	$mail  = (array) get_post_meta( $f->ID, '_mail', true );
	$hash  = substr( (string) get_post_meta( $f->ID, '_hash', true ), 0, 7 );
	// где вставлена шорткодом в контент
	$used = $wpdb->get_col( $wpdb->prepare( "SELECT CONCAT(p.ID,':',p.post_type) FROM {$wpdb->posts} p WHERE p.post_status='publish' AND p.post_type NOT IN ('revision') AND (p.post_content LIKE %s OR p.post_content LIKE %s OR p.post_content LIKE %s) LIMIT 5", '%contact-form-7 id="' . $f->ID . '"%', '%contact-form-7 id="' . $hash . '"%', '%title="' . $f->post_title . '"%' ) );
	$usedm = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE %s OR meta_value LIKE %s", '%contact-form-7 id="' . $f->ID . '"%', '%contact-form-7 id="' . $hash . '"%' ) );
	$cnt = '-';
	if ( $has_e ) $cnt = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}vxcf_leads WHERE form_id=%s", 'cf_' . $f->ID ) ) . ' (last ' . $wpdb->get_var( $wpdb->prepare( "SELECT MAX(created) FROM {$wpdb->prefix}vxcf_leads WHERE form_id=%s", 'cf_' . $f->ID ) ) . ')';
	printf( "#%d [%s] «%s» hash=%s mod=%s\n   fields: %s\n   mail_to: %s | subject: %s\n   in content: %s | in meta: %s | entries: %s\n", $f->ID, $f->post_status, $f->post_title, $hash, $f->post_modified, implode( ', ', $fields ), preg_replace( '/[^@\s,<]+@/', '***@', (string) ( $mail['recipient'] ?? '' ) ), $mail['subject'] ?? '', implode( ',', $used ) ?: '-', $usedm, $cnt );
}
echo "\n== theme forms map (get_localized_cf7_form)\n";
foreach ( array( 'en', 'ru', 'uk', 'de', 'es', 'fr', 'pt', 'tr', 'zh' ) as $l ) { if ( function_exists( 'PLL' ) ) PLL()->curlang = PLL()->model->get_language( $l ); $r = get_localized_cf7_form(); echo "  $l => ", $r['shortcode'] ?? json_encode( $r ), "\n"; }
echo "\n== entries tables: ", implode( ', ', $wpdb->get_col( "SHOW TABLES LIKE '%vxcf%'" ) ), "\n";
if ( $has_e ) foreach ( $wpdb->get_results( "SELECT form_id, COUNT(*) c, MIN(created) a, MAX(created) b FROM {$wpdb->prefix}vxcf_leads GROUP BY form_id ORDER BY c DESC" ) as $r ) echo "  {$r->form_id}: {$r->c} ({$r->a} … {$r->b})\n";
echo "\n== third-party embeds in content/meta\n";
foreach ( array( 'pipedrive' => '%pipedrive%', 'leadbooster' => '%leadbooster%', 'calendly' => '%calendly%', 'typeform' => '%typeform%', 'hubspot' => '%hsforms%', 'google forms' => '%docs.google.com/forms%', 'tally' => '%tally.so%', 'jotform' => '%jotform%', 'iframe' => '%<iframe%', '<form' => '%<form%' ) as $k => $like ) {
	$c = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status='publish' AND post_type NOT IN ('revision') AND post_content LIKE %s", $like ) );
	$cm = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID=pm.post_id AND p.post_status='publish' WHERE pm.meta_value LIKE %s AND pm.meta_key NOT LIKE '\_%%'", $like ) );
	if ( $c || $cm ) echo "  $k: posts=$c meta=$cm\n";
}
echo "\n== active plugins (forms/crm/chat): ", implode( ', ', preg_grep( '/form|cf7|contact|pipedrive|chat|lead|crm|calendly|mail/i', (array) get_option( 'active_plugins' ) ) ), "\n";
$all = get_option( 'active_plugins' ); $raw = $wpdb->get_var( "SELECT option_value FROM {$wpdb->options} WHERE option_name='active_plugins'" );
echo "== active plugins in DB (incl. disabled on staging): ", implode( ', ', preg_grep( '/form|cf7|contact|pipedrive|chat|lead|crm|calendly|mail/i', (array) maybe_unserialize( $raw ) ) ), "\n";

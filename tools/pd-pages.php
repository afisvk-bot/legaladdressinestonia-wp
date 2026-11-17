<?php
global $wpdb;
foreach ( $wpdb->get_results( "SELECT pm.post_id, pm.meta_key FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID=pm.post_id AND p.post_status='publish' WHERE pm.meta_value LIKE '%pipedriveWebForms%' AND pm.meta_key NOT LIKE '\\_%'" ) as $r ) {
	$p = get_post( $r->post_id );
	echo "#{$p->ID} {$p->post_type} ", pll_get_post_language( $p->ID ), " «{$p->post_title}» ", get_permalink( $p ), " tpl=", get_page_template_slug( $p->ID ), " key={$r->meta_key}\n";
}

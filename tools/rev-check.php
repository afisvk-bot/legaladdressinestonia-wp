<?php
foreach ( array_map( 'intval', $args ) as $id ) {
	$p = get_post( $id );
	echo "#$id ", pll_get_post_language( $id ), " {$p->post_status} \"{$p->post_title}\" slug={$p->post_name} mod={$p->post_modified} src=", get_post_meta( $id, '_lai_tr_source', true ), " c0=", mb_substr( wp_strip_all_tags( (string) get_post_meta( $id, 'content_blocks_0_content', true ) ), 0, 90 ), "\n";
	foreach ( wp_get_post_revisions( $id, array( 'posts_per_page' => 8 ) ) as $r ) echo "   rev #{$r->ID} {$r->post_modified} author=", get_the_author_meta( 'user_login', $r->post_author ), " \"{$r->post_title}\"\n";
	echo "   old slugs: ", implode( ',', get_post_meta( $id, '_wp_old_slug' ) ), " | edit_last=", get_the_author_meta( 'user_login', (int) get_post_meta( $id, '_edit_last', true ) ), "\n";
}

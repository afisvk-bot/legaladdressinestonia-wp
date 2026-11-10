<?php
foreach ( array( 3035, 2995, 3633, 4565, 4614 ) as $id ) {
	$p = get_post( $id );
	$tr = pll_get_post_translations( $id ); $man = array();
	foreach ( $tr as $l => $t ) $man[ $l ] = $t . ( get_post_meta( $t, '_lai_tr_source', true ) ? '*' : '' );
	echo $id, ' ', $p->post_type, ' ', pll_get_post_language( $id ), ' ', $p->post_name, ' | ', $p->post_date, ' | ', wp_json_encode( $man ), "\n";
}

<?php
foreach ( array( 1269, 2254, 2156, 2190, 2342, 2343, 2344, 2345, 2346, 4578, 844 ) as $id ) {
	$p = get_post( $id );
	echo $p ? "#$id {$p->post_type} " . pll_get_post_language( $id ) . " " . get_permalink( $id ) . "\n" : "#$id missing\n";
}
foreach ( array( 'nalogi-dlya-kompaniy-v-estonii', 'ezhemesyachnyj-buhgalterskij-uchet-v-estonii', 'gotovye-kompanii-v-estonii', 'kontaktnoe-litso-v-estonii', 'estonskaya-holdingovaya-kompaniya', 'finansovaya-litsenziya-v-estonii', 'vnzh-dlya-osnovatelya-kompanii-v-estonii', 'yuridicheskij-adres-v-estonii' ) as $s ) {
	$q = get_posts( array( 'name' => $s, 'post_type' => 'any', 'post_status' => 'any', 'lang' => '', 'numberposts' => 3 ) );
	foreach ( $q as $p ) echo "$s => #{$p->ID} {$p->post_type} ", pll_get_post_language( $p->ID ), " ", get_permalink( $p ), "\n";
	if ( ! $q ) echo "$s => none\n";
}

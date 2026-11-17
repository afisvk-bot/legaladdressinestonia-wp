<?php
$rules = get_option( 'rewrite_rules' );
echo 'rules: ', is_array( $rules ) ? count( $rules ) : 'NONE', "\n";
$n = 0; foreach ( (array) $rules as $re => $q ) if ( false !== strpos( $q, 'service' ) && $n++ < 12 ) echo "  $re => $q\n";
foreach ( array( 1372, 2564, 4725, 3627 ) as $id ) echo "#$id ", get_permalink( $id ), "\n";
echo 'pll options: hide_default=', PLL()->options['hide_default'] ?? '?', ' force_lang=', PLL()->options['force_lang'] ?? '?', ' rewrite=', PLL()->options['rewrite'] ?? '?', ' post_types=', wp_json_encode( PLL()->options['post_types'] ?? null ), "\n";

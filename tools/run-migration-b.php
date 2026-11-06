<?php
wp_set_current_user( 1 );
$r = lai_mig_20261106b_run();
update_option( 'lai_mig_20261106b', $r + array( 'time' => current_time( 'mysql' ) ), false );
echo $r['summary'], "\n", implode( "\n", array_slice( $r['log'], 0, 40 ) ), "\n";

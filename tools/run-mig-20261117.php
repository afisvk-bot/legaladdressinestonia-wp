<?php
$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
wp_set_current_user( (int) $admins[0] );
$log = lai_mig_20261117_run();
update_option( 'lai_mig_20261117', array( 'log' => $log, 'time' => current_time( 'mysql' ) ), false );
echo implode( "\n", $log ), "\n";

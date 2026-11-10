<?php
$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
wp_set_current_user( (int) $admins[0] );
echo implode( "\n", lai_mig_20261110b_run() ), "\n";

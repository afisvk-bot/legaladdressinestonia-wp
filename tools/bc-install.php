<?php
$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
wp_set_current_user( (int) $admins[0] );
$id = lai_bc_install_form();
$f  = WPCF7_ContactForm::get_instance( $id );
echo "form #$id ", $f ? $f->title() : '-', "\n";
$m = $f->prop( 'mail' );
echo 'recipient=', preg_replace( '/[^@\s,<]+@/', '***@', $m['recipient'] ), ' sender=', preg_replace( '/[^@\s,<]+@/', '***@', $m['sender'] ), "\n";
do_action( 'litespeed_purge_all' );

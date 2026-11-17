<?php
$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
wp_set_current_user( (int) $admins[0] );
if ( get_option( 'lai_ls_forms_ver' ) !== LAI_LS_VER ) { $log = lai_ls_update_forms(); update_option( 'lai_ls_forms_log', $log, false ); update_option( 'lai_ls_forms_ver', LAI_LS_VER, false ); echo implode( "\n", $log ), "\n"; } else echo "forms already v", LAI_LS_VER, "\n";
// Book a call upgrade (тот же код, что в admin_init lai-book-call.php)
if ( LAI_BC_FORM_VER !== get_option( 'lai_bc_form_ver' ) ) {
	$f = WPCF7_ContactForm::get_instance( lai_bc_form_id() );
	$mail = $f->prop( 'mail' );
	if ( false === strpos( (string) $mail['body'], '[lead-source]' ) ) $mail['body'] .= "\n\nHow did you hear about us: [lead-source]\nChannel (auto): [lead-channel]\nReferrer: [lead-referrer]\nUTM: [lead-utm]\nLanding page: [lead-landing]";
	$f->set_properties( array( 'form' => lai_bc_form_template(), 'mail' => $mail ) ); $f->save();
	update_option( 'lai_bc_form_ver', LAI_BC_FORM_VER, false ); echo "Book a call upgraded\n";
}
echo "--- form_RU now:\n", get_post_meta( 496, '_form', true ), "\n--- mail RU:\n", get_post_meta( 496, '_mail', true )['body'], "\n";
do_action( 'litespeed_purge_all' );

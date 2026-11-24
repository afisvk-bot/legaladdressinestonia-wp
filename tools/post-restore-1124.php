<?php
global $wpdb;
echo "== guard: ", file_exists( WPMU_PLUGIN_DIR . '/staging-guard.php' ) ? 'present' : 'MISSING', "\n";
echo "== mu-plugins: ", implode( ', ', array_diff( scandir( WPMU_PLUGIN_DIR ), array( '.', '..' ) ) ), "\n";
foreach ( array( 'lai_mig_20261110', 'lai_mig_20261110b', 'lai_mig_20261117', 'lai_bc_form_id', 'lai_bc_form_ver', 'lai_ls_forms_ver', 'lai_service_routes_ver' ) as $o ) { $v = get_option( $o ); echo "$o: ", is_array( $v ) ? 'applied ' . ( $v['time'] ?? '' ) : var_export( $v, true ), "\n"; }
$v = get_option( 'lai_mig_20261117' ); if ( is_array( $v ) ) echo "  20261117 issues: ", implode( ' | ', preg_grep( '/ERROR|SKIP|skipped/', $v['log'] ) ) ?: 'none', "\n";
echo "change-accountant group: ", wp_json_encode( pll_get_post_translations( 3627 ) ), "\n";
echo "4578 EN title: ", get_the_title( 4578 ), " slug ", get_post_field( 'post_name', 4578 ), "\n";
$f = (string) get_post_meta( 456, '_form', true ); echo "form_EN has lead-source: ", false !== strpos( $f, 'lead-source' ) ? 'yes' : 'no', "\n";
echo "Book a call form: ", lai_bc_form_id() ?? '-', "\n";
echo "theme header hooks: ", false !== strpos( file_get_contents( get_template_directory() . '/header.php' ), 'lai_header_contacts' ) ? 'yes' : 'no', "\n";
echo "snippets: "; foreach ( $wpdb->get_results( "SELECT id,name,active,modified FROM {$wpdb->prefix}snippets ORDER BY id" ) as $s ) echo "\n  #{$s->id} a={$s->active} {$s->modified} {$s->name}";
echo "\n== new/modified content since 2026-11-17 12:00:\n";
foreach ( $wpdb->get_results( "SELECT ID,post_type,post_status,post_date,post_modified,LEFT(post_title,70) t FROM {$wpdb->posts} WHERE post_type NOT IN ('revision','nav_menu_item','attachment','customize_changeset','oembed_cache','wpcf7_contact_form') AND post_modified > '2026-11-17 12:00:00' ORDER BY post_modified DESC LIMIT 40" ) as $r ) echo "  #$r->ID $r->post_type $r->post_status ", pll_get_post_language( $r->ID ), " d=$r->post_date m=$r->post_modified $r->t\n";
echo "== CF7 forms modified since 11-17: "; foreach ( $wpdb->get_results( "SELECT ID,post_title,post_modified FROM {$wpdb->posts} WHERE post_type='wpcf7_contact_form' AND post_modified > '2026-11-17 12:00:00'" ) as $r ) echo "#$r->ID $r->post_title $r->post_modified; ";
echo "\n== leads since 11-17: "; foreach ( $wpdb->get_results( "SELECT form_id, COUNT(*) c, MAX(created) m FROM {$wpdb->prefix}vxcf_leads WHERE created > '2026-11-17 12:00:00' GROUP BY form_id" ) as $r ) echo "$r->form_id=$r->c (last $r->m); ";
echo "\n";

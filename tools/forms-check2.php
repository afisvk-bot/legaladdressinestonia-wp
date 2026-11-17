<?php
global $wpdb;
echo "UK pages URLs: contacts=", get_permalink( (int) pll_get_post( 120, 'uk' ) ), " annual=", get_permalink( (int) pll_get_post( 1398, 'uk' ) ), " team=", get_permalink( (int) pll_get_post( (int) url_to_postid( home_url( '/our-team/' ) ), 'en' ) ), "\n";
echo "home UK id=", pll_get_post( 2, 'uk' ), " shortcode field: ", substr( (string) get_post_meta( (int) pll_get_post( 2, 'uk' ), 'shortcode', true ), 0, 80 ), "\n";
foreach ( array( 'form_UK' => 1301, 'form_EN' => 456 ) as $t => $id ) { $f = wpcf7_contact_form( $id ); echo "$t resolves: ", $f ? $f->id() : '-', "; by theme hash: "; $h = array( 'form_UK' => '9fbb0a8', 'form_EN' => '616a252' )[ $t ]; $x = wpcf7_get_contact_form_by_hash( $h ); echo $x ? $x->id() : 'NOT FOUND (fallback by title)', "\n"; }
echo "CF7 additional settings with webhooks/pipedrive: ";
foreach ( get_posts( array( 'post_type' => 'wpcf7_contact_form', 'numberposts' => -1, 'lang' => '' ) ) as $f ) { $a = (string) get_post_meta( $f->ID, '_additional_settings', true ); if ( trim( $a ) ) echo "\n  #{$f->ID}: ", str_replace( "\n", ' / ', $a ); }
echo "\noptions with pipedrive/webhook/zapier: ", implode( ', ', $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name REGEXP 'pipedrive|webhook|zapier|make_com|integromat' LIMIT 20" ) ), "\n";
$cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->prefix}vxcf_leads" ); echo "leads cols: ", implode( ',', $cols ), "\n";
echo "latest 5 leads: "; foreach ( $wpdb->get_results( "SELECT id, form_id, created, url FROM {$wpdb->prefix}vxcf_leads ORDER BY id DESC LIMIT 8" ) as $r ) echo "\n  {$r->id} {$r->form_id} {$r->created} ", substr( (string) $r->url, 0, 90 );
echo "\nUK-language leads (url contains /uk/): ", $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}vxcf_leads WHERE url LIKE '%/uk/%'" ), " by form: ";
foreach ( $wpdb->get_results( "SELECT form_id, COUNT(*) c, MAX(created) m FROM {$wpdb->prefix}vxcf_leads WHERE url LIKE '%/uk/%' GROUP BY form_id" ) as $r ) echo "{$r->form_id}={$r->c} (last {$r->m}) ";
echo "\nPD plugins active? ", wp_json_encode( array_values( preg_grep( '/pipedrive/i', (array) maybe_unserialize( $wpdb->get_var( "SELECT option_value FROM {$wpdb->options} WHERE option_name='active_plugins'" ) ) ) ) ), "\n";

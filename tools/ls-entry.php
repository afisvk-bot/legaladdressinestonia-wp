<?php
global $wpdb; $p = $wpdb->prefix;
$id = $wpdb->get_var( "SELECT MAX(id) FROM {$p}vxcf_leads WHERE form_id='cf_" . lai_bc_form_id() . "'" );
echo "lead #$id\n"; foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT name, value FROM {$p}vxcf_leads_detail WHERE lead_id=%d", $id ) ) as $r ) echo "  {$r->name} = {$r->value}\n";

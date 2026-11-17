<?php
/** wp eval-file tools/svc-inventory.php — услуги «Годовой отчёт» и «Смена бухгалтера»: дубли, переводы, контент, меню. */
global $wpdb;
$pats = array( '%годов%отч%', '%annual report%', '%annual account%', '%річн%звіт%', '%Jahresabschluss%', '%Jahresbericht%', '%informe anual%', '%rapport annuel%', '%comptes annuels%', '%relatório anual%', '%relatorio anual%', '%yıllık rapor%', '%年度报告%', '%年报%', '%смен%бухгалтер%', '%change%accountant%', '%changing%accountant%', '%зміна%бухгалтер%', '%Buchhalterwechsel%', '%cambio de contable%', '%changement de comptable%', '%mudança de contabilista%', '%muhasebeci değiş%', '%更换会计%', '%restoration%', '%восстановлен%учет%' );
$where = implode( ' OR ', array_fill( 0, count( $pats ), 'post_title LIKE %s' ) );
$rows = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_type,post_status,post_name,post_title,post_date,post_modified FROM {$wpdb->posts} WHERE post_type NOT IN ('revision','nav_menu_item','attachment') AND post_status NOT IN ('auto-draft') AND ($where) ORDER BY ID", $pats ) );
$menu_obj = array();
foreach ( $wpdb->get_results( "SELECT p.ID item, m.meta_value obj, t.name menu FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_menu_item_object_id' JOIN {$wpdb->term_relationships} r ON r.object_id=p.ID JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id=r.term_taxonomy_id AND tt.taxonomy='nav_menu' JOIN {$wpdb->terms} t ON t.term_id=tt.term_id WHERE p.post_type='nav_menu_item'" ) as $m ) $menu_obj[ (int) $m->obj ][] = $m->menu;
foreach ( $rows as $r ) {
	$blocks = get_post_meta( $r->ID, 'content_blocks', true );
	$text = 0; foreach ( get_post_meta( $r->ID ) as $k => $v ) if ( 0 === strpos( $k, 'content_blocks_' ) && ! is_serialized( $v[0] ) ) $text += mb_strlen( wp_strip_all_tags( $v[0] ) );
	printf( "#%d %s %s %s [%s] \"%s\" slug=%s date=%s mod=%s blocks=%s acf_text=%d content=%d rm_title=%s\n   group=%s\n   menus=%s\n",
		$r->ID, $r->post_type, $r->post_status, function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $r->ID ) : '?', implode( ',', wp_list_pluck( (array) get_the_terms( $r->ID, 'service_cat' ) ?: array(), 'name' ) ), $r->post_title, $r->post_name, $r->post_date, $r->post_modified,
		is_array( $blocks ) ? implode( ',', $blocks ) : var_export( $blocks, true ), $text, mb_strlen( wp_strip_all_tags( get_post_field( 'post_content', $r->ID ) ) ), get_post_meta( $r->ID, 'rank_math_title', true ),
		wp_json_encode( pll_get_post_translations( $r->ID ) ), implode( ' | ', $menu_obj[ $r->ID ] ?? array( '-' ) ) );
}
echo "\nTaxonomies of service: ", implode( ',', get_object_taxonomies( 'service' ) ), "\n";

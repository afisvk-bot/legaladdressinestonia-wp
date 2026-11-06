<?php
/**
 * wp eval-file tools/fix-duplicates.php [dry]
 * Связать переводы и объединить копии-дубли на одном языке (копия → корзина + 301 на основную).
 */
$dry = in_array( 'dry', $args, true );
$log = array();

/* ---------- 1. Связать переводы ---------- */
$links = array(
	// MTÜ: в группу EN #3639 китайской версией ставим настоящий ZH-перевод #3641 вместо английской копии #3642
	array( 'base' => 3639, 'lang' => 'zh', 'post' => 3641 ),
	// TSD: RU #3629 к EN #3633 (связь была записана только с одной стороны)
	array( 'base' => 3633, 'lang' => 'ru', 'post' => 3629 ),
);
foreach ( $links as $l ) {
	$group = pll_get_post_translations( $l['base'] );
	$before = $group;
	$group[ $l['lang'] ] = $l['post'];
	$log[] = sprintf( 'link #%d as %s into group of #%d: %s -> %s', $l['post'], $l['lang'], $l['base'], wp_json_encode( $before ), wp_json_encode( $group ) );
	if ( ! $dry ) {
		pll_set_post_language( $l['post'], $l['lang'] );
		pll_save_post_translations( $group );
	}
}

/* ---------- 2. Копии на одном языке: keep ← stray ---------- */
$pairs = array(
	array( 'keep' => 4545, 'stray' => 4535, 'take_slug' => true ),  // DE Start-ups: основная получает «чистый» slug без -2
	array( 'keep' => 4516, 'stray' => 4504 ),                        // PT Contabilidade e-residents
	array( 'keep' => 4471, 'stray' => 4468 ),                        // PT Registo por procuração
	array( 'keep' => 2461, 'stray' => 2456 ),                        // FR Crypto & Web3 (industry)
	array( 'keep' => 2376, 'stray' => 2372 ),                        // FR fondateurs hors UE (target)
	array( 'keep' => 2378, 'stray' => 2373 ),                        // PT fundadores de fora da UE (target)
	array( 'keep' => 2526, 'stray' => 2515 ),                        // ZH holding crypto/iGaming (licensing)
	array( 'keep' => 3641, 'stray' => 3642 ),                        // ZH MTÜ: английская копия → китайский перевод
);

$rm_ok = class_exists( '\RankMath\Redirections\Redirection' ) && \RankMath\Helper::is_module_active( 'redirections' );
$log[] = 'rank math redirections module: ' . ( $rm_ok ? 'active' : 'not active (fallback: _wp_old_slug)' );
$redirects = array();

foreach ( $pairs as $p ) {
	$keep = get_post( $p['keep'] ); $stray = get_post( $p['stray'] );
	if ( ! $keep || ! $stray ) { $log[] = 'missing post ' . wp_json_encode( $p ); continue; }
	if ( 'trash' === $stray->post_status ) { $log[] = "#{$stray->ID} already in trash"; continue; }
	if ( pll_get_post_language( $keep->ID ) !== pll_get_post_language( $stray->ID ) ) {
		$log[] = "SKIP #{$stray->ID}: language differs from #{$keep->ID}"; continue;
	}
	$stray_url = get_permalink( $stray );
	$stray_path = wp_parse_url( $stray_url, PHP_URL_PATH );
	$old_keep_url = get_permalink( $keep );

	if ( ! $dry ) {
		// убрать копию из любой группы переводов, затем в корзину
		$g = pll_get_post_translations( $stray->ID );
		if ( isset( $g[ pll_get_post_language( $stray->ID ) ] ) && (int) $g[ pll_get_post_language( $stray->ID ) ] === $stray->ID && count( $g ) > 1 ) {
			unset( $g[ pll_get_post_language( $stray->ID ) ] );
			pll_save_post_translations( $g );
		}
		wp_trash_post( $stray->ID );
		if ( ! empty( $p['take_slug'] ) ) {
			wp_update_post( array( 'ID' => $keep->ID, 'post_name' => preg_replace( '/__trashed$/', '', $stray->post_name ) ) );
			clean_post_cache( $keep->ID );
		}
	}
	$new_keep_url = $dry ? $old_keep_url : get_permalink( $keep->ID );
	$redirects[] = array( 'from' => $stray_path, 'to' => $new_keep_url );
	if ( ! empty( $p['take_slug'] ) && $old_keep_url !== $new_keep_url ) {
		$redirects[] = array( 'from' => wp_parse_url( $old_keep_url, PHP_URL_PATH ), 'to' => $new_keep_url );
	}
	$log[] = sprintf( '%s #%d %s "%s" -> trash; 301 %s -> %s', $dry ? '[dry]' : 'done', $stray->ID, pll_get_post_language( $stray->ID ), $stray->post_title, $stray_path, $new_keep_url );
}

/* ---------- 3. Редиректы ---------- */
foreach ( $redirects as $r ) {
	$from = ltrim( $r['from'], '/' );
	if ( $dry ) { $log[] = "[dry] redirect /$from -> {$r['to']}"; continue; }
	if ( $rm_ok ) {
		$red = \RankMath\Redirections\Redirection::from( array( 'url_to' => $r['to'], 'header_code' => 301, 'status' => 'active' ) );
		$red->add_source( untrailingslashit( $from ), 'exact' );
		$red->add_source( trailingslashit( $from ), 'exact' );
		$id = $red->save();
		$log[] = "rank math redirect #$id /$from -> {$r['to']}";
	} else {
		$target = url_to_postid( $r['to'] );
		add_post_meta( $target, '_wp_old_slug', basename( untrailingslashit( $from ) ) );
		$log[] = "old_slug /$from -> #$target";
	}
}

/* ---------- 4. DE «Terms and Conditions» ---------- */
$tc = 198;
if ( 'Terms and Conditions' === get_the_title( $tc ) ) {
	$log[] = ( $dry ? '[dry] ' : '' ) . 'DE #198 title -> Allgemeine Geschäftsbedingungen';
	if ( ! $dry ) {
		wp_update_post( array( 'ID' => $tc, 'post_title' => 'Allgemeine Geschäftsbedingungen' ) );
		$rt = get_post_meta( $tc, 'rank_math_title', true );
		if ( $rt && false !== stripos( $rt, 'Terms and Conditions' ) ) {
			update_post_meta( $tc, 'rank_math_title', str_ireplace( 'Terms and Conditions', 'Allgemeine Geschäftsbedingungen', $rt ) );
		}
	}
}

/* ---------- 5. Пересинхронизировать меню (MTÜ в ZH-меню → китайская страница) ---------- */
if ( ! $dry && function_exists( 'lai_sync_all_menus' ) ) {
	$rows = lai_sync_all_menus();
	$log[] = 'menu sync: ' . implode( '; ', array_map( fn( $r ) => $r['lang'] . ' ~' . $r['updated'] . ' +' . $r['created'] . ' -' . $r['removed'], array_filter( $rows, fn( $r ) => $r['updated'] || $r['created'] || $r['removed'] ) ) );
}
if ( ! $dry ) {
	flush_rewrite_rules( false );
	do_action( 'litespeed_purge_all' );
}

/* ---------- 6. Проверка редиректов ---------- */
if ( ! $dry ) {
	foreach ( $redirects as $r ) {
		$res = wp_remote_get( home_url( $r['from'] ), array( 'redirection' => 0, 'timeout' => 20, 'sslverify' => false ) );
		$log[] = sprintf( 'check %s -> %s %s', $r['from'], wp_remote_retrieve_response_code( $res ), wp_remote_retrieve_header( $res, 'location' ) );
	}
}
echo implode( "\n", $log ), "\n";

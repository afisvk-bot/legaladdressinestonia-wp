<?php
/** EN #4565: обрезанный заголовок + пустые SEO-поля; RU #4559: пустые SEO-поля (только если пусто). */
$fix = array(
	4565 => array( 'trunc' => 'Estonia Employment Contract Guide: Key Requirem', 'title' => 'Estonia Employment Contract Guide: Key Requirements', 'rm' => array( 'Estonia Employment Contract Guide: Key Requirements 2026', 'Employment contracts in Estonia in 2026: mandatory terms under the TLS, €946 minimum wage, TÖR registration, taxes and the right to work for non-residents.', 'employment contract' ) ),
	4559 => array( 'rm' => array( 'Трудовой договор в Эстонии: ключевые требования 2026', 'Трудовой договор в Эстонии в 2026: обязательные условия по TLS, минимальная зарплата 946 €, регистрация в TÖR, налоги и право на работу нерезидентов.', 'трудовой договор в Эстонии' ) ),
);
foreach ( $fix as $id => $f ) {
	echo "#$id " . pll_get_post_language( $id ) . ' title: "' . get_the_title( $id ) . "\"\n";
	if ( ! empty( $f['trunc'] ) && get_post_field( 'post_title', $id ) === $f['trunc'] ) { wp_update_post( array( 'ID' => $id, 'post_title' => $f['title'] ) ); echo "  title fixed\n"; }
	foreach ( array( 'rank_math_title', 'rank_math_description', 'rank_math_focus_keyword' ) as $i => $k ) {
		if ( '' === trim( (string) get_post_meta( $id, $k, true ) ) ) { update_post_meta( $id, $k, $f['rm'][ $i ] ); echo "  set $k\n"; }
	}
}

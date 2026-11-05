<?php
/** wp eval-file tools/dump-html.php <path> <regex> — вырезка HTML страницы по регулярке. */
$body = wp_remote_retrieve_body( wp_remote_get( home_url( $args[0] ?? '/' ) . '?nc=' . microtime( true ), array( 'timeout' => 30, 'sslverify' => false ) ) );
preg_match( '#' . ( $args[1] ?? '<div class="mob_ready">.*?</nav>' ) . '#s', $body, $m );
echo $m[0] ?? 'no match', "\n";

<?php
// Тест миграции на staging: запускает lai_mig_20261125_run() без отметки «выполнено» (кнопка остаётся).
$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
wp_set_current_user( (int) $admins[0] );
echo implode( "\n", lai_mig_20261125_run() ), "\n";

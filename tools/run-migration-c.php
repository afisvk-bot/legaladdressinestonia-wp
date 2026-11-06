<?php
delete_option( 'lai_mig_20261106c' ); $r = lai_mig_20261106c_run(); update_option( 'lai_mig_20261106c', $r, false ); echo $r['summary'], "\n";

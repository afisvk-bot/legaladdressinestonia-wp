<?php
foreach ( array( '20261117', '20261124', '20261125', '20261126' ) as $m ) { $o = get_option( 'lai_mig_' . $m ); echo $m, ': ', is_array( $o ) ? 'applied ' . ( $o['time'] ?? '' ) : 'NOT applied', "\n"; }

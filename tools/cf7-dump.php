<?php
foreach ( array_map( 'intval', $args ) as $id ) {
	echo "===== #$id ", get_the_title( $id ), "\n--- FORM\n", get_post_meta( $id, '_form', true ), "\n--- MAIL BODY\n";
	$m = get_post_meta( $id, '_mail', true ); echo $m['body'], "\n--- subject: ", $m['subject'], "\n--- additional: ", get_post_meta( $id, '_additional_settings', true ), "\n";
}

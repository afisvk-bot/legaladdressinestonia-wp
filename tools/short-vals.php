<?php
foreach ( array_map( 'intval', $args ) as $id ) { echo "== #$id\n"; foreach ( get_post_meta( $id ) as $k => $v ) { if ( 0 !== strpos( $k, 'content_blocks_' ) || is_serialized( $v[0] ) || is_numeric( $v[0] ) || preg_match( '/^field_/', $v[0] ) ) continue; if ( mb_strlen( $v[0] ) < 40 ) echo "$k :: ", $v[0], "\n"; } }

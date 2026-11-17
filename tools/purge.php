<?php
do_action( 'litespeed_purge_all' );
if ( class_exists( '\LiteSpeed\Purge' ) ) \LiteSpeed\Purge::purge_all();
wp_cache_flush();
echo "purged\n";

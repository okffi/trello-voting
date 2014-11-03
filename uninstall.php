<?php

/**
 * Delete database table when removing this plugin
 * @since  1.0
 */
if(!defined( 'WP_UNINSTALL_PLUGIN')) {
    exit(); 
}

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}trello_voting" );

?>
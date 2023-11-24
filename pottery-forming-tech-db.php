<?php
/**
 * Plugin Name: Pottery Forming Technology Database
 * Plugin URI: https://github.com/caroline-jeffra/pottery-forming-tech-db-plugin
 * Description: Create and manage a database and associated api of archaeological pottery technology data
 * Author: Caroline Jeffra
 * Author URI: https://github.com/caroline-jeffra
 * Text Domain: pottery-forming-tech-db
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Version: 1.0
 */

 register_activation_hook( __FILE__, 'pftd_setup_table');
 function pftd_setup_table() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'pottery_ftd_object';

  $sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    pot_type varchar (20) NOT NULL,
    forming_method mediumint (9) NOT NULL,
    shape varchar (100) NOT NULL,
    catalog_number varchar (100) NOT NULL,
    traces_observed varchar (255) NOT NULL,
    PRIMARY KEY (id)
    )";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
 }

 add_action( 'rest_api_init', 'pftd_register_routes' );

 function pftd_register_routes() {

  // GET all
  register_rest_route(
    'pottery-forming-tech-api/v1',
    '/pots/',
    array(
      'methods' => 'GET',
      'callback' => 'pftd_get_pots',
      'permission_callback' => '__return_true'
    )
    );

  // GET one
  register_rest_route(
    'pottery-forming-tech-api/v1',
    '/pot/(?P<id>\d+)',
    array(
      'methods' => 'GET',
      'callback' => 'pftd_get_pot',
      'permission_callback' => '__return_true'
    )
    );

  // POST
  register_rest_route(
    'pottery-forming-tech-api/v1',
    '/pot/',
    array(
      'methods' => 'POST',
      'callback' => 'pftd_create_pot',
      'permission_callback' => '__return_true'
    )
    );
 }

function pftd_get_pots() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'pottery_ftd_object';

  $results = $wpdb->get_results( "SELECT * FROM $table_name" );
  return $results;
}

function pftd_get_pot( $request ){
  $id = $request['id'];
  global $wpdb;
  $table_name = $wpdb->prefix . 'pottery_ftd_object';

  $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE id = $id" );

  return $results;
}

function pftd_create_pot( $request ){
  global $wpdb;
  $table_name = $wpdb->prefix . 'pottery_ftd_object';
  // create function to sanitize and filter inputs from $request

  $rows = $wpdb->insert(
    $table_name,
    array(
      'pot_type' => $request['pot_type'],
      'forming_method' => $request['forming_method'],
      'shape' => $request['shape'],
      'catalog_number' => $request['catalog_number'],
      'traces_observed' => $request['traces_observed'],
    )
    );
    return $rows;
}
?>

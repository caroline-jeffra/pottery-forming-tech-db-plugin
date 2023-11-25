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

 // Hooks
register_activation_hook(__FILE__, 'pftd_setup_table');
register_deactivation_hook( __FILE__, 'pftd_drop_table' );
add_action('rest_api_init', 'pftd_register_routes');


// callbacks
function pftd_setup_table()
{
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

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

function pftd_drop_table() {
  // first export table to CSV and write to new file


  // second drop table from database
  global $wpdb;
  $table_name = $wpdb->prefix . 'pottery_ftd_object';

  $sql = "DROP TABLE $table_name";

  require_once(ABSPATH . "wp-admin/includes/upgrade.php");
  dbDelta($sql);
}

function pftd_register_routes()
{

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

  // PATCH
  register_rest_route(
    'pottery-forming-tech-api/v1',
    '/pot/(?P<id>\d+)',
    array(
      'methods' => 'PATCH',
      'callback' => 'pftd_update_pot',
      'permission_callback' => '__return_true'
    )
  );

  // DELETE
  register_rest_route(
    'pottery-forming-tech-api/v1',
    '/pot/(?P<id>\d+)',
    array(
      'methods' => 'DELETE',
      'callback' => 'pftd_delete_pot',
      'permission_callback' => '__return_true'
    )
  );
}

function pftd_get_pots()
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'pottery_ftd_object';

  $results = $wpdb->get_results("SELECT * FROM $table_name");
  return $results;
}

function pftd_get_pot($request)
{
  $id = $request['id'];
  global $wpdb;
  $table_name = $wpdb->prefix . 'pottery_ftd_object';

  $results = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $id");

  return $results;
}

function pftd_create_pot($request)
{
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

function pftd_update_pot($request)
{
  $id = $request['id'];
  global $wpdb;
  $table_name = $wpdb->prefix . 'pottery_ftd_object';
  // sanitize and filter inputs from $request
  // currently overwriting any absent values with empty string
  $results = $wpdb->update(
    $table_name,
    array(
      'pot_type' => $request['pot_type'],
      'forming_method' => $request['forming_method'],
      'shape' => $request['shape'],
      'catalog_number' => $request['catalog_number'],
      'traces_observed' => $request['traces_observed'],
    ),
    array(
      'id' => $id,
    )
  );
  return $results;
}

function pftd_delete_pot($request)
{
  $id = $request['id'];
  global $wpdb;
  $table_name = $wpdb->prefix . 'pottery_ftd_object';

  $results = $wpdb->delete(
    $table_name,
    array(
      'id' => $id,
    )
  );
  return $results;
}

function csv_export_table($table_name){
  ob_start();

  global $wpdb;
  $domain = $_SERVER['SERVER_NAME'];
  $filename = $domain . '-' . $table_name . time() . '.csv';

  // get table header names
  $sql_headers = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ' . $wpdb->dbname . ' AND TABLE_NAME = ' . $table_name;
  $headers = $wpdb->get_results($sql_headers);
  $header_row = array();
  foreach ($headers as $header) {
    $header_row[] = $header;
  }

  // get table data rows
  $sql_rows = $wpdb->get_results("SELECT * FROM $table_name");
  $data_rows = array();
  foreach( $sql_rows as $row ){
    $row = array();
    foreach ($row as $item) {
      $row[] = $item;
    }
    $data_rows[] = $row;
  };

  $fh = @fopen( 'php://output', 'w' );

  // sql for selecting column data types: build string for fprintf data format parameter
  $sql_format = "SELECT `DATA_TYPE` FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ' . $wpdb->dbname . ' AND TABLE_NAME = " . $table_name;
  $formats = $wpdb->get_results($sql_format);
  $table_column_formats = "";
  $int_types = array('int', 'integer', 'bigint');
  $dec_types = array('float', 'double', 'double precision', 'decimal', 'dec');
  foreach ($formats as $format) {
    $format = strtolower($format);
    if (in_array($format, $int_types, true)) {
      $table_column_formats = $table_column_formats . printf("%b", $format);
    } elseif ( in_array($format, $dec_types, true )){
      $table_column_formats = $table_column_formats . printf("%f", $format);
    } else {
      $table_column_formats = $table_column_formats . printf("%s", $format);
    }
  };

  fprintf( $fh, $table_column_formats );

  ob_end_flush();

  die();
}
?>

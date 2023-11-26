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
register_deactivation_hook( __FILE__, 'pftd_deactivation_routine' );
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

  $csv_path = (plugin_dir_path( __FILE__ )) . "\\sample-data\\experimental_pottery.csv";
  csv_import_table($table_name, $csv_path);

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

function pftd_deactivation_routine() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'pottery_ftd_object';

  // first export table to CSV and write to new file
  csv_export_table($table_name);
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

  $table_info_query = "SHOW COLUMNS FROM ". DB_NAME .".". $table_name;
  $table_info_results = $wpdb->get_results( $table_info_query );

  $header_row = array();
  foreach ($table_info_results as $result ){
    array_push($header_row, $result->Field);
  };

  $sql_rows = $wpdb->get_results("SELECT * FROM $table_name");
  $data_rows = array();
  foreach( $sql_rows as $row ){
    $entry = array();
    foreach ($row as $item) {
      $entry[] = $item;
    }
    $data_rows[] = $entry;
  };

  $fh = @fopen( 'php://output', 'w' );

  header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
  header( 'Content-Description: File Transfer' );
  header( 'Content-type: text/csv' );
  header( "Content-Disposition: attachment; filename={$filename}" );
  header( 'Expires: 0' );
  header( 'Pragma: public' );
  fputcsv( $fh, $header_row, ",", "\"" );
  foreach ( $data_rows as $data_row ) {
    fputcsv( $fh, $data_row, ",", "\"" );
  }
  fclose( $fh );

  ob_end_flush();

  die();
}

function csv_import_table($table_name, $csv_path){
  if (($open = fopen($csv_path, 'r')) !== false) {
    $headers = fgetcsv($open, 1000, ',');
    while (($data = fgetcsv($open, 1000, ',')) !== false) {
      $keyed_data = array_combine($headers, $data);
      $db_entry = array(
        'pot_type' => $keyed_data['pot_type'],
        'forming_method' => $keyed_data['forming_method'],
        'shape' => $keyed_data['shape'],
        'catalog_number' => $keyed_data['catalog_number'],
        'traces_observed' => $keyed_data['traces_observed'],
      );
      pftd_create_pot($db_entry);
    }
    fclose($open);
  }
}


?>

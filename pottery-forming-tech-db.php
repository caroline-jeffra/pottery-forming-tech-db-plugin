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


?>

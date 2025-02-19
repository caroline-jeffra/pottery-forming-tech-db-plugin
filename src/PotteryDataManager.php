<?php
declare(strict_types=1);

namespace Cjeffra\PotteryFormingTechManager;

class PotteryDataManager
{
	static function import_csv($table_name, $csv_path)
	{
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
				PotteryApiManager::create_pot($db_entry);
			}
			fclose($open);
		}
	}

	static function setup_table()
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

		$csv_path = (plugin_dir_path(__FILE__)) . "\\sample-data\\experimental_pottery.csv";
		PotteryDataManager::import_csv($table_name, $csv_path);

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	static function export_csv()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'pottery_ftd_object';

		ob_start();

		$domain = $_SERVER['SERVER_NAME'];
		$filename = $domain . '-' . $table_name . time() . '.csv';

		$table_info_query = "SHOW COLUMNS FROM " . DB_NAME . "." . $table_name;
		$table_info_results = $wpdb->get_results($table_info_query);

		$header_row = array();
		foreach ($table_info_results as $result) {
			array_push($header_row, $result->Field);
		}
		;

		$sql_rows = $wpdb->get_results("SELECT * FROM $table_name");
		$data_rows = array();
		foreach ($sql_rows as $row) {
			$entry = array();
			foreach ($row as $item) {
				$entry[] = $item;
			}
			$data_rows[] = $entry;
		}
		;

		$fh = @fopen('php://output', 'w');

		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Description: File Transfer');
		header('Content-type: text/csv');
		header("Content-Disposition: attachment; filename={$filename}");
		header('Expires: 0');
		header('Pragma: public');
		fputcsv($fh, $header_row, ",", "\"");
		foreach ($data_rows as $data_row) {
			fputcsv($fh, $data_row, ",", "\"");
		}
		fclose($fh);

		ob_end_flush();

		die();
	}
}
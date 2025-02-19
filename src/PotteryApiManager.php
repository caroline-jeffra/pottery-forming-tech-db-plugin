<?php
declare(strict_types=1);

namespace Cjeffra\PotteryFormingTechManager;

class PotteryApiManager
{
	static function register_routes()
	{
		register_rest_route(
			'pottery-forming-tech-api/v1',
			'/pots/',
			array(
				'methods' => 'GET',
				'callback' => array('PotteryApiManager', 'get_pots'),
				'permission_callback' => '__return_true'
			)
		);

		register_rest_route(
			'pottery-forming-tech-api/v1',
			'/pot/(?P<id>\d+)',
			array(
				'methods' => 'GET',
				'callback' => array('PotteryApiManager', 'get_pot'),
				'permission_callback' => '__return_true'
			)
		);

		register_rest_route(
			'pottery-forming-tech-api/v1',
			'/pot/',
			array(
				'methods' => 'POST',
				'callback' => array('PotteryApiManager', 'create_pot'),
				'permission_callback' => '__return_true'
			)
		);

		register_rest_route(
			'pottery-forming-tech-api/v1',
			'/pot/(?P<id>\d+)',
			array(
				'methods' => 'PATCH',
				'callback' => array('PotteryApiManager', 'update_pot'),
				'permission_callback' => '__return_true'
			)
		);

		register_rest_route(
			'pottery-forming-tech-api/v1',
			'/pot/(?P<id>\d+)',
			array(
				'methods' => 'DELETE',
				'callback' => array('PotteryApiManager', 'delete_pot'),
				'permission_callback' => '__return_true'
			)
		);
	}

	static function get_pots()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'pottery_ftd_object';

		$results = $wpdb->get_results("SELECT * FROM $table_name");
		return $results;
	}

	static function get_pot($request)
	{
		$id = (int) $request['id'];
		if ($id === 0) {
			return wp_send_json(array('result' => 'Invalid ID passed'));
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'pottery_ftd_object';

		$results = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $id");

		return $results;
	}

	static function create_pot($request)
	{
		if (!current_user_can('publish_posts')) {
			return wp_send_json(array('result' => 'Authentication error'));
		}
		global $wpdb;
		$table_name = $wpdb->prefix . 'pottery_ftd_object';

		$rows = $wpdb->insert(
			$table_name,
			array(
				'pot_type' => sanitize_text_field($request['pot_type']),
				'forming_method' => sanitize_text_field($request['forming_method']),
				'shape' => sanitize_text_field($request['shape']),
				'catalog_number' => sanitize_text_field($request['catalog_number']),
				'traces_observed' => sanitize_text_field($request['traces_observed']),
			)
		);
		return $rows;
	}

	static function update_pot($request)
	{
		if (!current_user_can('edit_private_posts')) {
			return wp_send_json(array('result' => 'Authentication error'));
		}
		$id = (int) $request['id'];
		if ($id === 0) {
			return wp_send_json(array('result' => 'Invalid ID passed'));
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'pottery_ftd_object';

		$results = $wpdb->update(
			$table_name,
			array(
				'pot_type' => sanitize_text_field($request['pot_type']),
				'forming_method' => sanitize_text_field($request['forming_method']),
				'shape' => sanitize_text_field($request['shape']),
				'catalog_number' => sanitize_text_field($request['catalog_number']),
				'traces_observed' => sanitize_text_field($request['traces_observed']),
			),
			array(
				'id' => $id,
			)
		);
		return $results;
	}

	static function delete_pot($request)
	{
		if (!current_user_can('delete_private_posts')) {
			return wp_send_json(array('result' => 'Authentication error'));
		}
		$id = (int) $request['id'];
		if ($id === 0) {
			return wp_send_json(array('result' => 'Invalid ID passed'));
		}

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
}
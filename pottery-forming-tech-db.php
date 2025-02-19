<?php
/**
 * Plugin Name: Pottery Forming Technology Manager
 * Plugin URI: https://github.com/caroline-jeffra/pottery-forming-tech-db-plugin
 * Description: Create and manage a database and associated api of archaeological pottery technology data
 * Author: Caroline Jeffra
 * Author URI: https://github.com/caroline-jeffra
 * Text Domain: pottery-forming-tech-db
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Version: 1.0
 */

use Cjeffra\PotteryFormingTechManager\CustomEditor;

require 'vendor/autoload.php';

//register_activation_hook(__FILE__, array('PotteryDataManager', 'setup_table'));
//register_deactivation_hook(__FILE__, array('PotteryDataManager', 'export_csv'));
//add_action('rest_api_init', array('PotteryApiManager', 'register_routes'));

function loadCustomEditor(): CustomEditor
{
	global $customEditor;

	if (!isset($customEditor)) {
		$customEditor = new CustomEditor();
	}

	return $customEditor;
}

loadCustomEditor();
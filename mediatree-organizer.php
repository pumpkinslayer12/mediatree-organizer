<?php

/**
 * Plugin Name: MediaTree Organizer
 * 
 * Plugin URI: https://github.com/pumpkinslayer12/mediatree-organizer/
 *
 * Description: MediaTree Organizer enhances the WordPress Media Library by adding a dynamic directory tree for easy categorization and management of media files. It also offers a shortcode feature for displaying directories and files on the front-end.
 *
 * Author: pumpkinslayer12
 * 
 * Author URI: https://https://github.com/pumpkinslayer12
 * 
 * Version: 1.0
 *
 * Text Domain: mediatree-organizer
 * 
 * License: GPL v3 or later
 *
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package MediaTree_Organizer
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Define Constants for Plugin path and Plugin URL
 */
define('MTO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MTO_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/mto-functions.php
 */
require_once MTO_PLUGIN_PATH . 'includes/mto-functions.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require MTO_PLUGIN_PATH . 'admin/mto-admin.php';
require MTO_PLUGIN_PATH . 'public/mto-public.php';
<?php
/**
 * Plugin Name: BEST Application System
 * Description: Application System for beWANTED and CO
 * Version: 0.1
 * Author: Franz Papst
 * Author URI: http://www.bestvienna.at
 * License: MIT
 */

require("baps-admin.php");
require("baps-ui.php");

add_action("admin_menu", "baps_menu");
add_action('init', 'baps_init');

function baps_menu() {
    add_menu_page("BEST Application System", "BEST Application System", "publish_posts", "baps-admin", "baps_admin_page");
}

function baps_init() {
}

function baps_activation() {
    if (!is_dir(BAPS_UPLOAD_DIR)) {
        mkdir(BAPS_UPLOAD_DIR);
    }
}

function baps_deactivation() {
}

register_activation_hook(__FILE__, 'baps_activation');
register_deactivation_hook(__FILE__, 'baps_deactivation');

?>
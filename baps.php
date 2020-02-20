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
  global $wpdb;
  $wp = $wpdb->prefix;

  $query = "CREATE TABLE IF NOT EXISTS`{$wp}baps_applicants` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255),
      `email` varchar(255),
      `student_id` varchar(255),
      `uuid` varchar(255),
      `study_field` varchar(255),
      `semester` varchar(10),
      PRIMARY KEY (`id`),
      UNIQUE (id, uuid)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;";
    $wpdb->query($query);

  $query = "CREATE TABLE IF NOT EXISTS`{$wp}baps_companies` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255),
      `description` text,
      `timeslots` int(14),
      PRIMARY KEY (`id`),
      UNIQUE (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;";
  $wpdb->query($query);

  $query = "CREATE TABLE IF NOT EXISTS`{$wp}baps_timeslots` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `slot` varchar(10),
      PRIMARY KEY (`id`),
      UNIQUE (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;";
  $wpdb->query($query);

  $query = "CREATE TABLE IF NOT EXISTS `{$wp}baps_timeslots_applicants` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `applicant_id` int(11),
      `company_id` int(11),
      `timeslot_id` int(11),
      `timestamp` timestamp,
      UNIQUE (id),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;";
    $wpdb->query($query);

    $query = "CREATE TABLE IF NOT EXISTS `{$wp}baps_study_fields` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255),
      UNIQUE (id),
      PRIMARY KEY (`id`),
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;";
    $wpdb->query($query);

    // INSERT INTO `wp_baps_timeslots` (`id`, `slot`) VALUES (0, '09:00'), (1, '09:30'), (2, '10:00'), (3, '10:30'), (4, '11:00'), (5, '11:30'), (6, '12:00'), (7, '13:30'), (8, '14:00'), (9, '14:30'), (10, '15:00'), (11, '15:30'), (12, '16:00'), (13, '16:30')

    /*
    if (!is_dir(BAPS_UPLOAD_DIR)) {
        mkdir(BAPS_UPLOAD_DIR);
    }*/
}

function baps_deactivation() {
}

register_activation_hook(__FILE__, 'baps_activation');
register_deactivation_hook(__FILE__, 'baps_deactivation');

?>
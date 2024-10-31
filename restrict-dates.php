<?php
/*
Plugin Name: Restrict Dates Add-On for Gravity Forms
Plugin Url: https://pluginscafe.com/plugin/restrict-dates-for-gravity-forms-pro/
Version: 1.2.1
Description: This plugin adds date restrict options on gravity forms datepicker field
Author: KaisarAhmmed
Author URI: https://pluginscafe.com
License: GPLv2 or later
Text Domain: gravityforms
*/

if (!defined('ABSPATH')) {
    exit;
}


define('GF_RESTRICT_DATES_ADDON_VERSION', '1.2.1');
define('GF_RESTRICT_DATES_ADDON_URL', plugin_dir_url(__FILE__));

if (!function_exists('rdfgf_fs')) {
    // Create a helper function for easy SDK access.
    function rdfgf_fs() {
        global $rdfgf_fs;
        if (!isset($rdfgf_fs)) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';
            $rdfgf_fs = fs_dynamic_init(array(
                'id'             => '15094',
                'slug'           => 'restrict-dates-for-gravity-forms-pro',
                'premium_slug'   => 'restrict-dates-for-gravity-forms-pro',
                'type'           => 'plugin',
                'public_key'     => 'pk_febc62d94850f83a5b528b4a6db0b',
                'is_premium'     => false,
                'premium_suffix' => 'Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                    'first-path' => 'plugins.php',
                    'support'    => false,
                ),
                'is_live'        => true,
            ));
        }
        return $rdfgf_fs;
    }

    // Init Freemius.
    rdfgf_fs();
    // Signal that SDK was initiated.
    do_action('rdfgf_fs_loaded');
}

add_action('gform_loaded', array('GF_Restrict_Dates_AddOn_Bootstrap', 'load'), 5);

class GF_Restrict_Dates_AddOn_Bootstrap {

    public static function load() {

        if (! method_exists('GFForms', 'include_addon_framework')) {
            return;
        }
        // are we on GF 2.5+
        define('GFIC_GF_MIN_2_5', version_compare(GFCommon::$version, '2.5-dev-1', '>='));

        require_once('includes/class-review.php');
        require_once('class-gfrestrictdates.php');

        GFAddOn::register('GFRestrictDatesAddOn');
    }
}

function gf_restrict_dates() {
    return GFRestrictDatesAddOn::get_instance();
}


add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'pcafe_rd_add_action_links', 10, 1);

function pcafe_rd_add_action_links($links) {
    $links['support'] = '<a href="' . rdfgf_fs()->contact_url() . '"style="color:#0077FF;font-weight:700" target="_blank">' . __('Support', 'gravityforms') . '</a>';

    if (rdfgf_fs()->is_not_paying()) {
        $links['upgrade'] = '<a href="' . rdfgf_fs()->get_upgrade_url() . '"style="color:#7BBD02;font-weight:700" target="_blank">' . __('Upgrade Now', 'gravityforms') . '</a>';
    }
    return $links;
}

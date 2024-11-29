<?php
/**
 * Plugin Name: DLBR (Picture-in-Picture Preview)
 * Plugin URI: https://dlbr.dev
 * Description: Picture-in-Picture (PiP) allows you to preview changes in a floating window (always on top of other windows)
 * Version: 1.0.2
 * Author: Dlbr
 * Author URI: https://dlbr.dev
 * Text Domain: wp-pip
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_PiP {
    public function __construct() {
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('post_submitbox_minor_actions', array($this, 'add_pip'), 11);
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain('wp-pip', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function enqueue_scripts($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }

        wp_enqueue_script('wp-pip', plugin_dir_url(__FILE__) . 'js/wp-pip.js', array(), '1.0.2', true);
        wp_enqueue_style('wp-pip', plugin_dir_url(__FILE__) . 'css/wp-pip.css', array(), '1.0.2');

        $preview_link = $this->get_preview_link();
        wp_localize_script('wp-pip', 'wpPipData', array(
            'previewLink' => esc_url($preview_link),
            'pipButtonText' => esc_html__('PiP', 'wp-pip'),
            'unsupportedBrowserText' => esc_html__('Your browser does not support Picture-in-Picture.', 'wp-pip')
        ));
    }

    private function get_preview_link() {
        global $post;

        if (class_exists('WooCommerce') && function_exists('is_product') && is_product()) {
            $product = wc_get_product($post->ID);
            return $product->get_permalink();
        } else {
            return ($post->post_status === 'publish') ? get_permalink($post->ID) : get_preview_post_link($post);
        }
    }

    public function add_pip() {
        echo '<div id="wp-pip-container"></div>';
    }
}

new WP_PiP();

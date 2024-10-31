<?php
/*
Plugin Name: Polkadot Palette
Description: Link uploaded pictures to the colors and categories/tags of a post including the pictures, show the pictures one by one.
Version: 0.1
Author: io
Author URI: i-o.io
License: GPL2
Text Domain: polkadot_palette
Domain Path: /
*/

if (!defined('ABSPATH')) die;

define('POLKADOT_PALETTE_DIR',__DIR__);

require_once(POLKADOT_PALETTE_DIR.'/inc/Polkadot_palette.class.php');
require_once(POLKADOT_PALETTE_DIR.'/inc/Polkadot_palette_get.class.php');

function polkadot_palette_add_links($links) {
 $o = new Polkadot_palette();
 $o->set_language();
 $links[] = '<a href="admin.php?page=polkadot_palette_admin">'.$o->__('Settings').'</a>';
 $links[] = '<a href="admin.php?page=polkadot_palette_batch">'.$o->__('Start analyzing pictures').'</a>';
 return $links;
}

function polkadot_palette_start_it() {
 $o = new Polkadot_palette_admin();
 $o->set_tags_all();
}

function polkadot_palette_add_style() {
 global $wp_query;
 $o = new Polkadot_palette();
 if (!isset($wp_query->query[$o->get_val('name')])) {
  $option = $o->get_val('option');
  $width = $option['width']>0 ? $option['width'].'px' : '100%';
  $height = $option['height']>0 ? $option['height'].'px' : '100%';?>
<style>#polkadot_palette{background:#FFF;font-size:16px;height:<?php echo $height?>;margin:0 auto 30px;padding:0;position:relative;width:<?php echo $width?>;}#polkadot_palette_menu{margin-right:8px;}</style><?php
 }
}

function polkadot_palette_add_script() {
 global $wp_query;
 $o = new Polkadot_palette();
 $is_fullscreen = isset($wp_query->query[$o->get_val('name')]);
 $css_file = $is_fullscreen ? 'show_fullscreen.css' : 'show.css';
 $a = array(
  'url' => admin_url('admin-ajax.php'),
  'dir' => plugins_url('thumbnail/',__FILE__),
  'is_fullscreen' => $is_fullscreen,
 );
 if ($is_fullscreen) {
  $option = $o->get_val('option');
  foreach ($option as $k=>$v) {
   if (strpos($k,'bgm_')===0) $a[$k] = $v;
  }
 }
 wp_enqueue_style('polkadot_palette',plugins_url("/css/$css_file",__FILE__));
 wp_enqueue_script('polkadot_palette',plugins_url('/js/show.js',__FILE__),array('jquery'));
 wp_localize_script('polkadot_palette','PolkadotPalette',$a);
}

function polkadot_palette_add_get_option() {
 $o = new Polkadot_palette_get();
 $o->get_option();
}

function polkadot_palette_add_get_docs() {
 $o = new Polkadot_palette_get();
 $o->get_docs();
}

function polkadot_palette_add_get_palette() {
 $o = new Polkadot_palette_get();
 $o->get_palette();
}

function polkadot_palette_add_get_tags() {
 $o = new Polkadot_palette_get();
 $o->get_tags();
}

function polkadot_palette_add_rewrite_endpoint() {
 $o = new Polkadot_palette();
 $o->add_rewrite_endpoint();
}

function polkadot_palette_add_query_vars($vars) {
 $o = new Polkadot_palette();
 return $o->query_vars($vars);
}

function polkadot_palette_add_template() {
 global $wp_query;
 $o = new Polkadot_palette();
 if (isset($wp_query->query[$o->get_val('name')])) {
  require_once(POLKADOT_PALETTE_DIR.'/inc/Polkadot_palette_template.class.php');
  $o = new Polkadot_palette_template();
 }
}

if (is_admin()) {
 require_once(POLKADOT_PALETTE_DIR.'/inc/Polkadot_palette_admin.class.php');
 register_activation_hook(__FILE__,array('Polkadot_palette_admin','activate'));
 register_deactivation_hook(__FILE__,array('Polkadot_palette_admin','deactivate'));
 register_uninstall_hook(__FILE__,array('Polkadot_palette_admin','uninstall'));
 add_action('polkadot_palette_let_it_do','polkadot_palette_start_it');
 add_filter('plugin_action_links_'.plugin_basename(__FILE__),'polkadot_palette_add_links');
 $polkadot_palette_admin = new Polkadot_palette_admin();
 $polkadot_palette_admin->init_admin_pages();
}

add_action('deleted_option','polkadot_palette_add_rewrite_endpoint');
add_filter('query_vars','polkadot_palette_add_query_vars');
add_action('template_redirect','polkadot_palette_add_template');

add_action('wp_ajax_polkadot_palette_get_option','polkadot_palette_add_get_option');
add_action('wp_ajax_nopriv_polkadot_palette_get_option','polkadot_palette_add_get_option');
add_action('wp_ajax_polkadot_palette_get_palette','polkadot_palette_add_get_palette');
add_action('wp_ajax_nopriv_polkadot_palette_get_palette','polkadot_palette_add_get_palette');
add_action('wp_ajax_polkadot_palette_get_tags','polkadot_palette_add_get_tags');
add_action('wp_ajax_nopriv_polkadot_palette_get_tags','polkadot_palette_add_get_tags');
add_action('wp_ajax_polkadot_palette_get_docs','polkadot_palette_add_get_docs');
add_action('wp_ajax_nopriv_polkadot_palette_get_docs','polkadot_palette_add_get_docs');

add_action('wp_head','polkadot_palette_add_style',7);
add_action('wp_enqueue_scripts','polkadot_palette_add_script');

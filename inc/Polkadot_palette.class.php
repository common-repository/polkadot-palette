<?php
/**
 * Polkadot_palette
 * Author: io
 */
class Polkadot_palette {

 protected $name = 'polkadot_palette';
 protected $plugin_name = 'polkadot-palette';
 protected $table_name = array(
  'pic' => 'polkadot_palette_pic',
  'doc_pic' => 'polkadot_palette_doc_pic',
 );
 protected $option_default = array(
  'is_active' => 0,
  'is_only_on_doc' => 1,
  'threshold' => 15,
  'pattern' => 'circle',
  'thumbnail_width' => 320,
  'thumbnail_height' => 320,
  'thumbnail_quality' => 80,
  'batch_start_at' => 0,
  'batch_end_at' => 0,
  'batch_count' => 0,
  'width' => 0,
  'height' => 320,
  'unit' => 150,
  'space' => 6,
  'palette' => '',
  'title' => 'Showcase - Polkadot Palette',
  'link_text' => 'Showcase',
  'bgm_polkadot_mp3' => '',
  'bgm_polkadot_ogg' => '',
  'bgm_grid_mp3' => '',
  'bgm_grid_ogg' => '',
 );
 protected $option;
 protected $palette = array(
  'purple' => '226 22A 626 62A 62E AE2',
  'blue' => '22E 266 26A 26E',
  'cyan' => '2AA 2AE 2EE 6EE AEE',
  'green' => '262 266 2A2 2A6',
  'lime' => '2E2 2E6 2EA 6E2 6E6 6EA AE6',
  'yellow' => 'AA2 AA6 AE2 EE2 EE6 EEA',
  'orange' => 'A62 E62',
  'red' => 'A22 E22',
  'magenta' => 'E2A E2E',
  'violet' => '66A 66E AAE',
  'pink' => 'E6A E6E EAE',
  'white' => 'EEE',
  'gray' => '666 AAA',
  'black' => '222',
 );


 function __construct() {
  $this->option_default['palette'] = implode(',',array_keys($this->palette));
  $this->option = $this->get_option();
 }


 public function is_active() {
  return ( isset($this->option['is_active']) && !empty($this->option['is_active']) );
 }


 public function set_language() {
  load_plugin_textdomain($this->name,false,$this->plugin_name);
 }


 public function __($id='') {
  return __($id,$this->name);
 }


 public function _e($id='') {
  _e($id,$this->name);
 }


 /**
  * get_val
  * Get the private parameter.
  */
 public function get_val($name) {
  return $this->$name;
 }


 /**
  * get_option
  */
 public function get_option() {
  return wp_parse_args(get_option('polkadot_palette'),$this->option_default);
 }


 /**
  * add_rewrite_endpoint
  */
 public function add_rewrite_endpoint($is_forced=false) {
  if ( $this->is_active() || $is_forced ) {
   add_rewrite_endpoint($this->name,EP_ROOT);
   flush_rewrite_rules();
  }
 }


 /**
  * query_vars
  */
 public function query_vars($vars) {
  $vars[] = $this->name;
  return $vars;
 }

}

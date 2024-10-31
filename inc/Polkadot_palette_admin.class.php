<?php
/**
 * Polkadot_palette_admin
 * Author: io
 */
class Polkadot_palette_admin extends Polkadot_palette {

 private $thumbnail_dir = '';
 private $mime_types = array('image/jpeg','image/png','image/gif');
 private $pic_ext = '\.(jpe?g|png|gif)';
 private $batch_timeout = 3600;


 function __construct() {
  $dir = dirname(__DIR__);
  $this->thumbnail_dir = dirname(__DIR__).'/thumbnail';
  parent::__construct();
  if ($this->is_active()) require_once("{$dir}/inc/Polkadot_palette_analyzer.class.php");
 }


 /**
  * init
  */
 public function init_admin_pages() {
  if ($this->is_active()) {
   $this->set_language();
   add_action('admin_menu',array($this,'add_admin_page'));
   add_action('admin_init',array($this,'admin_settings'));
   add_action('admin_menu',array($this,'add_batch_page'));
   add_action('transition_post_status',array($this,'update'),10,3);
   add_action('update_attached_file',array($this,'update_file'),10,2);
  }
 }


 /**
  * add_admin_page
  */
 public function add_admin_page() {
  add_menu_page('Polkadot Palette Settings','Polkadot Palette','manage_options','polkadot_palette_admin',array($this,'create_admin_page'));
 }


 /**
  * add_batch_page
  */
 public function add_batch_page() {
  add_submenu_page('polkadot_palette_admin','Polkadot Palette',$this->__('Start analyzing pictures'),'manage_options','polkadot_palette_batch',array($this,'create_batch_page'));
 }


 /**
  * create_admin_page
  */
 public function create_admin_page() {?>
<div class="wrap">
 <h2>Polkadot Palette <?php $this->_e('Settings')?></h2>
 <form method="post" action="options.php"><?php
  settings_fields('polkadot_palette');
  do_settings_sections('polkadot_palette_admin');
  submit_button();?>
 </form>
</div><?php
}


 /**
  * admin_settings
  */
 public function admin_settings() {
  register_setting('polkadot_palette','polkadot_palette',array($this,'sanitize'));
  add_settings_section('target','<u>'.$this->__('Analyze pictures for').'</u><p>'.$this->__("After changing this option, 'Start analyzing pictures' is required.").'</p>','','polkadot_palette_admin');
  add_settings_field('id_is_only_on_doc','',array($this,'input_is_only_on_doc'),'polkadot_palette_admin','target');
  add_settings_field('id_threshold',$this->__('Color threshold'),array($this,'input_threshold'),'polkadot_palette_admin','target');
  #add_settings_field('id_pattern','',array($this,'input_pattern'),'polkadot_palette_admin','target');
  add_settings_section('thumbnail','<u>'.$this->__('Thumbnail').'</u><p>'.$this->__("After changing this option, 'Start analyzing pictures' is required.").'</p>','','polkadot_palette_admin');
  add_settings_field('id_thumbnail_width',$this->__('Width'),array($this,'input_thumbnail_width'),'polkadot_palette_admin','thumbnail');
  add_settings_field('id_thumbnail_height',$this->__('Height'),array($this,'input_thumbnail_height'),'polkadot_palette_admin','thumbnail');
  add_settings_field('id_thumbnail_quality',$this->__('Quality'),array($this,'input_thumbnail_quality'),'polkadot_palette_admin','thumbnail');
  add_settings_section('menu','<u>'.$this->__('Menu').'</u>','','polkadot_palette_admin');
  add_settings_field('id_palette',$this->__('Selectable colors'),array($this,'input_palette'),'polkadot_palette_admin','menu');
  add_settings_section('list','<u>'.$this->__('Display in footer').'</u>','','polkadot_palette_admin');
  add_settings_field('id_width',$this->__('Width'),array($this,'input_width'),'polkadot_palette_admin','list');
  add_settings_field('id_height',$this->__('Height'),array($this,'input_height'),'polkadot_palette_admin','list');
  add_settings_field('id_link_text',$this->__('Link text for Showcase'),array($this,'input_link_text'),'polkadot_palette_admin','show');
  add_settings_section('show','<u>'.$this->__('Showcase').'</u>','','polkadot_palette_admin');
  add_settings_field('title',$this->__('Title'),array($this,'input_title'),'polkadot_palette_admin','show');
  add_settings_field('id_bgm_grid',$this->__('BGM for Grid'),array($this,'input_bgm_grid'),'polkadot_palette_admin','show');
  add_settings_field('id_bgm_polkadot',$this->__('BGM for Polkadot'),array($this,'input_bgm_polkadot'),'polkadot_palette_admin','show');
 }


 /**
  * input_*
  */
 public function input_is_only_on_doc() {
  echo "<input name='polkadot_palette[pattern]' type='hidden' value='{$this->option['pattern']}' />";
  $checked = empty($this->option['is_only_on_doc']) ? '' : "checked='checked'";
  echo "<label><input {$checked} name='polkadot_palette[is_only_on_doc]' type='checkbox' value='1' /> ".$this->__('If this option is active, analyze the pictures on public posts only.')."</label>";
 }
 public function input_threshold() {
  echo "<input name='polkadot_palette[threshold]' max='100' min='0' style='width:80px;' type='number' value='{$this->option['threshold']}' />% (".sprintf($this->__('Default: %d'),$this->option_default['threshold'])."%)";
 }
 public function input_pattern() {
 }
 public function input_thumbnail_width() {
  echo "<input name='polkadot_palette[thumbnail_width]' min='0' style='width:80px;' type='number' value='{$this->option['thumbnail_width']}' />px";
 }
 public function input_thumbnail_height() {
  echo "<input name='polkadot_palette[thumbnail_height]' min='0' style='width:80px;' type='number' value='{$this->option['thumbnail_height']}' />px";
 }
 public function input_thumbnail_quality() {
  echo "<input name='polkadot_palette[thumbnail_quality]' max='100' min='1' style='width:80px;' type='number' value='{$this->option['thumbnail_quality']}' /> (".$this->__('Low:1 - High:100').")";
 }
 public function input_width() {
  echo "<input name='polkadot_palette[width]' min='0' style='width:80px;' type='number' value='{$this->option['width']}' />px";
  echo '<br />';
  $this->_e('0 will be set as 100%.');
 }
 public function input_height() {
  echo "<input name='polkadot_palette[height]' min='0' style='width:80px;' type='number' value='{$this->option['height']}' />px";
  echo '<br />';
  $this->_e('0 will be set as 100% but it does NOT work good.');
 }
 public function input_palette() {
  echo "<input name='polkadot_palette[palette]' type='hidden' value='{$this->option['palette']}' />";
  $palette = explode(',',$this->option['palette']);
  foreach ($this->palette as $k=>$v) {
   $checked = in_array($k,$palette) ? "checked='checked'" : '';
   echo "<label style='background:{$k};padding:10px;text-align:center;'><input {$checked} name='polkadot_palette[palette_color][]' type='checkbox' value='{$k}' /></label>";
  }
 }
 public function input_title() {
  echo '<input name="polkadot_palette[title]" type="string" value="'.htmlspecialchars($this->option['title']).'" />';
 }
 public function input_link_text() {
  echo '<input name="polkadot_palette[link_text]" type="string" value="'.htmlspecialchars($this->option['link_text']).'" />';
 }
 public function input_bgm_grid() {
  echo '<input name="polkadot_palette[bgm_grid_mp3]" placeholder="'.$this->__('URL/path for mp3').'" type="string" style="width:400px;" value="'.htmlspecialchars($this->option['bgm_grid_mp3']).'" /><br />';
  echo '<input name="polkadot_palette[bgm_grid_ogg]" placeholder="'.$this->__('URL/path for ogg').'" type="string" style="width:400px;" value="'.htmlspecialchars($this->option['bgm_grid_ogg']).'" /><br />';
  $this->_e("Playable audio file depends on browser. It's good to set the both.");
  echo "<br />";
  $this->_e("Empty doesn't play BGM.");
 }
 public function input_bgm_polkadot() {
  echo '<input name="polkadot_palette[bgm_polkadot_mp3]" placeholder="'.$this->__('URL/path for mp3').'" style="width:400px;" type="string" value="'.htmlspecialchars($this->option['bgm_polkadot_mp3']).'" /><br />';
  echo '<input name="polkadot_palette[bgm_polkadot_ogg]" placeholder="'.$this->__('URL/path for ogg').'" style="width:400px;" type="string" value="'.htmlspecialchars($this->option['bgm_polkadot_ogg']).'" /><br />';
  $this->_e("Playable audio file depends on browser. It's good to set the both.");
  echo "<br />";
  $this->_e("Empty doesn't play BGM.");
 }


 /**
  * sanitize
  * Sanitize input data.
  */
 public function sanitize($input) {
  $input['is_only_on_doc'] = (!isset($input['is_only_on_doc'])||empty($input['is_only_on_doc'])) ? 0 : 1;
  foreach (array('threshold','thumbnail_width','thumbnail_height','thumbnail_quality','unit','space') as $field) $input[$field] = (int)$input[$field];
  foreach (array('threshold','thumbnail_quality') as $field) {
   if ($input[$field]>100) $input[$field] = 100;
  }
  $input['palette'] = is_array($input['palette_color']) ? sanitize_text_field(implode(',',$input['palette_color'])) : '';
  foreach (array('pattern','title','link_text','bgm_grid_mp3','bgm_grid_ogg','bgm_polkadot_mp3','bgm_polkadot_ogg') as $k) $input[$k] = sanitize_text_field($input[$k]);
  foreach ($this->option as $k=>$v) {
   if (!isset($input[$k])) $input[$k] = $v;
  }
  return $input;
 }


 /**
  * create_batch_page
  */
 public function create_batch_page() {
  $title = '';
  $body = '';
  if ($this->is_ready()) {
   wp_schedule_single_event(time(),'polkadot_palette_let_it_do');
   $title = $this->__('Analyzing all the pictures will start soon.');
   $body = $this->__('The job will take a long time.<br />The job will keep running in background. You can close this window.');
  } else {
   $hour = ceil($this->batch_timeout/3600);
   $title = $this->__('The job is running');
   $body = sprintf($this->__("The job may take a long time.<br /><br />If it says the same message anytime, the job didn't finish successfully.<br />Attempt this job %d hours later."),$hour);
  }?>
<div class="wrap">
 <h2><?php echo $title?></h2>
 <p><?php echo $body?></p>
 <h3><?php $this->_e("Analyzing pictures doesn't start?")?></h3>
 <p><?php $this->_e('Check the follows:')?></p>
 <ol>
  <li><?php $this->_e('Imagick has been installed?')?></li>
  <li><?php $this->_e('cron works?')?></li>
 </ol>
 <p><?php $this->_e("You may have to add `define('ALTERNATE_WP_CRON', true);` to wp-config.php to use cron.<br />The cron starts when a page is loaded, click any link to let the cron start, please.")?></p>
</div><?php
 }


 /**
  * update
  * Get data and insert it into DB when a thing is posted.
  */
 public function update($new_status,$old_status,$post) {
  $pic_ids = $this->_get_image_ids($post->ID);
  foreach ($pic_ids as $id) $this->_set_tags($id);
 }


 /**
  * update_file
  * Get data and insert it into DB when a file is posted.
  */
 public function update_file($info,$id) {
  $this->_set_tags($id);
 }


 /**
  * set_option
  */
 public function set_option($a) {
  $this->option = $this->get_option();
  foreach ($a as $k=>$v) $this->option[$k] = $v;
  update_option('polkadot_palette',$this->option);
 }


 /**
  * is_ready
  */
 public function is_ready() {
  $now = time();
  $this->option = $this->get_option();
  if ( $this->option['batch_end_at']>=$this->option['batch_start_at'] || $now>$this->option['batch_start_at']+$this->batch_timeout ) {
   $this->set_option(array('batch_start_at'=>$now));
   return true;
  }
  return false;
 }


 /**
  * set_palette_all
  * Set color information from all pictures.
  */
 public function set_tags_all() {
  global $wpdb;
  $wpdb->query("TRUNCATE TABLE {$this->table_name['doc_pic']}");
  $wpdb->query("TRUNCATE TABLE {$this->table_name['pic']}");
  $count = 0;
  $this->set_option(array('batch_count'=>$count));
  $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_parent = 0 AND post_mime_type = ''");
  foreach ($ids as $id) $this->_get_image_ids($id);
  $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_parent = 0 AND guid !='' AND post_mime_type != ''");
  foreach ($ids as $id) if ($this->_set_tags($id)) $count++;
  $this->set_option(array('batch_end_at'=>time(),'batch_count'=>$count));
 }


 /**
  * _set_tags
  * Set color information from the picture.
  */
 private function _set_tags($id) {
  global $wpdb;

  $q = $wpdb->prepare("SELECT guid,post_mime_type,doc_id FROM {$wpdb->posts} P LEFT JOIN {$this->table_name['doc_pic']} PPDP ON PPDP.pic_id = P.ID WHERE ID = %s",$id);
  $a = $wpdb->get_row($q);
  if ( !$this->is_picture($a->post_mime_type) || !empty($this->option['is_only_on_doc']) && is_null($a->doc_id) ) return false;

  $path = "{$this->thumbnail_dir}/{$id}.jpg";
  $command = sprintf("curl '%s' | convert - -resize %dx%d -sampling-factor 4:2:0 -strip -quality %d -interlace JPEG -colorspace sRGB '%s'",$a->guid,$this->option['thumbnail_width'],$this->option['thumbnail_height'],$this->option['thumbnail_quality'],$path);
  `$command`;
  if (!chmod($path,0777)) return false;

  $info = $this->_get_palette($path);
  $q = $wpdb->prepare("SELECT meta FROM {$this->table_name['pic']} WHERE id = %d",$id);
  $a = $wpdb->get_col($q);
  $meta = isset($a) ? array() : json_decode($a[0],true);
  $meta['width'] = $info['width'];
  $meta['height'] = $info['height'];
  $tags = array_merge($this->_make_tags($info['colors'],'col'),$this->_get_tag_ids($id));
  $tags = array_unique($tags);

  $wpdb->replace($this->table_name['pic'],array('id'=>$id,'meta'=>json_encode($meta),'tags'=>implode(' ',$tags)),array('%d','%s','%s'));

  return true;
 }


 /**
  * _get_image_ids
  * Crawl $doc_id's document to get image ids.
  */
 private function _get_image_ids($doc_id) {
  global $wpdb;
  $pic_ids = array();

  $wpdb->delete($this->table_name['doc_pic'],array('doc_id'=>$doc_id));

  $q = $wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE ID = %d AND post_parent = 0",$doc_id);
  $a = $wpdb->get_col($q);
  $body = $a[0];
  if ($body==null) return $pic_ids;

  // Get picture URLs
  if (!preg_match_all('/<img [^<>]*src=(["\'])([^<>"\']+)\\1/',$body,$r,PREG_PATTERN_ORDER)) return $pic_ids;
  $paths = array_unique($r[2]);

  // Get IDs for pictures
  $docs = array();
  foreach ($paths as $path) {
   $path = $this->get_path($path);
   $path = preg_replace("/(-\d+x\d+)?{$this->pic_ext}$/",'',$path);
   $q = $wpdb->prepare("SELECT ID,guid FROM {$wpdb->posts} WHERE guid LIKE %s","%$path%");
   $docs = array_merge($docs,(array)$wpdb->get_results($q));
  }
  foreach ($docs as $a) {
   $pic_ids[] = $a->ID;
   $wpdb->insert($this->table_name['doc_pic'],array('doc_id'=>$doc_id,'pic_id'=>$a->ID),array('%d','%d'));
  }

  return $pic_ids;
 }


 /**
  * _get_palette
  * Get color information from the picture.
  */
 private function _get_palette($apath) {
  $o = new Polkadot_palette_analyzer($apath,$this->option['threshold']);
  return $o->get_result();
 }


 /**
  * _make_tags
  */
 private function _make_tags($a,$name) {
  foreach ($a as $k=>$v) $a[$k] = "{$v}_{$name}";
  return $a;
 }


 /**
  * _delete
  * Delete the record.
  */
 private function _delete($id=null) {
  global $wpdb;
  $o = get_post($id);
  $id = $o->ID;
  if ($o->is_picture($o->post_mime_type)) {
   $wpdb->delete($this->table_name['doc_pic'],array('pic_id'=>$id));
   $wpdb->delete($this->table_name['pic'],array('id'=>$id));
   unlink("{$this->thumbnail_dir}/{$id}.jpg");
  }
  else {
   $q = $wpdb->prepare("SELECT pic_id FROM {$this->table_name['doc_pic']} WHERE doc_id = %d",$id);
   $pic_ids = $wpdb->get_col($q);
   $wpdb->delete($this->table_name['doc_pic'],array('doc_id'=>$id));
   foreach ($pic_ids as $pic_id) {
    $tags = array_unique($this->_get_tag_ids($id));
    $wpdb->update($this->table_name['pic'],array('tags'=>implode(' ',$tags)),array('id'=>$pic_id),array('%s'),array('%d'));
   }
  }
 }


 /**
  * _get_tag_ids
  */
 private function _get_tag_ids($id) {
  global $wpdb;
  $tags = array();
  $q = $wpdb->prepare("SELECT tags FROM {$this->table_name['pic']} WHERE id = %d",$id);
  $a = $wpdb->get_col($q);
  if (isset($a[0])) {
   $tags = explode(' ',$a[0]);
   $tags = array_filter($tags,function($v){return strpos($v,'_col');});
  }
  $q = <<<Q
SELECT P.ID AS id, TT.term_taxonomy_id AS tag_id
FROM {$wpdb->posts} P
INNER JOIN {$wpdb->term_relationships} TR ON TR.object_id = P.ID
INNER JOIN {$wpdb->term_taxonomy} TT ON TT.term_taxonomy_id = TR.term_taxonomy_id
LEFT OUTER JOIN {$this->table_name['doc_pic']} PSDP ON PSDP.doc_id = P.ID
WHERE PSDP.pic_id = %d AND P.post_status = 'publish' AND ( TT.taxonomy = 'category' OR TT.taxonomy = 'post_tag' )
Q;
  $q = $wpdb->prepare($q,$id);
  $docs = $wpdb->get_results($q);
  foreach ($docs as $a) {
   $tags[] = "{$a->id}_id";
   $tags[] = "{$a->tag_id}_tag";
  }
  return array_unique($tags);
 }


 /**
  * get_path
  */
 private static function is_picture($mime_type) {
  return strpos($mime_type,'image/')===0;
 }


 /**
  * get_path
  */
 public static function get_path($str) {
  return preg_replace("|^https?://[^/]+/|",'',preg_replace("/^\.+/",'',$str));
 }


 /**
  * _init_db
  * Create the database for writing IDs to match documents and pictures.
  */
 private function _init_db() {
  global $wpdb;

  $charset = $wpdb->get_charset_collate();

  $q = <<<Q
CREATE TABLE IF NOT EXISTS {$this->table_name['pic']} (
 id BIGINT(20) UNSIGNED,
 meta TEXT,
 tags TEXT BINARY DEFAULT '' NOT NULL,
 PRIMARY KEY(id),
 FOREIGN KEY(id) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE,
 FULLTEXT(tags)
) $charset
ENGINE = MyISAM
Q;
  $wpdb->query($q);

  $q = <<<Q
CREATE TABLE IF NOT EXISTS {$this->table_name['doc_pic']} (
 doc_id BIGINT(20) UNSIGNED,
 pic_id BIGINT(20) UNSIGNED,
 INDEX(doc_id),
 INDEX(pic_id),
 FOREIGN KEY(doc_id) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE,
 FOREIGN KEY(pic_id) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
) $charset
Q;
  $wpdb->query($q);
 }


 /**
  * _init_dir
  * Create the directory for keeping the original pictures.
  */
 private function _init_dir() {
  if (!file_exists($this->thumbnail_dir)) {
   mkdir($this->thumbnail_dir);
   chmod($this->thumbnail_dir,0777);
  }
 }


 /**
  * activate
  */
 public static function activate() {
  $o = new Polkadot_palette_admin();
  $o->_init_dir();
  $o->_init_db();
  $option = $o->get_val('option_default');
  $option['is_active'] = 1;
  if (!update_option($o->get_val('name'),$option)) add_option($o->get_val('name'),$option);
  $o->add_rewrite_endpoint($option['is_active']);
 }


 /**
  * deactivate
  */
 public static function deactivate() {
  flush_rewrite_rules();
  $o = new Polkadot_palette_admin();
  $option = $o->get_val('option_default');
  $option['is_active'] = 0;
  update_option($o->get_val('name'),$option);
 }


 /**
  * uninstall
  * Remove the databases.
  */
 public static function uninstall() {
  global $wpdb;
  $o = new Polkadot_palette_admin();
  delete_option($o->get_val('name'));
  $table_name = $o->get_val('table_name');
  foreach ($table_name as $k=>$v) $wpdb->query("DROP TABLE {$v}");
 }

}

<?php
/**
 * Polkadot_palette_get
 * Author: io
 */
class Polkadot_palette_get {

 private $o;

 function __construct() {
  $this->o = new Polkadot_palette();
 }

 public function get_docs() {
  global $wpdb;

  $id = sanitize_text_field((string)$_GET['id']);
  $tag_ids = sanitize_text_field((string)$_GET['tag_ids']);
  $color = sanitize_text_field((string)$_GET['color']);
  $start = (int)$_GET['start'];
  $rows = (int)$_GET['rows'];

  if ($start<0) $start = 0;
  if ($rows==0) $rows = 210;

  $table_name = $this->o->get_val('table_name');
  $palette = $this->o->get_val('palette');

  $w = array('c'=>array(),'d'=>array());
  $c = array();

  $pics = array();
  $q = <<<Q
 SELECT PPP.id, PPP.meta, PPDP1.doc_id, P.post_date AS date, P.post_title AS title
 FROM {$table_name['pic']} PPP
 LEFT JOIN {$table_name['doc_pic']} PPDP1 ON PPDP1.pic_id = PPP.id
Q;

  if ($id!='') {
   $q .= ' AND PPDP1.doc_id != %d';
   $w['d'][] = (int)$id;
  }

  $q .= <<<Q
 LEFT JOIN {$table_name['doc_pic']} PPDP2 ON PPDP2.pic_id = PPDP1.pic_id AND PPDP2.doc_id > PPDP1.doc_id
 LEFT JOIN {$wpdb->posts} P ON P.ID = PPDP1.doc_id
Q;

  $w['c'][] = 'PPDP2.pic_id IS NULL';
  if ($tag_ids!='') {
   $tag_ids = explode(',',$tag_ids);
   foreach ($tag_ids as $tag_id) $c[] = "+{$tag_id}_tag";
  }
  if ( $color!='' && isset($palette[$color]) ) $c[] = '+('.str_replace(' ','_col ',$palette[$color]).'_col)';
  if (!empty($c)) {
   $w['c'][] = 'MATCH(PPP.tags) AGAINST (%s IN BOOLEAN MODE)';
   $w['d'][] = implode(' ',$c);
  }

  if (!empty($w['c'])) $q .= ' WHERE '.implode(' AND ',$w['c']);
  $q .= ' LIMIT %d,%d';
  $w['d'][] = $start;
  $w['d'][] = $rows;
  $q = $wpdb->prepare($q,$w['d']);
  $docs = $wpdb->get_results($q);
  $data = array('docs'=>$docs);

  die(json_encode($data));
 }


 public function get_palette() {
  $docs = array();

  $option = $this->o->get_option();
  $data = array_filter(explode(',',$option['palette']),'strlen');

  if (!empty($data)) {
   $this->o->set_language();
   $docs[] = array('id'=>'','name'=>$this->o->__('All'),'class'=>'on');
  }
  foreach ($data as $v) $docs[] = array('id'=>$v);

  die(json_encode($docs));
 }


 public function get_tags() {
  global $wpdb;

  $q = <<<Q
 SELECT TT.term_taxonomy_id AS id, TT.taxonomy, T.name
 FROM {$wpdb->term_taxonomy} TT
 INNER JOIN {$wpdb->terms} T ON T.term_id = TT.term_id
 WHERE ( TT.taxonomy = 'category' OR TT.taxonomy = 'post_tag' ) AND TT.count > 0
Q;
  $docs = (array)$wpdb->get_results($q);
  usort($docs,array($this,'sort_by_name'));

  $count = array();
  foreach ($docs as $a) {
   if (!isset($count[$a->name])) $count[$a->name] = 0;
   $count[$a->name]++;
  }

  $this->o->set_language();
  foreach ($docs as &$a) {
   if ($count[$a->name]>1) $a->name .= ' ('.($a->taxonomy=='category'?$this->o->__('Category'):$this->o->__('Tag')).')';
  }
  if (count($docs)>0) array_unshift($docs,(object)array('id'=>'','name'=>'-- '.$this->o->__('Category').' | '.$this->o->__('Tag').' --'));

  die(json_encode($docs));
 }


 private function sort_by_name($a,$b) {
  if ($a->name==$b->name) {
   return $a->taxonomy=='category' ? -1 : 1;
  }
  $c = array($a->name,$b->name);
  sort($c);
  return $c[0]==$a->name ? -1 : 1;
 }


 public function get_option() {
  $option = $this->o->get_option();
  $is_bgm = ( !empty($option['bgm_polkadot_mp3']) || !empty($option['bgm_polkadot_ogg']) || !empty($option['bgm_grid_mp3']) || !empty($option['bgm_grid_ogg']) );
  $docs = array(
   'is_bgm' => $is_bgm,
   'link_text' => $option['link_text'],
  );
  die(json_encode($docs));
 }


}

<?php
/**
 * Polkadot_palette_analyzer
 * Author: io
 */
class Polkadot_palette_analyzer {

 private $path = '';
 private $im = null;
 private $threshold = 0.1;
 private $unit = 10;


 function __construct($path,$threshold=10) {
  if (class_exists('Imagick')) $this->im = new Imagick($path);
  $this->path = $path;
  $this->threshold = $threshold * 0.01;
 }


 /**
  * get_result
  */
 public function get_result() {
  $a = $this->_get_info();
  $a['colors'] = array();

  // analyze the picture and count colors
  $count = $this->_analyze_concentric($a,$this->unit);

  if (!empty($count)) {
   $colors = array();
   $sum = array_sum(array_values($count));
   foreach ($count as $k=>$v) {
    if ($v/$sum>=$this->threshold) $colors[] = $k;
   }
   $a['colors'] = $colors;
  }
  unset($a['format']);

  return $a;
 }


 /**
  * _get_info
  * Get the picture's information.
  */
 private function _get_info() {
  $a = array('format'=>'','width'=>0,'height'=>0);
  if (is_null($this->im)) {
   $command = "identify -format '%m,%w,%h' '{$this->path}'";
   $r = explode(',',`$command`);
   $a['format'] = strtolower($r[0]);
   $a['width'] = (int)$r[1];
   $a['height'] = (int)$r[2];
  }
  else {
   $a['format'] = strtolower($this->im->getImageFormat());
   $a['width'] = $this->im->getImageWidth();
   $a['height'] = $this->im->getImageHeight();
  }
  return $a;
 }


 /**
  * _analyze_concentric
  *
  */
 private function _analyze_concentric($info,$u) {
  $n = array();
  $u += 2;
  $c = array($info['width']/2,$info['height']/2);
  $R = min($c) - 5;
  for ($r=$R/$u/2;$r<$R-$R/$u;$r+=$R/$u) {
   for ($Q=0;$Q<2*M_PI;$Q+=M_PI/8) {
    $rgb = $this->_rgb(round($c[0]+$r*cos($Q)),round($c[1]+$r*sin($Q)),$info);
    if ($rgb!='') {
     if (!isset($n[$rgb])) $n[$rgb] = 0;
     $n[$rgb]++;
    }
   }
  }
  return $n;
 }


 /**
  * _rgb
  * Get quantized color at point (x,y).
  *
  */
 private function _rgb($x,$y,$info) {
  $p = null;
  if (is_null($this->im)) {
   $command = "convert '{$this->path}' -define {$info['format']}:size={$info['width']}x{$info['height']} -crop 1x1+{$x}+{$y} -format '%[fx:r*255],%[fx:g*255],%[fx:b*255],%[fx:a*255]' info:";
   $rgba = `$command`;
   list($p['r'],$p['g'],$p['b'],$p['a']) = explode(',',$rgba);
   foreach ($p as $k=>$v) $p[$k] = (int)$v;
  }
  else $p = $this->im->getImagePixelColor($x,$y)->getColor();
  // Assume that super white (#FFF) and super black (#000) are not color in nature.
  return ( is_null($p) || $this->_is_artificial($p) ) ? '' : $this->_quantize($p['r'],4).$this->_quantize($p['g'],4).$this->_quantize($p['b'],4);
 }


 /**
  * _quantize
  *
  */
 private function _quantize($v,$n) {
  return sprintf("%01X",(8+16*floor($v*$n/256))/$n);
 }


 /**
  * _is_artificial
  * In case of assuming that super white and super black are not color in nature.
  */
 private function _is_artificial($p) {
  $sum = array_sum($p) - $p['a'];
  return ( !$p['a'] || $sum==0 || $sum==765 );
 }

}

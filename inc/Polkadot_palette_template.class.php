<?php
/**
 * Polkadot_palette_template
 * Author: io
 */
class Polkadot_palette_template extends Polkadot_palette {

 function __construct() {
  parent::__construct();
  $this->set_language();
  if (!$this->is_active()) die($this->__('Polkadot Palette has not been activated.<br />Activate the plugin, please.'));
 }

}

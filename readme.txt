=== Polkadot Palette ===
Contributors: httpioio
Tags: photo, picture, image, slideshow, showcase
Requires at least: 4.9.4
Tested up to: 4.9.4
Requires PHP: 5.4.16
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Polkadot Palette links uploaded pictures to the colors and categories/tags of a post including the pictures, show the pictures one by one.

== Description ==

Polkadot Palette links uploaded pictures to the colors and categories/tags of a post including the pictures, show the pictures one by one on footer in each page, the whole screen / the full screen mode called 'Showcase'.

== Installation ==

Polkadot Palette requires ImageMagick(Imagick).

1. Install `ImageMagick`
2. Confirm that command `convert` works ( It's better that class `Imagick` works on PHP )
3. Add `define('ALTERNATE_WP_CRON', true);` to wp-config.php
4. Install plugin `Polkadot Palette`

== Frequently Asked Questions ==

= The plugin doesn't work? =

Confirm if ImageMagick has been installed and cron on your WordPress works, please.

= I choose color but the picture shown doesn't have the color? =

There are 2 causes:
* This plugin gets some dots from each picture to get color information and quantize the information. The color you shoose might be a bit different from the quantized color.
* How much color included depends on the threshold. The default value is 15%. Change the value at the settings and analyze the pictures again.

= I can't change the size of pictures shown? =

You can change the size of thumbnails on the settings but the size of pictures shown in footer and in the Showcase mode can't be changed on the settings. Please edit js/show.js to change the values of 'this.unit_*'.

= I set BGM for the Showcase mode, but it doesn't work. =

Playable BGM file format depends on browser. Set mp3 file and ogg file both.

= All pictures don't show up? =

This plugin gets 210 pictures' data maximum at one access. Edit Polkadot_palette_get.class.php to change the value if you want to show more pictures.

= The picture is on a post with categories or tags but the picture is not liked with them =

This plugin requires img tags to link the pictures and the categories/tags of the post.

== Screenshots ==

1. Display Polkadot Palette in footer. You can change the width and the height.
2. Showcase mode shows pictures with the full screen to show pictures. You can add BGM.

== Changelog ==

= 0.1 =
*Release Date: 2018/03/28*
First release.

== Upgrade Notice ==
*Release Date: 2018/03/28*
First release.

<?php
/**
 * Draw a transparent image with platform name text.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
	// Getting platform name
	if (isset($_GET['caption']))
		$caption = strip_tags(utf8_decode(urldecode($_GET['caption'])));
	else
		$caption = 'No caption';

	// Configuring image
	$img_width = 30;
	$img_height = ceil(strlen($caption)*8);
	$background_color = 'FFFFFF';
	$font_color = 0;
	$font_size = 3;

	// Create color
	function make_color($color) {
		global $img;
		$red = hexdec(substr($color, 0, 2));
		$green = hexdec(substr($color, 2, 2));
		$blue = hexdec(substr($color, 4, 2));
		return imagecolorallocate($img, $red, $green, $blue);
	}
	
	// Sending header
	header("Content-type: image/png");
	
	/*
	 * Creating image
	 */
	// Creating ressource
	$img = imagecreate($img_width, $img_height);
	// Creating colors
	$background_color_id = make_color($background_color);
	$font_color = make_color($font_color);
	// Writing text
	imagestringup($img, $font_size, (int) ($img_width/2-$img_width/3), $img_height-3, $caption, $font_color);
	// Making image transparent
	imagecolortransparent($img, $background_color_id);
	// Returning image
	imagepng($img);
	// Freeing memory
	imagedestroy($img);
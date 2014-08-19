<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Draw a transparent image with platform name text.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Getting platform name.

if (isset($_GET['caption'])) {
    $caption = strip_tags(utf8_decode(urldecode($_GET['caption'])));
} else {
    $caption = 'No caption';
}

// Configuring image.
$img_width = 30;
$img_height = ceil(strlen($caption)*8);
$background_color = 'FFFFFF';
$font_color = 0;
$font_size = 3;

// Create color.
function make_color($color) {
    global $img;

    $red = hexdec(substr($color, 0, 2));
    $green = hexdec(substr($color, 2, 2));
    $blue = hexdec(substr($color, 4, 2));
    return imagecolorallocate($img, $red, $green, $blue);
}

// Sending header.
header("Content-type: image/png");

/*
 * Creating image
 */
// Creating ressource.
$img = imagecreate($img_width, $img_height);

// Creating colors.
$background_color_id = make_color($background_color);
$font_color = make_color($font_color);

// Writing text.
imagestringup($img, $font_size, (int) ($img_width/2-$img_width/3), $img_height-3, $caption, $font_color);

// Making image transparent.
imagecolortransparent($img, $background_color_id);

// Returning image.
imagepng($img);

// Freeing memory.
imagedestroy($img);
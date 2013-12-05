<?php

class block_vmoodle_renderer extends plugin_renderer_base {
	
	function pix_url($image, $subplugin = null){
		global $CFG, $OUTPUT;
		
		if (!$subplugin){
			return $OUTPUT->pix_url($image, 'block_vmoodle');
		}
		
		list($type, $plugin) = explode('_', $subplugin);
		
		$parts = pathinfo($image);
		
		$filepath = $CFG->dirroot.'/blocks/vmoodle/plugins/'.$plugin.'/pix/'.$parts['filename'];
		
		// we do not support SVG
		$realpath = block_vmoodle_renderer::image_exists($filepath, false);
		$parts = pathinfo($realpath);

		return $CFG->wwwroot.'/blocks/vmoodle/plugins/'.$plugin.'/pix/'.$parts['filename'].'.'.$parts['extension'];
	}

    /**
     * Checks if file with any image extension exists.
     *
     * The order to these images was adjusted prior to the release of 2.4
     * At that point the were the following image counts in Moodle core:
     *
     *     - png = 667 in pix dirs (1499 total)
     *     - gif = 385 in pix dirs (606 total)
     *     - jpg = 62  in pix dirs (74 total)
     *     - jpeg = 0  in pix dirs (1 total)
     *
     * There is work in progress to move towards SVG presently hence that has been prioritiesed.
     *
     * @param string $filepath
     * @param bool $svg If set to true SVG images will also be looked for.
     * @return string image name with extension
     */
    private static function image_exists($filepath, $svg = false) {
        if ($svg && file_exists("$filepath.svg")) {
            return "$filepath.svg";
        } else  if (file_exists("$filepath.png")) {
            return "$filepath.png";
        } else if (file_exists("$filepath.gif")) {
            return "$filepath.gif";
        } else  if (file_exists("$filepath.jpg")) {
            return "$filepath.jpg";
        } else  if (file_exists("$filepath.jpeg")) {
            return "$filepath.jpeg";
        } else {
            return false;
        }
    }
}

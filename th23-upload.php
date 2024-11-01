<?php
/*
Plugin Name: th23 Upload
Description: Resize images on upload to maximum allowed dimensions and add watermark
Version: 1.6.0
Author: Thorsten Hartmann (th23)
Author URI: http://th23.net/
Text Domain: th23-upload
Domain Path: /lang
License: GPLv2 only
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Copyright 2020, Thorsten Hartmann (th23)
http://th23.net/

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License version 2, as published by the Free Software Foundation. You may NOT assume that you can use any other version of the GPL.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
This license and terms apply for the Basic part of this program as distributed, but NOT for the separately distributed Professional add-on!
*/

class th23_upload {

	// Initialize class-wide variables
	public $plugin = array(); // plugin (setup) information
	public $options = array(); // plugin options (user defined, changable)
	public $data = array(); // data exchange between plugin functions

	function __construct() {

		// Setup basics
		$this->plugin['file'] = __FILE__;
		$this->plugin['basename'] = plugin_basename($this->plugin['file']);
		$this->plugin['dir_url'] = plugin_dir_url($this->plugin['file']);
		$this->plugin['version'] = '1.6.0';

    	// Load plugin options
		$this->options = (array) get_option('th23_upload_options');

		// Localization
		load_plugin_textdomain('th23-upload', false, dirname($this->plugin['basename']) . '/lang');

		// == customization: from here on plugin specific ==

		// Adjust WP default image resizing on upload
		add_action('init', array(&$this, 'adjust_wp_default'));

		// Resize images on upload
		add_filter('wp_handle_upload', array(&$this, 'resize_images'));

	}

	// Ensure PHP <5 compatibility
	function th23_upload() {
		self::__construct();
	}

	// Error logging
	function log($msg) {
		if(!empty(WP_DEBUG) && !empty(WP_DEBUG_LOG)) {
			if(empty($this->plugin['data'])) {
				$plugin_data = get_file_data($this->plugin['file'], array('Name' => 'Plugin Name'));
				$plugin_name = $plugin_data['Name'];
			}
			else {
				$plugin_name = $this->plugin['data']['Name'];
			}
			error_log($plugin_name . ': ' . print_r($msg, true));
		}
	}

	// === COMMON ===

	// Adjust WP default image resizing on upload
	function adjust_wp_default() {
		if(!empty($this->options['wp_default'])) {
			// disable hard width/height limit at 2560px upon upload
			add_filter('big_image_size_threshold', '__return_false');
			// prevent auto-creation of additional image sizes on upload (inaccessible via settings)
			add_filter('intermediate_image_sizes_advanced', array(&$this, 'adjust_wp_default_sizes'));
		}
	}
	function adjust_wp_default_sizes($sizes) {
		unset($sizes['1536x1536']);
		unset($sizes['2048x2048']);
		return $sizes;
	}

	// Resize images on upload
	// note: handled in main plugin module to be available on frontend as well, as some plugins use upload functionality for frontend operations, eg th23 Local Avatars plugin
	function resize_images($file_data) {

		// handle only JPG images
		// note: PNG difficult due to transparency / alpha channel, GIF difficult due to potential animation
		$image_types = array('image/jpeg', 'image/jpg');
		if(!in_array($file_data['type'], $image_types)) {
			return $file_data;
		}

		$file_original = $file_data['file'];

		// load default image editor
		$image_editor = wp_get_image_editor($file_original);
		if(is_wp_error($image_editor)) {
			return $file_data;
		}

		// check if dimensions of uploaded image exceed allowed
		// note: works also if one dimension is left empty, only the other is than a constraint to be checked
		$sizes = $image_editor->get_size();
		if((!empty($this->options['max_width']) && !empty($sizes['width']) && $sizes['width'] > $this->options['max_width']) || (!empty($this->options['max_height']) && !empty($sizes['height']) && $sizes['height'] > $this->options['max_height'])) {

			// resize by using default image editor, by default keeps aspect ration of source image, and ignores max value if 0
			$image_editor->resize($this->options['max_width'], $this->options['max_height'], false);

			// use given quality for new image - if not falls back to image editor default
			if(!empty($this->options['resize_quality']) && 0 < $this->options['resize_quality'] && 101 > $this->options['resize_quality']) {
				$image_editor->set_quality($this->options['resize_quality']);
			}

			// insert suffix for resized image to file (absolute server path) and url - before last occurance of "." as delimiter to the file extension (JPG or JPEG)
			if(!empty($this->options['resize_suffix'])) {
				$file_data['file'] = $this->str_lreplace('.', $this->options['resize_suffix'] . '.', $file_data['file']);
				$file_data['url'] = $this->str_lreplace('.', $this->options['resize_suffix'] . '.', $file_data['url']);
			}

			// save resized image (potentially under new name including suffix)
			$image_editor->save($file_data['file']);

			// remove originally uploaded image (in case a suffix is added, to avoid duplicate)
			if(!empty($this->options['resize_suffix'])) {
				unlink($file_original);
			}

		}

		return $file_data;

	}

	// String handling helper: Replace last occurance of $search in $subject by $replace
	function str_lreplace($search, $replace, $subject) {
    	$pos = strrpos($subject, $search);
		if($pos !== false) {
        	$subject = substr_replace($subject, $replace, $pos, strlen($search));
		}
		return $subject;
	}

}

// === INITIALIZATION ===

$th23_upload_path = plugin_dir_path(__FILE__);

// Load additional PRO class, if it exists
if(is_file($th23_upload_path . 'th23-upload-pro.php')) {
	require_once($th23_upload_path . 'th23-upload-pro.php');
}
// Mimic PRO class, if it does not exist
if(!class_exists('th23_upload_pro')) {
	class th23_upload_pro extends th23_upload {
		function __construct() {
			parent::__construct();
		}
		// Ensure PHP <5 compatibility
		function th23_upload_pro() {
			self::__construct();
		}
	}
}

// Load additional admin class, if required...
if(is_admin() && is_file($th23_upload_path . 'th23-upload-admin.php')) {
	require_once($th23_upload_path . 'th23-upload-admin.php');
	$th23_upload = new th23_upload_admin();
}
// ...or initiate plugin via (mimiced) PRO class
else {
	$th23_upload = new th23_upload_pro();
}

?>

<?php
/*
th23 Upload
Admin area

Copyright 2020, Thorsten Hartmann (th23)
http://th23.net/
*/

class th23_upload_admin extends th23_upload_pro {

	function __construct() {

		parent::__construct();

		// Setup basics (additions for backend)
		$this->plugin['settings_base'] = 'options-general.php';
		$this->plugin['settings_handle'] = 'th23-upload';
		$this->plugin['settings_permission'] = 'manage_options';
		$this->plugin['extendable'] = __('Want more? Add option to <strong>watermark your images</strong>, automatically upon upload or manually via the Media Library. Keeps unmarked original unaccesible to public, in case you want to restore it later.', 'th23-upload');
		// icon: "square" 48 x 48px (footer) / "horizontal" 36px height (header, width irrelevant) / both (resized if larger)
		$this->plugin['icon'] = array('square' => 'img/icon-square.png', 'horizontal' => 'img/icon-horizontal.png');
		$this->plugin['extension_files'] = array('th23-upload-pro.php', 'th23-upload-image-editors.php');
		$this->plugin['download_url'] = 'https://th23.net/th23-upload/';
		$this->plugin['support_url'] = 'https://th23.net/th23-upload-support/';
		$this->plugin['requirement_notices'] = array();

		// Install/ uninstall
		add_action('activate_' . $this->plugin['basename'], array(&$this, 'install'));
		add_action('deactivate_' . $this->plugin['basename'], array(&$this, 'uninstall'));

		// Update
		add_action('upgrader_process_complete', array(&$this, 'pre_update'), 10, 2);
		add_action('plugins_loaded', array(&$this, 'post_update'));

		// Requirements
		add_action('plugins_loaded', array(&$this, 'requirements'));
		add_action('admin_notices', array(&$this, 'admin_notices'));

		// Modify plugin overview page
		add_filter('plugin_action_links_' . $this->plugin['basename'], array(&$this, 'settings_link'), 10);
		add_filter('plugin_row_meta', array(&$this, 'contact_link'), 10, 2);

		// Add admin page and JS/ CSS
		add_action('admin_init', array(&$this, 'register_admin_js_css'));
		add_action('admin_menu', array(&$this, 'add_admin'));
		add_action('wp_ajax_th23_upload_screen_options', array(&$this, 'set_screen_options'));

		// == customization: from here on plugin specific ==

		// Add link to additional size settings on Settings / Media page
		add_action('admin_init', array(&$this, 'add_media_sizes_link'));

		// Settings: Screen options
		// note: default can handle boolean, integer or string
		$this->plugin['screen_options'] = array(
			'hide_description' => array(
				'title' => __('Hide settings descriptions', 'th23-upload'),
				'default' => false,
			),
		);

		// Settings: Help
		// note: use HTML formatting within content and help_sidebar text eg always wrap in "<p>", use "<a>" links, etc
		$this->plugin['help_tabs'] = array(
			'th23_upload_help_overview' => array(
				'title' => __('Settings and support', 'th23-upload'),
				'content' => __('<p>You can find video tutorials explaning the plugin settings for on <a href="https://www.youtube.com/channel/UCS3sNYFyxhezPVu38ESBMGA" target="_blank">my YouTube channel</a>.</p><p>More details and explanations are available on <a href="https://th23.net/th23-upload-support/" target="_blank">my Frequently Asked Questions (FAQ) page</a> or the <a href="https://wordpress.org/support/plugin/th23-upload/" target="_blank">plugin support section on WordPress.org</a>.</p>', 'th23-upload'),
			),
		);
		$this->plugin['help_sidebar'] = __('<p>Support me by <a href="https://wordpress.org/support/plugin/th23-upload/reviews/#new-post" target="_blank">leaving a review</a> or check out some of <a href="https://wordpress.org/plugins/search/th23/" target="_blank">my other plugins</a> <strong>:-)</strong></p>', 'th23-upload');

		// Settings: Prepare multi-line descriptions
		$size_section_description = __('Resizing images upon upload to maximum allowed dimensions. Aspect ratio of the original image will be preserved. The image will not be cropped.', 'th23-upload');
		$size_section_description .= '<br />' . __('Note: Only for JPG / JPEG images, as PNG (due to transparency) and GIF (due to animation) are difficult to handle', 'th23-upload');

		$suffix_description = __('Optional, extension to the file name for resized files', 'th23-upload');
		$suffix_description .= '<br />' . __('Example: "_resized" will change file name from "image.jpg" to "image_resized.jpg"', 'th23-upload');

		$default_description = __('By default WordPress limits image dimensions on uploads, automatically resizing larger ones to max 2560px width/height. Also additional image sizes are auto-generated taking up space on server.', 'th23-upload');
		$default_description .= '<br />' . __('Note: Disabling the default behaviour is recommended to make full use of the plugins capabilities!', 'th23-upload');

		// Settings: Define plugin options
		$this->plugin['options'] = array(
			'max_width' => array(
				'section' => __('Image Size', 'th23-upload'),
				'section_description' => $size_section_description,
				'title' => __('Max width', 'th23-upload'),
				'description' => __('Limit for image width in pixels', 'th23-upload'),
				'default' => 1500,
				/* translators: "px" unit symbol / shortcut for pixels eg after input field */
				'unit' => __('px', 'th23-upload'),
				'attributes' => array(
					'class' => 'small-text',
				),
			),
			'max_height' => array(
				'title' => __('Max height', 'th23-upload'),
				'description' => __('Limit for image height in pixels', 'th23-upload'),
				'default' => 1500,
				/* translators: "px" unit symbol / shortcut for pixels eg after input field */
				'unit' => 'px',
				'attributes' => array(
					'class' => 'small-text',
				),
			),
			'resize_quality' => array(
				'title' => __('Resize quality', 'th23-upload'),
				'description' => __('Quality for resized image, between 100 (excellent, large file) and 1 (poor, small file)', 'th23-upload'),
				'default' => 95,
				'attributes' => array(
					'class' => 'small-text',
				),
			),
			'resize_suffix' => array(
				'title' => __('Resize suffix', 'th23-upload'),
				'description' => $suffix_description,
				'default' => '_resized',
			),
			'wp_default' => array(
				'title' => __('Default resizing', 'th23-upload'),
				'description' => $default_description,
				'element' => 'checkbox',
				'default' => array(
					'single' => 1,
					0 => '',
					1 => __('Disable default image resizing on upload', 'th23-upload'),
				),
			),
		);

		// Settings: Professional options (placeholders shown to Basic users)
		// note: ensure all are at least defined in general admin module to ensure settings are kept upon updates
		if(!empty($this->plugin['extendable']) || !empty($this->plugin['pro'])) {

			// Professional description
			$pro_description = '<span class="notice notice-description notice-warning">' . sprintf(__('This option is only available with the %1$s extension of this plugin', 'th23-upload'), $this->plugin_professional()) . '</span>';

			// watermarks

			$this->plugin['options']['watermarks'] = array(
				'section' => __('Watermark', 'th23-upload'),
				'section_description' => __('Adding watermarks to images to ensure they can be identified belonging to this site.', 'th23-upload'),
				'title' => __('Enable watermarks', 'th23-upload'),
				'description' => $pro_description,
				'element' => 'checkbox',
				'default' => array(
					'single' => 0,
					0 => '',
					1 => __('Add watermarks to JPG attachments', 'th23-upload'),
				),
				'attributes' => array(
					'data-childs' => '.option-watermarks_upload,.option-watermarks_sizes,.option-watermarks_image,.option-watermarks_position,.option-watermarks_padding,.option-watermarks_maxcover,.option-watermarks_mass_actions',
					'disabled' => 'disabled',
				),
			);

			// watermarks_upload

			$this->plugin['options']['watermarks_upload'] = array(
				'title' => __('Upload', 'th23-upload'),
				'description' => __('Disabling this option will still leave you the chance to add watermarks for individual images in the media gallery', 'th23-upload') . $pro_description,
				'element' => 'checkbox',
				'default' => array(
					'single' => 1,
					0 => '',
					1 => __('Automatically apply watermark upon upload', 'th23-upload'),
				),
				'attributes' => array(
					'disabled' => 'disabled',
				),
			);

			// watermarks_sizes

			// note: list limited, only properly registered by WP core functions, excluding eg those handled by plugins deliberately outside WP core functions (ie excluded by using "intermediate_image_sizes_advanced" filter), excluding image sizes exceeding defined maximum ("max_width" and "max_width" settings)
			$watermarks_sizes_description = __('Select image sizes that the watermark should be applied to.', 'th23-upload');
			$watermarks_sizes_description .= '<br />' . __('Warning: Manual cropping of images done will be lost upon watermarking', 'th23-upload');
			$watermarks_sizes_description .= '<br />' . __('Recommendation: Select all un-cropped image sizes, esp the WP defaults "full", "large", "medium_large" and "medium"', 'th23-upload');
			$watermarks_sizes_description .= '<br />' . __('Note: List indicates maximum dimensions in pixels (width x height) for each and is limited to properly registered sizes, that are smaller than the maximum upload dimensions', 'th23-upload');
			$this->plugin['options']['watermarks_sizes'] = array(
				'title' => __('Image sizes', 'th23-upload'),
				'description' => $watermarks_sizes_description . $pro_description,
				'element' => 'checkbox',
				'default' => array(
					'multiple' => array(''),
				),
				'attributes' => array(
					'disabled' => 'disabled',
				),
			);
			foreach($this->get_image_sizes() as $size => $details) {
				// only show image sizes, which are not exceeding the max dimensions
				if($details['active']) {
					$cropped = ($details['crop']) ? '<span style="font-weight: bold; color: red;"> ' . __('cropped to', 'th23-upload') . '</span>' : __('max', 'th23-upload');
					$this->plugin['options']['watermarks_sizes']['default'][$size] = $size . ': ' . $cropped . ' ' . $details['width'] . ' x ' . $details['height'] . 'px';
				}
			}

			// watermarks image (PNG)

			$watermarks_image_description = __('Click to select image used as watermark', 'th23-upload');
			$watermarks_image_description .= '<br />' . __('Note: Ideally PNG file with transparent background and not too big in size / dimensions', 'th23-upload');

			$this->plugin['options']['watermarks_image'] = array(
				'title' => __('Watermark', 'th23-upload'),
				'description' => $watermarks_image_description . $pro_description,
				'render' => 'watermark_image',
				'default' => '',
				'element' => 'hidden',
				'attributes' => array(
					'disabled' => 'disabled',
				),
			);

			// watermarks position

			$this->plugin['options']['watermarks_position'] = array(
				'title' => __('Position', 'th23-upload'),
				'description' => __('Position of watermark on the image', 'th23-upload') . $pro_description,
				'element' => 'radio',
				'default' => array(
					'single' => 9,
					'1' => __('top left', 'th23-upload'),
					'2' => __('top center', 'th23-upload'),
					'3' => __('top right', 'th23-upload'),
					'4' => __('mid left', 'th23-upload'),
					'5' => __('mid center', 'th23-upload'),
					'6' => __('mid right', 'th23-upload'),
					'7' => __('bottom left', 'th23-upload'),
					'8' => __('bottom center', 'th23-upload'),
					'9' => __('bottom right', 'th23-upload'),
				),
				'attributes' => array(
					'disabled' => 'disabled',
				),
			);

			// watermarks padding

			$this->plugin['options']['watermarks_padding'] = array(
				'title' => __('Offset', 'th23-upload'),
				'description' => __('Distance of watermark from image borders in pixels', 'th23-upload') . $pro_description,
				'default' => 10,
				/* translators: "px" unit symbol / shortcut for pixels eg after input field */
				'unit' => 'px',
				'attributes' => array(
					'class' => 'small-text',
					'disabled' => 'disabled',
				),
			);

			// watermarks maxcover (%)

			$this->plugin['options']['watermarks_maxcover'] = array(
				'title' => __('Maximum coverage', 'th23-upload'),
				'description' => __('Maximum width / height of image covered by watermark in % - watermark will be shrinked, if required', 'th23-upload') . $pro_description,
				'default' => 30,
				/* translators: "%" unit symbol / shortcut for percent eg after input field */
				'unit' => '%',
				'attributes' => array(
					'class' => 'small-text',
					'disabled' => 'disabled',
				),
			);

		}

	}

	// Ensure PHP <5 compatibility
	function th23_upload_admin() {
		self::__construct();
	}

	// Plugin versions
	// Note: Any CSS styling needs to be "hardcoded" here as plugin CSS might not be loaded (e.g. on plugin overview page)
	function plugin_professional($highlight = false) {
		$title = '<i>Professional</i>';
		return ($highlight) ? '<span style="font-weight: bold; color: #336600;">' . $title . '</span>' : $title;
	}
	function plugin_basic() {
		return '<i>Basic</i>';
	}
	function plugin_upgrade($highlight = false) {
		/* translators: "Professional" as name of the version */
		$title = sprintf(__('Upgrade to %s version', 'th23-upload'), $this->plugin_professional());
		return ($highlight) ? '<span style="font-weight: bold; color: #CC3333;">' . $title . '</span>' : $title;
	}

	// Get validated plugin options
	function get_options($options = array(), $html_input = false) {
		$checked_options = array();
		foreach($this->plugin['options'] as $option => $option_details) {
			$default = $option_details['default'];
			// default array can be template or allowing multiple inputs
			$default_value = $default;
			$type = '';
			if(is_array($default)) {
				$default_value = reset($default);
				$type = key($default);
			}

			// if we have a template, pass all values for each element through the check against the template defaults
			if($type == 'template') {
				unset($default['template']);
				// create complete list of all elements - those from previous settings (re-activation), overruled by (most recent) defaults and merged with any possible user input
				$elements = array_keys($default);
				if($html_input && !empty($option_details['extendable']) && !empty($_POST['input_' . $option . '_elements'])) {
					$elements = array_merge($elements, explode(',', $_POST['input_' . $option . '_elements']));
				}
				else {
					$elements = array_merge(array_keys($options[$option]), $elements);
				}
				$elements = array_unique($elements);
				// loop through all elements - and validate previous / user values
				$checked_options[$option] = array();
				$sort_elements = array();
				foreach($elements as $element) {
					$checked_options[$option][$element] = array();
					// loop through all (sub-)options
					foreach($default_value as $sub_option => $sub_option_details) {
						$sub_default = $sub_option_details['default'];
						$sub_default_value = $sub_default;
						$sub_type = '';
						if(is_array($sub_default)) {
							$sub_default_value = reset($sub_default);
							$sub_type = key($sub_default);
						}
						unset($value);
						// force pre-set options for elements given in default
						if(isset($default[$element][$sub_option])) {
							$value = $default[$element][$sub_option];
						}
						// html input
						elseif($html_input) {
							if(isset($_POST['input_' . $option . '_' . $element . '_' . $sub_option])) {
								// if only single value allowed, only take first element from value array for validation
								if($sub_type == 'single' && is_array($_POST['input_' . $option . '_' . $element . '_' . $sub_option])) {
									$value = reset($_POST['input_' . $option . '_' . $element . '_' . $sub_option]);
								}
								else {
									$value = stripslashes($_POST['input_' . $option . '_' . $element . '_' . $sub_option]);
								}
							}
							// avoid empty items filled with default - will be filled with default in case empty/0 is not allowed for single by validation
							elseif($sub_type == 'multiple') {
								$value = array();
							}
							elseif($sub_type == 'single') {
								$value = '';
							}
						}
						// previous value
						elseif(isset($options[$option][$element][$sub_option])) {
							$value = $options[$option][$element][$sub_option];
						}
						// in case no value is given, take default
						if(!isset($value)) {
							$value = $sub_default_value;
						}
						// verify and store value
						$value = $this->get_valid_option($sub_default, $value);
						$checked_options[$option][$element][$sub_option] = $value;
						// prepare sorting
						if($sub_option == 'order') {
							$sort_elements[$element] = $value;
						}
					}
				}
				// sort verified elements according to order field (after validation to sort along valid order values)
				if(isset($default_value['order'])) {
					asort($sort_elements);
					$sorted_elements = array();
					foreach($sort_elements as $element => $null) {
						$sorted_elements[$element] = $checked_options[$option][$element];
					}
					$checked_options[$option] = $sorted_elements;
				}
			}
			// normal input fields
			else {
				unset($value);
				// html input
				if($html_input) {
					if(isset($_POST['input_' . $option])) {
						// if only single value allowed, only take first element from value array for validation
						if($type == 'single' && is_array($_POST['input_' . $option])) {
							$value = reset($_POST['input_' . $option]);
						}
						elseif($type == 'multiple' && is_array($_POST['input_' . $option])) {
							$value = array();
							foreach($_POST['input_' . $option] as $key => $val) {
								$value[$key] = stripslashes($val);
							}
						}
						else {
							$value = stripslashes($_POST['input_' . $option]);
						}
					}
					// avoid empty items filled with default - will be filled with default in case empty/0 is not allowed for single by validation
					elseif($type == 'multiple') {
						$value = array();
					}
					elseif($type == 'single') {
						$value = '';
					}
				}
				// previous value
				elseif(isset($options[$option])) {
					$value = $options[$option];
				}
				// in case no value is given, take default
				if(!isset($value)) {
					$value = $default_value;
				}
				// check value defined by user
				$checked_options[$option] = $this->get_valid_option($default, $value);
			}
		}
		return $checked_options;
	}

	// Validate / type match value against default
	function get_valid_option($default, $value) {
		if(is_array($default)) {
			$default_value = reset($default);
			$type = key($default);
			unset($default[$type]);
			if($type == 'multiple') {
				// note: multiple selections / checkboxes can be empty
				$valid_value = array();
				foreach($value as $selected) {
					// force allowed type - determined by first default element / no mixed types allowed
					if(gettype($default_value[0]) != gettype($selected)) {
						settype($selected, gettype($default_value[0]));
					}
					// check against allowed values - including type check
					if(isset($default[$selected])) {
						$valid_value[] = $selected;
					}
				}
			}
			else {
				// force allowed type - determined default value / no mixed types allowed
				if(gettype($default_value) != gettype($value)) {
					settype($value, gettype($default_value));
				}
				// check against allowed values
				if(isset($default[$value])) {
					$valid_value = $value;
				}
				// single selections (radio buttons, dropdowns) should have a valid value
				else {
					$valid_value = $default_value;
				}
			}
		}
		else {
			// force allowed type - determined default value
			if(gettype($default) != gettype($value)) {
				settype($value, gettype($default));
			}
			$valid_value = $value;
		}
		return $valid_value;
	}

	// Install
	function install() {
		// Prefill values in an option template, keeping them user editable (and therefore not specified in the default value itself)
		// need to check, if items exist(ed) before and can be reused - so we dont' overwrite them (see uninstall with delete_option inactive)
		if(isset($this->plugin['presets'])) {
			if(!isset($this->options) || !is_array($this->options)) {
				$this->options = array();
			}
			$this->options = array_merge($this->plugin['presets'], $this->options);
		}
		// Set option values
		update_option('th23_upload_options', $this->get_options($this->options));
		$this->options = (array) get_option('th23_upload_options');
	}

	// Uninstall
	// note: keeping all settings etc in case plugin is reactivated again - to start from scratch remove "/*" and "*/" below
	function uninstall() {
		/*
		// Delete option values
		delete_option('th23_upload_options');
		*/
		// Remove update transients (allowing fresh start after manual deactivation, eg no Professional update reminder)
		delete_transient('th23_upload_update');
		delete_transient('th23_upload_update_pro');
	}

	// Update - store previous version before plugin is updated
	// note: this function is still run by the old version of the plugin, ie before the update
	function pre_update($upgrader_object, $options) {
		if('update' == $options['action'] && 'plugin' == $options['type'] && !empty($options['plugins']) && is_array($options['plugins']) && in_array($this->plugin['basename'], $options['plugins'])) {
			set_transient('th23_upload_update', $this->plugin['version']);
			if(!empty($this->plugin['pro'])) {
				set_transient('th23_upload_update_pro', $this->plugin['pro']);
			}
		}
	}

	// Update - check for previous update and trigger requird actions
	function post_update() {

		// previous Professional extension - remind to update/re-upload
		if(!empty(get_transient('th23_upload_update_pro')) && empty($this->plugin['pro'])) {
			add_action('th23_upload_requirements', array(&$this, 'post_update_missing_pro'));
		}

		if(empty($previous = get_transient('th23_upload_update'))) {
			return;
		}

		/* execute required update actions, optionally depending on previously installed version
		if(version_compare($previous, '1.6.0', '<')) {
			// action required
		}
		*/

		// upon successful update, delete transient (update only executed once)
		delete_transient('th23_upload_update');

	}
	// previous Professional extension - remind to update/re-upload
	function post_update_missing_pro($context) {
		if('plugin_settings' == $context) {
			$missing = '<label for="th23-upload-pro-file"><strong>' . __('Upload Professional extension?', 'th23-upload') . '</strong></label>';
		}
		else {
			$missing = '<a href="' . esc_url($this->plugin['settings_base'] . '?page=' . $this->plugin['settings_handle']) . '"><strong>' . __('Go to plugin settings page for upload...', 'th23-upload') . '</strong></a>';
		}
		/* translators: 1: "Professional" as name of the version, 2: link to "th23.net" plugin download page, 3: link to "Go to plugin settings page to upload..." page or "Upload updated Professional extension?" link */
		$notice = sprintf(__('Due to an update the previously installed %1$s extension is missing. Please get the latest version of the %1$s extension from %2$s. %3$s', 'th23-upload'), $this->plugin_professional(), '<a href="' . esc_url($this->plugin['download_url']) . '">th23.net</a>', $missing);
		$this->plugin['requirement_notices']['missing_pro'] = '<strong>' . __('Error', 'th23-upload') . '</strong>: ' . $notice;
	}

	// Requirements - checks
	function requirements() {

		// check requirements only on relevant admin pages
		global $pagenow;
		if(empty($pagenow)) {
			return;
		}
		if('index.php' == $pagenow) {
			// admin dashboard
			$context = 'admin_index';
		}
		elseif('plugins.php' == $pagenow) {
			// plugins overview page
			$context = 'plugins_overview';
		}
		elseif($this->plugin['settings_base'] == $pagenow && !empty($_GET['page']) && $this->plugin['settings_handle'] == $_GET['page']) {
			// plugin settings page
			$context = 'plugin_settings';
		}
		else {
			return;
		}

		// Check - plugin not designed for multisite setup
		if(is_multisite()) {
			$this->plugin['requirement_notices']['multisite'] = '<strong>' . __('Warning', 'th23-upload') . '</strong>: ' . __('Your are running a multisite installation - the plugin is not designed for this setup and therefore might not work properly', 'th23-upload');
		}

		// Check - PRO file not matching main version
		// todo: remove in future version (1.6.0 or 1.8.0) - it's handled within the Professional extension from v1.4.2 onwards, but here to prevent somebody is re-installing a previous Professional extension (< v1.4.2)
		if(!empty($this->plugin['pro']) && $this->plugin['pro'] != $this->plugin['version']) {
			if('plugin_settings' == $context) {
				$outdated = '<label for="th23-upload-pro-file"><strong>' . __('Upload Professional extension?', 'th23-upload') . '</strong></label>';
			}
			else {
				$outdated = '<a href="' . esc_url($this->plugin['settings_base'] . '?page=' . $this->plugin['settings_handle']) . '"><strong>' . __('Go to plugin settings page for upload...', 'th23-upload') . '</strong></a>';
			}
			/* translators: 1: "Professional" as name of the version, 2: "...-pro.php" as file name, 3: version number of the PRO file, 4: version number of main file, 5: link to WP update page, 6: link to "th23.net" plugin download page, 7: link to "Go to plugin settings page to upload..." page or "Upload updated Professional extension?" link */
			$notice = sprintf(__('The version of the %1$s extension (%2$s, version %3$s) does not match with the overall plugin (version %4$s). Please make sure you update the overall plugin to the latest version via the <a href="%5$s">automatic update function</a> and get the latest version of the %1$s extension from %6$s. %7$s', 'th23-upload'), $this->plugin_professional(), '<code>th23-upload-pro.php</code>', $this->plugin['pro'], $this->plugin['version'], 'update-core.php', '<a href="' . esc_url($this->plugin['download_url']) . '">th23.net</a>', $outdated);
			$this->plugin['requirement_notices']['wrong_version'] = '<strong>' . __('Error', 'th23-upload') . '</strong>: ' . $notice;
		}

		// allow further checks by Professional extension (without re-assessing $context)
		do_action('th23_upload_requirements', $context);

	}

	// Requirements - show requirement notices on admin dashboard
	function admin_notices() {
		global $pagenow;
		if(!empty($pagenow) && 'index.php' == $pagenow && !empty($this->plugin['requirement_notices'])) {
			echo '<div class="notice notice-error">';
			echo '<p style="font-size: 14px;"><strong>' . $this->plugin['data']['Name'] . '</strong></p>';
			foreach($this->plugin['requirement_notices'] as $notice) {
				echo '<p>' . $notice . '</p>';
			}
			echo '</div>';
		}
	}

	// Add settings link to plugin actions in plugin overview page
	function settings_link($links) {
		$links['settings'] = '<a href="' . esc_url($this->plugin['settings_base'] . '?page=' . $this->plugin['settings_handle']) . '">' . __('Settings', 'th23-upload') . '</a>';
		return $links;
	}

	// Add supporting information (eg links and notices) to plugin row in plugin overview page
	// Note: Any CSS styling needs to be "hardcoded" here as plugin CSS might not be loaded (e.g. when plugin deactivated)
	function contact_link($links, $file) {
		if($this->plugin['basename'] == $file) {
			// Use internal version number and expand version details
			if(!empty($this->plugin['pro'])) {
				/* translators: parses in plugin version number (optionally) together with upgrade link */
				$links[0] = sprintf(__('Version %s', 'th23-upload'), $this->plugin['version']) . ' ' . $this->plugin_professional(true);
			}
			elseif(!empty($this->plugin['extendable'])) {
				/* translators: parses in plugin version number (optionally) together with upgrade link */
				$links[0] = sprintf(__('Version %s', 'th23-upload'), $this->plugin['version']) . ' ' . $this->plugin_basic() . ((empty($this->plugin['requirement_notices']) && !empty($this->plugin['download_url'])) ? ' - <a href="' . esc_url($this->plugin['download_url']) . '">' . $this->plugin_upgrade(true) . '</a>' : '');
			}
			// Add support link
			if(!empty($this->plugin['support_url'])) {
				$links[] = '<a href="' . esc_url($this->plugin['support_url']) . '">' . __('Support', 'th23-upload') . '</a>';
			}
			// Show warning, if installation requirements are not met - add it after/ to last link
			if(!empty($this->plugin['requirement_notices'])) {
				$notices = '';
				foreach($this->plugin['requirement_notices'] as $notice) {
					$notices .= '<div style="margin: 1em 0; padding: 5px 10px; background-color: #FFFFFF; border-left: 4px solid #DD3D36; box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);">' . $notice . '</div>';
				}
				$last = array_pop($links);
				$links[] = $last . $notices;
			}
		}
		return $links;
	}

	// Register admin JS and CSS
	function register_admin_js_css() {
		wp_register_script('th23-upload-admin-js', $this->plugin['dir_url'] . 'th23-upload-admin.js', array('jquery'), $this->plugin['version'], true);
		wp_register_style('th23-upload-admin-css', $this->plugin['dir_url'] . 'th23-upload-admin.css', array(), $this->plugin['version']);
	}

	// Register admin page in admin menu/ prepare loading admin JS and CSS/ trigger screen options
	function add_admin() {
		$this->plugin['data'] = get_plugin_data($this->plugin['file']);
		$page = add_submenu_page($this->plugin['settings_base'], $this->plugin['data']['Name'], $this->plugin['data']['Name'], $this->plugin['settings_permission'], $this->plugin['settings_handle'], array(&$this, 'show_admin'));
		add_action('admin_print_scripts-' . $page, array(&$this, 'load_admin_js'));
		add_action('admin_print_styles-' . $page, array(&$this, 'load_admin_css'));
		if(!empty($this->plugin['screen_options'])) {
			add_action('load-' . $page, array(&$this, 'add_screen_options'));
		}
		if(!empty($this->plugin['help_tabs'])) {
			add_action('load-' . $page, array(&$this, 'add_help'));
		}
	}

	// Load admin JS
	function load_admin_js() {
		wp_enqueue_script('th23-upload-admin-js');
	}

	// Load admin CSS
	function load_admin_css() {
		wp_enqueue_style('th23-upload-admin-css');
	}

	// Handle screen options
	function add_screen_options() {
		add_filter('screen_settings', array(&$this, 'show_screen_options'), 10, 2);
	}
	function show_screen_options($html, $screen) {
		$html .= '<div id="th23-upload-screen-options">';
		$html .= '<input type="hidden" id="th23-upload-screen-options-nonce" value="' . wp_create_nonce('th23-upload-screen-options-nonce') . '" />';
		$html .= $this->get_screen_options(true);
		$html .= '</div>';
		return $html;
	}
	function get_screen_options($html = false) {
		if(empty($this->plugin['screen_options'])) {
			return array();
		}
		if(empty($user = get_user_meta(get_current_user_id(), 'th23_upload_screen_options', true))) {
			$user = array();
		}
		$screen_options = ($html) ? '' : array();
		foreach($this->plugin['screen_options'] as $option => $details) {
			$type = gettype($details['default']);
			$value = (isset($user[$option]) && gettype($user[$option]) == $type) ? $user[$option] : $details['default'];
			if($html) {
				$name = 'th23_upload_screen_options_' . $option;
				$class = 'th23-upload-screen-option-' . $option;
				if('boolean' == $type) {
					$checked = (!empty($value)) ? ' checked="checked"' : '';
					$screen_options .= '<fieldset class="' . $name . '"><label><input name="' . $name .'" id="' . $name .'" value="1" type="checkbox"' . $checked . ' data-class="' . $class . '">' . esc_html($details['title']) . '</label></fieldset>';
				}
				elseif('integer' == $type) {
					$min_max = (isset($details['range']['min'])) ? ' min="' . $details['range']['min'] . '"' : '';
					$min_max .= (isset($details['range']['max'])) ? ' max="' . $details['range']['max'] . '"' : '';
					$screen_options .= '<fieldset class="' . $name . '"><label for="' . $name . '">' . esc_html($details['title']) . '</label><input id="' . $name . '" name="' . $name . '" type="number"' . $min_max . ' value="' . $value . '" data-class="' . $class . '" /></fieldset>';
				}
				elseif('string' == $type) {
					$screen_options .= '<fieldset class="' . $name . '"><label for="' . $name . '">' . esc_html($details['title']) . '</label><input id="' . $name . '" name="' . $name . '" type="text" value="' . esc_attr($value) . '" data-class="' . $class . '" /></fieldset>';
				}
			}
			else {
				$screen_options[$option] = $value;
			}
		}
		return $screen_options;
	}
	// update user preference for screen options via AJAX
	function set_screen_options() {
		if(!empty($_POST['nonce']) || wp_verify_nonce($_POST['nonce'], 'th23-upload-screen-options-nonce')) {
			$screen_options = $this->get_screen_options();
			$new = array();
			foreach($screen_options as $option => $value) {
				$name = 'th23_upload_screen_options_' . $option;
				if('boolean' == gettype($value)) {
					if(empty($_POST[$name])) {
						$screen_options[$option] = $value;
					}
					elseif('true' == $_POST[$name]) {
						$screen_options[$option] = true;
					}
					else {
						$screen_options[$option] = false;
					}
				}
				else {
					settype($_POST[$name], gettype($value));
					$screen_options[$option] = $_POST[$name];
				}
			}
			update_user_meta(get_current_user_id(), 'th23_upload_screen_options', $screen_options);
		}
		wp_die();
	}

	// Add help
	function add_help() {
		$screen = get_current_screen();
		foreach($this->plugin['help_tabs'] as $id => $details) {
			$screen->add_help_tab(array(
				'id' => $id,
				'title' => $details['title'],
				'content' => $details['content'],
			));
		}
		if(!empty($this->plugin['help_sidebar'])) {
			$screen->set_help_sidebar($this->plugin['help_sidebar']);
		}
	}

	// Show admin page
	function show_admin() {

		global $wpdb;
		$form_classes = array();

		// Open wrapper and show plugin header
		echo '<div class="wrap th23-upload-options">';

		// Header - logo / plugin name
		echo '<h1>';
		if(!empty($this->plugin['icon']['horizontal'])) {
			echo '<img class="icon" src="' . esc_url($this->plugin['dir_url'] . $this->plugin['icon']['horizontal']) . '" alt="' . esc_attr($this->plugin['data']['Name']) . '" />';
		}
		else {
			echo $this->plugin['data']['Name'];
		}
		echo '</h1>';

		// Get screen options, ie user preferences - and build CSS class
		if(!empty($this->plugin['screen_options'])) {
			$screen_options = $this->get_screen_options();
			foreach($screen_options as $option => $value) {
				if($value === true) {
					$form_classes[] = 'th23-upload-screen-option-' . $option;
				}
				elseif(!empty($value)) {
					$form_classes[] = 'th23-upload-screen-option-' . $option . '-' . esc_attr(str_replace(' ', '_', $value));
				}
			}
		}

		// start form
		echo '<form method="post" enctype="multipart/form-data" id="th23-upload-options" action="' . esc_url($this->plugin['settings_base'] . '?page=' . $this->plugin['settings_handle']) . '" class="' . implode(' ', $form_classes) . '">';

		// Show warnings, if requirements are not met
		if(!empty($this->plugin['requirement_notices'])) {
			foreach($this->plugin['requirement_notices'] as $notice) {
				echo '<div class="notice notice-error"><p>' . $notice . '</p></div>';
			}
		}

		// Do update of plugin options if required
		if(!empty($_POST['th23-upload-options-do'])) {
			check_admin_referer('th23_upload_settings', 'th23-upload-settings-nonce');
			$new_options = $this->get_options($this->options, true);
			// check against unfiltered options stored (as "services" can be altered at runtime by plugins)
			$options_unfiltered = (array) get_option('th23_upload_options');
			if($new_options != $options_unfiltered) {
				update_option('th23_upload_options', $new_options);
				$this->options = $new_options;
				echo '<div class="notice notice-success is-dismissible"><p><strong>' . __('Done', 'th23-upload') . '</strong>: ' . __('Settings saved', 'th23-upload') . '</p><button class="notice-dismiss" type="button"></button></div>';
			}
		}

		// Handle Profesional extension upload and show upgrade information
		if(empty($this->pro_upload()) && empty($this->plugin['pro']) && empty($this->plugin['requirement_notices']) && !empty($this->plugin['extendable']) && !empty($this->plugin['download_url'])) {
			echo '<div class="th23-upload-admin-about">';
			echo '<p>' . $this->plugin['extendable'] . '</p>';
			echo '<p><a href="' . esc_url($this->plugin['download_url']) . '">' . $this->plugin_upgrade(true) . '</a></p>';
			echo '</div>';
		}

		// Show plugin settings
		// start table
		echo '<table class="form-table"><tbody>';

		// collect all children options - and the no shows
		$child_list = '';
		$sub_child_list = '';
		$no_show_list = '';

		// loop through all options
		foreach($this->plugin['options'] as $option => $option_details) {

			// add children options and no shows
			if(isset($option_details['element']) && $option_details['element'] == 'checkbox' && !empty($option_details['attributes']['data-childs'])) {
				// if the current option itself is on the child list, then the options in data-childs are sub childs
				if(strpos($child_list, 'option-' . $option . ',') !== false) {
					$sub_child_list .= $option_details['attributes']['data-childs'] . ',';
				}
				// otherwise we have first level children
				else {
					$child_list .= $option_details['attributes']['data-childs'] . ',';
				}
				if(empty($this->options[$option]) || strpos($no_show_list, 'option-' . $option . ',') !== false) {
					$no_show_list .= $option_details['attributes']['data-childs'] . ',';
				}
			}
			// assign proper child or sub-child class - for proper indent
			$child_class = '';
			if(strpos($child_list, 'option-' . $option . ',') !== false) {
				$child_class = ' child';
			}
			elseif(strpos($sub_child_list, 'option-' . $option . ',') !== false) {
				$child_class = ' sub-child';
			}
			// prepare show/hide style for current element
			$no_show_style = (strpos($no_show_list, 'option-' . $option . ',') !== false) ? ' style="display: none;"' : '';

			$key = '';
			if(is_array($option_details['default'])) {
				$default_value = reset($option_details['default']);
				$key = key($option_details['default']);
				unset($option_details['default'][$key]);
				if($key == 'template') {

					echo '</tbody></table>';
					echo '<div class="option option-template option-' . $option . $child_class . '"' . $no_show_style . '>';
					echo '<h2>' . $option_details['title'] . '</h2>';
					if(!empty($option_details['description'])) {
						echo '<p class="section-description">' . $option_details['description'] . '</p>';
					}
					echo '<table class="option-template"><tbody>';

					// create template headers
					echo '<tr>';
					foreach($default_value as $sub_option => $sub_option_details) {
						$hint_open = '';
						$hint_close = '';
						if(isset($sub_option_details['description'])) {
							$hint_open = '<span class="hint" title="' . esc_attr($sub_option_details['description']) . '">';
							$hint_close = '</span>';
						}
						echo '<th class="' . $sub_option . '">' . $hint_open . $sub_option_details['title'] . $hint_close . '</th>';
					}
					// show add button, if template list is user editable
					if(!empty($option_details['extendable'])) {
						echo '<td class="template-actions"><button type="button" id="template-add-' . $option . '" value="' . $option . '">' . __('+', 'th23-upload') . '</button></td>';
					}
					echo '</tr>';

					// get elements for rows - and populate hidden input (adjusted by JS for adding/ deleting rows)
					$elements = array_keys(array_merge($this->options[$option], $option_details['default']));
					// sort elements array according to order field
					if(isset($default_value['order'])) {
						$sorted_elements = array();
						foreach($elements as $element) {
							$sorted_elements[$element] = (isset($this->options[$option][$element]['order'])) ? $this->options[$option][$element]['order'] : 0;
						}
						asort($sorted_elements);
						$elements = array_keys($sorted_elements);
					}

					// add list of elements and empty row as source for user inputs - filled with defaults
					if(!empty($option_details['extendable'])) {
						echo '<input id="input_' . $option . '_elements" name="input_' . $option . '_elements" value="' . implode(',', $elements) . '" type="hidden" />';
						$elements[] = 'template';
					}

					// show template rows
					foreach($elements as $element) {
						echo '<tr id="' . $option . '-' . $element . '">';
						foreach($default_value as $sub_option => $sub_option_details) {
							echo '<td>';
							// get sub value default - and separate any array to show as sub value
							$sub_key = '';
							if(is_array($sub_option_details['default'])) {
								$sub_default_value = reset($sub_option_details['default']);
								$sub_key = key($sub_option_details['default']);
								unset($sub_option_details['default'][$sub_key]);
							}
							else {
								$sub_default_value = $sub_option_details['default'];
							}
							// force current value to be default and disable input field for preset elements / fields (not user changable / editable)
							if(isset($option_details['default'][$element][$sub_option])) {
								// set current value to default (not user-changable)
								$this->options[$option][$element][$sub_option] = $option_details['default'][$element][$sub_option];
								// disable input field
								if(!isset($sub_option_details['attributes']) || !is_array($sub_option_details['attributes'])) {
									$sub_option_details['attributes'] = array();
								}
								$sub_option_details['attributes']['disabled'] = 'disabled';
								// show full value in title, as field is disabled and thus sometimes not scrollable
								$sub_option_details['attributes']['title'] = esc_attr($this->options[$option][$element][$sub_option]);
							}
							// set to template defined default, if not yet set (eg options added via filter before first save)
							elseif(!isset($this->options[$option][$element][$sub_option])) {
								$this->options[$option][$element][$sub_option] = $sub_default_value;
							}
							// build and show input field
							$html = $this->build_input_field($option . '_' . $element . '_' . $sub_option, $sub_option_details, $sub_key, $sub_default_value, $this->options[$option][$element][$sub_option]);
							if(!empty($html)) {
								echo $html;
							}
							echo '</td>';
						}
						// show remove button, if template list is user editable and element is not part of the default set
						if(!empty($option_details['extendable'])) {
							$remove = (empty($this->plugin['options'][$option]['default'][$element]) || $element == 'template') ? '<button type="button" id="template-remove-' . $option . '-' . $element . '" value="' . $option . '" data-element="' . $element . '">' . __('-', 'th23-upload') . '</button>' : '';
							echo '<td class="template-actions">' . $remove . '</td>';
						}
						echo '</tr>';
					}

					echo '</tbody></table>';
					echo '</div>';
					echo '<table class="form-table"><tbody>';

					continue;

				}
			}
			else {
				$default_value = $option_details['default'];
			}

			// separate option sections - break table(s) and insert heading
			if(!empty($option_details['section'])) {
				echo '</tbody></table>';
				echo '<h2 class="option option-section option-' . $option . $child_class . '"' . $no_show_style . '>' . $option_details['section'] . '</h2>';
				if(!empty($option_details['section_description'])) {
					echo '<p class="section-description">' . $option_details['section_description'] . '</p>';
				}
				echo '<table class="form-table"><tbody>';
			}

			// Build input field and output option row
			if(!isset($this->options[$option])) {
				// might not be set upon fresh activation
				$this->options[$option] = $default_value;
			}
			$html = $this->build_input_field($option, $option_details, $key, $default_value, $this->options[$option]);
			if(!empty($html)) {
				echo '<tr class="option option-' . $option . $child_class . '" valign="top"' . $no_show_style . '>';
				$option_title = $option_details['title'];
				if(!isset($option_details['element']) || ($option_details['element'] != 'checkbox' && $option_details['element'] != 'radio')) {
					$brackets = (isset($option_details['element']) && ($option_details['element'] == 'list' || $option_details['element'] == 'dropdown')) ? '[]' : '';
					$option_title = '<label for="input_' . $option . $brackets . '">' . $option_title . '</label>';
				}
				echo '<th scope="row">' . $option_title . '</th>';
				echo '<td><fieldset>';
				// Rendering additional field content via callback function
				// passing on to callback function as parameters: $default_value = default value, $this->options[$option] = current value
				if(!empty($option_details['render']) && method_exists($this, $option_details['render'])) {
					$render = $option_details['render'];
					echo $this->$render($default_value, $this->options[$option]);
				}
				echo $html;
				if(!empty($option_details['description'])) {
					echo '<span class="description">' . $option_details['description'] . '</span>';
				}
				echo '</fieldset></td>';
				echo '</tr>';
			}

		}

		// end table
		echo '</tbody></table>';
		echo '<br/>';

		// submit
		echo '<input type="hidden" name="th23-upload-options-do" value=""/>';
		echo '<input type="button" id="th23-upload-options-submit" class="button-primary th23-upload-options-submit" value="' . esc_attr(__('Save Changes', 'th23-upload')) . '"/>';
		wp_nonce_field('th23_upload_settings', 'th23-upload-settings-nonce');

		echo '<br/>';

		// Plugin information
		echo '<div class="th23-upload-admin-about">';
		if(!empty($this->plugin['icon']['square'])) {
			echo '<img class="icon" src="' . esc_url($this->plugin['dir_url'] . $this->plugin['icon']['square']) . '" alt="' . esc_attr($this->plugin['data']['Name']) . '" /><p>';
		}
		else {
			echo '<p><strong>' . $this->plugin['data']['Name'] . '</strong>' . ' | ';
		}
		if(!empty($this->plugin['pro'])) {
			/* translators: parses in plugin version number (optionally) together with upgrade link */
			echo sprintf(__('Version %s', 'th23-upload'), $this->plugin['version']) . ' ' . $this->plugin_professional(true);
		}
		else {
			/* translators: parses in plugin version number (optionally) together with upgrade link */
			echo sprintf(__('Version %s', 'th23-upload'), $this->plugin['version']);
			if(!empty($this->plugin['extendable']) && empty($this->plugin['requirement_notices']) && !empty($this->plugin['download_url'])) {
				echo ' ' . $this->plugin_basic() . ' - <a href="' . esc_url($this->plugin['download_url']) . '">' . $this->plugin_upgrade(true) . '</a> (<label for="th23-upload-pro-file">' . __('Upload upgrade', 'th23-upload') . ')</label>';
			}
		}
		// embed upload for Professional extension
		if(!empty($this->plugin['extendable'])) {
			echo '<input type="file" name="th23-upload-pro-file" id="th23-upload-pro-file" />';
		}
		/* translators: parses in plugin author name */
		echo ' | ' . sprintf(__('By %s', 'th23-upload'), $this->plugin['data']['Author']);
		if(!empty($this->plugin['support_url'])) {
			echo ' | <a href="' . esc_url($this->plugin['support_url']) . '">' . __('Support', 'th23-upload') . '</a>';
		}
		elseif(!empty($this->plugin['data']['PluginURI'])) {
			echo ' | <a href="' . $this->plugin['data']['PluginURI'] . '">' . __('Visit plugin site', 'th23-upload') . '</a>';
		}
		echo '</p></div>';

		// Close form and wrapper
		echo '</form>';
		echo '</div>';

	}

	// Handle Profesional extension upload
	function pro_upload() {

		if(empty($_FILES['th23-upload-pro-file']) || empty($pro_upload_name = $_FILES['th23-upload-pro-file']['name'])) {
			return;
		}

		global $th23_upload_path;
		$files = array();
		$try_again = '<label for="th23-upload-pro-file">' . __('Try again?', 'th23-upload') . '</label>';

		// zip archive
		if('.zip' == substr($pro_upload_name, -4)) {
			// check required ZipArchive class (core component of most PHP installations)
			if(!class_exists('ZipArchive')) {
				echo '<div class="notice notice-error"><p><strong>' . __('Error', 'th23-upload') . '</strong>: ';
				/* translators: parses in "Try again?" link */
				echo sprintf(__('Your server can not handle zip files. Please extract it locally and try again with the individual files. %s', 'th23-upload'), $try_again) . '</p></div>';
				return;
			}
			// open zip file
			$zip = new ZipArchive;
			if($zip->open($_FILES['th23-upload-pro-file']['tmp_name']) !== true) {
				echo '<div class="notice notice-error"><p><strong>' . __('Error', 'th23-upload') . '</strong>: ';
				/* translators: parses in "Try again?" link */
				echo sprintf(__('Failed to open zip file. %s', 'th23-upload'), $try_again) . '</p></div>';
				return;
			}
			// check zip contents
			for($i = 0; $i < $zip->count(); $i++) {
			    $zip_file = $zip->statIndex($i);
				$files[] = $zip_file['name'];
			}
			if(!empty(array_diff($files, $this->plugin['extension_files']))) {
				echo '<div class="notice notice-error"><p><strong>' . __('Error', 'th23-upload') . '</strong>: ';
				/* translators: parses in "Try again?" link */
				echo sprintf(__('Zip file seems to contain files not belonging to the Professional extension. %s', 'th23-upload'), $try_again) . '</p></div>';
				return;
			}
			// extract zip to plugin folder (overwrites existing files by default)
			$zip->extractTo($th23_upload_path);
			$zip->close();
		}
		// (invalid) individual file
		elseif(!in_array($pro_upload_name, $this->plugin['extension_files'])) {
			echo '<div class="notice notice-error"><p><strong>' . __('Error', 'th23-upload') . '</strong>: ';
			/* translators: parses in "Try again?" link */
			echo sprintf(__('This does not seem to be a proper Professional extension file. %s', 'th23-upload'), $try_again) . '</p></div>';
			return;
		}
		// idividual file
		else {
			move_uploaded_file($_FILES['th23-upload-pro-file']['tmp_name'], $th23_upload_path . $pro_upload_name);
			$files[] = $pro_upload_name;
		}

		// ensure proper file permissions (as done by WP core function "_wp_handle_upload" after upload)
		$stat = stat($th23_upload_path);
		$perms = $stat['mode'] & 0000666;
		foreach($files as $file) {
			chmod($th23_upload_path . $file, $perms);
		}

		// check for missing extension files
		$missing_file = false;
		foreach($this->plugin['extension_files'] as $file) {
			if(!is_file($th23_upload_path . $file)) {
				$missing_file = true;
				break;
			}
		}

		// upload success message
		if($missing_file) {
			$missing = '<label for="th23-upload-pro-file">' . __('Upload missing file(s)!', 'th23-upload') . '</label>';
			echo '<div class="notice notice-warning"><p><strong>' . __('Done', 'th23-upload') . '</strong>: ';
			/* translators: parses in "Upload missing files!" link */
			echo sprintf(__('Professional extension file uploaded. %s', 'th23-upload'), $missing) . '</p></div>';
			return true;
		}
		else {
			$reload = '<a href="' . esc_url($this->plugin['settings_base'] . '?page=' . $this->plugin['settings_handle']) . '">' . __('Reload page to see Professional settings!', 'th23-upload') . '</a>';
			echo '<div class="notice notice-success is-dismissible"><p><strong>' . __('Done', 'th23-upload') . '</strong>: ';
			/* translators: parses in "Reload page to see Professional settings!" link */
			echo sprintf(__('Professional extension file uploaded. %s', 'th23-upload'), $reload) . '</p><button class="notice-dismiss" type="button"></button></div>';
			return true;
		}

	}

	// Create admin input field
	// note: uses the chance to point out any invalid combinations for element and validation options
	function build_input_field($option, $option_details, $key, $default_value, $current_value) {

		if(!isset($option_details['element'])) {
			$option_details['element'] = 'input';
		}
		$element_name = 'input_' . $option;
		$element_attributes = array();
		if(!isset($option_details['attributes']) || !is_array($option_details['attributes'])) {
			$option_details['attributes'] = array();
		}
		$element_attributes_suggested = array();
		$valid_option_field = true;
		if($option_details['element'] == 'checkbox') {
			// exceptional case: checkbox allows "single" default to handle (yes/no) checkbox
			if(empty($key) || ($key == 'multiple' && !is_array($default_value)) || ($key == 'single' && is_array($default_value))) {
				$valid_option_field = false;
			}
			$element_name .= '[]';
			$element_attributes['type'] = 'checkbox';
		}
		elseif($option_details['element'] == 'radio') {
			if(empty($key) || $key != 'single' || is_array($default_value)) {
				$valid_option_field = false;
			}
			$element_name .= '[]';
			$element_attributes['type'] = 'radio';
		}
		elseif($option_details['element'] == 'list') {
			if(empty($key) || $key != 'multiple' || !is_array($default_value)) {
				$valid_option_field = false;
			}
			$element_name .= '[]';
			$element_attributes['multiple'] = 'multiple';
			$element_attributes_suggested['size'] = '5';
		}
		elseif($option_details['element'] == 'dropdown') {
			if(empty($key) || $key != 'single' || is_array($default_value)) {
				$valid_option_field = false;
			}
			$element_name .= '[]';
			$element_attributes['size'] = '1';
		}
		elseif($option_details['element'] == 'hidden') {
			if(!empty($key)) {
				$valid_option_field = false;
			}
			$element_attributes['type'] = 'hidden';
		}
		else {
			if(!empty($key)) {
				$valid_option_field = false;
			}
			$element_attributes_suggested['type'] = 'text';
			$element_attributes_suggested['class'] = 'regular-text';
		}
		// no valid option field, due to missmatch of input field and default value
		if(!$valid_option_field) {
			$support_open = '';
			$support_close = '';
			if(!empty($this->plugin['support_url'])) {
				$support_open = '<a href="' . esc_url($this->plugin['support_url']) . '">';
				$support_close = '</a>';
			}
			elseif(!empty($this->plugin['data']['PluginURI'])) {
				$support_open = '<a href="' . $this->plugin['data']['PluginURI'] . '">';
				$support_close = '</a>';
			}
			echo '<div class="notice notice-error"><p><strong>' . __('Error', 'th23-upload') . '</strong>: ';
			/* translators: 1: option name, 2: opening a tag of link to support/ plugin page, 3: closing a tag of link */
			echo sprintf(__('Invalid combination of input field and default value for "%1$s" - please %2$scontact the plugin author%3$s', 'th23-upload'), $option, $support_open, $support_close);
			echo '</p></div>';
			return '';
		}

		$html = '';

		// handle repetitive elements (checkboxes and radio buttons)
		if($option_details['element'] == 'checkbox' || $option_details['element'] == 'radio') {
			$html .= '<div>';
			// special handling for single checkboxes (yes/no)
			$checked = ($option_details['element'] == 'radio' || $key == 'single') ? array($current_value) : $current_value;
			foreach($option_details['default'] as $value => $text) {
				// special handling for yes/no checkboxes
				if(!empty($text)){
					$html .= '<div><label><input name="' . $element_name . '" id="' . $element_name . '_' . $value . '" value="' . $value . '" ';
					foreach(array_merge($element_attributes_suggested, $option_details['attributes'], $element_attributes) as $attr => $attr_value) {
						$html .= $attr . '="' . $attr_value . '" ';
					}
					$html .= (in_array($value, $checked)) ? 'checked="checked" ' : '';
					$html .= '/>' . $text . '</label></div>';
				}
			}
			$html .= '</div>';
		}
		// handle repetitive elements (dropdowns and lists)
		elseif($option_details['element'] == 'list' || $option_details['element'] == 'dropdown') {
			$html .= '<select name="' . $element_name . '" id="' . $element_name . '" ';
			foreach(array_merge($element_attributes_suggested, $option_details['attributes'], $element_attributes) as $attr => $attr_value) {
				$html .= $attr . '="' . $attr_value . '" ';
			}
			$html .= '>';
			$selected = ($option_details['element'] == 'dropdown') ? array($current_value) : $current_value;
			foreach($option_details['default'] as $value => $text) {
				$html .= '<option value="' . $value . '"';
				$html .= (in_array($value, $selected)) ? ' selected="selected"' : '';
				$html .= '>' . $text . '</option>';
			}
			$html .= '</select>';
			if($option_details['element'] == 'dropdown' && !empty($option_details['unit'])) {
				$html .= '<span class="unit">' . $option_details['unit'] . '</span>';
			}
		}
		// textareas
		elseif($option_details['element'] == 'textarea') {
			$html .= '<textarea name="' . $element_name . '" id="' . $element_name . '" ';
			foreach(array_merge($element_attributes_suggested, $option_details['attributes'], $element_attributes) as $attr => $attr_value) {
				$html .= $attr . '="' . $attr_value . '" ';
			}
			$html .= '>' . stripslashes($current_value) . '</textarea>';
		}
		// simple (self-closing) inputs
		else {
			$html .= '<input name="' . $element_name . '" id="' . $element_name . '" ';
			foreach(array_merge($element_attributes_suggested, $option_details['attributes'], $element_attributes) as $attr => $attr_value) {
				$html .= $attr . '="' . $attr_value . '" ';
			}
			$html .= 'value="' . stripslashes($current_value) . '" />';
			if(!empty($option_details['unit'])) {
				$html .= '<span class="unit">' . $option_details['unit'] . '</span>';
			}
		}

		return $html;

	}

	// == customization: from here on plugin specific ==

	// Add link to additional size settings on Settings / Media page
	function add_media_sizes_link(){
		add_settings_field('th23-upload-sizes', __('Maximum size', 'th23-upload'), array(&$this, 'media_sizes_link'), 'media', 'default');
	}
	function media_sizes_link($args){
		printf(__('For further image dimension settings and resizing options upon upload see %s', 'th23-upload'), '<a href="' . esc_url($this->plugin['settings_base'] . '?page=' . $this->plugin['settings_handle']) . '"><strong>' . $this->plugin['data']['Name'] . ' ' . __('Settings', 'th23-upload') . '</strong></a>');
	}

	// Get information about available image sizes
	// note: does NOT include image sizes hidden through usage of filter "intermediate_image_sizes_advanced", eg th23 Social, th23 Featured which are "hiding" their image sizes from normal WP handling
	function get_image_sizes() {

		// note: WP introduced intermediate image sizes without being accessible in admin area to handle large files (see https://wordpress.org/support/topic/scaled-jpg-innecesary-sufix-and-added-2-image-sizes/)
		$additional_sizes = wp_get_additional_image_sizes();

		// prefill to include "full" as image size
		$sizes = array(
			'full' => array(
				'width' => (int) $this->options['max_width'],
				'height' => (int) $this->options['max_height'],
				'crop' => false,
				'active' => true,
			),
		);

		// reverse array as default WP sorts from smallest to biggest size
		$image_sizes = array_reverse(get_intermediate_image_sizes(), true);
		foreach($image_sizes as $size) {

			if(in_array($size, array('thumbnail', 'medium', 'medium_large', 'large'))) {
				$sizes[$size]['width'] = get_option($size . '_size_w');
				$sizes[$size]['height'] = get_option($size . '_size_h');
				$sizes[$size]['crop'] = (bool) get_option($size . '_crop');
			}
			elseif(isset($additional_sizes[$size])) {
				$sizes[$size]['width'] = $additional_sizes[$size]['width'];
				$sizes[$size]['height'] = $additional_sizes[$size]['height'];
				$sizes[$size]['crop'] = $additional_sizes[$size]['crop'];
			}

			// only include default image sizes to be selected as "active" which are bigger than the defined max dimensions
			if((!empty($this->options['max_width']) && $sizes[$size]['width'] >= $this->options['max_width']) || (!empty($this->options['max_height']) && $sizes[$size]['height'] >= $this->options['max_height'])) {
				$sizes[$size]['active'] = false;
			}
			else {
				$sizes[$size]['active'] = true;
			}

		}

		return $sizes;

	}

	// Show current watermark image and upload input field in plugin settings
	function watermark_image($default, $current_watermark) {

		$upload_dir = wp_get_upload_dir();
		$watermark_file = '/th23-upload/' . $current_watermark;

		$html = '<div class="th23-upload-watermark-image">';
		if(is_file($upload_dir['basedir'] . $watermark_file)) {
			$html .= '<img src="' . $upload_dir['baseurl'] . $watermark_file . '" />';
		}
		else {
			$html .= '<div class="th23-upload-watermark-placeholder">' . __('Select watermark', 'th23-upload') . '</div>';
		}
		$html .= '</div>';

		return $html;

	}

}

?>

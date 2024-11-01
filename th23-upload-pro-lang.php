<?php
/*
th23 Upload
Professional extension - Language strings

Copyright 2020, Thorsten Hartmann (th23)
http://th23.net
*/

// This file should not be executed - but only be read by the gettext parser to prepare for translations
die();

// Function to extract i18n calls from PRO file
$file = file_get_contents('th23-upload-pro.php');
preg_match_all("/__\\(.*?'\\)|_n\\(.*?'\\)|\\/\\* translators:.*?\\*\\//s", $file, $matches);
foreach($matches[0] as $match) {
	echo $match . ";\n";
}

__('Watermarks disabled!', 'th23-upload');
__('No JPG image!', 'th23-upload');
__('Failed to create backup!', 'th23-upload');
__('Watermark missing!', 'th23-upload');
__('Failed marking image size!', 'th23-upload');
__('Note: All setting changes only apply to newly uploaded images and their auto-generated sizes', 'th23-upload');
__('Note: A copy of your originally uploaded image is kept unaccessible to users, so you can restore an unmarked version later. Auto-generated unscaled / unrotated copies of your uploaded images will not be kept, as these would otherwise be accessible without watermark. ', 'th23-upload');
__('Disabling this option will still leave you the chance to add watermarks for individual images in the media gallery', 'th23-upload');
__('Select image sizes that the watermark should be applied to.', 'th23-upload');
__('Warning: Manual cropping of images done will be lost upon watermarking', 'th23-upload');
__('Recommendation: Select all un-cropped image sizes, esp the WP defaults "full", "large", "medium_large" and "medium"', 'th23-upload');
__('Note: List indicates maximum dimensions in pixels (width x height) for each and is limited to properly registered sizes, that are smaller than the maximum upload dimensions', 'th23-upload');
__('Click to select image used as watermark', 'th23-upload');
__('Note: Ideally PNG file with transparent background and not too big in size / dimensions', 'th23-upload');
__('<strong>Warning</strong>: Watermark upload folder is missing and could not be created. Please use a FTP program to create the folder "%s" on your server within this WordPress installation. And upload your watermark image to it.', 'th23-upload');
__('Position of watermark on the image', 'th23-upload');
__('Distance of watermark from image borders in pixels', 'th23-upload');
__('Maximum width / height of image covered by watermark in % - watermark will be shrinked, if required', 'th23-upload');
__('Mass actions', 'th23-upload');
__('<strong>Warning</strong>: Image editor does not support watermarking images. Adding watermarks will not work. Please ensure the file "%1$s" is uploaded to the folder "%2$s" of your WordPress installation.', 'th23-upload');
__('<strong>Warning</strong>: Selected watermark image is not available. No watermark will be added to images.', 'th23-upload');
__('Upload watermark', 'th23-upload');
__('Delete', 'th23-upload');
__('Select watermark', 'th23-upload');
__('Watermark all JPG attachments', 'th23-upload');
__('Remove watermark from all JPG attachments', 'th23-upload');
__('Mass add / remove watermark to all JPG images in the media gallery. Please confirm below before clicking above buttons.', 'th23-upload');
__('Note: This can take a long time and heavily utilize your server as %d attachments have to processed', 'th23-upload');
__('Confirm starting mass action', 'th23-upload');
__('Stop', 'th23-upload');
__('Close', 'th23-upload');
__('Upload Professional extension?', 'th23-upload');
__('Go to plugin settings page for upload...', 'th23-upload');
/* translators: 1: "Professional" as name of the version, 2: "...-pro.php" as file name, 3: version number of the PRO file, 4: version number of main file, 5: link to WP update page, 6: link to "th23.net" plugin download page, 7: link to "Go to plugin settings page to upload..." page or "Upload updated Professional extension?" link */;
__('The version of the %1$s extension (%2$s, version %3$s) does not match with the overall plugin (version %4$s). Please make sure you update the overall plugin to the latest version via the <a href="%5$s">automatic update function</a> and get the latest version of the %1$s extension from %6$s. %7$s', 'th23-upload');
__('Error', 'th23-upload');
__('Upload missing file?', 'th23-upload');
__('Go to plugin settings page for upload...', 'th23-upload');
/* translators: parses in 1: file name, 2: plugin folder, 3: "Upload missing file?" link */;
__('<strong>Warning</strong>: Important plugin file missing. Adding watermarks will not work. Please ensure the file "%1$s" is uploaded to the folder "%2$s" of your WordPress installation. %3$s', 'th23-upload');
__('<strong>Warning</strong>: Non-watermarked original images are accessible to users. Required server rules could not be added automatically. Please ensure a "%1$s" file exists in the folder "%2$s" of your WordPress installation and contains the following lines.', 'th23-upload');
__('Unmarking...', 'th23-upload');
__('Remove watermark', 'th23-upload');
__('Watermarking...', 'th23-upload');
__('Add watermark', 'th23-upload');
__('Invalid request!', 'th23-upload');
__('No permission!', 'th23-upload');
__('No valid action!', 'th23-upload');
__('No valid attachment!', 'th23-upload');
__('Not watermarked!', 'th23-upload');
__('Missing unmarked file!', 'th23-upload');
__('Failed to restore unmarked!', 'th23-upload');
__('Watermark removed', 'th23-upload');
__('Watermarking...', 'th23-upload');
__('Add watermark', 'th23-upload');
__('Watermarked', 'th23-upload');
__('Unmarking...', 'th23-upload');
__('Remove watermark', 'th23-upload');
__('Invalid request!', 'th23-upload');
__('No permission!', 'th23-upload');
__('No valid action!', 'th23-upload');
__('No valid watermark!', 'th23-upload');
__('Failed to delete watermark!', 'th23-upload');
__('No valid watermark!', 'th23-upload');
__('Failed to upload watermark!', 'th23-upload');
__('Watermarks', 'th23-upload');
__('For watermark setting upon image upload see %s', 'th23-upload');
__('Settings', 'th23-upload');

?>

=== th23 Upload ===
Contributors: th23
Donate link: http://th23.net/th23-upload
Tags: upload, watermark, image, resize, dimensions, jpg, maximum, mark, stamp, copyright
Requires at least: 4.2
Tested up to: 5.4
Stable tag: 1.6.0
Requires PHP: 5.6.32
License: GPLv2 only
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Resize images on upload to defined maximum dimensions, saving space and bandwidth. Watermark images automatically upon upload or manually via Media Library (Professional extension)


== Description ==

Provides easy admin access to **define maximum width and height** of uploaded images. Uploads exceeding these dimensions will be resized accordingly. Only the defined maximum size will be stored on the server. This way you will be able to **save space and bandwidth** on your webserver, while serving the pages faster to your visitors due to the reduced image size.

**Watermark your precious images** with the Professional extension, keeping track of them in the internet. New uploads can automatically be marked or you can **add/remove watermarks** via the Media Library. Unmarked copies of images are kept inaccessible to the public in case you want to restore it.

[youtube https://www.youtube.com/watch?v=umfS6tGseqI]

= Additional capabilities =

* Original aspect ration of uploaded images will be kept, preventing automatic cropping.
* Optionally specify a suffix to be added to names of resized images, eg upload of too large image "test.jpg" will be stored as "test_resized.jpg".
* Set quality for resized images, allowing to save further space and bandwidth.

= Professional extension =

Further functionality is available as [Professional extension](https://th23.net/th23-upload/):

* Select image sizes to watermark, eg to exclude thumbnails.
* Mass-add/-remove watermarks for already uploaded image attachments.
* Chose location of the watermark on the image and maximum width/height to cover.
* Supports GD Library and ImageMagick (Imagick)

= Special opportunity =

If you are **interested in trying out the Professional version** for free, write a review for the plugin and in return get a year long license including updates, please [register at my website](https://th23.net/user-management/?register) and [contact me](https://th23.net/contact/). First come, first serve - limited opportunity for the first 10 people!

= Configuration =

To see intro videos about installation and configuration visit the [th23 Youtube channel](https://www.youtube.com/channel/UCS3sNYFyxhezPVu38ESBMGA).

Note: Only handles JPG / JPEG images, as PNG (transparency) and GIF (animation) could loose their features upon resizing.

For more information on the plugin or to get the Professional extension visit the [authors website](http://th23.net/th23-upload/).

== Installation ==

The plugin can be installed most easily through your admin panel:

[youtube https://www.youtube.com/watch?v=voXCzBw13cY]

For a manual installation follow these steps:

1. Download the plugin and extract the ZIP file
1. Upload the plugin files to the `/wp-content/plugins/th23-upload` directory on your webserver
1. Activate the plugin through the 'Plugins' screen in the WordPress admin area
1. Use the 'Settings' -> 'th23 Upload' screen to configure the plugin

That is it - future uploads will be handled according to your settings automatically!

= Get and install the Professional extension =

For upgrading to the Professional extension, please follow the steps in our video tutorial:

[youtube https://www.youtube.com/watch?v=PlPJoYZMIWY]


== Frequently Asked Questions ==

= Video tutorial: Explaining all plugin settings in the admin area =

[youtube https://www.youtube.com/watch?v=Cll7btE7udE]

[youtube https://www.youtube.com/watch?v=dHO0qUTx1QE]

= How does this plugin help compared to WPs default scaling of uploads? =

By default WordPress limits the upload of images to max 2560px width/height, without providing any admin options to adjust this setting. This plugin allows you to disable this default and replace it with a setting accessible via the admin options.

This plugin additionally allows a customized suffix for resized images, compared to the unchangeable default "-scaled" suffix.

Also the plugin can prevent creation of additional intermediate sizes of all uploaded images, which due to their dimensions of 1536px and 2048px would be taking up much additional space on your webserver.

= What is the ideal image to use as a watermark? =

You should use a PNG image as watermark with transparent background. Try it out locally over various backgrounds/ images - light ones as well as darker ones.

The size depends largely on the size of your images to be marked. You can save a larger copy of the watermark PNG and limit the maximum space to cover via the plugin options. Make sure your watermark is still readable/ visible also when scaled down.

= Why can only JPG / JPEG images be watermarked? =

Simple answer: Most images used in the internet are in the JPG format. Watermarking JPG images should meet most users needs.

Technical answer: Other image formats can contain information and have special characteristics making their handling very tricky and error-prone. For example: PNG files can contain transparency levels, additionally they can be animated. Similar for GIF images.

Honest answer: To limit complexity of the plugin ;-)


== Screenshots ==

1. Plugin settings (maximum image upload size)
2. Media uploader
3. Media library (add/remove watermarks, Professional extension)
4. Watermarked image (bottom right corner, Professional extension)
5. Watermark settings (part 1, Professional extension)
6. Watermark settings (part 2, Professional extension)


== Changelog ==

= v1.6.0 =
* [enhancement, Pro] manage watermark images via plugin settings page
* [enhancement, Basic/Pro] improved plugin settings page, add screen options and help, add units to settings fields
* [fix, Basic/Pro] prevent installation of older Professional extension on newer Basic plugin
* [fix, Basic/Pro] prevent requirement messages from showing multiple times
* [fix, Basic/Pro] various small bug fixes
* [fix, Pro] check for non-empty nonce upon AJAX requests

= v1.4.2 =
* [fix, Basic/Pro] reminder about update/re-installation of Professional extension after plugin update

= v1.4.1 =
* [fix, Basic/Pro] ensure all options are always captured upon setting updates, including disabled ones

= v1.4.0 =
* [enhancement, Basic/Pro] allow upload of Professional extension through the admin panel
* [enhancement, Basic] allow for hidden input fields in settings area
* [fix, Basic/Pro] avoid empty default values upon fresh install / upgrade to Professional
* [fix, Basic/Pro] small visual / CSS fixes

= v1.2.0 =
* [enhancement, Basic/Pro] enabled Professional extension

= v1.0.0 (first public release) =
* n/a


== Upgrade Notice ==

= v1.6.0 =
* Easy upload and selection of watermark images via plugin settings in the admin panel

= v1.4.0 =
* Enjoy simplified handling of Professional extension upgrade and updates through the admin panel

= v1.2.0 =
* Watermark your images with the Professional extension

= v1.0.0 (first public release) =
* n/a

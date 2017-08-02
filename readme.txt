=== Movies ===
Contributors: wonderboymusic
Tags: media, attachments, admin, video, videos, cms, jquery, manage, music, upload, VideoJS, HTML5
Requires at least: 3.0
Tested up to: 3.0
Stable Tag: 0.6

HTML5 Video (on supported browsers), Flash fallback, CSS-skin'd player, hMedia Micro-formats, attach images to videos (when used with Shuffle), associated Ogg Theora videos with MP4s/H.264 (When used with Shuffle) 

== Description ==

Movies allows you to use simple functions in your theme to display videos you have attached to Posts/Pages/Custom Post Types in your Media Library. Your player is styled 100% with CSS/images (if you want). The video player uses the MediaElement (by default, or VideoJS - you pick!) library and your browser's native HTML5 capabilities when available with a fallback to Flash when necessary. Allows you to play video inline on mobile browsers that support HTML5 Video. Video metadata is written to the page using the hMedia micro-format for semantic markup.

You can use this shortcode <code>[movies]</code> or <code>the_movies()</code> or <code>the_videos()</code> in your theme to output your item's attachments.

You may need to add these Mime-Type declarations to <code>httpd.conf</code> or your <code>.htaccess</code> file
<code>
AddType video/ogg .ogv 
AddType video/mp4 .mp4 
AddType video/webm .webm
</code>

Read More here: http://scottctaylor.wordpress.com/2010/11/24/new-plugin-movies/

Follow-up: http://scottctaylor.wordpress.com/2010/11/28/movies-plugin-now-supports-webm/

Latest: http://scottctaylor.wordpress.com/2010/12/07/movies-v0-4-now-with-mediaelement-support/

== Changelog ==
= 0.6 = 
* Updates MediaElement to 2.0.2
* Sets <code>src="<MP4 file>"</code> on video tag to fix bug in Firefox
* Never sets empty <code>poster=""</code> so that there is no broken image icon on top of video
* Fixes the getID3 library so that PHP4-like calls to class methods statically without the proper access modifier won't throw notices or errors in the server logs, even when <code>error_reporting(-1)</code>

= 0.5 =
* Doesn't load scripts and stylesheets in admin anymore, adds extra check in JS to remove any accidental error from script being loaded in the wrong context

= 0.4 =
* MediaElement is now the default player for Movies. To use VideoJS, set <code>define('MEDIA_ELEMENT', false)</code> at the top of <code>plugins/movies/movies.php</code>. To remove the warning about this, set <code>define('WARNING', false)</code> in the same location.
* MediaElement CSS is not currently overridable as it has a unified UI across HTML5, Flash, and Silverlight
* added a function called <code>the_flash_video()</code> to return the video as Flash, this is useful for completely bypassing HTML5 if you are having problems with it

= 0.3 =
* Support for WebM added when used with [Shuffle](http://wordpress.org/extend/plugins/shuffle/ "Shuffle"), fixes Media Uploader to support WebM 

= 0.2 = 
* Some bug fixes, definitely update

= 0.1 =
* Initial release

== Screenshots ==

1. Using [Shuffle](http://wordpress.org/extend/plugins/shuffle/ "Shuffle"), you associate images and OGV files with MP4 files, all will be loaded automatically into the HTML5 video player
 
2. You can customize the look of your player and playlist by adding a video.css file in your theme's directory

== Upgrade Notice ==

* Update to get the latest bug fixes from ongoing development. 0.2 fixes bugs related to dynamic rendering of Video in Firfox.
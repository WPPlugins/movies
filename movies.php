<?php
/*
Plugin Name: Movies
Description: HTML5 Video (on supported browsers), Flash fallback, CSS-skin'd player, hMedia Micro-formats, attach images to Videos (when used with Shuffle)
Author: Scott Taylor
Version: 0.7
Author URI: http://tsunamiorigami.com
*/

if ( !class_exists( 'getID3' ) ) {
	require_once( 'getid3/getid3/getid3.php' );
}

/**
 *
 * defaults to MediaElement
 * set VIDEO_JS to 'false' to use VideoJS
 *
 */
define( 'VIDEO_JS', true );
define( 'MEDIA_ELEMENT', true );
define( 'SECURE', false );

class Movies {
	function init() {
		add_filter( 'upload_mimes', array( $this, 'upload_mimes' ) );
		add_action( 'wp_print_scripts', array( $this, 'scripts' ) );
		add_action( 'wp_print_styles', array( $this, 'styles' ) );
		add_shortcode( 'movies', 'shortcode' );
	}
	
	function upload_mimes( $mimes ) {
		$mimes['ogv'] = 'video/ogg';
		$mimes['webm'] = 'video/webm';
		return $mimes;	
	}
	
	function scripts() {
		if ( !is_admin() ) {
			$js = WP_PLUGIN_URL . '/movies/js';
		
			if ( MEDIA_ELEMENT ) {
				wp_register_script( 'mejs', $js . '/mediaelement-and-player.min.js', array( 'jquery' ) );	
				wp_register_script( 'movies', $js . '/dynamic.js', array( 'media-element' ) );
			} else {
				wp_register_script( 'video-js', $js . '/videoJS-2.0.js');	
				wp_register_script( 'movies', $js . '/dynamic.js', array( 'video-js', 'jquery' ) );
			}
		
			if ( SECURE ) {
				wp_enqueue_script( 'base64', $js . '/Base64.js' );
			}
		
			wp_enqueue_script( 'movies' );
		}		
	}
	
	function styles() {
		if ( !is_admin() ) {
			$css = WP_PLUGIN_URL . '/movies/css';

			if ( MEDIA_ELEMENT ) {
				wp_enqueue_style( 'media-element', $css . '/mediaelementplayer.min.css' );
			} else {
				wp_register_style( 'video-js', $css . '/videoJS-2.0.1-modified.css' );	
				wp_enqueue_style( 'movies', $css . '/video.css', array( 'video-js' ) );
			
				if ( is_file( STYLESHEETPATH . '/video.css' ) ) {
					wp_enqueue_style( 'movies-user', get_bloginfo( 'stylesheet_directory' ) . '/video.css', 
						array( 'movies' ) );		
				}	
			}
		}		
	}
	
	function shortcode( $atts, $content = null ) {
	     ob_start();
	     the_videos();
	     $videos = ob_get_contents();
	     ob_end_clean();
	     return $videos;
	}
}
$_movies_plugin = new Movies();
$_movies_plugin->init();

/**
 * Template Tags
 *
 */
function video_get_ogg_object( $id ) {
	$ogg = '';
	if ( function_exists( 'shuffle_by_mime_type' ) ) {
		$oggs = get_video( $id );
		if ( is_array( $oggs ) && count( $oggs ) > 0 ) {
			foreach ( $oggs as $o ) {
				if ( 'video/ogg' === $o->post_mime_type ) {
					$ogg = $o;
					break;				
				}
			}
		}
		unset($oggs);
	}
	return $ogg;
}

function video_get_webm_object( $id ) {
	$webm = '';
	if ( function_exists( 'shuffle_by_mime_type' ) ) {
		$webms = get_video( $id );
		if ( is_array( $webms ) && count( $webms ) > 0 ) {
			foreach ( $webms as $w ) {
				if ( 'video/webm' === $w->post_mime_type ) {
					$webm = $w;
					break;				
				}
			}
		}
		unset( $webms );
	}
	return $webm;
}

function video_get_ogg( $id ) {
	$ogg = '';
	
	$obj = video_get_ogg_object( $id );
	if ( !empty( $obj ) ) {
		$ogg = $obj->guid;
	}
	
	return $ogg;
}

function video_get_webm( $id ) {
	$webm = '';
	
	$obj = video_get_webm_object( $id );
	if ( !empty( $obj ) ) {
		$webm = $obj->guid;
	}
	
	return $webm;
}

function video_get_poster( $id ) {
	$image = '';
	if ( function_exists( 'shuffle_by_mime_type' ) ) {
		$images = get_images( $id );
		if ( is_array( $images ) && count( $images ) > 0 ) {
			$image = $images[0]->guid;
		}
		unset( $images );
	}
	return $image;
}

function video_flash_object( $source = '', $w = 0, $h = 0, $image = '' ) { 
	$w = $w < 400 ? 400 : $w; 

if ( MEDIA_ELEMENT ): ?>
	<object width="<?php echo $w ?>" height="<?php echo $h ?>" type="application/x-shockwave-flash" data="<?php echo WP_PLUGIN_URL ?>/movies/js/flashmediaelement.swf"> 		
		<param name="movie" value="<?php echo WP_PLUGIN_URL ?>/movies/js/flashmediaelement.swf" /> 
		<param name="flashvars" value="controls=true&amp;poster=<?php echo $image ?>&amp;file=<?php echo $source ?>" /> 		
	</object> 
<?php else: ?>
	<object class="vjs-flash-fallback" width="<?php echo $w ?>" height="<?php echo $h ?>" type="application/x-shockwave-flash"
	        data="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf">
	    <param name="movie" value="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf" />
	    <param name="allowfullscreen" value="true" />
	    <param name="wmode" value="transparent" />
	    <?php if ( !empty( $image ) ): ?><param name="flashvars" value='config={"playlist":["<?php echo $image ?>", {"autoPlay": false, "url": "<?php echo $source ?>"}]}' />
	    <?php else: ?><param name="flashvars" value='config={"clip": {"autoPlay": false, "url": "<?php echo $source ?>"}}' />
	    <?php endif ?>
	</object>
<?php   
endif; 
}

function videojs_embed( &$post, &$info ) {
if ( 'video/mp4' === $post->post_mime_type ):	
	$mp4 = $post->guid;
	$image = video_get_poster( $post->ID );
	$ogg = video_get_ogg( $post->ID );	
	$webm = video_get_webm( $post->ID );
	$w = $info['width'];
	$h = $info['height'];
?>
<?php if ( MEDIA_ELEMENT ): ?>
	<video width="<?php echo $w ?>" height="<?php echo $h ?>" poster="<?php echo $image ?>" controls="controls" src="<?php echo $mp4 ?>" preload="none">
	    <source type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"' src="<?php echo $mp4 ?>"/>
	    <?php if ( !empty( $ogg ) ): ?><source type='video/ogg; codecs="theora, vorbis"' src="<?php echo $ogg ?>"/><?php endif ?>
		<?php if ( !empty( $webm ) ): ?><source type='video/webm; codecs="vp8, vorbis"' src="<?php echo $webm ?>"/><?php endif ?>	
	</video>
<?php else: ?>	
	<video id="video-playlist" class="video-js player" width="<?php echo $w ?>" height="<?php echo $h ?>" preload="preload" controls="controls" <?php if (!empty($image)): ?>poster="<?php echo $image ?>"<?php endif ?>>
	    <source type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"' src="<?php echo $mp4 ?>"/>
	    <?php if ( !empty( $ogg ) ): ?><source type='video/ogg; codecs="theora, vorbis"' src="<?php echo $ogg ?>"/><?php endif ?>
		<?php if ( !empty( $webm ) ): ?><source type='video/webm; codecs="vp8, vorbis"' src="<?php echo $webm ?>"/><?php endif ?>
		<?php video_flash_object( $mp4, $w, $h, $image ); ?>
	</video>	    
<?php endif;
endif;
}

function video_get_src( $url ) {
	if ( SECURE ) {
		$url = base64_encode( $url );	
	}

	return $url;
}

function video_enclosure( &$post, &$info ) {
	$mime = $post->post_mime_type;
	$source = $post->guid;
	$title = apply_filters( 'the_title', $post->post_title );
	$attr = apply_filters( 'the_title_attribute', $post->post_title );	
?>
	<a rel="enclosure" type="<?php echo $mime ?>" title="Permalink for <?php echo $attr ?>" href="<?php echo video_get_src( $source ) ?>"><?php echo $title ?> (<span class="width"><?php echo $info['width'] ?></span> x <span class="height"><?php echo $info['height'] ?></span>)</a>
<?php
}

function video_formatted_item( &$post, &$info ) {
	$title = apply_filters( 'the_title', $post->post_title );
	$attr = apply_filters( 'the_title_attribute', $post->post_title );
	$artist =  $post->post_excerpt;
	$img = video_get_poster( $post->ID );
	$description = $post->post_content;
?>
	<div class="hMedia">
		<?php if ( !empty( $img ) ): ?><img class="photo" src="<?php echo $img ?>" alt="<?php echo $attr ?>"/>
		<?php endif ?><span class="fn">&#8220;<?php echo $title ?>&#8221;</span>
		<span class="contributor">
			<span class="vcard">
				<span class="fn org"><?php echo $artist ?></span>
			</span>
		</span>	   
	<?php 
		video_enclosure( $post, $info );
		$ogg = video_get_ogg_object( $post->ID );
		if ( !empty( $ogg ) ): video_enclosure( $ogg, $info ); endif;
		$webm = video_get_webm_object( $post->ID );
		if ( !empty( $webm ) ): video_enclosure( $webm, $info ); endif;
	?> 	
		<p><?php echo $description ?></p>    	    
	</div>
<?php
}

function video_get_id3( &$post ) {
	$parts = parse_url( $post->guid );	
	$local_file = getcwd() . $parts['path'];		

	$getID3 = new getID3;
	$fileinfo = $getID3->analyze( $local_file );
	getid3_lib::CopyTagsToComments( $fileinfo );
	
	$info = array();
	$info['width'] = $fileinfo['video']['resolution_x'];
	$info['height'] = $fileinfo['video']['resolution_y'];
	
	return $info;
}

function the_flash_video() {
	$video =& get_posts( array(
		'post_parent'    => get_the_id(),
		'post_mime_type' => 'video',
		'order'       	 => 'ASC',
		'orderby'     	 => 'menu_order',
		'post_type'   	 => 'attachment',
		'post_status' 	 => 'inherit',
		'numberposts' 	 => 1	
	) );
	$v = $video[0];
	$info = video_get_id3( $v );
	$img = video_get_poster( $v->ID );
	
	video_flash_object( $v->guid, $info['width'], $info['height'], $img );	
}

function the_videos() {
if ( function_exists( 'shuffle_by_mime_type' ) ):
	$videos = get_video(); 
else:
	// this is functionality ported over from Shuffle
	// you should be using Shuffle!!!	
	$videos = get_posts( array(
		'post_parent'    => get_the_id(),
		'post_mime_type' => 'video',
		'order'       	 => 'ASC',
		'orderby'     	 => 'menu_order',
		'post_type'   	 => 'attachment',
		'post_status' 	 => 'inherit',
		'numberposts' 	 => -1	
	) );
endif;
if ( is_array( $videos ) ): ?>
<div class="videos-wrapper" data-library="<?php echo MEDIA_ELEMENT ? 'me-js' : 'video-js'?>">	
<?php if ( 1 === count( $videos ) ): ?>
<div class="video-js-box" id="video-js-box">
	<div class="vjs-no-video"></div>
	<?php
	$info = video_get_id3( $videos[0] );
	
	videojs_embed( $videos[0], $info );
	?></div>
	<?php 
	video_formatted_item( $videos[0], $info ); 
	unset( $info );
	else: 
	/**
	 * Embed code will be loaded via JavaScript
	 *
	 */
	?>
<div class="video-js-box" id="video-js-box"><div class="vjs-no-video"></div></div>
<div class="videos">
<?php 
foreach ( $videos as $video ): 
	$info = video_get_id3( $video );	
	video_formatted_item( $video, $info );
	unset( $info ); 
endforeach ?>
</div>	
<?php endif;
	unset( $videos ); ?>
</div>	
<?php endif;
}
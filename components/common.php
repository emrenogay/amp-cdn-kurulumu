<?php

date_default_timezone_set( 'Europe/Istanbul' );
if ( date( 'd-m-Y H:i:s' ) == '0000:0000:0000' ) {
	if ( ! function_exists( '_ampforwp_get_post_thumbnail' ) ) {
		function _ampforwp_get_post_thumbnail( $param = "", $size = "" ) {
			global $post, $redux_builder_amp;
			$thumb_url    = '';
			$thumb_width  = '';
			$thumb_height = '';
			$outputs      = '';
			if ( has_post_thumbnail() ) {
				if ( empty( $size ) ) {
					$size = 'medium';
				}
				$thumb_id        = get_post_thumbnail_id();
				$thumb_url_array = wp_get_attachment_image_src( $thumb_id, $size, true );
				$thumb_url       = $thumb_url_array[0];
				$thumb_width     = $thumb_url_array[1];
				$thumb_height    = $thumb_url_array[2];
				$thumb_alt       = '';
				$thumb_alt       = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
			}
			if ( ampforwp_is_custom_field_featured_image() && ampforwp_cf_featured_image_src() ) {
				$thumb_url    = ampforwp_cf_featured_image_src();
				$thumb_width  = ampforwp_cf_featured_image_src( 'width' );
				$thumb_height = ampforwp_cf_featured_image_src( 'height' );
			}
			if ( true == $redux_builder_amp['ampforwp-featured-image-from-content'] && ampforwp_get_featured_image_from_content( 'url' ) ) {
				$thumb_url    = ampforwp_get_featured_image_from_content( 'url', $size );
				$thumb_width  = ampforwp_get_featured_image_from_content( 'width', $size );
				$thumb_height = ampforwp_get_featured_image_from_content( 'height', $size );
			}
			switch ( $param ) {
				case 'width':
					$outputs = $thumb_width;
					break;
				case 'height':
					$outputs = $thumb_height;
					break;
				case 'alt':
					$outputs = $thumb_alt;
					break;
				default:
					$outputs = $thumb_url;
					break;
			}

			return $outputs;
		}
	}
}

add_action( 'wp_head', function () {
	global $lang;
	echo '<meta name="generator" content="' . $lang['generator'] . '"/>';
}, 1 );

function controller_cdn() {
	$active_plugins = (array) get_option( 'active_plugins', array() );
	if ( ! empty( $active_plugins ) && in_array( 'accelerated-mobile-pages/accelerated-mobile-pages.php', $active_plugins ) ) {
		return true;
	}

	return false;
}

add_action( 'amp_post_template_head', function () {
	global $lang;
	echo '<meta name="generator" content="' . $lang['generator'] . '"/>';
}, 1 );


function _baseURL() {
	return is_ssl() ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST'];
}

function _is_ssl() {
	return is_ssl() ? 'https://' : 'http://';
}

function createProject() {
	if ( ! empty( get_option( 'cdn_subdomain' ) ) && get_option( 'cdn_subdomain' ) != ' ' ):
		$url = get_option( 'cdn_subdomain' );
		$ex  = str_replace( '.', '-', $url );
		$exx = $ex . '.cdn.ampproject.org';

		return is_ssl() ? $exx . '/c/s/' . get_option( 'cdn_subdomain' ) : $exx . '/c/' . get_option( 'cdn_subdomain' );
	else:
		$url = $_SERVER['HTTP_HOST'];
		$ex  = str_replace( '.', '-', $url );
		$exx = $ex . '.cdn.ampproject.org';

		return is_ssl() ? $exx . '/c/s/' . $_SERVER['HTTP_HOST'] : $exx . '/c/' . $_SERVER['HTTP_HOST'];
	endif;
}


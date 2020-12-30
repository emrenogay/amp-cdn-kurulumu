<?php

if ( ! function_exists( '_ampforwp_get_author_page_url' ) ) {
	function _ampforwp_get_author_page_url() {
		global $redux_builder_amp, $post;
		$author_id       = '';
		$author_page_url = '';
		$author_id       = get_the_author_meta( 'ID' );
		$author_page_url = get_author_posts_url( $author_id );

		if ( isset( $redux_builder_amp['ampforwp-archive-support'] ) && $redux_builder_amp['ampforwp-archive-support'] ) {
			$author_page_url = ampforwp_url_controller( $author_page_url );
		}

		return $author_page_url;
	}
}


function _find( $finder ) {
	global $lang;


	if ( strpos( $finder, 'https://cdn.ampproject.org/' ) !== false && ! is_admin() ) {

		$search = [
			'action="' . get_home_url() . '"',
			'https://cdn.ampproject.org/v0/amp-form-.js',
			'action="/amp'
		];

		$replace = [
			'action="' . 'https://' . createProject() . '"',
			'https://cdn.ampproject.org/v0/amp-form-0.1.js',
			'action="https://' . createProject().'/amp'
		];

		$finder = str_replace($search, $replace, $finder);

		$site = str_replace(['http://', 'https://'], '', rtrim(get_site_url(), '/'));
		$pattern = '@<a(.*?)href="https?://'.$site.'@si';
		$replace = '<a$1href="https://'.createProject();
		$finder = $finder .= '<!-- '.$lang['generator'].' -->';
		return preg_replace( $pattern, $replace, $finder );
	}

	if (get_option('cdn_subdomain') == ' ' || empty(get_option('cdn_subdomain'))) {
		$finder = str_replace('<link rel="amphtml" href="' . get_home_url(), '<link rel="amphtml" href="' . _is_ssl() . $_SERVER['HTTP_HOST'], $finder);
	} else {
		$finder = str_replace('<link rel="amphtml" href="' . get_home_url(), '<link rel="amphtml" href="' . _is_ssl() . get_option('cdn_subdomain'), $finder);
	}

	return $finder;
}


if ( ! function_exists( '_ampforwp_get_author_details' ) ) {
	function _ampforwp_get_author_details( $post_author, $params = '' ) {
		global $redux_builder_amp, $post;
		$post_author_url  = '';
		$post_author_name = '';
		$post_author_name = $post_author->display_name;
		$post_author_url  = ampforwp_get_author_page_url();
		$and_text         = '';
		$and_text         = ampforwp_translation( $redux_builder_amp['amp-translator-and-text'], 'and' );
		if ( function_exists( 'coauthors' ) ) {
			$post_author_name = coauthors( $and_text, $and_text, null, null, false );
		}
		if ( function_exists( 'coauthors_posts_links' ) ) {
			$post_author_url = coauthors_posts_links( $and_text, $and_text, null, null, false );
		}
		switch ( $params ) {
			case 'meta-info':
				if ( isset( $redux_builder_amp['ampforwp-author-page-url'] ) && $redux_builder_amp['ampforwp-author-page-url'] ) {
					if ( function_exists( 'coauthors_posts_links' ) ) {
						return '<span class="amp-wp-author author vcard">' . $post_author_url . '</span>';
					}

					return '<span class="amp-wp-author author vcard"><a href="' . esc_url( $post_author_url ) . '"  title="' . esc_html( $post_author_name ) . '" >' . esc_html( $post_author_name ) . '</a></span>';
				} else {
					return '<span class="amp-wp-author author vcard">' . esc_html( $post_author_name ) . '</span>';
				}
				break;

			case 'meta-taxonomy':
				if ( isset( $redux_builder_amp['ampforwp-author-page-url'] ) && $redux_builder_amp['ampforwp-author-page-url'] ) {
					if ( function_exists( 'coauthors_posts_links' ) ) {
						return $post_author_url;
					}

					return '<a href="' . esc_url( $post_author_url ) . ' "><strong>' . esc_html( $post_author_name ) . '</strong></a>: ';
				} else {
					return '<strong> ' . esc_html( $post_author_name ) . '</strong>: ';
				}
				break;
		}
	}
}

<?php

namespace Yoast\YoastCom\AlgoliaModifications;

class Attribute_Manager {

	public function register_hooks() {
		add_filter( 'algolia_searchable_post_shared_attributes', array( $this, 'add_excerpt_to_post' ), 10, 2 );
		add_filter( 'algolia_searchable_post_shared_attributes', array( $this, 'add_metadesc_to_post' ), 10, 2 );
		add_filter( 'algolia_searchable_post_shared_attributes', array( $this, 'add_author_url' ), 10, 2 );
	}

	public function add_author_url( array $shared_attributes, \WP_Post $post ) {
		$shared_attributes['post_author']['author_link'] = get_author_posts_url( $post->post_author, get_the_author_meta( 'nicename', $post->post_author ) );

		return $shared_attributes;
	}

	public function add_metadesc_to_post( array $shared_attributes, \WP_Post $post ) {
		$shared_attributes['metadesc'] = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );

		return $shared_attributes;
	}

	public function add_excerpt_to_post( array $shared_attributes, \WP_Post $post ) {

		$excerpt = apply_filters( 'the_excerpt', get_the_excerpt( $post->ID ) );
		if ( is_string( $excerpt ) && ! empty( $excerpt ) ) {
			$shared_attributes['excerpt'] = $excerpt;
		} else {
			// todo: The content filter returned null for some reason. Use this filter once we know why that happend.
//			$post_content                 = strip_shortcodes( apply_filters( 'the_content', $post->post_content ) );
			$post_content                 = strip_shortcodes( $post->post_content );
			$generated_excerpt            = wp_trim_excerpt( $post_content );
			$shared_attributes['excerpt'] = $generated_excerpt;
		}

		$shared_attributes['excerpt'] = $this->removeTrailingDots( $shared_attributes['excerpt'] );
		$shared_attributes['excerpt'] = wp_trim_words( $shared_attributes['excerpt'] );

		return $shared_attributes;
	}

	/**
	 * Removes trailing dots (including &hellip;) from the end of a string.
	 *
	 * @param $text String
	 *
	 * @return string
	 */
	private function removeTrailingDots( $text ) {
		$text = rtrim( $text, '.…' );
		$text = preg_replace( '/(&#8230;|&hellip;)$/', '', $text );

		return $text;
	}


}

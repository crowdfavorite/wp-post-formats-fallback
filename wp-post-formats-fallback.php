<?php

/*
Plugin Name: CF Post Formats Fallback
Plugin URI: http://crowdfavorite.com
Description: Add post format field content to main post content.
Version: 1.0dev
Author: crowdfavorite
Author URI: http://crowdfavorite.com 
*/

/**
 * Copyright (c) 2011 Crowd Favorite, Ltd. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

function cfpff_init() {
	if (!is_admin()) {
		add_action('the_posts', 'cfpff_the_posts');
	}
}
add_action('init', 'cfpff_init');

function cfpff_the_posts($posts) {
	if (is_array($posts) && count($posts)) {
		foreach ($posts as &$post) {
// check to see if the post has a format
// taken directly from get_post_format(), but without the check for post format support
	
			$_format = get_the_terms( $post->ID, 'post_format' );
		
			if ( empty( $_format ) )
				continue;
		
			$format = array_shift( $_format );
			$format = str_replace('post-format-', '', $format->slug);
		
// check for custom fields and attach to the post content
		
			switch ($format) {
				case 'link':
				case 'image':
				case 'gallery':
				case 'video':
				case 'audio':
				case 'quote':
					$post->post_content = call_user_func('cfpff_fallback_'.$format, $post);
			}
		}
	}
	return $posts;
}

function cfpff_fallback_link($post) {
	$url = get_post_meta($post->ID, '_format_link_url', true);
	if (!empty($url)) {
		$parts = parse_url($url);
		$post->post_content .= "\n\n"
		.'<p><a href="'.esc_url($url).'">'.sprintf(__('View on %s', 'cf-post-formats-fallback'), esc_html($parts['host'])).'</a></p>';
	}
	return $post->post_content;
}

// TODO - test
function cfpff_fallback_image($post) {
	$image_id = intval(get_post_meta($post->ID, '_thumbnail_id', true));
	if ($image_id) {
		$post->post_content = wp_get_attachment_image($image_id, 'small')."\n\n".$post->post_content;
	}
	return $post->post_content;
}

// TODO - test
function cfpff_fallback_gallery($post) {
	$gallery = do_shortcode('[gallery]');
	if (!empty($gallery)) {
		$post->post_content = $gallery."\n\n".$post->post_content;
	}
	return $post->post_content;
}

// TODO - test
function cfpff_fallback_video($post) {
	$embed = get_post_meta($post->ID, '_format_video_embed', true);
	if (!empty($embed)) {
		$post->post_content = $embed."\n\n".$post->post_content;
	}
	return $post->post_content;
}

// TODO - test
function cfpff_fallback_audio($post) {
	$embed = get_post_meta($post->ID, '_format_audio_embed', true);
	if (!empty($embed)) {
		$post->post_content = $embed."\n\n".$post->post_content;
	}
	return $post->post_content;
}

// TODO - test
function cfpff_fallback_quote($post) {
	$name = get_post_meta($post->ID, '_format_quote_source_name', true);
	$url = get_post_meta($post->ID, '_format_quote_source_url', true);
	if (!empty($name)) {
		$post->post_content .= "\n\n".'<p>&mdash; <i>'.(!empty($url) ? '<a href="'.esc_url($url).'">'.esc_html($name).'</a>' : esc_html($name)).'</i></p>';
	}
	return $post->post_content;
}



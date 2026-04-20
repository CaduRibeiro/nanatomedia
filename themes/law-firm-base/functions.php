<?php
/**
 * Core theme bootstrap for Law Firm Base.
 *
 * @package LawFirmBase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up basic theme supports.
 *
 * @return void
 */
function law_firm_base_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
}
add_action( 'after_setup_theme', 'law_firm_base_setup' );

/**
 * Enqueues the main theme stylesheet.
 *
 * @return void
 */
function law_firm_base_enqueue_styles() {
	wp_enqueue_style(
		'law-firm-base-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'law_firm_base_enqueue_styles' );

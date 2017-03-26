<?php

/*
Plugin Name: Contact Form 7 Range Slider Extender
Plugin URI:  https://github.com/DanielDrabik/Contact-Form-7-Range-Slider-Extender
Description: Extends usability of stock range slider available in Contact Form 7 plugin.
Version:     0.1
Author:      Daniel Drabik
Author URI:  https://github.com/DanielDrabik
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: ddrabik
*/

/*
	Add some style for the output
*/

function wpcf7_range_slider_extender_style() {
    wp_enqueue_style( 'wpcf7_range_slider_extender', plugins_url( 'css/styles.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'wpcf7_range_slider_extender_style' );


/*
	Override existing function of 'tag_number'
*/

remove_action( 'wpcf7_init', 'wpcf7_add_form_tag_number' );

add_action('wpcf7_init', 'wpcf7_range_slider_extender');

function wpcf7_range_slider_extender() {
	wpcf7_add_form_tag( array( 'number', 'number*', 'range', 'range*' ),
		'wpcf7_range_slider_extender_handler', array( 'name-attr' => true ) );
}

/*
	Fixed function
*/

function wpcf7_range_slider_extender_handler ( $tag ) {

	$tag = new WPCF7_FormTag( $tag );

	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	$class .= ' wpcf7-validates-as-number';

	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );
	$atts['min'] = $tag->get_option( 'min', 'signed_int', true );
	$atts['max'] = $tag->get_option( 'max', 'signed_int', true );
	$atts['step'] = $tag->get_option( 'step', 'int', true );

	if ( $tag->has_option( 'readonly' ) ) {
		$atts['readonly'] = 'readonly';
	}

	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}

	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

	$value = (string) reset( $tag->values );

	if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
	}

	$value = $tag->get_default_option( $value );

	$value = wpcf7_get_hangover( $tag->name, $value );

	$atts['value'] = $value;

	if ( wpcf7_support_html5() ) {
		$atts['type'] = $tag->basetype;
	} else {
		$atts['type'] = 'text';
	}

	$atts['name'] = $tag->name;

	$atts = wpcf7_format_atts( $atts );

	
	$html = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><input id="%5$s" oninput="output%5$s.value=%5$s.value" %2$s />%3$s</span><output class="contactform7-output" name="%1$s" id="output%5$s" for="%5$s">%4$s</output>',
		sanitize_html_class( $tag->name ), $atts, $validation_error, $value,  sanitize_tag_name( $tag->name ));

	return $html;
}

/*
	Tag's ID cannot store nonalphabetic/numeric signs
*/

function sanitize_tag_name($string) {
	return preg_replace("/[^A-Za-z0-9]/", '', $string);
}


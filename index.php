<?php

/*
 * Plugin Name: KGR Cookie Duration
 * Plugin URI: https://github.com/constracti/kgr-cookie-duration
 * Description: Filters the authentication cookie duration.
 * Version: 1.0.2
 * Requires at least: 3.5.0
 * Requires PHP: 7.0
 * Author: constracti
 * Author URI: https://github.com/constracti
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: kgr-cookie-duration
 * Domain Path: /languages
 */

if ( !defined( 'ABSPATH' ) )
	exit;

define( 'KGR_COOKIE_DURATION_SHORT', 2 );
define( 'KGR_COOKIE_DURATION_LONG', 14 );

add_filter( 'plugin_action_links', function( array $actions, string $plugin_file ): array {
	if ( $plugin_file !== basename( __DIR__ ) . '/' . basename( __FILE__ ) )
		return $actions;
	$actions['settings'] = sprintf( '<a href="%s">%s</a>',
		admin_url( 'options-general.php' ),
		esc_html__( 'Settings', 'kgr-cookie-duration' )
	);
	return $actions;
}, 10, 2 );

add_action( 'admin_init', function(): void {
	if ( !current_user_can( 'manage_options' ) )
		return;
	$page = 'general';
	$section = 'kgr-cookie-duration';
	$title = esc_html__( 'Cookie Duration', 'kgr-cookie-duration' );
	add_settings_section( $section, $title, '__return_null', $page );
	$callback = function( array $args ): void {
		echo sprintf( '<input type="number" class="small-text" id="%s" name="%s" value="%s" />',
			esc_attr( $args['name'] ),
			esc_attr( $args['label_for'] ),
			esc_attr( get_option( $args['name'] ) )
		) . "\n";
		echo esc_html__( 'days', 'kgr-cookie-duration' ) . "\n";
	};
	// https://developer.wordpress.org/reference/functions/wp_set_auth_cookie/
	$id = 'kgr-cookie-duration-short';
	register_setting( $page, $id, [
		'type' => 'integer',
		'default' => KGR_COOKIE_DURATION_SHORT,
	] );
	$title = esc_html__( 'Cookie short duration', 'kgr-cookie-duration' );
	add_settings_field( $id, $title, $callback, $page, $section, [
		'name' => $id,
		'label_for' => $id,
	] );
	$id = 'kgr-cookie-duration-long';
	register_setting( $page, $id, [
		'type' => 'integer',
		'default' => KGR_COOKIE_DURATION_LONG,
	] );
	$title = esc_html__( 'Cookie long duration', 'kgr-cookie-duration' );
	add_settings_field( $id, $title, $callback, $page, $section, [
		'name' => $id,
		'label_for' => $id,
	] );
} );

add_filter( 'auth_cookie_expiration', function( int $length, int $user_id, bool $remember ): int {
	// https://developer.wordpress.org/reference/hooks/auth_cookie_expiration/
	if ( $remember )
		return get_option( 'kgr-cookie-duration-long', KGR_COOKIE_DURATION_LONG ) * DAY_IN_SECONDS; // 3.5.0
	else
		return get_option( 'kgr-cookie-duration-short', KGR_COOKIE_DURATION_SHORT ) * DAY_IN_SECONDS; // 3.5.0
}, 10, 3 );

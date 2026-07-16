<?php

/**
 * Bootstrap fallback for the PressGang parent-theme framework.
 *
 * PressGang is a *parent-theme framework*: it boots from the Composer autoloader
 * supplied by an active child theme. When PressGang is activated — or "Live
 * Preview"-ed — directly, no child theme is present to provide that autoloader,
 * so `PressGang\PressGang` cannot load and both this bootstrap and every template
 * stub (index.php, front-page.php, …) would fatal on the framework class.
 *
 * functions.php requires this file only in that case, and returns. It runs *before*
 * autoloading, so it deliberately uses WordPress core functions only — never a
 * PressGang class, Snippet or Controller, all of which depend on the very
 * autoloader that is absent here.
 *
 * @see functions.php
 * @package PressGang
 */

// Front end / Customizer preview: intercept the request before the template loader
// reaches a stub that would call the (missing) framework renderer, and explain the
// situation instead of fataling.
\add_action( 'template_redirect', static function (): void {
	\wp_die(
		\wp_kses_post(
			'<h1 style="margin-bottom:.5em">PressGang is a parent-theme framework</h1>'
			. '<p>It has no front end of its own. Activate a <strong>child theme</strong> built on '
			. 'PressGang — the child supplies the Composer autoloader that boots the framework.</p>'
			. '<p><a href="https://pressgang.dev">Learn more at pressgang.dev &rarr;</a></p>'
		),
		\esc_html__( 'PressGang framework', 'pressgang' ),
		[ 'response' => 200 ]
	);
}, 0 );

// wp-admin: surface the same guidance for anyone who activated the framework directly.
\add_action( 'admin_notices', static function (): void {
	printf(
		'<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
		\esc_html__( 'PressGang is a parent-theme framework.', 'pressgang' ),
		\wp_kses_post(
			__( 'It can’t run on its own — activate a child theme built on PressGang (the child provides the Composer autoloader that boots it). Learn more at <a href="https://pressgang.dev">pressgang.dev</a>.', 'pressgang' )
		)
	);
} );

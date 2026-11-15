<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;

/**
 * Sentry error reporting. Errors only: no tracing, and the SDK's default
 * send_default_pii = false keeps cookies, IPs and bodies out of events.
 *
 * Set `'sentry_dsn' => '...'` in the environment's config to enable; an empty
 * or missing DSN leaves the SDK uninitialised (local dev stays silent).
 *
 * Kirby registers its own (Whoops) error handler before plugins load, and
 * Sentry chains to whatever handler was registered previously, so Kirby's
 * error pages keep working.
 */
App::plugin('invis/sentry', []);

$dsn = kirby()->option('sentry_dsn');

if (!empty($dsn) && function_exists('Sentry\init')) {
	\Sentry\init([
		'dsn' => $dsn,
		'environment' => kirby()->option('env', 'prod'),
		'send_default_pii' => false,
		'traces_sample_rate' => 0,
	]);
}

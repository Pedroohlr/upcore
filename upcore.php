<?php

/**
 * Plugin Name: UpCore
 * Plugin URI: https://github.com/Pedroohlr/upcore
 * Description: Baseline de seguranca e performance da UpSites para projetos WordPress.
 * Version: 0.1.0
 * Author: UpSites
 * Author URI: https://upsites.digital
 * Text Domain: upcore
 * Requires PHP: 8.1
 * Requires at least: 6.4
 */

declare(strict_types=1);

namespace UpCore;

if (! defined('ABSPATH')) {
    exit;
}

define('UPCORE_VERSION', '0.1.0');
define('UPCORE_FILE', __FILE__);
define('UPCORE_DIR', __DIR__);
define('UPCORE_URL', rtrim(plugin_dir_url(__FILE__), '/'));

require_once __DIR__ . '/vendor/autoload.php';

(new Plugin())->boot();

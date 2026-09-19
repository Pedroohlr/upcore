<?php

/**
 * UpCore loader.
 *
 * Copie este arquivo para wp-content/mu-plugins/upcore-loader.php em cada projeto.
 * MU-plugins dentro de subpastas nao sao carregados automaticamente pelo WordPress,
 * entao este stub e quem inicializa o plugin de fato, que vive em mu-plugins/upcore/.
 */

if (! defined('ABSPATH')) {
    exit;
}

$upcore_bootstrap = WPMU_PLUGIN_DIR . '/upcore/upcore.php';

if (file_exists($upcore_bootstrap)) {
    require_once $upcore_bootstrap;
}

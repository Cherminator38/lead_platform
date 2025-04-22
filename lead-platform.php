<?php
/**
 * Plugin Name: Lead Platform
 * Description: Plugin modulaire pour la gestion des leads et tarification dynamique.
 * Version: 1.0.0
 * Author: Cherminator38
 */

if (!defined('ABSPATH')) {
    exit;
}

// Autoload et initialisation
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
    \PD\Core::init();
} else {
    add_action('admin_notices', function() {
        echo '<div class="error"><p>Veuillez exécuter <code>composer install</code> dans le dossier du plugin pour générer l\'autoload.</p></div>';
    });
}

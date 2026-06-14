<?php

if (!function_exists('safe_query')) {
    die('Access denied');
}

global $plugin;

PluginInstallerHelper::install([

    'modulname'  => 'discord',
    'name'       => 'Discord',
    'version'    => (string)($plugin['version'] ?? '0.0.0'),
    'author'     => 'Nexpell-Team',
    'website'    => 'https://www.nexpell.de',
    'path'       => 'includes/plugins/discord/',
    'admin_file' => 'admin_discord',
    'index_link' => 'discord',
    'sidebar'    => 'deactivated',

    'languages' => [
        'plugin_info_discord' => [
            'de' => 'Discord-Community-Integration mit Seite, Sidebar-Widget und Admin-Konfiguration.',
            'en' => 'Discord community integration with page, sidebar widget, and admin configuration.',
            'it' => 'Integrazione della community Discord con pagina, widget sidebar e configurazione admin.'
        ]
    ],

    'permissions' => [
        'discord'
    ],

    'widgets' => [
        [
            'widget_key'    => 'widget_discord_sidebar',
            'title'         => 'Discord Widget Sidebar',
            'description'   => 'Discord server sidebar widget.',
            'allowed_zones' => 'left,right'
        ]
    ],

    'admin_navigation' => [
        [
            'url'   => 'admincenter.php?site=admin_discord',
            'catID' => 11,
            'sort'  => 1,
            'labels' => [
                'de' => 'Discord',
                'en' => 'Discord',
                'it' => 'Discord'
            ]
        ]
    ],

    'website_navigation' => [
        [
            'url'        => 'index.php?site=discord',
            'mnavID'     => 6,
            'sort'       => 1,
            'indropdown' => 1,
            'labels' => [
                'de' => 'Discord',
                'en' => 'Discord',
                'it' => 'Discord'
            ]
        ]
    ]

]);

safe_query("CREATE TABLE IF NOT EXISTS plugins_discord (
  name VARCHAR(100) NOT NULL,
  value TEXT,
  PRIMARY KEY (name)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

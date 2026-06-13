<?php

if (!function_exists('safe_query')) {
    die('Access denied');
}

global $plugin;

$version = isset($plugin['version']) ? (string)$plugin['version'] : ($version ?? '1.0.0');
$pluginPath = 'includes/plugins/discord/';

if (!function_exists('discord_sql')) {
    function discord_sql($value): string
    {
        return escape((string)$value);
    }
}

safe_query("CREATE TABLE IF NOT EXISTS plugins_discord (
  name VARCHAR(100) NOT NULL,
  value TEXT,
  PRIMARY KEY (name)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

PluginInstallerHelper::registerPlugin([
    'modulname'   => 'discord',
    'name'        => 'Discord',
    'version'     => $version,
    'admin_file'  => 'admin_discord',
    'path'        => $pluginPath,
    'author'      => 'T-Seven',
    'website'     => 'https://www.nexpell.de',
    'index_link'  => 'discord',
    'hiddenfiles' => '',
    'sidebar'     => 'deactivated'
]);

PluginInstallerHelper::addLanguageItem('plugin_info_discord', 'discord', [
    'de' => 'Discord-Community-Integration mit Seite, Sidebar-Widget und Admin-Konfiguration.',
    'en' => 'Discord community integration with page, sidebar widget, and admin configuration.',
    'it' => 'Integrazione della community Discord con pagina, widget sidebar e configurazione admin.'
]);

safe_query("
    INSERT INTO settings_widgets
        (widget_key, title, modulname, plugin, description, allowed_zones, active, version, created_at)
    VALUES
        ('widget_discord_sidebar', 'Discord Widget Sidebar', 'discord', 'discord', 'Discord server sidebar widget.', 'left,right', 1, '" . discord_sql($version) . "', NOW())
    ON DUPLICATE KEY UPDATE
        title = VALUES(title),
        modulname = VALUES(modulname),
        plugin = VALUES(plugin),
        description = VALUES(description),
        allowed_zones = VALUES(allowed_zones),
        active = VALUES(active),
        version = VALUES(version)
");

PluginInstallerHelper::registerAdminNavigation([
    'modulname' => 'discord',
    'url'       => 'admincenter.php?site=admin_discord',
    'catID'     => 11,
    'sort'      => 1,
    'labels'    => [
        'de' => 'Discord',
        'en' => 'Discord',
        'it' => 'Discord'
    ]
]);

PluginInstallerHelper::registerWebsiteNavigation([
    'modulname'  => 'discord',
    'url'        => 'index.php?site=discord',
    'mnavID'     => 6,
    'sort'       => 1,
    'indropdown' => 1,
    'labels'     => [
        'de' => 'Discord',
        'en' => 'Discord',
        'it' => 'Discord'
    ]
]);

PluginInstallerHelper::registerAdminRight('discord');

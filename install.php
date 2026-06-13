<?php

if (!function_exists('safe_query')) {
    die('Access denied');
}

global $_database, $plugin;

$modulname = 'discord';
$version = isset($plugin['version']) ? (string)$plugin['version'] : ($version ?? '1.0.0');
$pluginName = 'Discord';
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

## SYSTEM #######################################################################

$pluginRes = safe_query("SELECT pluginID FROM settings_plugins WHERE modulname = 'discord' LIMIT 1");
if ($pluginRes && ($pluginRow = mysqli_fetch_assoc($pluginRes))) {
    safe_query("UPDATE settings_plugins SET
        admin_file = 'admin_discord',
        activate = 1,
        author = 'T-Seven',
        website = 'https://www.nexpell.de',
        index_link = 'discord',
        hiddenfiles = '',
        version = '" . discord_sql($version) . "',
        path = '" . discord_sql($pluginPath) . "',
        status_display = 1,
        plugin_display = 1,
        widget_display = 1,
        delete_display = 1,
        sidebar = 'deactivated'
        WHERE pluginID = " . (int)$pluginRow['pluginID'] . "
    ");
} else {
    safe_query("INSERT INTO settings_plugins
        (modulname, admin_file, activate, author, website, index_link, hiddenfiles, version, path, status_display, plugin_display, widget_display, delete_display, sidebar)
    VALUES
        ('discord', 'admin_discord', 1, 'T-Seven', 'https://www.nexpell.de', 'discord', '', '" . discord_sql($version) . "', '" . discord_sql($pluginPath) . "', 1, 1, 1, 1, 'deactivated')
    ");
}

safe_query("
    INSERT INTO settings_plugins_lang
        (content_key, language, content, modulname, updated_at)
    VALUES
        ('plugin_name_discord', 'de', 'Discord', 'discord', NOW()),
        ('plugin_name_discord', 'en', 'Discord', 'discord', NOW()),
        ('plugin_name_discord', 'it', 'Discord', 'discord', NOW()),
        ('plugin_info_discord', 'de', 'Discord-Community-Integration mit Seite, Sidebar-Widget und Admin-Konfiguration.', 'discord', NOW()),
        ('plugin_info_discord', 'en', 'Discord community integration with page, sidebar widget, and admin configuration.', 'discord', NOW()),
        ('plugin_info_discord', 'it', 'Integrazione della community Discord con pagina, widget sidebar e configurazione admin.', 'discord', NOW())
    ON DUPLICATE KEY UPDATE
        content = VALUES(content),
        modulname = VALUES(modulname),
        updated_at = VALUES(updated_at)
");

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

## NAVIGATION ###################################################################

$linkID = 0;
$linkRes = safe_query("
    SELECT linkID FROM navigation_dashboard_links
    WHERE modulname = 'discord'
    ORDER BY linkID ASC LIMIT 1
");
if ($linkRes && ($linkRow = mysqli_fetch_assoc($linkRes))) {
    $linkID = (int)($linkRow['linkID'] ?? 0);
    safe_query("
        UPDATE navigation_dashboard_links SET
            catID = 11,
            url = 'admincenter.php?site=admin_discord',
            sort = 1
        WHERE linkID = " . $linkID . "
    ");
} else {
    safe_query("
        INSERT INTO navigation_dashboard_links
            (catID, modulname, url, sort)
        VALUES
            (11, 'discord', 'admincenter.php?site=admin_discord', 1)
    ");
    $linkID = (int)mysqli_insert_id($_database);
}

if ($linkID > 0) {
    safe_query("
        INSERT INTO navigation_dashboard_lang
            (content_key, language, content, modulname, updated_at)
        VALUES
            ('nav_link_{$linkID}', 'de', 'Discord', 'discord', NOW()),
            ('nav_link_{$linkID}', 'en', 'Discord', 'discord', NOW()),
            ('nav_link_{$linkID}', 'it', 'Discord', 'discord', NOW())
        ON DUPLICATE KEY UPDATE
            content = VALUES(content),
            modulname = VALUES(modulname),
            updated_at = VALUES(updated_at)
    ");
}

$snavID = 0;
$snavRes = safe_query("
    SELECT snavID FROM navigation_website_sub
    WHERE modulname = 'discord'
    ORDER BY snavID ASC LIMIT 1
");
if ($snavRes && ($snavRow = mysqli_fetch_assoc($snavRes))) {
    $snavID = (int)($snavRow['snavID'] ?? 0);
    safe_query("
        UPDATE navigation_website_sub SET
            mnavID = 6,
            url = 'index.php?site=discord',
            sort = 1,
            indropdown = 1,
            last_modified = NOW()
        WHERE snavID = " . $snavID . "
    ");
} else {
    safe_query("
        INSERT INTO navigation_website_sub
            (mnavID, modulname, url, sort, indropdown, last_modified)
        VALUES
            (6, 'discord', 'index.php?site=discord', 1, 1, NOW())
    ");
    $snavID = (int)mysqli_insert_id($_database);
}

if ($snavID > 0) {
    safe_query("
        INSERT INTO navigation_website_lang
            (content_key, language, content, modulname, updated_at)
        VALUES
            ('nav_sub_{$snavID}', 'de', 'Discord', 'discord', NOW()),
            ('nav_sub_{$snavID}', 'en', 'Discord', 'discord', NOW()),
            ('nav_sub_{$snavID}', 'it', 'Discord', 'discord', NOW())
        ON DUPLICATE KEY UPDATE
            content = VALUES(content),
            modulname = VALUES(modulname),
            updated_at = VALUES(updated_at)
    ");
}

safe_query("
    INSERT IGNORE INTO user_role_admin_navi_rights
        (id, roleID, type, modulname)
    VALUES
        ('', 1, 'link', 'discord')
");

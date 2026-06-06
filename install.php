<?php

safe_query("CREATE TABLE IF NOT EXISTS plugins_discord (
  name VARCHAR(100) NOT NULL,
  value TEXT,
  PRIMARY KEY (name)
)");


## SYSTEM #####################################################################################################################################

safe_query("
    INSERT IGNORE INTO settings_plugins
        (pluginID, modulname, admin_file, activate, author, website, index_link, hiddenfiles, version, path, status_display, plugin_display, widget_display, delete_display, sidebar)
    VALUES
        ('', 'discord', 'admin_discord', 1, 'T-Seven', 'https://www.nexpell.de', 'discord', '', '0.1', 'includes/plugins/discord/', 1, 1, 1, 1, 'deactivated');
");

safe_query("
    INSERT IGNORE INTO settings_plugins_lang 
        (content_key, language, content, updated_at)
    VALUES
        ('plugin_name_discord', 'de', 'Discord', NOW()),
        ('plugin_name_discord', 'en', 'Discord', NOW()),
        ('plugin_name_discord', 'it', 'Discord', NOW()),

        ('plugin_info_discord', 'de', 'Dieses Widget zeigt die Entwicklungsgeschichte und wichtige Meilensteine von nexpell auf Ihrer Webseite an.', NOW()),
        ('plugin_info_discord', 'en', 'This widget displays the development history and key milestones of nexpell on your website.', NOW()),
        ('plugin_info_discord', 'it', 'Questo widget mostra la storia dello sviluppo e le tappe fondamentali di nexpell sul tuo sito web.', NOW())
");

safe_query("INSERT IGNORE INTO settings_widgets (widget_key, title, plugin, modulname) VALUES
('widget_discord_sidebar', 'Discord Widget Sidebar', 'discord', 'discord')");

## NAVIGATION #####################################################################################################################################

safe_query("
    INSERT IGNORE INTO navigation_dashboard_links
        (catID, modulname, url, sort)
    VALUES
        (11, 'discord', 'admincenter.php?site=admin_discord', 1)
");
$linkID = mysqli_insert_id($_database);

safe_query("
    INSERT IGNORE INTO navigation_dashboard_lang
        (content_key, language, content, updated_at)
    VALUES
        ('nav_link_{$linkID}', 'de', 'Discord', NOW()),
        ('nav_link_{$linkID}', 'en', 'Discord', NOW()),
        ('nav_link_{$linkID}', 'it', 'Discord', NOW())
");

safe_query("
    INSERT IGNORE INTO navigation_website_sub
        (mnavID, modulname, url, sort, indropdown, last_modified)
    VALUES
        (6, 'discord', 'index.php?site=discord', 1, 1, NOW())
");

$snavID = mysqli_insert_id($_database);

safe_query("
    INSERT IGNORE INTO navigation_website_lang
        (content_key, language, content, updated_at)
    VALUES
        ('nav_sub_{$snavID}', 'de', 'Discord', NOW()),
        ('nav_sub_{$snavID}', 'en', 'Discord', NOW()),
        ('nav_sub_{$snavID}', 'it', 'Discord', NOW())
");

#######################################################################################################################################
safe_query("
  INSERT IGNORE INTO user_role_admin_navi_rights (id, roleID, type, modulname)
  VALUES ('', 1, 'link', 'discord')
");
 ?>

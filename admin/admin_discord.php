<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use nexpell\LanguageService;
use nexpell\AccessControl;
use nexpell\NavigationUpdater;// SEO Anpassung

// LanguageService initialisieren
global $_database, $languageService;

// Admin-Zugriff prüfen
//AccessControl::checkAdminAccess('discord');

function setPluginConfig($key, $value) {
    $check = safe_query("SELECT name FROM plugins_discord WHERE name = '" . escape($key) . "'");
    if (mysqli_num_rows($check)) {
        safe_query("UPDATE plugins_discord SET value = '" . escape($value) . "' WHERE name = '" . escape($key) . "'");
    } else {
        safe_query("INSERT INTO plugins_discord (name, value) VALUES ('" . escape($key) . "', '" . escape($value) . "')");
    }
        $admin_file = basename(__FILE__, '.php');
        echo NavigationUpdater::updateFromAdminFile($admin_file);
}

function getPluginConfig($key) {
    $res = safe_query("SELECT value FROM plugins_discord WHERE name = '" . escape($key) . "'");
    if (mysqli_num_rows($res)) {
        $row = mysqli_fetch_assoc($res);
        return $row['value'];
    }
    return '';
}

// Speichern
if (isset($_POST['save'])) {
    $serverID = trim($_POST['discord_server_id']);
    setPluginConfig('server_id', $serverID);
    nx_audit_update('admin_discord', null, true, null, 'admincenter.php?site=admin_discord');
    nx_alert('success', 'alert_saved', false);
}

// Aktuelle ID laden
$serverID = getPluginConfig('server_id');
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-discord"></i> <span>Discord Widget</span>
        </div>
    </div>

    <div class="card-body">
        <form method="post">
            <div class="form-group mb-2">
                <label for="discord_server_id"><?= $languageService->get('label_server_id') ?></label>
                <input
                    type="text"
                    name="discord_server_id"
                    id="discord_server_id"
                    class="form-control"
                    value="<?= htmlspecialchars($serverID) ?>"
                    required
                >
            </div>

            <!-- Info-Block -->
            <div class="alert alert-info mt-2">
                <?= $languageService->get('info_server_id') ?>
            </div>

            <button type="submit" name="save" class="btn btn-primary mt-2">
                <?= $languageService->get('save') ?>
            </button>
        </form>
    </div>
</div>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use nexpell\LanguageService;
use nexpell\RoleManager;

global $_database, $languageService;

$lang = $languageService->detectLanguage();
$languageService->readPluginModule('discord');

$tpl = new Template();
$config = mysqli_fetch_array(safe_query("SELECT selected_style FROM settings_headstyle_config WHERE id=1"));
$class = htmlspecialchars($config['selected_style']);

    // Header-Daten
  $data_array = [
    'class'    => $class,
    'title' => $languageService->get('title'),
    'subtitle' => 'Discord'
  ];

echo $tpl->loadTemplate("discord", "head", $data_array, 'plugin');

if (!function_exists('discord_text')) {
    function discord_text($languageService, string $key, string $fallback): string
    {
        $value = $languageService->get($key);
        if (!is_string($value) || $value === '' || $value === '[' . $key . ']') {
            return $fallback;
        }
        return $value;
    }
}

if (!function_exists('getPluginConfig')) {
    function getPluginConfig($key) {
        $res = safe_query("SELECT value FROM plugins_discord WHERE name = '" . escape($key) . "'");
        if (mysqli_num_rows($res)) {
            $row = mysqli_fetch_assoc($res);
            return $row['value'];
        }
        return '';
    }
}

$userID = $_SESSION['userID'] ?? null;
$roles = [];
$isAdmin = false;

if ($userID !== null) {
    $roleIDs = RoleManager::getUserRoleIDs($userID);

    if (!empty($roleIDs)) {
        global $_database;

        $in = implode(',', array_fill(0, count($roleIDs), '?'));
        $types = str_repeat('i', count($roleIDs));

        $stmt = $_database->prepare("SELECT role_name FROM user_roles WHERE roleID IN ($in)");
        $stmt->bind_param($types, ...$roleIDs);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $roles[] = $row['role_name'];
        }
    }

    $isAdmin = in_array('Admin', $roles, true);
}

$serverID = getPluginConfig('server_id');
$discordDeclined = (isset($_COOKIE['nexpell_consent_discord']) && $_COOKIE['nexpell_consent_discord'] === 'declined');
?>

<?php if (!empty($serverID)): ?>
  <div class="discord-embed-shell" id="discord-sidebar-card" style="display:none;">
    <div class="card border border-primary rounded shadow mb-4 widget-discord discord-panel">
      <div class="card-header discord-header discord-panel-header">
        <h3 class="card-title mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-discord"></i>
          <span>nexpell CMS - Das modulare CMS</span>
        </h3>
      </div>
      <div class="card-body p-0" id="discord-sidebar-widget"></div>
    </div>
  </div>
  <div id="fallback-discord-sidebar" class="alert alert-info text-center mt-3"<?= $discordDeclined ? '' : ' style="display:none;"' ?>>
    <?= htmlspecialchars((string)$languageService->get('cookie_accept_discord'), ENT_QUOTES, 'UTF-8') ?>
  </div>

<?php else: ?>
  <div class="container my-4 discord-fallback">
    <div class="alert alert-warning text-center" role="alert">
      <?= $languageService->get('discord_not_available') ?><br>
      <?php if ($isAdmin): ?>
        <?= $languageService->get('admin_discord_server_id') ?>
      <?php else: ?>
        <?= $languageService->get('discord_no_connection') ?>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<script>
  const DISCORD_SIDEBAR_CONFIG = {
    serverID: "<?= htmlspecialchars($serverID) ?>",
    texts: {
      online: "<?= htmlspecialchars(discord_text($languageService, 'online', 'Online'), ENT_QUOTES, 'UTF-8') ?>",
      members: "<?= htmlspecialchars(discord_text($languageService, 'members', 'Mitglieder'), ENT_QUOTES, 'UTF-8') ?>",
      voiceChannels: "<?= htmlspecialchars(discord_text($languageService, 'voice_channels', 'Sprachkanäle'), ENT_QUOTES, 'UTF-8') ?>",
      onlineMembers: "<?= htmlspecialchars(discord_text($languageService, 'online_members', 'Mitglieder online'), ENT_QUOTES, 'UTF-8') ?>",
      join: "<?= htmlspecialchars(discord_text($languageService, 'join_discord', 'Discord beitreten'), ENT_QUOTES, 'UTF-8') ?>",
      unavailable: "<?= htmlspecialchars(discord_text($languageService, 'discord_not_available', 'Discord derzeit nicht verfügbar'), ENT_QUOTES, 'UTF-8') ?>"
    }
  };
  window.NX_REQUIRE_DISCORD_CONSENT = true;

  (function () {
    function getCookie(name) {
      const cookies = document.cookie ? document.cookie.split('; ') : [];
      for (let i = 0; i < cookies.length; i++) {
        const parts = cookies[i].split('=');
        const key = parts.shift();
        if (key === name) {
          return decodeURIComponent(parts.join('='));
        }
      }
      return '';
    }

    function escapeHtml(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function collectChannelMembers(channels) {
      if (!Array.isArray(channels)) {
        return [];
      }

      const seen = new Set();
      const result = [];

      channels.forEach(function (channel) {
        const members = Array.isArray(channel && channel.members) ? channel.members : [];
        members.forEach(function (member) {
          const key = String(member && (member.id || member.username || member.nick || ''));
          if (key !== '' && seen.has(key)) {
            return;
          }
          if (key !== '') {
            seen.add(key);
          }
          result.push(member);
        });
      });

      return result;
    }

    function resolveVisibleMembers(channels, members) {
      const channelMembers = collectChannelMembers(channels);
      if (channelMembers.length > 0) {
        return channelMembers;
      }
      return Array.isArray(members) ? members : [];
    }

    function buildMemberRows(members) {
      if (!Array.isArray(members) || members.length === 0) {
        return '';
      }

      return members.slice(0, 8).map(function (member) {
        const avatar = member.avatar_url
          ? '<img src="' + escapeHtml(member.avatar_url) + '" alt="" class="discord-member-avatar">'
          : '<span class="discord-member-avatar discord-member-avatar-fallback"><i class="bi bi-person-fill"></i></span>';

        return ''
          + '<div class="discord-member">'
          +   avatar
          +   '<span class="discord-member-name">' + escapeHtml(member.username || member.nick || 'Discord') + '</span>'
          + '</div>';
      }).join('');
    }

    function buildChannelRows(channels) {
      if (!Array.isArray(channels) || channels.length === 0) {
        return '<div class="discord-empty-state">' + escapeHtml(DISCORD_SIDEBAR_CONFIG.texts.unavailable) + '</div>';
      }

      return channels.map(function (channel) {
        return ''
          + '<div class="discord-channel">'
          +   '<div class="discord-channel-name">'
          +     '<i class="bi bi-volume-up-fill"></i>'
          +     '<span>' + escapeHtml(channel.name) + '</span>'
          +   '</div>'
          + '</div>';
      }).join('');
    }

    function renderWidget(data) {
      const card = document.getElementById('discord-sidebar-card');
      const widget = document.getElementById('discord-sidebar-widget');
      if (!card || !widget || !data) return;

      const channels = Array.isArray(data.channels)
        ? data.channels.filter(function (channel) {
            return channel && typeof channel.name === 'string' && channel.name.trim() !== '';
          })
        : [];
      const members = Array.isArray(data.members) ? data.members : [];
      const onlineMembers = resolveVisibleMembers(channels, members);
      const inviteUrl = data.instant_invite || ('https://discord.com/channels/' + encodeURIComponent(DISCORD_SIDEBAR_CONFIG.serverID));

      widget.innerHTML = ''
        + '<div class="discord-server-info">'
        +   '<div class="discord-server-icon discord-server-icon-fallback">'
        +     '<i class="bi bi-discord"></i>'
        +   '</div>'
        +   '<div class="discord-server-details">'
        +     '<h3 class="discord-server-name">' + escapeHtml(data.name || 'Discord') + '</h3>'
        +     '<div class="discord-stats">'
        +       '<span class="discord-stat online"><span class="status-dot online"></span>' + escapeHtml(data.presence_count || onlineMembers.length || 0) + ' ' + escapeHtml(DISCORD_SIDEBAR_CONFIG.texts.online) + '</span>'
        +       '<span class="discord-stat members"><span class="status-dot offline"></span>' + escapeHtml(members.length) + ' ' + escapeHtml(DISCORD_SIDEBAR_CONFIG.texts.members) + '</span>'
        +     '</div>'
        +   '</div>'
        + '</div>'
        + '<div class="discord-voice-channels">'
        +   '<div class="discord-section-title"><i class="bi bi-headphones me-1"></i>' + escapeHtml(DISCORD_SIDEBAR_CONFIG.texts.voiceChannels) + '</div>'
        +   buildChannelRows(channels)
        +   '<div class="discord-section-title discord-members-title"><i class="bi bi-people-fill me-1"></i>' + escapeHtml(DISCORD_SIDEBAR_CONFIG.texts.onlineMembers) + '</div>'
        +   '<div class="discord-members-list">' + buildMemberRows(onlineMembers) + '</div>'
        + '</div>'
        + '<div class="card-footer discord-footer">'
        +   '<a href="' + escapeHtml(inviteUrl) + '" target="_blank" rel="noopener noreferrer" class="btn btn-discord w-100" aria-label="' + escapeHtml(DISCORD_SIDEBAR_CONFIG.texts.join) + ' - ' + escapeHtml(data.name || 'Discord') + '">'
        +     '<i class="bi bi-discord me-2"></i>'
        +     escapeHtml(DISCORD_SIDEBAR_CONFIG.texts.join)
        +   '</a>'
        + '</div>';

      card.style.display = 'block';
    }

    function showUnavailable() {
      const card = document.getElementById('discord-sidebar-card');
      const widget = document.getElementById('discord-sidebar-widget');
      if (widget) {
        widget.innerHTML = '<div class="discord-empty-state">' + escapeHtml(DISCORD_SIDEBAR_CONFIG.texts.unavailable) + '</div>';
      }
      if (card) {
        card.style.display = 'block';
      }
    }

    function loadDiscordWidget(serverID) {
      fetch('https://discord.com/api/guilds/' + encodeURIComponent(serverID) + '/widget.json?v=' + Date.now(), {
        method: 'GET',
        headers: {
          'Accept': 'application/json'
        }
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('discord-widget-fetch-failed');
          }
          return response.json();
        })
        .then(function (data) {
          renderWidget(data);
        })
        .catch(function () {
          showUnavailable();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
      const consent = getCookie('nexpell_consent_discord');

      if (consent === 'accepted' && DISCORD_SIDEBAR_CONFIG.serverID) {
        loadDiscordWidget(DISCORD_SIDEBAR_CONFIG.serverID);
      } else if (consent === 'declined') {
        const card = document.getElementById('discord-sidebar-card');
        const fallback = document.getElementById('fallback-discord-sidebar');
        if (card) card.style.display = 'none';
        if (fallback) fallback.style.display = 'block';
      }
    });
  })();
</script>

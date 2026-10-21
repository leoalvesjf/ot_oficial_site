<?php
defined('MYAAC') or die('Direct access not allowed!');

use MyAAC\Models\ServerRecord;

$menus = get_template_menus();
$serverName = escapeHtml($config['lua']['serverName']);
$isHome = (PAGE === 'news');

// categoria ativa do menu, pela primeira parte da URL
$uriParts = explode('/', preg_replace('/^index\.php\/?|\?.*$/', '', URI));
$activeCategory = null;
foreach ($menus as $category => $links) {
	foreach ($links as $link) {
		if (explode('/', $link['link'])[0] === ($uriParts[0] ?: 'news')) {
			$activeCategory = $category;
			break 2;
		}
	}
}

$record = null;
if ($db->hasTable('server_record')) {
	$record = ServerRecord::max('record');
}

$topPlayers = getTopPlayers(5);

$serverInfo = array_filter([
	'Versão' => $config['server_version'] ?? '',
	'Exp rate' => $config['server_exp_rate'] ?? '',
	'Skill rate' => $config['server_skill_rate'] ?? '',
	'Magic rate' => $config['server_magic_rate'] ?? '',
	'Tipo' => $config['server_pvp_type'] ?? '',
]);

$downloadLink = !empty($config['download_link']) ? $config['download_link'] : getLink('downloads');

// textos editáveis no config.ini ({servidor} vira o nome do servidor)
$text = function (string $key, string $default) use ($config, $serverName): string {
	$value = isset($config[$key]) && $config[$key] !== '' ? escapeHtml($config[$key]) : $default;
	return str_replace('{servidor}', $serverName, $value);
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<?php echo template_place_holder('head_start'); ?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;900&family=Inter:wght@400;500;600&family=Pixelify+Sans:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo $template_path; ?>/style.css" type="text/css" />
	<script type="text/javascript" src="tools/basic.js"></script>
	<?php echo template_place_holder('head_end'); ?>
</head>
<body class="<?php echo $isHome ? 'is-home' : 'is-inner'; ?>">
<?php echo template_place_holder('body_start'); ?>

<header class="hero<?php echo $isHome ? '' : ' hero--compact'; ?>">
	<nav class="nav" aria-label="Principal">
		<a class="brand" href="<?php echo getLink('news'); ?>"><?php echo $serverName; ?></a>

		<input type="checkbox" id="nav-toggle" class="nav-toggle" />
		<label for="nav-toggle" class="nav-burger" aria-label="Abrir menu"><span></span></label>

		<ul class="nav-menu">
			<?php foreach ($config['menu_categories'] as $id => $cat):
				if (!isset($menus[$id]) || ($id == MENU_CATEGORY_SHOP && !setting('core.gifts_system'))) continue; ?>
			<li class="nav-item<?php echo $id === $activeCategory ? ' is-active' : ''; ?>">
				<a class="nav-link" href="<?php echo $menus[$id][0]['link_full']; ?>"><?php echo $cat['name']; ?></a>
				<ul class="nav-dropdown">
					<?php foreach ($menus[$id] as $link): ?>
					<li><a href="<?php echo $link['link_full']; ?>"<?php echo $link['target_blank'] . ' ' . $link['style_color']; ?>><?php echo $link['name']; ?></a></li>
					<?php endforeach; ?>
				</ul>
			</li>
			<?php endforeach; ?>
		</ul>

		<div class="nav-actions">
			<?php if ($logged): ?>
			<a class="btn btn-ghost btn-sm" href="<?php echo getLink('account/logout'); ?>">Sair</a>
			<a class="btn btn-primary btn-sm" href="<?php echo getLink('account/manage'); ?>">Minha conta</a>
			<?php else: ?>
			<a class="btn btn-ghost btn-sm" href="<?php echo getLink('account/manage'); ?>">Entrar</a>
			<a class="btn btn-primary btn-sm" href="<?php echo getLink('account/create'); ?>">Criar conta</a>
			<?php endif; ?>
		</div>
	</nav>

	<?php if ($isHome): ?>
	<div class="hero-body">
		<?php if ($status['online']): ?>
		<div class="pill pill--online"><span class="dot"></span>Servidor online · <?php echo $status['players']; ?> jogadores</div>
		<?php else: ?>
		<div class="pill pill--offline"><span class="dot"></span>Servidor offline</div>
		<?php endif; ?>
		<h1 class="hero-title"><?php echo $serverName; ?></h1>
		<?php if (!empty($config['hero_tagline'])): ?>
		<p class="hero-tagline"><?php echo $text('hero_tagline', ''); ?></p>
		<?php endif; ?>
		<div class="hero-actions">
			<a class="btn btn-primary btn-lg" href="<?php echo $downloadLink; ?>"><?php echo $text('hero_button_download', 'Baixar launcher'); ?></a>
			<a class="btn btn-ghost btn-lg" href="<?php echo getLink('account/create'); ?>"><?php echo $text('hero_button_account', 'Criar conta'); ?></a>
		</div>
	</div>
	<?php else: ?>
	<div class="hero-body hero-body--compact">
		<h1 class="page-title"><?php echo $title; ?></h1>
	</div>
	<?php endif; ?>
</header>

<section class="status" aria-label="Status do servidor">
	<div class="status-item">
		<div class="label">Status</div>
		<div class="value <?php echo $status['online'] ? 'is-online' : 'is-offline'; ?>"><?php echo $status['online'] ? 'Online' : 'Offline'; ?></div>
	</div>
	<div class="status-item">
		<div class="label">Jogadores</div>
		<div class="value"><?php echo $status['players'] . ' / ' . $status['playersMax']; ?></div>
	</div>
	<div class="status-item">
		<div class="label">Recorde</div>
		<div class="value"><?php echo $record ?? '—'; ?></div>
	</div>
	<div class="status-item">
		<div class="label">Uptime</div>
		<div class="value"><?php echo $status['online'] ? ($status['uptimeReadable'] ?? '—') : '—'; ?></div>
	</div>
</section>

<main class="layout">
	<section class="main-content">
		<?php if ($isHome): ?>
		<div class="section-head">
			<h2><?php echo $text('news_title', 'Últimas notícias'); ?></h2>
			<a href="<?php echo getLink('news/archive'); ?>">Arquivo de notícias →</a>
		</div>
		<?php endif; ?>
		<div class="content-box<?php echo $isHome ? ' content-box--news' : ''; ?>">
			<?php echo tickers() . template_place_holder('center_top') . $content; ?>
		</div>
	</section>

	<aside class="sidebar">
		<section class="card">
			<h2 class="card-title">Minha conta</h2>
			<?php if ($logged): ?>
			<p class="muted">Bem-vindo de volta!</p>
			<a class="btn btn-primary btn-block" href="<?php echo getLink('account/manage'); ?>">Gerenciar conta</a>
			<a class="btn btn-ghost btn-block" href="<?php echo getLink('account/logout'); ?>">Sair</a>
			<?php else: ?>
			<form class="login-form" action="<?php echo getLink('account/manage'); ?>" method="post">
				<?php echo csrf(true); ?>
				<label>Conta
					<input type="text" name="account_login" autocomplete="username" />
				</label>
				<label>Senha
					<input type="password" name="password_login" autocomplete="current-password" />
				</label>
				<button class="btn btn-primary btn-block" type="submit">Entrar</button>
				<div class="login-links">
					<a href="<?php echo getLink('account/create'); ?>">Criar conta</a>
					<a class="muted" href="<?php echo getLink('account/lost'); ?>">Perdeu a conta?</a>
				</div>
			</form>
			<?php endif; ?>
		</section>

		<?php if (!empty($topPlayers)): ?>
		<section class="card">
			<div class="card-head">
				<h2 class="card-title">Top 5 level</h2>
				<a href="<?php echo getLink('highscores'); ?>">Ver todos</a>
			</div>
			<ol class="top-list">
				<?php foreach ($topPlayers as $player): ?>
				<li>
					<span class="rank"><?php echo $player['rank']; ?></span>
					<a class="name" href="<?php echo getLink('characters/' . urlencode($player['name'])); ?>"><?php echo escapeHtml($player['name']); ?></a>
					<span class="lvl">Lv <?php echo $player['level']; ?></span>
				</li>
				<?php endforeach; ?>
			</ol>
		</section>
		<?php endif; ?>

		<?php if (!empty($serverInfo)): ?>
		<section class="card">
			<h2 class="card-title">Servidor</h2>
			<dl class="info-list">
				<?php foreach ($serverInfo as $label => $value): ?>
				<div><dt><?php echo $label; ?></dt><dd><?php echo escapeHtml($value); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		</section>
		<?php endif; ?>
	</aside>
</main>

<section id="download" class="band">
	<div class="band-inner">
		<div class="eyebrow"><?php echo $text('band_eyebrow', 'Launcher oficial'); ?></div>
		<h2><?php echo $text('band_title', 'Sua aventura começa aqui'); ?></h2>
		<p><?php echo $text('band_text', 'Baixe o launcher, crie sua conta e entre no {servidor}.'); ?></p>
		<div class="hero-actions">
			<a class="btn btn-primary btn-lg" href="<?php echo $downloadLink; ?>"><?php echo $text('band_button_download', 'Baixar para Windows'); ?></a>
			<a class="btn btn-ghost btn-lg" href="<?php echo getLink('rules'); ?>"><?php echo $text('band_button_rules', 'Regras do servidor'); ?></a>
		</div>
	</div>
</section>

<footer class="footer">
	<div class="brand"><?php echo $serverName; ?></div>
	<nav class="footer-links" aria-label="Rodapé">
		<a href="<?php echo getLink('rules'); ?>">Regras</a>
		<a href="<?php echo getLink('faq'); ?>">FAQ</a>
		<a href="<?php echo getLink('team'); ?>">Equipe</a>
		<?php if (!empty($config['discord_link'])): ?>
		<a href="<?php echo escapeHtml($config['discord_link']); ?>" target="_blank" rel="noopener">Discord</a>
		<?php endif; ?>
	</nav>
	<div class="footer-meta"><?php echo template_footer(); ?></div>
	<?php if (setting('core.template_allow_change')): ?>
	<div class="footer-template"><?php echo template_form(); ?></div>
	<?php endif; ?>
</footer>

<?php echo template_place_holder('body_end'); ?>
</body>
</html>

<?php
require_once __DIR__ . '/includes/functions.php';
$data = load_data();

$profile = $data['profile'] ?? [];
$social = $data['social'] ?? [];
$education = $data['education'] ?? [];
$experience = $data['experience'] ?? [];
$skills = $data['skills'] ?? [];
// Private repos are for the admin's own reference only — never surfaced publicly.
$projects = array_values(array_filter($data['projects'] ?? [], fn ($p) => empty($p['private'])));
$gaming = $data['gaming'] ?? [];
$hobbies = $data['hobbies'] ?? [];
$travel = $data['travel'] ?? [];

$skillProgress = $data['skillProgress'] ?? [];

$projectCount = count($projects);
$visitedCount = count($travel['visited'] ?? []);
$visitedJson = json_encode($travel['visited'] ?? []);

$startYear = date('Y');
if (!empty($experience[0]['period']) && preg_match('/\d{4}/', $experience[0]['period'], $m)) {
    $startYear = (int) $m[0];
}
$yearsExperience = max(1, (int) date('Y') - $startYear);

// The 3D explore-the-world game is built and working, just switched off for
// now per request — flip this back to true (and restore the nav link below)
// to bring it back. Nothing about the feature has been deleted.
$worldEnabled = false;

// Stations for the walkable 3D world — each one summarizes a real section
// on the page using the same data, so nothing here is invented.
$worldStations = [
    [
        'id' => 'about', 'label' => 'About Me', 'color' => '#8B5CF6', 'link' => '#about',
        'summary' => $profile['about'] ?? '',
    ],
    [
        'id' => 'experience', 'label' => 'Experience', 'color' => '#22D3EE', 'link' => '#experience',
        'summary' => !empty($experience[0])
            ? trim(($experience[0]['role'] ?? '') . ' @ ' . ($experience[0]['company'] ?? '') . ' (' . ($experience[0]['period'] ?? '') . ')')
            : '',
    ],
    [
        'id' => 'projects', 'label' => 'Projects', 'color' => '#F43F5E', 'link' => '#projects',
        'summary' => $projectCount . '+ public projects on GitHub — mostly real-time multiplayer games built with plain PHP, MySQL, and vanilla JS.',
    ],
    [
        'id' => 'skills', 'label' => 'Skills', 'color' => '#F59E0B', 'link' => '#skills',
        'summary' => 'Strongest in: ' . implode(', ', array_slice($skills, 0, 5)) . '.',
    ],
    [
        'id' => 'gaming', 'label' => 'Gaming', 'color' => '#10B981', 'link' => '#gaming',
        'summary' => 'Plays ' . implode(', ', array_slice($gaming['games'] ?? [], 0, 5)) . ' and more.',
    ],
    [
        'id' => 'travel', 'label' => 'Travel', 'color' => '#0EA5E9', 'link' => '#travel',
        'summary' => 'Visited ' . $visitedCount . ' countries so far. ' . ($travel['intro'] ?? ''),
    ],
    [
        'id' => 'contact', 'label' => 'Contact', 'color' => '#A855F7', 'link' => '#contact',
        'summary' => 'Reach out at ' . ($profile['email'] ?? '') . ' or on GitHub.',
    ],
];

// Each entry carries its own viewBox because the icons come from different
// source sets at different native coordinate sizes (16x16 vs 24x24) — using
// one fixed viewBox for all of them crops the 24x24 ones (Threads, WhatsApp).
$socialIcons = [
    'github' => ['viewBox' => '0 0 16 16', 'path' => '<path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>'],
    'linkedin' => ['viewBox' => '0 0 16 16', 'path' => '<path d="M14.82 0H1.18C.53 0 0 .53 0 1.18v13.64C0 15.47.53 16 1.18 16h13.64c.65 0 1.18-.53 1.18-1.18V1.18C16 .53 15.47 0 14.82 0zM4.75 13.62H2.4V6.1h2.35v7.52zM3.58 5.1a1.36 1.36 0 1 1 0-2.72 1.36 1.36 0 0 1 0 2.72zm10.04 8.52h-2.35V9.96c0-.87-.02-2-1.22-2-1.22 0-1.4.95-1.4 1.93v3.73H6.3V6.1h2.26v1.03h.03c.31-.6 1.08-1.22 2.23-1.22 2.38 0 2.82 1.57 2.82 3.6v4.11z"/>'],
    'facebook' => ['viewBox' => '0 0 16 16', 'path' => '<path d="M16 8.05C16 3.6 12.42 0 8 0S0 3.6 0 8.05C0 12.06 2.93 15.4 6.75 16v-5.62H4.72V8.05h2.03V6.28c0-2.02 1.2-3.14 3.02-3.14.87 0 1.79.16 1.79.16v1.98h-1.01c-1 0-1.31.62-1.31 1.27v1.5h2.23l-.36 2.33H9.24V16C13.07 15.4 16 12.06 16 8.05z"/>'],
    'instagram' => ['viewBox' => '0 0 16 16', 'path' => '<path d="M8 1.44c2.14 0 2.39.01 3.24.05.78.03 1.2.16 1.48.27.37.14.64.32.92.6.28.28.45.54.6.92.11.28.24.7.27 1.48.04.85.05 1.1.05 3.24s-.01 2.39-.05 3.24c-.03.78-.16 1.2-.27 1.48-.14.37-.32.64-.6.92-.28.28-.55.45-.92.6-.28.11-.7.24-1.48.27-.85.04-1.1.05-3.24.05s-2.39-.01-3.24-.05c-.78-.03-1.2-.16-1.48-.27a2.47 2.47 0 0 1-.92-.6 2.47 2.47 0 0 1-.6-.92c-.11-.28-.24-.7-.27-1.48C1.45 10.39 1.44 10.14 1.44 8s.01-2.39.05-3.24c.03-.78.16-1.2.27-1.48.14-.37.32-.64.6-.92.28-.28.55-.45.92-.6.28-.11.7-.24 1.48-.27.85-.04 1.1-.05 3.24-.05M8 0C5.83 0 5.56.01 4.7.05c-.85.04-1.44.18-1.95.38-.53.2-.98.48-1.43.93-.45.45-.72.9-.93 1.43-.2.51-.34 1.1-.38 1.95C.01 5.56 0 5.83 0 8s.01 2.44.05 3.3c.04.85.18 1.44.38 1.95.2.53.48.98.93 1.43.45.45.9.72 1.43.93.51.2 1.1.34 1.95.38.86.04 1.13.05 3.3.05s2.44-.01 3.3-.05c.85-.04 1.44-.18 1.95-.38.53-.2.98-.48 1.43-.93.45-.45.72-.9.93-1.43.2-.51.34-1.1.38-1.95.04-.86.05-1.13.05-3.3s-.01-2.44-.05-3.3c-.04-.85-.18-1.44-.38-1.95a3.9 3.9 0 0 0-.93-1.43A3.9 3.9 0 0 0 11.25.43c-.51-.2-1.1-.34-1.95-.38C8.44.01 8.17 0 8 0zm0 3.9A4.1 4.1 0 1 0 8 12.1 4.1 4.1 0 0 0 8 3.9zm0 6.76a2.66 2.66 0 1 1 0-5.32 2.66 2.66 0 0 1 0 5.32zm5.23-6.92a.96.96 0 1 1-1.92 0 .96.96 0 0 1 1.92 0z"/>'],
    'x' => ['viewBox' => '0 0 16 16', 'path' => '<path d="M12.6.75h2.45l-5.36 6.13L16 15.25h-4.94l-3.87-5.06-4.43 5.06H.3l5.73-6.55L0 .75h5.06l3.5 4.63L12.6.75zm-.86 13.03h1.36L4.32 2.15H2.86l8.88 11.63z"/>'],
    'threads' => ['viewBox' => '0 0 24 24', 'path' => '<path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.586 1.472 12.01v-.017c.03-3.579.879-6.43 2.525-8.482C5.845 1.205 8.6.024 12.18 0h.014c2.746.02 5.043.725 6.826 2.098 1.677 1.29 2.858 3.13 3.509 5.467l-2.04.569c-1.104-3.96-3.898-5.984-8.304-6.015-2.91.022-5.11.936-6.54 2.717C4.307 6.504 3.616 8.914 3.589 12c.027 3.086.718 5.496 2.057 7.164 1.43 1.783 3.631 2.698 6.54 2.717 2.623-.02 4.358-.631 5.8-2.045 1.647-1.613 1.618-3.593 1.09-4.798-.31-.71-.873-1.3-1.634-1.75-.192 1.352-.622 2.446-1.284 3.272-.886 1.102-2.14 1.704-3.73 1.79-1.202.065-2.361-.218-3.259-.801-1.063-.689-1.685-1.74-1.752-2.964-.065-1.19.408-2.285 1.33-3.082.88-.76 2.119-1.207 3.583-1.291a13.853 13.853 0 0 1 3.02.142c-.126-.742-.375-1.332-.75-1.757-.513-.586-1.308-.883-2.359-.89h-.029c-.844 0-1.992.232-2.721 1.32L7.734 7.847c.98-1.454 2.568-2.256 4.478-2.256h.044c3.194.02 5.097 1.975 5.287 5.388.108.046.216.094.321.142 1.49.7 2.58 1.761 3.154 3.07.797 1.82.871 4.79-1.548 7.158-1.85 1.81-4.094 2.628-7.277 2.65Zm1.003-11.69c-.242 0-.487.007-.739.021-1.836.103-2.98.946-2.916 2.143.067 1.256 1.452 1.839 2.784 1.767 1.224-.065 2.818-.543 3.086-3.71a10.5 10.5 0 0 0-2.215-.221z"/>'],
    'whatsapp' => ['viewBox' => '0 0 24 24', 'path' => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($profile['name'] ?? 'Portfolio') ?> — <?= e($profile['title'] ?? '') ?></title>
<meta name="description" content="<?= e($profile['about'] ?? '') ?>">
<link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<script>
  // Applied before first paint so the page never flashes the wrong theme.
  (function () {
    const saved = localStorage.getItem('theme');
    const theme = saved || 'light';
    document.documentElement.setAttribute('data-theme', theme);
  })();

  tailwind.config = {
    darkMode: ['selector', '[data-theme="light"]'],
    theme: {
      extend: {
        // Pointing DEFAULT at the CSS custom properties (defined in style.css)
        // is what lets the light/dark toggle repaint bare bg-bg / text-cyan /
        // etc. classes at once. The numbered 50-950 shades are spelled out
        // (copied from Tailwind's real defaults) because setting `cyan` or
        // `purple` to a bare string, as this used to do, replaces the WHOLE
        // color object — silently breaking every bg-cyan-500, text-purple-300,
        // etc. utility anywhere else on the page (that's what was making the
        // "Forza Horizon"/"NFS"/"Security Updates"/"Movies" chips render with
        // no color at all: those specific chips land on the cyan/purple slot
        // in the palette cycle).
        colors: {
          bg: 'var(--bg)',
          bgsoft: 'var(--bg-soft)',
          cyan: { DEFAULT: 'var(--cyan)', 50: '#ecfeff', 100: '#cffafe', 200: '#a5f3fc', 300: '#67e8f9', 400: '#22d3ee', 500: '#06b6d4', 600: '#0891b2', 700: '#0e7490', 800: '#155e75', 900: '#164e63', 950: '#083344' },
          purple: { DEFAULT: 'var(--purple)', 50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff', 300: '#d8b4fe', 400: '#c084fc', 500: '#a855f7', 600: '#9333ea', 700: '#7e22ce', 800: '#6b21a8', 900: '#581c87', 950: '#3b0764' },
          violet: { DEFAULT: 'var(--violet)', 50: '#f5f3ff', 100: '#ede9fe', 200: '#ddd6fe', 300: '#c4b5fd', 400: '#a78bfa', 500: '#8b5cf6', 600: '#7c3aed', 700: '#6d28d9', 800: '#5b21b6', 900: '#4c1d95', 950: '#2e1065' },
          blue: { DEFAULT: 'var(--blue)', 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a', 950: '#172554' },
        },
        fontFamily: { sans: ['Inter', 'sans-serif'] },
      },
    },
  };
</script>
</head>
<body class="bg-bg text-gray-200 font-sans antialiased">

<div id="scroll-progress"></div>

<!-- =============== NAV =============== -->
<header class="fixed top-0 inset-x-0 z-50 backdrop-blur bg-bg/70 border-b border-white/5">
  <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
    <a href="#home" class="font-extrabold text-lg gradient-text">Atikur Rahman</a>
    <nav class="hidden md:flex items-center gap-8 text-sm font-medium">
      <a href="#about" class="nav-link">About</a>
      <a href="#experience" class="nav-link">Experience</a>
      <a href="#projects" class="nav-link">Projects</a>
      <a href="#skills" class="nav-link">Skills</a>
      <a href="#gaming" class="nav-link">Gaming</a>
      <?php if ($worldEnabled): ?><a href="#world" class="nav-link">Explore</a><?php endif; ?>
      <a href="#travel" class="nav-link">Travel</a>
      <a href="#contact" class="nav-link">Contact</a>
    </nav>
    <div class="flex items-center gap-3">
      <button id="theme-toggle" type="button" aria-label="Toggle light/dark theme"
              class="w-9 h-9 inline-flex items-center justify-center rounded-full bg-white/5 border border-white/10 hover:border-purple/60 transition text-gray-300">
        <svg id="theme-icon-dark" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36l-.7-.7M6.34 6.34l-.7-.7m12.02 0l-.7.7M6.34 17.66l-.7.7M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        <svg id="theme-icon-light" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="hidden"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
      </button>
      <a href="<?= e($social['github'] ?? '#') ?>" target="_blank" rel="noopener"
         class="hidden md:inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 hover:border-purple/60 transition text-sm">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8z"/></svg>
        GitHub
      </a>
      <button id="nav-toggle" class="md:hidden text-gray-300">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>
  <div id="mobile-nav" class="hidden md:hidden px-6 pb-4 flex flex-col gap-3 text-sm">
    <a href="#about" class="nav-link">About</a>
    <a href="#experience" class="nav-link">Experience</a>
    <a href="#projects" class="nav-link">Projects</a>
    <a href="#skills" class="nav-link">Skills</a>
    <a href="#gaming" class="nav-link">Gaming</a>
    <?php if ($worldEnabled): ?><a href="#world" class="nav-link">Explore</a><?php endif; ?>
    <a href="#travel" class="nav-link">Travel</a>
    <a href="#contact" class="nav-link">Contact</a>
  </div>
</header>

<!-- =============== HERO =============== -->
<section id="home" class="relative min-h-screen flex items-center overflow-hidden pt-24">
  <div id="hero-canvas"></div>
  <div class="hero-scrim"></div>
  <div class="relative z-10 max-w-6xl mx-auto px-6 grid md:grid-cols-[1fr,auto] gap-10 items-center">
    <div class="order-1 md:order-2 rounded-full p-1.5 bg-gradient-to-br from-cyan via-purple to-violet mx-auto md:mx-0 shadow-2xl shadow-purple/30">
      <img src="<?= e($profile['photo'] ?? 'assets/img/placeholder-avatar.svg') ?>" alt="<?= e($profile['name'] ?? '') ?>"
           class="w-48 h-48 md:w-72 md:h-72 rounded-full object-cover border-4 border-bg">
    </div>
    <div class="hero-panel order-2 md:order-1 text-center md:text-left rounded-3xl border border-white/10 p-6 md:p-10">
      <p class="text-cyan font-mono mb-2">Hi, my name is</p>
      <h1 class="text-4xl md:text-6xl font-extrabold mb-2 gradient-text"><?= e($profile['name'] ?? '') ?></h1>
      <h2 class="text-xl md:text-2xl font-semibold text-white mb-6">
        <span id="typed-title"></span><span class="typed-cursor">&nbsp;</span>
      </h2>
      <p class="max-w-xl text-gray-200 mb-8"><?= e($profile['about'] ?? '') ?></p>
      <div class="flex flex-wrap justify-center md:justify-start gap-4 mb-10">
        <a href="#projects" class="px-6 py-3 rounded-full bg-gradient-to-r from-cyan to-purple font-semibold text-black hover:opacity-90 transition">View Projects</a>
        <a href="#contact" class="px-6 py-3 rounded-full border border-white/15 hover:border-cyan/60 transition">Contact Me</a>
        <a href="resume.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-white/15 hover:border-cyan/60 transition">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
          Resume
        </a>
      </div>
      <div class="grid grid-cols-3 gap-3 max-w-md mx-auto md:mx-0">
        <div class="stat-chip"><div class="num"><?= $projectCount ?>+</div><div class="lbl">Projects</div></div>
        <div class="stat-chip"><div class="num"><?= $yearsExperience ?>+</div><div class="lbl">Yrs Experience</div></div>
        <div class="stat-chip"><div class="num"><?= $visitedCount ?></div><div class="lbl">Countries</div></div>
      </div>
      <!-- In-flow on mobile so it sits under the stat chips instead of
           overlapping them; pinned to the viewport bottom on desktop where
           there's room for it below without colliding with content. -->
      <a href="#about" class="flex md:hidden justify-center mt-8 text-gray-400 hover:text-cyan transition animate-bounce">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
      </a>
    </div>
  </div>
  <a href="#about" class="hidden md:flex absolute bottom-8 left-1/2 -translate-x-1/2 z-10 text-gray-400 hover:text-cyan transition animate-bounce">
    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
  </a>
</section>

<!-- =============== ABOUT =============== -->
<section id="about" class="relative overflow-hidden py-24 border-t border-white/5 dot-grid">
  <div class="blob w-96 h-96 bg-purple-500 -top-20 -left-20"></div>
  <div class="relative max-w-6xl mx-auto px-6">
    <p class="eyebrow">01 · Get to know me</p>
    <h2 class="text-3xl font-bold mb-2">About <span class="gradient-text">Me</span></h2>
    <p class="text-gray-400 mb-12 max-w-2xl">A quick look at who I am, what I've studied, and how I got here.</p>

    <div class="grid md:grid-cols-2 gap-10">
      <div class="card p-8 fade-up">
        <p class="text-gray-300 leading-relaxed mb-8"><?= e($profile['about'] ?? '') ?></p>
        <div class="flex flex-wrap gap-3 text-sm">
          <a href="tel:<?= e($profile['phone'] ?? '') ?>" class="contact-card contact-card--call group relative overflow-hidden rounded-xl bg-white/5 border border-white/10 p-3">
            <div class="flex items-center gap-2.5">
              <span class="contact-card__icon flex-none w-8 h-8 rounded-full bg-emerald-500/15 text-emerald-400 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 group-hover:rotate-[-8deg]">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
              </span>
              <div class="min-w-0">
                <p class="text-gray-500 text-[10px] uppercase tracking-wide leading-tight">Phone</p>
                <p class="text-gray-200 font-medium leading-tight whitespace-nowrap"><?= e($profile['phone'] ?? '') ?></p>
              </div>
            </div>
            <p class="contact-card__hint text-emerald-400 text-xs font-medium">Tap to call →</p>
          </a>
          <a href="mailto:<?= e($profile['email'] ?? '') ?>" class="contact-card contact-card--mail group relative overflow-hidden rounded-xl bg-white/5 border border-white/10 p-3">
            <div class="flex items-center gap-2.5">
              <span class="contact-card__icon flex-none w-8 h-8 rounded-full bg-sky-500/15 text-sky-400 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 group-hover:translate-y-[-2px]">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 6l-10 7L2 6"/></svg>
              </span>
              <div class="min-w-0">
                <p class="text-gray-500 text-[10px] uppercase tracking-wide leading-tight">Email</p>
                <p class="text-gray-200 font-medium leading-tight whitespace-nowrap"><?= e($profile['email'] ?? '') ?></p>
              </div>
            </div>
            <p class="contact-card__hint text-sky-400 text-xs font-medium">Tap to email →</p>
          </a>
        </div>
      </div>

      <div class="card p-8 fade-up">
        <h3 class="text-lg font-bold mb-6 text-purple">Education</h3>
        <ul class="space-y-6">
          <?php foreach ($education as $edu): ?>
          <li class="border-l-2 border-purple/40 pl-4">
            <p class="font-semibold text-gray-100"><?= e($edu['degree'] ?? '') ?></p>
            <p class="text-sm text-gray-400">
              <?php if (!empty($edu['url'])): ?>
              <a href="<?= e($edu['url']) ?>" target="_blank" rel="noopener" class="hover:text-cyan transition underline decoration-dotted"><?= e($edu['school'] ?? '') ?></a>
              <?php else: ?>
              <?= e($edu['school'] ?? '') ?>
              <?php endif; ?>
            </p>
            <p class="text-xs text-gray-500"><?= e($edu['period'] ?? '') ?><?= !empty($edu['grade']) ? ' · ' . e($edu['grade']) : '' ?></p>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- =============== SKILL PROGRESS =============== -->
<section id="skill-progress" class="py-24 border-t border-white/5 bg-bgsoft/40">
  <div class="max-w-6xl mx-auto px-6">
    <p class="eyebrow">02 · Where I'm strongest</p>
    <h2 class="text-3xl font-bold mb-2">Skill <span class="gradient-text">Expertise</span></h2>
    <p class="text-gray-400 mb-12 max-w-2xl">Self-rated proficiency across the areas I work in most.</p>

    <?php
    // Straight even split down the middle — first half of the list in
    // column one, second half in column two.
    $half = (int) ceil(count($skillProgress) / 2);
    $columns = [array_slice($skillProgress, 0, $half), array_slice($skillProgress, $half)];
    ?>
    <div class="grid md:grid-cols-2 gap-x-10 gap-y-8">
      <?php foreach ($columns as $columnItems): ?>
      <div class="space-y-6">
        <?php foreach ($columnItems as $sp): ?>
        <div class="progress-row fade-up">
          <div class="flex justify-between mb-2 text-sm">
            <span class="text-gray-200 font-medium"><?= e($sp['label'] ?? '') ?></span>
            <span class="text-cyan font-mono"><?= (int) ($sp['percent'] ?? 0) ?>%</span>
          </div>
          <div class="bar-track">
            <div class="bar-fill" data-percent="<?= (int) ($sp['percent'] ?? 0) ?>"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =============== EXPERIENCE =============== -->
<section id="experience" class="py-24 border-t border-white/5">
  <div class="max-w-6xl mx-auto px-6">
    <p class="eyebrow">03 · Career so far</p>
    <h2 class="text-3xl font-bold mb-2">Work <span class="gradient-text">Experience</span></h2>
    <p class="text-gray-400 mb-12 max-w-2xl">Where I've put my backend skills to work.</p>

    <div class="timeline space-y-10">
      <?php foreach ($experience as $exp): ?>
      <div class="timeline-item card p-8 fade-up">
        <div class="flex flex-wrap items-baseline justify-between gap-2 mb-4">
          <h3 class="text-xl font-bold text-gray-100"><?= e($exp['role'] ?? '') ?> · <span class="text-cyan"><?= e($exp['company'] ?? '') ?></span></h3>
          <span class="text-sm text-gray-500 font-mono"><?= e($exp['period'] ?? '') ?></span>
        </div>
        <ul class="list-disc list-inside space-y-2 text-gray-400">
          <?php foreach (($exp['bullets'] ?? []) as $bullet): ?>
          <li><?= e($bullet) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =============== PROJECTS =============== -->
<section id="projects" class="py-24 border-t border-white/5 bg-bgsoft/40">
  <div class="max-w-6xl mx-auto px-6">
    <p class="eyebrow">04 · What I've shipped</p>
    <h2 class="text-3xl font-bold mb-2">Projects <span class="gradient-text">(<?= $projectCount ?>+)</span></h2>
    <p class="text-gray-400 mb-8 max-w-2xl">A snapshot of what I've built and shipped, pulled straight from my GitHub.</p>

    <?php
    $categoryLabels = ['games' => 'Games', 'college' => 'College Project'];
    $presentCategories = array_values(array_unique(array_map(fn ($p) => $p['category'] ?? 'other', $projects)));
    ?>
    <div class="flex flex-wrap gap-2 mb-10" id="project-filters">
      <button type="button" class="project-filter-btn active" data-filter="all">All</button>
      <?php foreach ($presentCategories as $cat): ?>
      <button type="button" class="project-filter-btn" data-filter="<?= e($cat) ?>"><?= e($categoryLabels[$cat] ?? ucfirst($cat)) ?></button>
      <?php endforeach; ?>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" id="project-grid">
      <?php foreach ($projects as $project):
        $primaryUrl = !empty($project['website']) ? $project['website'] : ($project['url'] ?? '');
        $primaryLabel = !empty($project['website']) ? 'Visit Website →' : 'View on GitHub →';
      ?>
      <div class="card overflow-hidden flex flex-col fade-up project-card" data-category="<?= e($project['category'] ?? 'other') ?>">
        <?php if (!empty($project['image'])): ?>
        <a href="<?= e($primaryUrl) ?>" target="_blank" rel="noopener" class="block aspect-video overflow-hidden bg-black/20">
          <img src="<?= e($project['image']) ?>" alt="<?= e($project['name'] ?? '') ?> preview" class="w-full h-full object-cover object-top hover:scale-105 transition duration-300">
        </a>
        <?php else: ?>
        <div class="aspect-video flex items-center justify-center text-3xl font-black tracking-tight text-white/10" style="background:linear-gradient(135deg, <?= e(lang_color($project['language'] ?? '')) ?>33, transparent)">
          <?= e($project['name'] ?? '') ?>
        </div>
        <?php endif; ?>

        <div class="p-6 flex flex-col flex-1">
          <h3 class="font-bold text-gray-100 break-all mb-3"><?= e($project['name'] ?? '') ?></h3>
          <p class="text-sm text-gray-400 flex-1 mb-4">
            <?= !empty($project['description']) ? e($project['description']) : '<span class="text-gray-600">No description yet.</span>' ?>
          </p>
          <div class="flex items-center justify-between text-xs text-gray-500">
            <?php if (!empty($project['language'])): ?>
            <span class="inline-flex items-center gap-1.5">
              <span class="lang-dot" style="background:<?= e(lang_color($project['language'])) ?>"></span>
              <?= e($project['language']) ?>
            </span>
            <?php else: ?>
            <span></span>
            <?php endif; ?>
            <?php if (!empty($primaryUrl)): ?>
            <a href="<?= e($primaryUrl) ?>" target="_blank" rel="noopener" class="text-cyan hover:text-purple transition"><?= e($primaryLabel) ?></a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =============== SKILLS =============== -->
<section id="skills" class="relative overflow-hidden py-24 border-t border-white/5">
  <div class="blob w-96 h-96 bg-amber-500 top-0 -right-20"></div>
  <div class="blob w-72 h-72 bg-sky-500 bottom-0 left-0"></div>
  <div class="relative max-w-6xl mx-auto px-6">
    <p class="eyebrow">05 · Toolbox</p>
    <h2 class="text-3xl font-bold mb-2">Skills</h2>
    <p class="text-gray-400 mb-10 max-w-2xl">Tools and areas I work with day to day.</p>
    <div class="flex flex-wrap gap-3">
      <?php foreach ($skills as $i => $skill): ?>
      <span class="chip fade-up <?= chip_class($i) ?>"><?= e($skill) ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =============== GAMING =============== -->
<section id="gaming" class="relative overflow-hidden py-24 border-t border-white/5 bg-bgsoft/40 dot-grid">
  <div class="blob w-96 h-96 bg-rose-500 -top-10 left-1/4"></div>
  <div class="relative max-w-6xl mx-auto px-6">
    <p class="eyebrow">06 · Off the clock</p>
    <h2 class="text-3xl font-bold mb-2">Gaming <span class="gradient-text">Life</span></h2>
    <p class="text-gray-400 mb-10 max-w-2xl"><?= e($gaming['intro'] ?? '') ?></p>

    <div class="grid md:grid-cols-2 gap-10">
      <div class="card p-8 fade-up">
        <h3 class="text-lg font-bold mb-6 text-purple">What I Play</h3>
        <div class="flex flex-wrap gap-3">
          <?php foreach (($gaming['games'] ?? []) as $i => $game):
            $localIcon = local_game_icon($game);
            $icon = $localIcon ?? brand_icon_url($game);
            // Local files are full-color logos (don't invert); CDN icons are
            // flat white monochrome marks that need to flip to black in
            // light mode or they vanish against a light background.
            $isMonochrome = $localIcon === null && $icon !== null;
          ?>
          <span class="chip <?= chip_class($i + 3) ?>">
            <?php if ($icon): ?>
              <img src="<?= e($icon) ?>" alt="" width="16" height="16" class="object-contain rounded-sm <?= $isMonochrome ? 'brand-icon' : '' ?>">
            <?php else: ?>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="opacity-80"><rect x="2" y="7" width="20" height="10" rx="5"/><path stroke-linecap="round" d="M7 10v4M5 12h4M15.5 11h.01M18.5 13h.01"/></svg>
            <?php endif; ?>
            <?= e($game) ?>
          </span>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="card p-8 fade-up">
        <h3 class="text-lg font-bold mb-6 text-purple">Platforms</h3>
        <ul class="space-y-3 text-gray-400">
          <?php foreach (($gaming['platforms'] ?? []) as $platform):
            $inlineIcon = inline_brand_icon($platform['name'] ?? '');
            $picon = $inlineIcon ? null : brand_icon_url($platform['name'] ?? '');
          ?>
          <li class="flex items-center justify-between border-b border-white/5 pb-2">
            <span class="inline-flex items-center gap-2">
              <?php if ($inlineIcon): ?>
              <svg width="16" height="16" viewBox="<?= e($inlineIcon['viewBox']) ?>" fill="currentColor"><?= $inlineIcon['path'] ?></svg>
              <?php elseif ($picon): ?>
              <img src="<?= e($picon) ?>" alt="" width="16" height="16" class="opacity-90 brand-icon">
              <?php endif; ?>
              <?= e($platform['name'] ?? '') ?>
            </span>
            <span class="text-gray-500"><?= !empty($platform['tag']) ? e($platform['tag']) : 'Add tag in admin' ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<?php if ($worldEnabled): ?>
<!-- =============== 3D EXPLORABLE WORLD =============== -->
<section id="world" class="relative overflow-hidden py-24 border-t border-white/5">
  <div class="max-w-6xl mx-auto px-6">
    <p class="eyebrow">07 · Just for fun</p>
    <h2 class="text-3xl font-bold mb-2">Walk Around &amp; <span class="gradient-text">Explore</span></h2>
    <p class="text-gray-400 mb-8 max-w-2xl">
      Control a little explorer and walk up to each glowing marker to learn a bit about me — it's the same info as the
      sections above, just wandered into instead of scrolled past.
    </p>

    <div class="card p-3 md:p-4 fade-up">
      <div id="world-stage" class="relative rounded-2xl overflow-hidden bg-black" style="aspect-ratio: 16 / 10;">
        <div id="world-canvas" class="absolute inset-0" tabindex="0"></div>

        <!-- click-to-play overlay, shown until first interaction -->
        <div id="world-start" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/70 backdrop-blur-sm cursor-pointer text-center px-6">
          <div class="text-4xl">🎮</div>
          <p class="text-gray-100 font-semibold">Click to start walking</p>
          <p class="text-gray-400 text-sm">WASD / Arrow keys to move · on-screen joystick on touch devices</p>
        </div>

        <!-- station info card, populated by world3d.js on proximity -->
        <div id="world-info" class="absolute left-4 right-4 bottom-4 md:left-6 md:right-auto md:max-w-sm hidden">
          <div class="rounded-2xl border border-white/10 bg-black/80 backdrop-blur p-4 md:p-5">
            <p id="world-info-label" class="text-sm font-bold mb-1"></p>
            <p id="world-info-summary" class="text-xs text-gray-300 mb-3"></p>
            <a id="world-info-link" href="#" class="text-xs text-cyan hover:text-purple transition">Read the full section ↓</a>
          </div>
        </div>

        <!-- mobile virtual joystick -->
        <div id="world-joystick" class="absolute bottom-4 right-4 w-24 h-24 rounded-full bg-white/5 border border-white/15 md:hidden">
          <div id="world-joystick-nub" class="absolute w-10 h-10 rounded-full bg-cyan/70" style="top:28px;left:28px;"></div>
        </div>
      </div>
      <p class="text-center text-xs text-gray-500 mt-3">
        Markers: <?php foreach ($worldStations as $i => $st): ?><span style="color:<?= e($st['color']) ?>"><?= e($st['label']) ?></span><?= $i < count($worldStations) - 1 ? ' · ' : '' ?><?php endforeach; ?>
      </p>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- =============== TRAVEL =============== -->
<section id="travel" class="relative overflow-hidden py-24 border-t border-white/5">
  <div class="blob w-96 h-96 bg-sky-500 top-0 right-0"></div>
  <div class="relative max-w-6xl mx-auto px-6">
    <p class="eyebrow">07 · Beyond the desk</p>
    <h2 class="text-3xl font-bold mb-2">Hobbies &amp; <span class="gradient-text">Travel</span></h2>
    <p class="text-gray-400 mb-10 max-w-2xl"><?= e($travel['intro'] ?? '') ?></p>

    <div class="flex flex-wrap gap-3 mb-10">
      <?php foreach ($hobbies as $i => $hobby): ?>
      <span class="chip fade-up <?= chip_class($i + 5) ?>"><?= e($hobby) ?></span>
      <?php endforeach; ?>
    </div>

    <div class="card p-4 md:p-8 fade-up">
      <div id="travel-map" class="w-full h-[240px] sm:h-[320px] md:h-[420px]"></div>
      <p class="text-center text-xs text-gray-500 mt-4">
        Highlighted: countries I've actually visited. Singapore is shown as a marker pin since this map's dataset doesn't render city-states as filled regions.
      </p>
    </div>
  </div>
</section>

<!-- =============== CONTACT / SOCIAL =============== -->
<section id="contact" class="py-24 border-t border-white/5">
  <div class="max-w-6xl mx-auto px-6 text-center">
    <p class="eyebrow mx-auto">08 · Get in touch</p>
    <h2 class="text-3xl font-bold mb-2">Let's <span class="gradient-text">Connect</span></h2>
    <p class="text-gray-400 mb-10">Every platform I'm on, one tap away.</p>

    <div class="flex flex-wrap justify-center gap-4 mb-10">
      <?php
      $whatsappDigits = preg_replace('/\D/', '', $social['whatsapp'] ?? '');
      $links = [
        ['key' => 'github', 'label' => 'GitHub', 'url' => $social['github'] ?? ''],
        ['key' => 'linkedin', 'label' => 'LinkedIn', 'url' => $social['linkedin'] ?? ''],
        ['key' => 'facebook', 'label' => 'Facebook', 'url' => $social['facebook'] ?? ''],
        ['key' => 'instagram', 'label' => 'Instagram', 'url' => $social['instagram'] ?? ''],
        ['key' => 'x', 'label' => 'X (Twitter)', 'url' => $social['x'] ?? ''],
        ['key' => 'threads', 'label' => 'Threads', 'url' => $social['threads'] ?? ''],
        ['key' => 'whatsapp', 'label' => 'WhatsApp', 'url' => $whatsappDigits ? 'https://wa.me/' . $whatsappDigits : ''],
      ];
      foreach ($links as $link):
        if (empty($link['url'])) continue;
      ?>
      <?php $icon = $socialIcons[$link['key']] ?? ['viewBox' => '0 0 16 16', 'path' => '']; ?>
      <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener" title="<?= e($link['label']) ?>"
         class="social-btn">
        <svg width="18" height="18" viewBox="<?= e($icon['viewBox']) ?>" fill="currentColor"><?= $icon['path'] ?></svg>
      </a>
      <?php endforeach; ?>
    </div>

    <a href="mailto:<?= e($profile['email'] ?? '') ?>"
       class="inline-block px-8 py-3 rounded-full bg-gradient-to-r from-cyan to-purple font-semibold text-black hover:opacity-90 transition">
      Email Me
    </a>

    <?php if (!empty($social['wechat'])): ?>
    <p class="text-gray-500 text-xs mt-6">
      Also on WeChat: <span class="text-gray-300 font-medium"><?= e($social['wechat']) ?></span>
      <span class="block text-gray-600 mt-0.5">(WeChat doesn't support direct profile links — search this ID in-app)</span>
    </p>
    <?php endif; ?>
  </div>
</section>

<footer class="py-10 text-center text-gray-600 text-sm border-t border-white/5">
  <p>&copy; <?= date('Y') ?> <?= e($profile['name'] ?? '') ?></p>
</footer>

<script>
  window.VISITED_COUNTRIES = <?= $visitedJson ?>;
  window.TYPED_PHRASES = <?= json_encode(array_values(array_unique(array_filter(array_merge(
      [$profile['title'] ?? ''],
      array_map(fn ($x) => trim(($x['role'] ?? '') . ' @ ' . ($x['company'] ?? ''), ' @'), $experience)
  ))))) ?>;
  <?php if ($worldEnabled): ?>
  window.WORLD_STATIONS = <?= json_encode($worldStations) ?>;
  <?php endif; ?>
</script>
<script src="https://unpkg.com/three@0.160.0/build/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"></script>
<script src="assets/js/hero3d.js"></script>
<?php if ($worldEnabled): ?><script src="assets/js/world3d.js"></script><?php endif; ?>
<script src="assets/js/main.js"></script>
</body>
</html>

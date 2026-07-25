<?php

define('DATA_FILE', __DIR__ . '/../data/data.json');

function load_data(): array {
    if (!file_exists(DATA_FILE)) {
        return [];
    }
    $json = file_get_contents(DATA_FILE);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function save_data(array $data): bool {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return file_put_contents(DATA_FILE, $json) !== false;
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function chip_class(int $index): string {
    $palette = [
        'bg-cyan-500/10 border-cyan-500/30 text-cyan-300',
        'bg-purple-500/10 border-purple-500/30 text-purple-300',
        'bg-rose-500/10 border-rose-500/30 text-rose-300',
        'bg-amber-500/10 border-amber-500/30 text-amber-300',
        'bg-emerald-500/10 border-emerald-500/30 text-emerald-300',
        'bg-sky-500/10 border-sky-500/30 text-sky-300',
        'bg-fuchsia-500/10 border-fuchsia-500/30 text-fuchsia-300',
        'bg-orange-500/10 border-orange-500/30 text-orange-300',
    ];
    return $palette[$index % count($palette)];
}

// Maps a game/platform name to a verified-working slug on the Simple Icons
// CDN (cdn.simpleicons.org). Only entries confirmed to return HTTP 200 are
// listed — anything else falls back to a generic controller icon rather
// than guessing at a logo that might not exist or might be wrong.
function brand_icon_url(?string $name): ?string {
    $slugs = [
        'valorant' => 'valorant',
        'gta v' => 'rockstargames',
        'grand theft auto v' => 'rockstargames',
        'pubg' => 'pubg',
        'roblox' => 'roblox',
        'ea fc' => 'ea',
        'ea sports fc' => 'ea',
        'playstation' => 'playstation',
        'ps5' => 'playstation',
        'ps4' => 'playstation',
        'pc' => 'steam',
        'steam' => 'steam',
    ];
    $key = strtolower(trim($name ?? ''));
    if (!isset($slugs[$key])) {
        return null;
    }
    return 'https://cdn.simpleicons.org/' . $slugs[$key] . '/ffffff';
}

// Local logo files take priority over the CDN lookup above — used for games
// that don't have an entry on Simple Icons (Forza Horizon, Need For Speed,
// Age of Mythology). Drop the matching file in assets/img/games/ and it's
// picked up automatically; nothing else needs to change.
function local_game_icon(?string $name): ?string {
    $files = [
        'forza horizon' => 'forza-horizon.png',
        'nfs' => 'need-for-speed.png',
        'need for speed' => 'need-for-speed.png',
        'age of mythology' => 'age-of-mythology.png',
    ];
    $key = strtolower(trim($name ?? ''));
    if (!isset($files[$key])) {
        return null;
    }
    $path = __DIR__ . '/../assets/img/games/' . $files[$key];
    if (!file_exists($path)) {
        return null;
    }
    return 'assets/img/games/' . $files[$key];
}

function game_icon(?string $name): ?string {
    return local_game_icon($name) ?? brand_icon_url($name);
}

// A couple of brand marks (Xbox notably) aren't on the cdn.simpleicons.org
// mirror even though the underlying icon set has them, so they're embedded
// directly here instead of depending on that endpoint. Rendered inline with
// fill="currentColor" so it follows the surrounding text color automatically
// in both themes — no invert-filter hack needed like the CDN <img> icons.
function inline_brand_icon(?string $name): ?array {
    $icons = [
        'xbox' => [
            'viewBox' => '0 0 24 24',
            'path' => '<path d="M4.102 21.033A11.95 11.95 0 0 0 12 24a11.96 11.96 0 0 0 7.902-2.967c1.877-1.912-4.316-8.709-7.902-11.417c-3.582 2.708-9.779 9.505-7.898 11.417m11.16-14.406c2.5 2.961 7.484 10.313 6.076 12.912A11.94 11.94 0 0 0 24 12.004a11.95 11.95 0 0 0-3.57-8.536s-.027-.022-.082-.042a.8.8 0 0 0-.281-.045c-.592 0-1.985.434-4.805 3.246M3.654 3.426c-.057.02-.082.041-.086.042A11.96 11.96 0 0 0 0 12.004c0 2.854.998 5.473 2.661 7.533c-1.401-2.605 3.579-9.951 6.08-12.91c-2.82-2.813-4.216-3.245-4.806-3.245a.7.7 0 0 0-.281.046zM12 3.551S9.055 1.828 6.755 1.746c-.903-.033-1.454.295-1.521.339C7.379.646 9.659 0 11.984 0H12c2.334 0 4.605.646 6.766 2.085c-.068-.046-.615-.372-1.52-.339C14.946 1.828 12 3.545 12 3.545z"/>',
        ],
    ];
    $key = strtolower(trim($name ?? ''));
    return $icons[$key] ?? null;
}

function lang_color(?string $language): string {
    $colors = [
        'PHP' => '#4F5D95',
        'JavaScript' => '#F1E05A',
        'TypeScript' => '#3178C6',
        'HTML' => '#E34C26',
        'CSS' => '#563D7C',
        'Java' => '#B07219',
        'Kotlin' => '#A97BFF',
        'Dart' => '#00B4AB',
        'Python' => '#3572A5',
        'Jupyter Notebook' => '#DA5B0B',
        'Hack' => '#878787',
        'C++' => '#F34B7D',
        'C' => '#555555',
    ];
    return $colors[$language ?? ''] ?? '#8B5CF6';
}

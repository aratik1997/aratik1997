<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$data = load_data();

function lines(string $value): array {
    return array_values(array_filter(array_map('trim', explode("\n", $value)), fn ($v) => $v !== ''));
}

// --- Profile ---
$data['profile'] = [
    'name' => trim($_POST['profile_name'] ?? ''),
    'title' => trim($_POST['profile_title'] ?? ''),
    'phone' => trim($_POST['profile_phone'] ?? ''),
    'email' => trim($_POST['profile_email'] ?? ''),
    'address' => trim($_POST['profile_address'] ?? ''),
    'about' => trim($_POST['profile_about'] ?? ''),
    'photo' => $data['profile']['photo'] ?? 'assets/img/placeholder-avatar.svg',
];

// Photo upload
if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($_FILES['photo']['tmp_name']);

    if (isset($allowed[$ext]) && $mime === $allowed[$ext] && $_FILES['photo']['size'] <= 5 * 1024 * 1024) {
        $uploadDir = __DIR__ . '/../assets/img/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = 'profile-' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
            $data['profile']['photo'] = 'assets/img/uploads/' . $filename;
        }
    }
}

// --- Social ---
$data['social'] = [
    'github' => trim($_POST['social_github'] ?? ''),
    'facebook' => trim($_POST['social_facebook'] ?? ''),
    'instagram' => trim($_POST['social_instagram'] ?? ''),
    'linkedin' => trim($_POST['social_linkedin'] ?? ''),
    'x' => trim($_POST['social_x'] ?? ''),
    'threads' => trim($_POST['social_threads'] ?? ''),
    'whatsapp' => trim($_POST['social_whatsapp'] ?? ''),
    'wechat' => trim($_POST['social_wechat'] ?? ''),
    'xbox' => trim($_POST['social_xbox'] ?? ''),
    'psn' => trim($_POST['social_psn'] ?? ''),
    'steam' => trim($_POST['social_steam'] ?? ''),
];

// --- Education ---
$education = [];
$degrees = $_POST['education_degree'] ?? [];
foreach ($degrees as $i => $degree) {
    $degree = trim($degree);
    if ($degree === '') continue;
    $education[] = [
        'degree' => $degree,
        'school' => trim($_POST['education_school'][$i] ?? ''),
        'period' => trim($_POST['education_period'][$i] ?? ''),
        'grade' => trim($_POST['education_grade'][$i] ?? ''),
        'url' => trim($_POST['education_url'][$i] ?? ''),
    ];
}
$data['education'] = $education;

// --- Experience ---
$experience = [];
$roles = $_POST['experience_role'] ?? [];
foreach ($roles as $i => $role) {
    $role = trim($role);
    if ($role === '') continue;
    $experience[] = [
        'role' => $role,
        'company' => trim($_POST['experience_company'][$i] ?? ''),
        'period' => trim($_POST['experience_period'][$i] ?? ''),
        'bullets' => lines($_POST['experience_bullets'][$i] ?? ''),
    ];
}
$data['experience'] = $experience;

// --- Skills ---
$data['skills'] = lines($_POST['skills'] ?? '');

// --- Skill expertise bars ---
$skillProgress = [];
$spLabels = $_POST['skill_progress_label'] ?? [];
foreach ($spLabels as $i => $label) {
    $label = trim($label);
    if ($label === '') continue;
    $percent = (int) ($_POST['skill_progress_percent'][$i] ?? 0);
    $skillProgress[] = [
        'label' => $label,
        'percent' => max(0, min(100, $percent)),
    ];
}
$data['skillProgress'] = $skillProgress;

// --- Projects ---
$projects = [];
$names = $_POST['projects_name'] ?? [];
foreach ($names as $i => $name) {
    $name = trim($name);
    if ($name === '') continue;
    $projects[] = [
        'name' => $name,
        'description' => trim($_POST['projects_description'][$i] ?? ''),
        'language' => trim($_POST['projects_language'][$i] ?? ''),
        'url' => trim($_POST['projects_url'][$i] ?? ''),
        'website' => trim($_POST['projects_website'][$i] ?? ''),
        'image' => trim($_POST['projects_image'][$i] ?? ''),
        'category' => trim($_POST['projects_category'][$i] ?? ''),
        'private' => ($_POST['projects_visibility'][$i] ?? 'public') === 'private',
    ];
}
$data['projects'] = $projects;

// --- Gaming ---
$platforms = [];
$platformNames = $_POST['gaming_platform_name'] ?? [];
foreach ($platformNames as $i => $name) {
    $name = trim($name);
    if ($name === '') continue;
    $platforms[] = [
        'name' => $name,
        'tag' => trim($_POST['gaming_platform_tag'][$i] ?? ''),
    ];
}
$data['gaming'] = [
    'intro' => trim($_POST['gaming_intro'] ?? ''),
    'games' => lines($_POST['gaming_games'] ?? ''),
    'platforms' => $platforms,
];

// --- Hobbies & Travel ---
$data['hobbies'] = lines($_POST['hobbies'] ?? '');
$data['travel'] = [
    'intro' => trim($_POST['travel_intro'] ?? ''),
    'visited' => array_map('strtoupper', lines($_POST['travel_visited'] ?? '')),
];

save_data($data);

header('Location: dashboard.php?saved=1');
exit;

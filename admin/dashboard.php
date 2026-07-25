<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$data = load_data();
$profile = $data['profile'] ?? [];
$social = $data['social'] ?? [];
$education = $data['education'] ?? [];
$experience = $data['experience'] ?? [];
$skills = $data['skills'] ?? [];
$skillProgress = $data['skillProgress'] ?? [];
$projects = $data['projects'] ?? [];
$gaming = $data['gaming'] ?? [];
$hobbies = $data['hobbies'] ?? [];
$travel = $data['travel'] ?? [];

// Pad arrays with blank rows so the admin can add new entries inline.
function pad_rows(array $rows, int $blanks, array $template): array {
    for ($i = 0; $i < $blanks; $i++) {
        $rows[] = $template;
    }
    return $rows;
}

$education = pad_rows($education, 2, ['degree' => '', 'school' => '', 'period' => '', 'grade' => '']);
$experience = pad_rows($experience, 1, ['role' => '', 'company' => '', 'period' => '', 'bullets' => []]);
$projects = pad_rows($projects, 5, ['name' => '', 'description' => '', 'language' => '', 'url' => '', 'private' => false]);
$platforms = pad_rows($gaming['platforms'] ?? [], 1, ['name' => '', 'tag' => '']);
$skillProgress = pad_rows($skillProgress, 2, ['label' => '', 'percent' => 0]);

$saved = isset($_GET['saved']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  .field { width: 100%; padding: 0.5rem 0.75rem; border-radius: 0.5rem; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #e5e7eb; }
  .field:focus { outline: none; border-color: #8B5CF6; }
  .label { display:block; font-size: 0.8rem; color: #9CA3AF; margin-bottom: 0.25rem; }
  .card { background: #11151c; border: 1px solid rgba(255,255,255,0.08); border-radius: 1rem; }
</style>
</head>
<body class="bg-[#0D1117] text-gray-200 min-h-screen">

<header class="sticky top-0 z-10 bg-[#0D1117]/90 backdrop-blur border-b border-white/10">
  <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
    <h1 class="font-bold bg-gradient-to-r from-cyan-400 to-purple-500 bg-clip-text text-transparent">Admin Dashboard</h1>
    <div class="flex items-center gap-4 text-sm">
      <a href="../index.php" target="_blank" class="text-gray-400 hover:text-gray-200">View site ↗</a>
      <a href="logout.php" class="text-red-400 hover:text-red-300">Log out</a>
    </div>
  </div>
</header>

<div class="max-w-5xl mx-auto px-6 py-10">

<?php if ($saved): ?>
  <div class="mb-8 text-sm text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-4 py-3">
    Changes saved successfully.
  </div>
<?php endif; ?>

<form method="post" action="save.php" enctype="multipart/form-data" class="space-y-10">

  <!-- PROFILE -->
  <div class="card p-6">
    <h2 class="text-lg font-bold mb-4 text-purple-400">Profile</h2>
    <div class="grid md:grid-cols-2 gap-4">
      <div><label class="label">Name</label><input class="field" type="text" name="profile_name" value="<?= e($profile['name'] ?? '') ?>"></div>
      <div><label class="label">Title</label><input class="field" type="text" name="profile_title" value="<?= e($profile['title'] ?? '') ?>"></div>
      <div><label class="label">Phone</label><input class="field" type="text" name="profile_phone" value="<?= e($profile['phone'] ?? '') ?>"></div>
      <div><label class="label">Email</label><input class="field" type="email" name="profile_email" value="<?= e($profile['email'] ?? '') ?>"></div>
      <div class="md:col-span-2"><label class="label">Address</label><input class="field" type="text" name="profile_address" value="<?= e($profile['address'] ?? '') ?>"></div>
      <div class="md:col-span-2"><label class="label">About</label><textarea class="field" name="profile_about" rows="4"><?= e($profile['about'] ?? '') ?></textarea></div>
      <div class="md:col-span-2">
        <label class="label">Photo</label>
        <div class="flex items-center gap-4">
          <img src="../<?= e($profile['photo'] ?? 'assets/img/placeholder-avatar.svg') ?>" class="w-16 h-16 rounded-full object-cover border border-white/10">
          <input type="file" name="photo" accept="image/*" class="text-sm text-gray-400">
        </div>
      </div>
    </div>
  </div>

  <!-- SOCIAL -->
  <div class="card p-6">
    <h2 class="text-lg font-bold mb-4 text-purple-400">Social &amp; Gaming Accounts</h2>
    <div class="grid md:grid-cols-2 gap-4">
      <div><label class="label">GitHub URL</label><input class="field" type="url" name="social_github" value="<?= e($social['github'] ?? '') ?>"></div>
      <div><label class="label">LinkedIn URL</label><input class="field" type="url" name="social_linkedin" value="<?= e($social['linkedin'] ?? '') ?>"></div>
      <div><label class="label">Facebook URL</label><input class="field" type="url" name="social_facebook" value="<?= e($social['facebook'] ?? '') ?>"></div>
      <div><label class="label">Instagram URL</label><input class="field" type="url" name="social_instagram" value="<?= e($social['instagram'] ?? '') ?>"></div>
      <div><label class="label">X (Twitter) URL</label><input class="field" type="url" name="social_x" value="<?= e($social['x'] ?? '') ?>"></div>
      <div><label class="label">Threads URL</label><input class="field" type="url" name="social_threads" value="<?= e($social['threads'] ?? '') ?>"></div>
      <div><label class="label">WhatsApp number (with country code, e.g. +8801...)</label><input class="field" type="text" name="social_whatsapp" value="<?= e($social['whatsapp'] ?? '') ?>"></div>
      <div><label class="label">WeChat ID / number</label><input class="field" type="text" name="social_wechat" value="<?= e($social['wechat'] ?? '') ?>"></div>
      <div><label class="label">Xbox Gamertag</label><input class="field" type="text" name="social_xbox" value="<?= e($social['xbox'] ?? '') ?>"></div>
      <div><label class="label">PSN ID</label><input class="field" type="text" name="social_psn" value="<?= e($social['psn'] ?? '') ?>"></div>
      <div><label class="label">Steam</label><input class="field" type="text" name="social_steam" value="<?= e($social['steam'] ?? '') ?>"></div>
    </div>
  </div>

  <!-- EDUCATION -->
  <div class="card p-6">
    <h2 class="text-lg font-bold mb-4 text-purple-400">Education</h2>
    <div class="space-y-4">
      <?php foreach ($education as $i => $edu): ?>
      <div class="grid md:grid-cols-4 gap-3 border-b border-white/5 pb-4">
        <input class="field" type="text" name="education_degree[]" placeholder="Degree" value="<?= e($edu['degree'] ?? '') ?>">
        <input class="field" type="text" name="education_school[]" placeholder="School" value="<?= e($edu['school'] ?? '') ?>">
        <input class="field" type="text" name="education_period[]" placeholder="Period" value="<?= e($edu['period'] ?? '') ?>">
        <input class="field" type="text" name="education_grade[]" placeholder="Grade" value="<?= e($edu['grade'] ?? '') ?>">
        <input class="field md:col-span-4" type="url" name="education_url[]" placeholder="School website (optional) — makes the name clickable" value="<?= e($edu['url'] ?? '') ?>">
      </div>
      <?php endforeach; ?>
    </div>
    <p class="text-xs text-gray-500 mt-3">Leave a row's Degree blank to drop it. Blank rows at the bottom are for adding new entries.</p>
  </div>

  <!-- EXPERIENCE -->
  <div class="card p-6">
    <h2 class="text-lg font-bold mb-4 text-purple-400">Work Experience</h2>
    <div class="space-y-6">
      <?php foreach ($experience as $i => $exp): ?>
      <div class="grid md:grid-cols-3 gap-3 border-b border-white/5 pb-4">
        <input class="field" type="text" name="experience_role[]" placeholder="Role" value="<?= e($exp['role'] ?? '') ?>">
        <input class="field" type="text" name="experience_company[]" placeholder="Company" value="<?= e($exp['company'] ?? '') ?>">
        <input class="field" type="text" name="experience_period[]" placeholder="Period" value="<?= e($exp['period'] ?? '') ?>">
        <textarea class="field md:col-span-3" name="experience_bullets[]" rows="3" placeholder="One bullet point per line"><?= e(implode("\n", $exp['bullets'] ?? [])) ?></textarea>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="text-xs text-gray-500 mt-3">Leave a row's Role blank to drop it.</p>
  </div>

  <!-- SKILLS -->
  <div class="card p-6">
    <h2 class="text-lg font-bold mb-4 text-purple-400">Skills</h2>
    <label class="label">One skill per line</label>
    <textarea class="field" name="skills" rows="6"><?= e(implode("\n", $skills)) ?></textarea>
  </div>

  <!-- SKILL EXPERTISE BARS -->
  <div class="card p-6">
    <h2 class="text-lg font-bold mb-4 text-purple-400">Skill Expertise Bars</h2>
    <p class="text-xs text-gray-500 mb-4">Shown as animated 0-100% bars on the site. Leave Label blank to drop a row.</p>
    <div class="space-y-3">
      <?php foreach ($skillProgress as $sp): ?>
      <div class="grid grid-cols-[1fr,120px] gap-3">
        <input class="field" type="text" name="skill_progress_label[]" placeholder="e.g. Backend Engineering" value="<?= e($sp['label'] ?? '') ?>">
        <input class="field" type="number" min="0" max="100" name="skill_progress_percent[]" placeholder="%" value="<?= e((string) ($sp['percent'] ?? '')) ?>">
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- PROJECTS -->
  <div class="card p-6">
    <h2 class="text-lg font-bold mb-4 text-purple-400">Projects</h2>
    <div class="space-y-4 max-h-[32rem] overflow-y-auto pr-2">
      <?php foreach ($projects as $i => $project): ?>
      <div class="grid md:grid-cols-5 gap-3 border-b border-white/5 pb-4">
        <input class="field" type="text" name="projects_name[]" placeholder="Repo name" value="<?= e($project['name'] ?? '') ?>">
        <input class="field md:col-span-2" type="text" name="projects_description[]" placeholder="Description" value="<?= e($project['description'] ?? '') ?>">
        <input class="field" type="text" name="projects_language[]" placeholder="Language" value="<?= e($project['language'] ?? '') ?>">
        <select class="field" name="projects_visibility[]">
          <option value="public" <?= empty($project['private']) ? 'selected' : '' ?>>Public</option>
          <option value="private" <?= !empty($project['private']) ? 'selected' : '' ?>>Private</option>
        </select>
        <input class="field md:col-span-5" type="url" name="projects_url[]" placeholder="GitHub URL — https://github.com/aratik1997/..." value="<?= e($project['url'] ?? '') ?>">
        <input class="field md:col-span-2" type="url" name="projects_website[]" placeholder="Live website (optional) — shown instead of the GitHub link if set" value="<?= e($project['website'] ?? '') ?>">
        <input class="field md:col-span-2" type="text" name="projects_image[]" placeholder="Preview image path, e.g. assets/img/poker.png" value="<?= e($project['image'] ?? '') ?>">
        <input class="field" type="text" name="projects_category[]" placeholder="Category, e.g. games" value="<?= e($project['category'] ?? '') ?>">
      </div>
      <?php endforeach; ?>
    </div>
    <p class="text-xs text-gray-500 mt-3">Leave a row's repo name blank to drop it. Scroll for more — blank rows at the bottom are for adding new projects.</p>
  </div>

  <!-- GAMING -->
  <div class="card p-6">
    <h2 class="text-lg font-bold mb-4 text-purple-400">Gaming</h2>
    <div class="mb-4">
      <label class="label">Intro line</label>
      <input class="field" type="text" name="gaming_intro" value="<?= e($gaming['intro'] ?? '') ?>">
    </div>
    <div class="mb-4">
      <label class="label">Games (one per line)</label>
      <textarea class="field" name="gaming_games" rows="5"><?= e(implode("\n", $gaming['games'] ?? [])) ?></textarea>
    </div>
    <div>
      <label class="label mb-2">Platforms &amp; tags</label>
      <div class="space-y-3">
        <?php foreach ($platforms as $p): ?>
        <div class="grid md:grid-cols-2 gap-3">
          <input class="field" type="text" name="gaming_platform_name[]" placeholder="Platform (e.g. Xbox)" value="<?= e($p['name'] ?? '') ?>">
          <input class="field" type="text" name="gaming_platform_tag[]" placeholder="Gamertag / ID" value="<?= e($p['tag'] ?? '') ?>">
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- HOBBIES & TRAVEL -->
  <div class="card p-6">
    <h2 class="text-lg font-bold mb-4 text-purple-400">Hobbies &amp; Travel</h2>
    <div class="mb-4">
      <label class="label">Hobbies (one per line)</label>
      <textarea class="field" name="hobbies" rows="4"><?= e(implode("\n", $hobbies)) ?></textarea>
    </div>
    <div class="mb-4">
      <label class="label">Travel intro line</label>
      <input class="field" type="text" name="travel_intro" value="<?= e($travel['intro'] ?? '') ?>">
    </div>
    <div>
      <label class="label">Visited countries — one ISO 3166-1 alpha-2 code per line (e.g. BD, IN, MY, SA, AE, SG, TH)</label>
      <textarea class="field" name="travel_visited" rows="4"><?= e(implode("\n", $travel['visited'] ?? [])) ?></textarea>
    </div>
  </div>

  <button type="submit" class="w-full py-3 rounded-lg bg-gradient-to-r from-cyan-400 to-purple-500 font-semibold text-black hover:opacity-90 transition">
    Save Changes
  </button>
</form>
</div>
</body>
</html>

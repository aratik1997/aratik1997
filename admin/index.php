<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
start_admin_session();

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$mode = admin_exists() ? 'login' : 'setup';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($mode === 'setup') {
        $confirm = $_POST['confirm'] ?? '';
        if ($username === '' || strlen($password) < 6) {
            $error = 'Username is required and password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            create_admin($username, $password);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header('Location: dashboard.php');
            exit;
        }
    } else {
        if (verify_admin($username, $password)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — <?= $mode === 'setup' ? 'Create Account' : 'Login' ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#0D1117] text-gray-200 font-sans min-h-screen flex items-center justify-center px-4" style="font-family:'Inter',sans-serif;">
  <div class="w-full max-w-sm bg-[#11151c] border border-white/10 rounded-2xl p-8">
    <h1 class="text-xl font-bold mb-1 bg-gradient-to-r from-cyan-400 to-purple-500 bg-clip-text text-transparent">
      <?= $mode === 'setup' ? 'Create Admin Account' : 'Admin Login' ?>
    </h1>
    <p class="text-sm text-gray-500 mb-6">
      <?= $mode === 'setup' ? 'First run — set your admin username and password.' : 'Sign in to edit your portfolio content.' ?>
    </p>

    <?php if ($error): ?>
      <div class="mb-4 text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-2"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm text-gray-400 mb-1">Username</label>
        <input type="text" name="username" required
               class="w-full px-4 py-2 rounded-lg bg-black/30 border border-white/10 focus:border-purple-500 outline-none">
      </div>
      <div>
        <label class="block text-sm text-gray-400 mb-1">Password</label>
        <input type="password" name="password" required minlength="6"
               class="w-full px-4 py-2 rounded-lg bg-black/30 border border-white/10 focus:border-purple-500 outline-none">
      </div>
      <?php if ($mode === 'setup'): ?>
      <div>
        <label class="block text-sm text-gray-400 mb-1">Confirm Password</label>
        <input type="password" name="confirm" required minlength="6"
               class="w-full px-4 py-2 rounded-lg bg-black/30 border border-white/10 focus:border-purple-500 outline-none">
      </div>
      <?php endif; ?>
      <button type="submit"
              class="w-full py-2 rounded-lg bg-gradient-to-r from-cyan-400 to-purple-500 font-semibold text-black hover:opacity-90 transition">
        <?= $mode === 'setup' ? 'Create Account & Continue' : 'Log In' ?>
      </button>
    </form>

    <a href="../index.php" class="block text-center text-xs text-gray-500 mt-6 hover:text-gray-300">← Back to site</a>
  </div>
</body>
</html>

<?php
session_start();

// Sudah login: langsung ke dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

include 'koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $koneksi->prepare("SELECT id, username, password FROM admin WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: dashboard.php");
            exit;
        }

        // Pesan sengaja dibuat umum agar tidak membocorkan username mana yang ada
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-box {
            max-width: 380px;
            margin: 60px auto;
            padding: 24px;
            background: var(--card, #fff);
            border: 1px solid var(--border, #e3e5ec);
            border-radius: 12px;
            box-shadow: 0 5px 16px rgba(22,33,62,.05);
        }

        .login-box h1 {
            margin: 0 0 18px;
            font-size: 22px;
            color: var(--navy, #16213e);
        }

        .login-box label {
            display: block;
            margin: 12px 0 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .login-box input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border, #e3e5ec);
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
        }

        .login-box button {
            width: 100%;
            margin-top: 18px;
            padding: 11px;
            border: 0;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            background: var(--accent, #2fbf8f);
            color: #fff;
        }

        .login-error {
            margin-bottom: 8px;
            padding: 10px 12px;
            border-radius: 8px;
            background: #FDEBEC;
            border: 1px solid #F3C4C8;
            color: #A63A43;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h1>Login Admin Absensi</h1>

    <?php if ($error !== ''): ?>
        <div class="login-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Masuk</button>
    </form>
</div>

</body>
</html>

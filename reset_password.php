<?php
include __DIR__ . '/config/config.php';
$pesan = '';
$token_valid = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        $token_valid = true;
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $password_baru = $_POST['password_baru'];
            $konfirmasi_password = $_POST['konfirmasi_password'];
            
            if ($password_baru !== $konfirmasi_password) {
                $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Konfirmasi password tidak cocok.</div>";
            } elseif (strlen($password_baru) < 8) {
                $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Password baru minimal harus 8 karakter.</div>";
            } else {
                $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user['id']);
                
                if ($update_stmt->execute()) {
                    header("Location: login.php?status=sukses_reset");
                    exit();
                } else {
                    $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Gagal mereset password. Silakan coba lagi.</div>";
                }
            }
        }
    } else {
        $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Token tidak valid atau sudah kedaluwarsa. Silakan minta link reset baru.</div>";
    }
} else {
    header("Location: lupa_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Catatan Rasa Digital</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#1a1a1a", "foreground-light": "#181411", "foreground-dark": "#f8f7f6", "input-light": "#f4f2f0", "input-dark": "#3a2d21" },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-foreground-light dark:text-foreground-dark">
<div class="flex flex-col items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="index.php" class="text-3xl font-bold text-primary">Catatan Rasa Digital</a>
            <h2 class="text-2xl font-bold mt-4">Atur Ulang Password Anda</h2>
        </div>

        <div class="bg-white dark:bg-gray-800/50 p-8 rounded-xl shadow-md space-y-6">
            <?php if ($pesan): ?>
                <div class="mb-4"><?php echo $pesan; ?></div>
            <?php endif; ?>
            
            <?php if ($token_valid): ?>
            <form method="POST" class="space-y-6">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
                <div>
                    <label for="password_baru" class="block text-sm font-medium mb-2">Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru" required class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter.</p>
                </div>
                <div>
                    <label for="konfirmasi_password" class="block text-sm font-medium mb-2">Konfirmasi Password Baru</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" required class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full h-12 px-5 rounded-full bg-primary text-white font-bold tracking-wide shadow-lg hover:bg-opacity-90 transition-colors">
                        Reset Password
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
        <p class="text-center mt-6 text-gray-500 dark:text-gray-400">
            <a href="login.php" class="font-semibold text-primary hover:underline">Kembali ke Halaman Login</a>
        </p>
    </div>
</div>
</body>
</html>
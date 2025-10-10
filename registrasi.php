<?php
include __DIR__ . '/config/config.php';
$pesan = '';

// Buat pertanyaan CAPTCHA baru setiap kali halaman dimuat (jika bukan POST)
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $angka1 = rand(1, 10);
    $angka2 = rand(1, 10);
    $_SESSION['captcha_question'] = "$angka1 + $angka2";
    $_SESSION['captcha_answer'] = $angka1 + $angka2;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $captcha_user = $_POST['captcha'];

    // Validasi input
    if (empty($username) || empty($password) || empty($konfirmasi_password) || empty($captcha_user)) {
        $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Semua kolom wajib diisi.</div>";
    } elseif ($captcha_user != $_SESSION['captcha_answer']) {
        $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Jawaban verifikasi salah!</div>";
    } elseif ($password !== $konfirmasi_password) {
        $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Konfirmasi password tidak cocok.</div>";
    } elseif (strlen($password) < 8) {
        $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Password minimal harus 8 karakter.</div>";
    } else {
        // Cek apakah username sudah ada
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($result->num_rows > 0) {
            $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Username '{$username}' sudah digunakan.</div>";
        } else {
            // Hash password dan simpan user baru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt_insert->bind_param("ss", $username, $hashed_password);
            
            if ($stmt_insert->execute()) {
            
                // Redirect ke halaman login dengan pesan sukses
                header("Location: login.php?status=sukses_daftar");
                exit();
            } else {
                $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Pendaftaran gagal. Silakan coba lagi.</div>";
            }
        }
        $stmt_check->close();
    }

    // Jika terjadi error, buat captcha baru
    $angka1 = rand(1, 10);
    $angka2 = rand(1, 10);
    $_SESSION['captcha_question'] = "$angka1 + $angka2";
    $_SESSION['captcha_answer'] = $angka1 + $angka2;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran - Catatan Rasa Digital</title>
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
            <h1 class="text-3xl font-bold text-primary">Catatan Rasa Digital</h1>
            <h2 class="text-2xl font-bold mt-4">Daftar Akun Baru</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Buat akun untuk mulai menyimpan resep Anda.</p>
        </div>

        <div class="bg-white dark:bg-gray-800/50 p-8 rounded-xl shadow-md space-y-6">
            <?php if ($pesan): ?>
                <div class="mb-4"><?php echo $pesan; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium mb-2">Username</label>
                    <input type="text" id="username" name="username" required class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium mb-2">Password</label>
                    <input type="password" id="password" name="password" required class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter.</p>
                </div>

                <div>
                    <label for="konfirmasi_password" class="block text-sm font-medium mb-2">Konfirmasi Password</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" required class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="captcha" class="block text-sm font-medium mb-2">Verifikasi: <?php echo $_SESSION['captcha_question']; ?> = ?</label>
                    <input type="number" id="captcha" name="captcha" required class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full h-12 px-5 rounded-full bg-primary text-white font-bold tracking-wide shadow-lg hover:bg-opacity-90 transition-colors">
                        Daftar
                    </button>
                </div>
            </form>
        </div>
        <p class="text-center mt-6 text-gray-500 dark:text-gray-400">
            Sudah punya akun? <a href="login.php" class="font-semibold text-primary hover:underline">Login di sini</a>
        </p>
    </div>
</div>
</body>
</html>
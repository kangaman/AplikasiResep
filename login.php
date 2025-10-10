<?php
include __DIR__ . '/config/config.php';
$pesan = '';

// Jika pengguna sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Cek apakah ada status dari halaman lain (misal: setelah registrasi atau reset password)
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'sukses_daftar') {
        $pesan = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4' role='alert'>Pendaftaran berhasil! Silakan login.</div>";
    }
    if ($_GET['status'] == 'sukses_reset') {
        $pesan = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4' role='alert'>Password berhasil direset! Silakan login dengan password baru Anda.</div>";
    }
}

// Jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Username dan password wajib diisi.</div>";
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;
                header("Location: dashboard.php");
                exit();
            } else {
                $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Password salah!</div>";
            }
        } else {
            $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Username tidak ditemukan.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Catatan Rasa Digital</title>
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
            <h2 class="text-2xl font-bold mt-4">Selamat Datang Kembali</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Login untuk melanjutkan ke dashboard Anda.</p>
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
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-medium">Password</label>
                        <a href="lupa_password.php" class="text-sm text-primary hover:underline">Lupa Password?</a>
                    </div>
                    <input type="password" id="password" name="password" required class="w-full h-12 px-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full h-12 px-5 rounded-full bg-primary text-white font-bold tracking-wide shadow-lg hover:bg-opacity-90 transition-colors">
                        Login
                    </button>
                </div>
            </form>
        </div>
        <p class="text-center mt-6 text-gray-500 dark:text-gray-400">
            Belum punya akun? <a href="registrasi.php" class="font-semibold text-primary hover:underline">Daftar di sini</a>
        </p>
    </div>
</div>
</body>
</html>
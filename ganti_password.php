<?php
include __DIR__ . '/config/config.php';
// Cek sesi login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pesan = '';

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi dasar
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Semua kolom wajib diisi.</div>";
    } elseif ($password_baru !== $konfirmasi_password) {
        $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Konfirmasi password baru tidak cocok.</div>";
    } elseif (strlen($password_baru) < 8) {
        $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Password baru minimal harus 8 karakter.</div>";
    } else {
        // Ambil hash password saat ini dari database
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // Verifikasi password lama
        if ($user && password_verify($password_lama, $user['password'])) {
            // Jika password lama benar, hash password baru
            $hashed_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);

            // Update password di database
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password_baru, $user_id);

            if ($update_stmt->execute()) {
                $pesan = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'>Password berhasil diubah!</div>";
            } else {
                $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Terjadi kesalahan saat mengubah password.</div>";
            }
            $update_stmt->close();
        } else {
            // Jika password lama salah
            $pesan = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Password lama yang Anda masukkan salah.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#ec7f13", "background-light": "#f8f7f6", "background-dark": "#221910", "foreground-light": "#181411", "foreground-dark": "#f8f7f6", "input-light": "#f4f2f0", "input-dark": "#3a2d21" },
                    fontFamily: { "display": ["Epilogue", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-foreground-light dark:text-foreground-dark">
<div class="flex flex-col min-h-screen">
    <header class="sticky top-0 z-10 flex items-center bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm p-4 justify-between">
        <a href="dashboard.php" class="text-lg font-bold text-gray-900 dark:text-white hidden md:block">Catatan Rasa Digital</a>
        <a href="dashboard.php" class="p-2 md:hidden"><span class="material-symbols-outlined">arrow_back</span></a>
        <div class="hidden md:flex items-center gap-4">
            <a href="daftar_belanja.php" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-primary">Daftar Belanja</a>
            <a href="kalkulator_db.php" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-primary">Kalkulator</a>
            <a href="manajemen_bahan.php" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-primary">Harga Bahan</a>
            <a href="ganti_password.php" class="text-sm font-bold text-primary">Ganti Password</a>
            <a href="logout.php" class="text-sm font-medium bg-red-500 text-white px-3 py-1.5 rounded-full hover:bg-red-600">Logout</a>
        </div>
        <div class="w-10 md:hidden"></div>
    </header>

    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <h2 class="text-2xl font-bold text-center mb-6">Ubah Password Anda</h2>
            <?php echo $pesan; ?>
            <form method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label for="password_lama" class="font-medium">Password Lama</label>
                    <input type="password" id="password_lama" name="password_lama" required class="w-full h-14 p-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="space-y-2">
                    <label for="password_baru" class="font-medium">Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru" required class="w-full h-14 p-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="space-y-2">
                    <label for="konfirmasi_password" class="font-medium">Konfirmasi Password Baru</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" required class="w-full h-14 p-4 rounded-lg bg-input-light dark:bg-input-dark border-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full h-12 px-5 rounded-full bg-primary text-white font-bold tracking-wide shadow-lg hover:bg-opacity-90">
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
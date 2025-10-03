<?php
include __DIR__ . '/config/config.php';
$pesan = '';
$token_valid = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Cek apakah token ada dan belum kedaluwarsa
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        $token_valid = true; // Token valid, tampilkan form
        
        // Jika form password baru disubmit
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $password_baru = $_POST['password_baru'];
            $konfirmasi_password = $_POST['konfirmasi_password'];
            
            if ($password_baru !== $konfirmasi_password) {
                $pesan = "<div class='alert alert-danger'>Konfirmasi password tidak cocok.</div>";
            } elseif (strlen($password_baru) < 8) {
                $pesan = "<div class='alert alert-danger'>Password baru minimal harus 8 karakter.</div>";
            } else {
                // Hash password baru
                $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                // Update password dan hapus token
                $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user['id']);
                
                if ($update_stmt->execute()) {
                    header("Location: login.php?status=sukses_reset");
                    exit();
                } else {
                    $pesan = "<div class='alert alert-danger'>Gagal mereset password. Silakan coba lagi.</div>";
                }
            }
        }
    } else {
        $pesan = "<div class='alert alert-danger'>Token tidak valid atau sudah kedaluwarsa. Silakan minta link reset baru.</div>";
    }
} else {
    // Jika tidak ada token di URL
    header("Location: lupa_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Catatan Rasa Digital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Reset Password Anda</h2>
        <?php echo $pesan; ?>
        <?php if ($token_valid): ?>
        <form method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-group">
                <label for="password_baru">Password Baru</label>
                <input type="password" id="password_baru" name="password_baru" required>
            </div>
            <div class="form-group">
                <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                <input type="password" id="konfirmasi_password" name="konfirmasi_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
        <?php endif; ?>
         <p style="text-align:center; margin-top:15px;"><a href="login.php">Kembali ke Login</a></p>
    </div>
</body>
</html>
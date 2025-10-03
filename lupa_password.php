<?php
include __DIR__ . '/config/config.php';
$pesan = '';

// ===================================================================
// == TAMBAHAN KODE UNTUK MENYAMAKAN ZONA WAKTU ==
// ===================================================================
date_default_timezone_set('Asia/Jakarta');
// ===================================================================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Buat token acak yang aman
        $token = bin2hex(random_bytes(50));
        // Atur waktu kedaluwarsa (1 jam dari sekarang, DALAM ZONA WAKTU YANG BENAR)
        $expiry = date("Y-m-d H:i:s", time() + 3600);

        // Simpan token dan waktu kedaluwarsa ke database
        $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $token, $expiry, $user['id']);
        
        if ($update_stmt->execute()) {
            // SIMULASI PENGIRIMAN EMAIL
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
            
            $pesan = "<div class='alert alert-success'>Link reset password telah dibuat. <br><br><strong>Salin dan buka link di bawah ini:</strong><br><a href='{$reset_link}' target='_blank'>{$reset_link}</a></div>";
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal membuat token reset. Silakan coba lagi.</div>";
        }
    } else {
        $pesan = "<div class='alert alert-danger'>Username tidak ditemukan.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password - Catatan Rasa Digital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Lupa Password</h2>
        <p>Masukkan username Anda untuk membuat link reset password.</p>
        <?php echo $pesan; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Link Reset</button>
            <p style="text-align:center; margin-top:15px;"><a href="login.php">Kembali ke Login</a></p>
        </form>
    </div>
</body>
</html>
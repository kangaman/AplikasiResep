<?php
// Periksa apakah sesi sudah dimulai. Jika belum, baru mulai.
// Ini mencegah error "Notice: session_start(): A session had already been started"
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Gunakan include_once untuk config agar tidak terjadi re-deklarasi jika terpanggil ganda
include_once __DIR__ . '/../config/config.php';

$theme = 'light'; // Default theme
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Gunakan prepared statement untuk keamanan
    if ($stmt = $conn->prepare("SELECT theme FROM user_settings WHERE user_id = ?")) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $theme = $row['theme'];
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="<?php if($theme === 'dark') echo 'dark'; ?>">

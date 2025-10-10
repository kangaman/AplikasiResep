<?php
session_start();
include __DIR__ . '/../config/config.php';

$theme = 'light'; // Default theme
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT theme FROM user_settings WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $theme = $row['theme'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id" class="<?php if($theme === 'dark') echo 'dark'; ?>">
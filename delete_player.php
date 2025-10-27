<?php
/**
 * Project: Gold Star Academy - Player Management System
 * Author: Ahmed Maher
 * Date: 27-10-2025
 *
 * File: delete_player.php
 * Description: Handles the deletion of a player record and their associated photo from the server.
 */

// --- Development Settings (Show Errors) ---
// NOTE: Should be disabled in production environment for security.
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// --- Authentication Check ---
// Ensure the user is logged in before allowing deletion
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// --- Input Validation: Check for Player ID ---
// Ensure a valid numeric ID is provided in the URL query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirect back to the dashboard if ID is missing or invalid
    header('Location: dashboard.php'); 
    exit;
}

// Sanitize the input by casting to integer
$player_id = (int)$_GET['id'];

// --- Database Connection Configuration ---
$db_host = "YOUR_HOST"; // 
$db_name = "YOUR_DB_NAME";
$db_user = "YOUR_USERNAME";
$db_pass = ""; // <--  YOUR_PASSWORD

// Establish database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// --- Connection Check ---
if ($conn->connect_error) {
    // Log the detailed error in production instead of exposing it via die()
    // error_log("Database Connection Failed: " . $conn->connect_error);
    die("Database Connection Failed: " . $conn->connect_error); 
}
$conn->set_charset("utf8mb4"); // Ensure UTF-8 for Arabic characters

// --- Step 1: Retrieve Photo Path Before Deleting Record ---
// Prepare statement to prevent SQL injection
$stmt_select = $conn->prepare("SELECT player_photo FROM players WHERE player_id = ?");
$stmt_select->bind_param("i", $player_id);
$stmt_select->execute();
$result = $stmt_select->get_result();
$photo_path = null; // Initialize photo path variable

if ($row = $result->fetch_assoc()) {
    $photo_path = $row['player_photo']; // Get the photo path from the result
}
$stmt_select->close();

// --- Step 2: Delete Player Photo from Server (if exists) ---
// NOTE: Ensure the web server has write permissions in the uploads directory.
if (!empty($photo_path) && $photo_path !== 'N/A' && file_exists($photo_path)) {
    // Use @ to suppress potential warnings if unlink fails (e.g., due to permissions)
    @unlink($photo_path); 
}

// --- Step 3: Delete Player Record from Database ---
// Prepare statement to prevent SQL injection
$stmt_delete = $conn->prepare("DELETE FROM players WHERE player_id = ?");
$stmt_delete->bind_param("i", $player_id);
$stmt_delete->execute();
// TODO: Add error handling here to check if the deletion was successful
$stmt_delete->close();

// --- Close Database Connection ---
$conn->close();

// --- Step 4: Redirect Back to Dashboard ---
// Redirect after successful deletion
header('Location: dashboard.php'); 
exit; // Ensure no further code is executed after redirection
?>
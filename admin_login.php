<?php
/**
 * Project: Gold Star Academy - Player Management System
 * Author: Ahmed Maher
 * Date: 27-10-2025
 *
 * File: admin_login.php
 * Description: Handles administrator login authentication. Verifies credentials and manages the session.
 */

session_start(); // Start the session to store login state

// --- Redirect if already logged in ---
// Check if the user is already authenticated
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Redirect logged-in users directly to the dashboard
    header('Location: dashboard.php'); // Ensure this matches your dashboard filename
    exit;
}

// --- Hardcoded Credentials ---
// NOTE: For better security, consider storing hashed passwords in a database.
// This is a simple implementation for demonstration.
$CORRECT_USERNAME = 'admin';
$CORRECT_PASSWORD = '123'; // Changed from 'password123' as per user's code
// ----------------------------------------------------

// --- Variable Initialization ---
$error_message = ''; // To store any login error messages

// --- Process Form Submission (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Retrieve username and password from the form ---
    // Basic sanitization could be added here if needed, though comparison protects against injection here.
    $username = $_POST['username'];
    $password = $_POST['password'];

    // --- Validate Credentials ---
    if ($username === $CORRECT_USERNAME && $password === $CORRECT_PASSWORD) {
        // --- Login Successful ---
        
        // Store login status and username in the session
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username; // Store username for potential future use (e.g., display)
        
        // Redirect the user to the main dashboard
        header('Location: dashboard.php'); // Ensure this matches your dashboard filename
        exit; // Terminate script execution after redirection
        
    } else {
        // --- Login Failed ---
        $error_message = "خطأ في اسم المستخدم أو كلمة المرور. حاول مرة أخرى.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Gold Star</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* --- General Styling --- */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #f0f4f8, #ffffff);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* Ensure body takes full viewport height */
        }
        
        /* --- Login Box Styling --- */
        .login-container {
            width: 350px;
            max-width: 100%; /* Responsive width */
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            text-align: center;
        }
        .login-container h1 {
            color: #8b4513; /* Academy theme color */
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 24px; /* Adjust font size */
        }
        
        /* --- Form Elements Styling --- */
        .form-group {
            margin-bottom: 18px;
            text-align: right; /* Align labels and inputs for RTL */
        }
        label {
            display: block;
            font-weight: bold;
            color: #333;
            margin-bottom: 6px;
        }
        input[type="text"],
        input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 100%;
            box-sizing: border-box;
            text-align: right; /* Input text alignment for RTL */
        }
        button {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            background-color: #8b4513; /* Academy theme color */
            color: white;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #a0522d; /* Slightly lighter shade on hover */
        }
        
        /* --- Error Message Styling --- */
        .error-message {
            background-color: #f8d7da; /* Light red background */
            color: #721c24; /* Dark red text */
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px; /* Adjust font size */
        }
    </style>
</head>
<body>
    
    <div class="login-container">
        <h1>تسجيل دخول لوحة التحكم</h1>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="admin_login.php" method="POST">
            <div class="form-group">
                <label for="username">اسم المستخدم:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">تسجيل الدخول</button>
        </form>
    </div>

</body>
</html>
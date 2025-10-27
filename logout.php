<?php
/**
 * Project: Gold Star Academy - Player Management System
 * Author: Ahmed Maher
 * Date: 27-10-2025
 *
 * File: logout.php
 * Description: Handles administrator logout by destroying the session and redirecting to the login page.
 */

// --- Start the session ---
// Required to access and modify session data
session_start(); 

// --- Clear Session Data ---
// Remove all session variables (e.g., 'loggedin', 'username')
session_unset(); 

// --- Destroy the Session ---
// Completely remove the session from the server
session_destroy(); 

// --- Redirect to Login Page ---
// Send the user back to the admin login page after logging out
header('Location: admin_login.php');
exit; // Ensure no further code is executed after redirection
?>
<?php
/**
 * Project: Gold Star Academy - Player Management System
 * Author: Ahmed Maher
 * Date: 27-10-2025
 *
 * File: success.php
 * Description: Displays a success message after successful player registration, showing the new player's ID.
 */

// --- Retrieve player code and name from URL parameters ---
// Sanitize input using htmlspecialchars to prevent XSS attacks
$code = isset($_GET['code']) ? htmlspecialchars($_GET['code']) : 'غير معروف';
$name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'لاعبنا الجديد';
?>
<!DOCTYPE html>
<html>
<head>
    <title>تم التسجيل بنجاح!</title>
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
            min-height: 100vh;
            flex-direction: column;
            text-align: center;
        }

        /* --- Content Container --- */
        .container {
            width: 450px;
            max-width: 100%; /* Ensure responsiveness on smaller screens */
            padding: 40px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            border-top: 5px solid #28a745; /* Green top border for success indication */
        }

        /* --- Typography --- */
        h1 {
            color: #28a745; /* Success green color */
            margin-top: 0;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            color: #333;
            line-height: 1.6;
        }

        /* --- Player Code Display --- */
        .player-code {
            font-size: 28px;
            font-weight: bold;
            color: #8b4513; /* Academy brown color */
            margin: 20px 0;
            display: block;
            border: 2px dashed #ccc;
            padding: 15px;
            border-radius: 8px;
        }

        /* --- Link/Button Styling --- */
        a {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            background-color: #8b4513; /* Academy brown color */
            color: white;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        a:hover {
            background-color: #a0522d; /* Slightly lighter brown on hover */
        }
    </style>
</head>
<body>
    
    <div class="container">
        <h1>✔ تم التسجيل بنجاح!</h1>
        <p>مرحباً بك يا <strong><?php echo $name; ?></strong> في أكاديمية جولد ستار.</p>
        <p>تم تسجيل بياناتك بنجاح. كود اللاعب الخاص بك هو:</p>
        
        <span class="player-code"><?php echo $code; ?></span>
        
        <p>برجاء الاحتفاظ بهذا الكود.</p>
        
        <a href="register.html">تسجيل لاعب جديد</a>
    </div>

</body>
</html>
<?php
/**
 * Project: Gold Star Academy - Player Management System
 * Author: Ahmed Maher
 * Date: 27-10-2025
 *
 * File: edit_player.php
 * Description: Handles both displaying the edit form for a player (GET) and processing the updated data (POST).
 */

// --- Development Settings (Show Errors) ---
// NOTE: Should be disabled in production environment for security.
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// --- Authentication Check ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// --- Database Connection Configuration ---
$db_host = "YOUR_HOST"; // 
$db_name = "YOUR_DB_NAME";
$db_user = "YOUR_USERNAME";
$db_pass = ""; // <--YOUR_PASSWORD
// Establish connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { 
    // Log error instead of dying in production
    die("Database Connection Failed: " . $conn->connect_error); 
}
$conn->set_charset("utf8mb4"); // Essential for Arabic characters

// --- Variable Initialization ---
$player = null; // Will hold player data fetched from DB
$message = ""; // For displaying success/error messages after POST
$player_id_to_fetch = null; // ID of the player to fetch/edit

/*
=====================================================
== Handle POST Request (Form Submission for Update) ==
=====================================================
*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['player_id'])) {

    // --- Retrieve and Sanitize Form Data ---
    $player_id = (int)$_POST['player_id']; // Cast to integer for security
    // TODO: Add more robust validation and sanitization for all inputs
    $playerName = $_POST['playerName'];
    $playerNID  = !empty($_POST['playerNID']) ? $_POST['playerNID'] : null;
    $playerDob  = $_POST['playerDob'];
    $age        = (int)$_POST['age']; // Cast to integer
    $gender     = $_POST['gender'];
    $phoneNumber= $_POST['phoneNumber'];
    $address    = $_POST['address'];
    $fatherName = $_POST['fatherName'];
    $fatherNID  = !empty($_POST['fatherNID']) ? $_POST['fatherNID'] : null;
    $fatherJob  = $_POST['fatherJob'];
    $motherName = $_POST['motherName'];
    $motherNID  = !empty($_POST['motherNID']) ? $_POST['motherNID'] : null;
    $motherJob  = $_POST['motherJob'];
    $sport      = $_POST['sport'];
    $beltDegree = $_POST['beltDegree'];
    $playerNumber = !empty($_POST['playerNumber']) ? $_POST['playerNumber'] : null;
    $subscriptionFee = $_POST['subscriptionFee'];
    
    // Default to the existing photo path
    $photoPath = $_POST['current_photo_path']; 

    // --- Handle Image Upload (if a new photo was submitted) ---
    // NOTE: Consider adding image resizing and validation (file type, size).
    if (isset($_FILES['playerPhoto']) && $_FILES['playerPhoto']['error'] == 0) {
        $upload_dir = 'uploads/'; // Ensure this directory exists and is writable
        if (!is_dir($upload_dir)) { 
            // Attempt to create directory if it doesn't exist
            mkdir($upload_dir, 0755, true); 
        }
        
        $file_info = pathinfo($_FILES['playerPhoto']['name']);
        $file_extension = isset($file_info['extension']) ? strtolower($file_info['extension']) : 'jpg';
        
        // Generate a unique filename using player ID to prevent overwrites
        $new_filename = $player_id . '.' . $file_extension;
        $destination = $upload_dir . $new_filename;

        // Move the uploaded file
        if (move_uploaded_file($_FILES['playerPhoto']['tmp_name'], $destination)) {
            // Delete the old photo if it exists and is different from the new one
            if (!empty($photoPath) && $photoPath !== 'N/A' && file_exists($photoPath) && $photoPath !== $destination) {
                @unlink($photoPath); // Use @ to suppress errors if deletion fails
            }
            $photoPath = $destination; // Update photo path to the new file
        } else {
            // Handle upload failure (optional: set error message)
            $message = "حدث خطأ أثناء رفع الصورة الجديدة."; 
            // Keep the old photo path if upload fails
            $photoPath = $_POST['current_photo_path']; 
        }
    }

    // --- Update Player Data in Database ---
    $sql = "UPDATE players SET 
        player_name = ?, player_nid = ?, player_dob = ?, age = ?, gender = ?, 
        phone_number = ?, address = ?, father_name = ?, father_nid = ?, father_job = ?, 
        mother_name = ?, mother_nid = ?, mother_job = ?, sport = ?, belt_degree = ?, 
        player_number = ?, subscription_fee = ?, player_photo = ?
        WHERE player_id = ?";
    
    // Prepare and bind parameters to prevent SQL injection
    $stmt = $conn->prepare($sql);
    // 's' = string, 'i' = integer, 'd' = double
    $stmt->bind_param("sssisissssssssssisi", 
        $playerName, $playerNID, $playerDob, $age, $gender, 
        $phoneNumber, $address, $fatherName, $fatherNID, $fatherJob,
        $motherName, $motherNID, $motherJob, $sport, $beltDegree,
        $playerNumber, $subscriptionFee, $photoPath,
        $player_id
    );
    
    // Execute the update
    if ($stmt->execute()) {
        $message = "تم تحديث البيانات بنجاح!";
    } else {
        // Provide specific error in development, generic in production
        $message = "خطأ أثناء التحديث: " . $stmt->error; 
    }
    $stmt->close();
    
    // Set the ID to fetch updated data for display
    $player_id_to_fetch = $player_id;

} 
/*
=====================================================
== Handle GET Request (Display Edit Form) ==
=====================================================
*/
else if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    // Player ID is present in the URL
    $player_id_to_fetch = (int)$_GET['id'];
} 
/*
=====================================================
== Handle Invalid Access (No ID provided) ==
=====================================================
*/
else {
    // If accessed directly without an ID
    die("خطأ: لم يتم تحديد اللاعب. الرجاء العودة للداشبورد والضغط على 'تعديل'.");
}

// --- Fetch Player Data (Executed for both GET and after successful POST) ---
// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM players WHERE player_id = ?");
$stmt->bind_param("i", $player_id_to_fetch);
$stmt->execute();
$result = $stmt->get_result();

// Check if player was found
if ($result->num_rows === 1) {
    $player = $result->fetch_assoc(); // Fetch player data into the $player array
} else {
    // Player ID doesn't exist in the database
    die("لم يتم العثور على اللاعب بالمعرف المحدد.");
}
$stmt->close();
$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html>
<head>
    <title>تعديل بيانات لاعب - <?php echo htmlspecialchars($player['player_name']); // Display player name in title ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* --- General & Layout --- */
        body { font-family: Arial, sans-serif; background: linear-gradient(to bottom, #f0f4f8, #ffffff); margin: 0; padding: 0 0 20px 0; display: flex; justify-content: center; align-items: center; flex-direction: column; }
        
        /* Containers for header and form */
        .form-container, .header-container {
            width: 100%;
            max-width: 550px;
            box-sizing: border-box;
        }
        
        /* Header section with title and back link */
        .header-container {
            padding: 20px;
            text-align: center;
        }
        .header-container h1 { color: #8b4513; margin: 0; }
        .header-container a { /* Back link styling */
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .header-container a:hover {
            background-color: #5a6268;
        }
        
        /* Form container styling */
        .form-container { 
            padding: 30px 20px; 
            background-color: #ffffff; 
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Success/Error message styling */
        .message {
            padding: 15px;
            background-color: #d4edda; /* Light green for success */
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        /* TODO: Add distinct styling for error messages (e.g., red background) */
        
        /* Responsive adjustments for larger screens */
        @media (min-width: 576px) {
            body { padding: 20px; } /* Restore body padding */
            .form-container { border-radius: 12px; padding: 30px; } /* Round corners and restore padding */
        }

        /* --- Form Elements --- */
        .form-group { margin-bottom: 18px; }
        label { display: block; width: auto; font-weight: bold; color: #333; margin-bottom: 6px; }
        input[type="text"], 
        input[type="number"], 
        input[type="tel"], 
        input[type="date"], 
        input[type="file"], 
        select { 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 6px; 
            width: 100%; 
            box-sizing: border-box; 
        }
        /* Remove number input spinners (optional aesthetic change) */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type=number] {
            -moz-appearance: textfield; /* Firefox */
        }

        /* Radio button group styling */
        .radio-group label { display: inline-block; font-weight: normal; margin-right: 15px; }
        .radio-group input { margin-right: 5px; }
        
        /* Submit button styling */
        button { padding: 12px 25px; border: none; border-radius: 6px; background-color: #28a745; /* Green color for update */ color: white; cursor: pointer; font-weight: bold; font-size: 16px; width: 100%; transition: background-color 0.3s; }
        button:hover { background-color: #218838; }
        
        /* Current image preview styling */
        .current-image { max-width: 100px; max-height: 100px; border-radius: 5px; margin-top: 10px; display: block; /* Center image */ margin-left: auto; margin-right: auto; }
    </style>
</head>
<body>
    
    <div class="header-container">
        <h1>تعديل بيانات: <?php echo htmlspecialchars($player['player_name']); ?></h1>
        <a href="dashboard.php">الرجوع إلى لوحة التحكم</a> 
    </div>

    <form action="edit_player.php" method="post" class="form-container" enctype="multipart/form-data">
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <input type="hidden" name="player_id" value="<?php echo $player['player_id']; ?>">
        <input type="hidden" name="current_photo_path" value="<?php echo htmlspecialchars($player['player_photo']); ?>">

        <div class="form-group">
            <label for="pName">الاسم:</label>
            <input type="text" id="pName" name="playerName" value="<?php echo htmlspecialchars($player['player_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>الصورة الحالية:</label>
            <?php if (!empty($player['player_photo']) && $player['player_photo'] !== 'N/A' && file_exists($player['player_photo'])): ?>
                <img src="<?php echo htmlspecialchars($player['player_photo']); ?>" alt="الصورة الحالية" class="current-image">
            <?php else: ?>
                <p style="text-align: center;">لا يوجد</p>
            <?php endif; ?>
            <label for="pPhoto" style="margin-top: 10px;">تغيير الصورة (اختياري):</label>
            <input type="file" id="pPhoto" name="playerPhoto" accept="image/*">
        </div>

        <div class="form-group">
            <label for="pNID">رقم قومي (اللاعب):</label>
            <input type="number" id="pNID" name="playerNID" value="<?php echo htmlspecialchars($player['player_nid']); ?>">
        </div>

        <div class="form-group">
            <label for="fName">اسم الأب:</label>
            <input type="text" id="fName" name="fatherName" value="<?php echo htmlspecialchars($player['father_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="fNID">رقم قومي (الأب):</label>
            <input type="number" id="fNID" name="fatherNID" value="<?php echo htmlspecialchars($player['father_nid']); ?>">
        </div>
        <div class="form-group">
            <label for="fJob">وظيفة الأب:</label>
            <input type="text" id="fJob" name="fatherJob" value="<?php echo htmlspecialchars($player['father_job']); ?>" required>
        </div>

        <div class="form-group">
            <label for="mName">اسم الأم:</label>
            <input type="text" id="mName" name="motherName" value="<?php echo htmlspecialchars($player['mother_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="mNID">رقم قومي (الأم):</label>
            <input type="number" id="mNID" name="motherNID" value="<?php echo htmlspecialchars($player['mother_nid']); ?>">
        </div>
        <div class="form-group">
            <label for="mJob">وظيفة الأم:</label>
            <input type="text" id="mJob" name="motherJob" value="<?php echo htmlspecialchars($player['mother_job']); ?>" required>
        </div>

        <div class="form-group">
            <label for="pAge">السن:</label>
            <input type="number" id="pAge" name="age" value="<?php echo $player['age']; ?>" required>
        </div>
        <div class="form-group">
            <label for="pDob">تاريخ الميلاد:</label>
            <input type="date" id="pDob" name="playerDob" value="<?php echo htmlspecialchars($player['player_dob']); ?>" required>
        </div>
        <div class="form-group">
            <label for="pPhone">التليفون:</label>
            <input type="tel" id="pPhone" name="phoneNumber" value="<?php echo htmlspecialchars($player['phone_number']); ?>" required>
        </div>
        <div class="form-group">
            <label for="pAddress">العنوان:</label>
            <input type="text" id="pAddress" name="address" value="<?php echo htmlspecialchars($player['address']); ?>" required>
        </div>
        <div class="form-group">
            <label for="pNumber">رقم اللاعب:</label>
            <input type="number" id="pNumber" name="playerNumber" value="<?php echo htmlspecialchars($player['player_number']); ?>">
        </div>

        <div class="form-group radio-group">
            <label>النوع:</label>
            <input type="radio" id="male" name="gender" value="male" <?php if($player['gender'] == 'male') echo 'checked'; ?> required>
            <label for="male">ذكر</label>
            <input type="radio" id="female" name="gender" value="female" <?php if($player['gender'] == 'female') echo 'checked'; ?> required>
            <label for="female">أنثى</label>
        </div>

        <div class="form-group">
            <label for="sport">الرياضة:</label>
            <input type="text" id="sport" name="sport" value="<?php echo htmlspecialchars($player['sport']); ?>" required>
        </div>
        <div class="form-group">
            <label for="belt">الحزام:</label>
            <select id="belt" name="beltDegree" required>
                <?php
                // Array of belt options
                $belts = ["أبيض", "أصفر 10", "أصفر 9", "برتقاني 8", "برتقاني 7", "أخضر 6", "أخضر 5", "أزرق 4", "أزرق 3", "بني 2", "بني 1", "أسود"];
                // Loop through options and mark the current player's belt as selected
                foreach ($belts as $belt) {
                    $selected = (trim($player['belt_degree']) == trim($belt)) ? 'selected' : '';
                    echo "<option value=\"$belt\" $selected>" . htmlspecialchars($belt) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="subFee">الاشتراك (ج):</label>
            <input type="number" id="subFee" name="subscriptionFee" value="<?php echo htmlspecialchars($player['subscription_fee']); ?>" required step="0.01"> </div>

        <button type="submit">تحديث البيانات</button>
        
    </form>

</body>
</html>
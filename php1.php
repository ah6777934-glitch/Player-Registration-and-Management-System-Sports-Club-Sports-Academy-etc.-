<?php
/**
 * Project: Gold Star Academy - Player Management System
 * Author: Ahmed Maher
 * Date: 27-10-2025
 *
 * File: process_form.php (CSV Version)
 * Description: Handles player registration form submission and saves data to a CSV file.
 * Includes basic validation for duplicate entries (name, phone, NID) and handles image uploads.
 * NOTE: This version uses CSV storage, which is suitable for small-scale use but not recommended for larger applications due to performance and data integrity limitations. Consider migrating to a database (like MySQL) for better scalability.
 */

// --- Development Settings (Show Errors) ---
// ini_set('display_errors', 1); // Uncomment during development to see errors
// error_reporting(E_ALL);    // Uncomment during development to see errors

// --- Set Content Type ---
// Ensure proper character encoding for Arabic content and error messages
header('Content-Type: text/html; charset=utf-8');

// --- Check Request Method ---
// Only process POST requests to prevent direct access
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. Retrieve and Sanitize Form Data ---
    // Use trim() to remove leading/trailing whitespace
    // TODO: Implement more robust sanitization (e.g., filter_input) for security.
    $new_playerName = trim($_POST['playerName']);
    $new_playerNID  = trim($_POST['playerNID']);
    $new_fatherName = trim($_POST['fatherName']);
    $new_fatherNID  = trim($_POST['fatherNID']);
    $new_fatherJob  = trim($_POST['fatherJob']);
    $new_motherName = trim($_POST['motherName']);
    $new_motherNID  = trim($_POST['motherNID']);
    $new_motherJob  = trim($_POST['motherJob']);
    $new_age        = trim($_POST['age']); // Consider calculating age from DOB
    $new_playerDob  = trim($_POST['playerDob']);
    $new_phoneNumber= trim($_POST['phoneNumber']);
    $new_address    = trim($_POST['address']);
    $new_playerNumber = trim($_POST['playerNumber']);
    $new_gender     = $_POST['gender'];
    $new_sport      = isset($_POST['sport']) ? $_POST['sport'] : 'Karate'; // Default sport
    $new_beltDegree = $_POST['beltDegree'];
    $new_subscriptionFee = trim($_POST['subscriptionFee']);

    
    // --- 2. Duplicate Check Configuration ---
    $filename = "data.csv"; // Name of the CSV data file
    $name_duplicate  = false; // Flag for duplicate full name
    $phone_duplicate = false; // Flag for duplicate phone with different father
    $nid_duplicate   = false; // Flag for duplicate player National ID

    // Check if player name is at least three parts
    $newNameParts = preg_split('/\s+/', $new_playerName);
    $isThreePartOrMore = (count($newNameParts) >= 3);


    // --- 3. Read Existing CSV and Perform Duplicate Checks ---
    // NOTE: Reading the entire file for checks can be inefficient for large files. Databases handle this much better.
    if (file_exists($filename) && is_readable($filename)) { // Check if file exists and is readable
        $file_handle_read = fopen($filename, 'r');
        if ($file_handle_read !== FALSE) {
            
            fgetcsv($file_handle_read); // Skip the header row

            // Loop through each row in the CSV
            while (($row = fgetcsv($file_handle_read)) !== FALSE) {
                
                // Get existing data, using null coalescing operator for safety
                $existing_name  = $row[1] ?? ''; // Player Name (Column Index 1)
                $existing_nid   = $row[3] ?? ''; // Player NID (Column Index 3)
                $existing_phone = $row[7] ?? ''; // Phone Number (Column Index 7 - Adjusted index)
                $existing_father_name = $row[9] ?? ''; // Father Name (Column Index 9 - Adjusted index)
                
                // --- Duplicate Name Check ---
                // Case-insensitive comparison if the new name has 3+ parts
                if ($isThreePartOrMore && strcasecmp($new_playerName, trim($existing_name)) == 0) {
                    $name_duplicate = true;
                }

                // --- Duplicate National ID Check ---
                // Check only if a new NID was provided
                if (!empty($new_playerNID) && $new_playerNID === trim($existing_nid)) {
                    $nid_duplicate = true;
                }

                // --- Duplicate Phone Number Check (with different father) ---
                // Check if phone numbers match but father names don't (case-insensitive)
                if ($new_phoneNumber === trim($existing_phone)) {
                    if (strcasecmp($new_fatherName, trim($existing_father_name)) != 0) {
                        $phone_duplicate = true;
                    }
                }

                // Exit loop early if any duplicate is found
                if ($name_duplicate || $phone_duplicate || $nid_duplicate) {
                    break;
                }
            } // End while loop
            fclose($file_handle_read); // Close the file handle
        } // End if file opened successfully
    } // End if file exists

    // --- 4. Display Error Messages If Duplicates Found ---
    if ($name_duplicate || $phone_duplicate || $nid_duplicate) {
        
        // Simple HTML structure for error display
        echo '<div style="text-align: center; font-family: Arial, sans-serif; margin-top: 50px; background: #fff; border: 2px solid red; padding: 20px; border-radius: 10px; width: 90%; max-width: 400px; margin-left: auto; margin-right: auto; box-sizing: border-box;">';
        echo '<h1>خطأ في التسجيل</h1>';
        
        // Display specific error messages
        if ($name_duplicate) {
            echo '<p style="font-size: 18px; color: red;">هذا الاسم الثلاثي مسجل بالفعل من قبل.</p>';
        }
        if ($nid_duplicate) {
            echo '<p style="font-size: 18px; color: red;">الرقم القومي لهذا اللاعب مسجل بالفعل.</p>';
        }
        if ($phone_duplicate) {
            echo '<p style="font-size: 18px; color: red;">رقم التليفون هذا مسجل باسم لاعب آخر (أب مختلف).</p>';
        }
        
        echo '<br>';
        // Link back to the registration form
        echo '<a href="register.html" style="padding: 10px 20px; background: #8b4513; color: white; text-decoration: none; border-radius: 5px;">الرجوع لصفحة التسجيل</a>';
        echo '</div>';
        
        exit; // Stop script execution
    } // End duplicate check error display


    // --- 5. If No Duplicates, Proceed with Saving ---

    // --- Generate New Player ID using a counter file ---
    // NOTE: This method is prone to race conditions under high load. Databases handle auto-increment IDs safely.
    $counter_file = 'id_counter.txt'; // File to store the next ID
    $new_id = 1; // Default starting ID if file doesn't exist

    if (file_exists($counter_file)) {
        $fp = fopen($counter_file, 'c+'); // Open for reading and writing; place pointer at the beginning
        if (flock($fp, LOCK_EX)) { // Lock the file exclusively to prevent race conditions
            $current_id_str = fgets($fp); // Read the current ID
            $current_id = (int)$current_id_str;
            $new_id = $current_id + 1; // Increment the ID
            
            ftruncate($fp, 0); // Clear the file content
            rewind($fp); // Move pointer back to the beginning
            fwrite($fp, (string)$new_id); // Write the new ID
            fflush($fp); // Ensure data is written to disk
            flock($fp, LOCK_UN); // Release the lock
        }
        fclose($fp);
    } else {
        // If counter file doesn't exist, create it with the starting ID
        file_put_contents($counter_file, (string)$new_id, LOCK_EX);
    }


    // --- Handle Image Upload (using the generated new_id) ---
    $upload_dir = 'uploads/'; // Directory for storing player photos
    $new_photoPath = 'N/A'; // Default value if no photo is uploaded

    // Ensure the uploads directory exists, create if not
    if (!is_dir($upload_dir)) {
        // Attempt to create the directory recursively with appropriate permissions
        mkdir($upload_dir, 0755, true); 
    }

    // Check if a file was uploaded successfully
    if (isset($_FILES['playerPhoto']) && $_FILES['playerPhoto']['error'] == UPLOAD_ERR_OK) { // Use UPLOAD_ERR_OK constant
        
        $file_info = pathinfo($_FILES['playerPhoto']['name']);
        // Ensure extension exists, default to 'jpg' otherwise
        $file_extension = isset($file_info['extension']) ? strtolower($file_info['extension']) : 'jpg'; 
        // TODO: Validate allowed file extensions (e.g., jpg, jpeg, png).

        // Create a unique filename based on the new player ID
        $new_filename = $new_id . '.' . $file_extension;
        $destination = $upload_dir . $new_filename;

        // Move the uploaded file to the destination directory
        if (move_uploaded_file($_FILES['playerPhoto']['tmp_name'], $destination)) {
            $new_photoPath = $destination; // Store the relative path
        } else {
             // Optional: Log or display an error if move_uploaded_file fails
             // echo "Error uploading file.";
        }
    } // End image upload handling

    // --- Prepare Data Row for CSV ---
    // Match the order defined in $header_row
    $data_row = [
        $new_id, 
        $new_playerName,
        $new_photoPath,  
        $new_playerNID,  
        $new_playerDob,  
        $new_age, 
        $new_gender,
        $new_phoneNumber, 
        $new_address,
        $new_fatherName,
        $new_fatherNID,  
        $new_fatherJob,
        $new_motherName, 
        $new_motherNID,  
        $new_motherJob,
        $new_sport, 
        $new_beltDegree, 
        $new_playerNumber, 
        $new_subscriptionFee
    ];
    
    // --- Define CSV Header Row ---
    // Ensure this order matches $data_row
    $header_row = [
        "Code", "Player Name", "Player Photo", "Player NID", "Date of Birth", "Age", "Gender",
        "Phone Number", "Address", 
        "Father Name", "Father NID", "Father's Job", 
        "Mother Name", "Mother NID", "Mother's Job",
        "Sport", "Belt Degree", "Player Number", "Subscription (EGP)"
    ];

    // --- Write Data to CSV File ---
    $file_exists = file_exists($filename);
    // Open file in append mode ('a')
    $file_handle_write = fopen($filename, 'a'); 

    if ($file_handle_write !== FALSE) {
        // If the file is new, write the header row first
        if (!$file_exists || filesize($filename) == 0) { // Also check filesize to handle empty files
             // Write UTF-8 BOM (Byte Order Mark) for Excel compatibility with Arabic
            fwrite($file_handle_write, "\xEF\xBB\xBF"); 
            fputcsv($file_handle_write, $header_row);
        }

        // Write the new player data row
        fputcsv($file_handle_write, $data_row);
        
        // Close the file handle
        fclose($file_handle_write);

        // --- Redirect to Success Page ---
        // Pass the new player ID and name in the URL
        header('Location: success.php?code=' . $new_id . '&name=' . urlencode($new_playerName));
        exit; // Terminate script execution after redirection

    } else {
        // Handle error if the file cannot be opened for writing
        die("Error: Could not open data file for writing. Check permissions.");
    } // End file writing block

} else {
    // --- Handle Direct Access (Non-POST Request) ---
    // Display an error message if someone tries to access this script directly
    // TODO: Redirect to the registration form instead of showing a plain error.
    echo "Error: You cannot access this page directly.";
} // End request method check
?>
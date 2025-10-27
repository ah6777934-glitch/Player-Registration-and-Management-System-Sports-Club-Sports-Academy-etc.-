<?php
/**
 * Project: Gold Star Academy - Player Management System
 * Author: Ahmed Maher
 * Date: 27-10-2025
 *
 * File: dashboard.php
 * Description: Main administrative dashboard for viewing, searching, editing, and deleting player records.
 * Connects to the database, handles search queries, and displays player data in a table.
 */

session_start(); // Start the session to manage login state

// --- Authentication Check ---
// Redirect to login page if the user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: admin_login.php');
    exit; // Stop script execution after redirection
}

/*
=====================================================
== (1) Database Connection Configuration ==
=====================================================
*/
$db_host = "YOUR_HOST"; // 
$db_name = "YOUR_DB_NAME";
$db_user = "YOUR_USERNAME";
$db_pass = ""; // <--  YOUR_PASSWORD
// Establish connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection for errors
if ($conn->connect_error) {
    // Log error in production instead of dying
    // error_log("Database Connection Failed: " . $conn->connect_error);
    die("Database Connection Failed: " . $conn->connect_error);
}
// Set character set to UTF-8 for proper Arabic support
$conn->set_charset("utf8mb4");

/*
=====================================================
== (2) Handle Search Query (GET Request) ==
=====================================================
*/
$search_term = ""; // Variable to store the search term entered by the user
$sql = "SELECT * FROM players"; // Base SQL query to select all players

// Check if a search term was submitted via GET request
if (isset($_GET['search']) && !empty(trim($_GET['search']))) { // Trim to ignore whitespace searches
    $search_term = trim($_GET['search']);
    $search_like = '%' . $search_term . '%'; // Prepare the search term for LIKE comparison

    // Modify SQL query to include WHERE clause for searching by name or ID
    $sql .= " WHERE player_name LIKE ? OR player_id LIKE ?"; // Use prepared statements to prevent SQL injection
    $sql .= " ORDER BY player_id DESC"; // Order results, newest first
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    // Bind the search term parameter (ss = two strings)
    $stmt->bind_param("ss", $search_like, $search_like);

} else {
    // If no search term, fetch all players, ordered by newest first
    $sql .= " ORDER BY player_id DESC";
    $stmt = $conn->prepare($sql);
}

// Execute the prepared statement
if ($stmt) {
    $stmt->execute();
    // Get the result set
    $result = $stmt->get_result();
} else {
    // Handle potential errors during statement preparation
    die("Error preparing SQL statement: " . $conn->error);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - بيانات اللاعبين</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* --- General Styling --- */
        body { font-family: Arial, sans-serif; background-color: #f0f4f8; margin: 0; padding: 20px; padding-top: 60px; /* Add padding for logout button */ }
        h1 { color: #8b4513; }
        
        /* Logout Link Styling */
        .logout-link { position: absolute; top: 20px; right: 20px; background: #D90000; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; font-size: 14px; }

        /* --- Table Styling --- */
        .table-container { width: 100%; overflow-x: auto; background: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: right; white-space: nowrap; /* Prevent text wrapping in cells */ }
        th { background-color: #8b4513; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; /* Zebra striping */ }
        tr:hover { background-color: #f1f1f1; /* Highlight row on hover */ }
        td img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; vertical-align: middle; /* Align image nicely */ }

        /* --- Search Bar Styling --- */
        .search-container { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; display: flex; flex-wrap: wrap; /* Allow wrapping on small screens */ gap: 10px; }
        .search-container form { display: flex; flex-grow: 1; /* Make form take available space */ gap: 10px; min-width: 250px; /* Prevent form from becoming too small */ }
        .search-container input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .search-container button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .search-container a { /* "Show All" link */ padding: 10px 15px; background-color: #6c757d; color: white; border-radius: 5px; text-decoration: none; display: flex; align-items: center; white-space: nowrap; }

        /* --- Action Buttons (Edit/Delete) --- */
        .action-btn { padding: 5px 10px; text-decoration: none; color: white !important; /* Ensure white text */ border-radius: 4px; font-size: 12px; margin: 0 2px; display: inline-block; /* Ensure buttons align properly */ }
        .action-btn.edit { background-color: #28a745; }
        .action-btn.delete { background-color: #dc3545; }
        .action-btn.edit:hover { background-color: #218838; }
        .action-btn.delete:hover { background-color: #c82333; }

        /* --- Print Styles --- */
        @media print {
            /* Hide elements not needed for printing */
            .logout-link, .print-button, .search-container, .action-btn, th:last-child, td:last-child { display: none; }
            body { padding: 0; margin: 0; } /* Remove padding for print */
            .table-container { box-shadow: none; margin-top: 0; overflow-x: visible; border: none; } /* Remove shadow/border */
            table { font-size: 10px; width: 100%; border: 1px solid #ccc; } /* Add border for print */
            td, th { padding: 5px; border: 1px solid #ccc; } /* Adjust padding and borders */
            tr { page-break-inside: avoid; /* Try to keep rows on the same page */ }
            td img { display: none; } /* Hide images in print */
            h1 { font-size: 18pt; text-align: center; margin-bottom: 10px; } /* Adjust heading for print */
        }
    </style>
</head>
<body>
    <a href="logout.php" class="logout-link">تسجيل الخروج</a>
    
    <h1>لوحة التحكم: بيانات اللاعبين المسجلين</h1>

    <button onclick="window.print()" class="print-button" style="background-color: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; margin-bottom: 20px;">طباعة الصفحة 🖨️</button>

    <div class="search-container">
        <form action="dashboard.php" method="GET">
            <input type="text" name="search" placeholder="ابحث بالاسم أو الكود..." value="<?php echo htmlspecialchars($search_term); // Display current search term ?>">
            <button type="submit">بحث</button>
        </form>
        <a href="dashboard.php">عرض الكل</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>الكود</th>
                    <th>الاسم</th>
                    <th>الصورة</th>
                    <th>رقم قومي (اللاعب)</th>
                    <th>تاريخ الميلاد</th>
                    <th>السن</th>
                    <th>النوع</th>
                    <th>التليفون</th>
                    <th>العنوان</th>
                    <th>اسم الأب</th>
                    <th>رقم قومي (الأب)</th>
                    <th>وظيفة الأب</th>
                    <th>اسم الأم</th>
                    <th>رقم قومي (الأم)</th>
                    <th>وظيفة الأم</th>
                    <th>الرياضة</th>
                    <th>الحزام</th>
                    <th>رقم اللاعب</th>
                    <th>الاشتراك (ج)</th>
                    <th>تاريخ التسجيل</th>
                    <th>إجراءات</th> 
                </tr>
            </thead>
            <tbody>
                <?php 
                // Check if there are any results
                if ($result && $result->num_rows > 0): 
                    // Loop through each player record found
                    while($row = $result->fetch_assoc()): 
                ?>
                        <tr>
                            <td><?php echo $row['player_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['player_name']); ?></td>
                            <td>
                                <?php 
                                // Display image thumbnail if a valid path exists
                                if (!empty($row['player_photo']) && $row['player_photo'] !== 'N/A' && file_exists($row['player_photo'])): 
                                ?>
                                    <a href="<?php echo htmlspecialchars($row['player_photo']); ?>" target="_blank" title="عرض الصورة بالحجم الكامل">
                                        <img src="<?php echo htmlspecialchars($row['player_photo']); ?>" alt="صورة اللاعب <?php echo htmlspecialchars($row['player_name']); ?>">
                                    </a>
                                <?php else: ?>
                                    لا يوجد
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['player_nid']); ?></td>
                            <td><?php echo htmlspecialchars($row['player_dob']); ?></td>
                            <td><?php echo $row['age']; ?></td>
                            <td><?php echo htmlspecialchars($row['gender']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['father_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['father_nid']); ?></td>
                            <td><?php echo htmlspecialchars($row['father_job']); ?></td>
                            <td><?php echo htmlspecialchars($row['mother_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['mother_nid']); ?></td>
                            <td><?php echo htmlspecialchars($row['mother_job']); ?></td>
                            <td><?php echo htmlspecialchars($row['sport']); ?></td>
                            <td><?php echo htmlspecialchars($row['belt_degree']); ?></td>
                            <td><?php echo htmlspecialchars($row['player_number']); ?></td>
                            <td><?php echo htmlspecialchars(number_format((float)$row['subscription_fee'], 2)); // Format currency ?></td>
                            <td><?php echo date('Y-m-d', strtotime($row['registration_date'])); // Format date ?></td>
                            
                            <td>
                                <a href="edit_player.php?id=<?php echo $row['player_id']; ?>" class="action-btn edit">تعديل</a>
                                <a href="delete_player.php?id=<?php echo $row['player_id']; ?>" class="action-btn delete" onclick="return confirm('هل أنت متأكد من حذف هذا اللاعب؟ سيتم مسح بياناته وصورته نهائياً.');">حذف</a>
                                </td>
                        </tr>
                <?php 
                    endwhile; // End of player loop
                else: // If no players found
                ?>
                    <tr>
                        <td colspan="21" style="text-align: center;">
                            <?php if (!empty($search_term)): ?>
                                لا توجد نتائج للبحث عن "<?php echo htmlspecialchars($search_term); ?>".
                            <?php else: ?>
                                لا يوجد أي بيانات مسجلة حتى الآن.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; // End of result check ?>
            </tbody>
        </table>
    </div> </body>
</html>
<?php
// Close the statement and the connection
if ($stmt) $stmt->close();
if ($conn) $conn->close();
?>
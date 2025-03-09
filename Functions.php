<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Establish PDO connection
    $pdo = new PDO("mysql:host=localhost;dbname=MobileShop_byme", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable error mode
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Fetch data as associative array
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Insert new seller
if (isset($_POST['Save'])) {
    // Get form data
    $sname = $_POST['SNameTb'];
    $sgender = $_POST['SGenCb']; // Corrected from SGenderTb
    $sphone = $_POST['SPhoneTb'];
    $saddress = $_POST['SAddressTb']; // Corrected from SAdressTb
    $semail = $_POST['SEmailTb'];
    $spassword = password_hash($_POST['SPasswordTb'], PASSWORD_DEFAULT); // Hashing password

    try {
        // Prepared statement for secure insertion
        $stmt = $pdo->prepare("INSERT INTO sellers (sell_name, sell_gender, sell_phone, sell_adress, sell_email, sell_password) 
                               VALUES (:sname, :sgender, :sphone, :saddress, :semail, :spassword)");
        
        // Execute the query
        $stmt->execute([
            ':sname' => $sname,
            ':sgender' => $sgender,
            ':sphone' => $sphone,
            ':saddress' => $saddress,
            ':semail' => $semail,
            ':spassword' => $spassword
        ]);

        // Redirect to Sellers.php after successful insertion
        header("Location: Sellers.php");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

$pdo = null; // Close connection
?>

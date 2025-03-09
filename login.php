<?php
session_start();

// Check if already logged in
if (isset($_SESSION['seller_id'])) {
    header("Location: Items.php");
    exit();
}

// Establish PDO connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=MobileShop_byme", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$error = "";

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM sellers WHERE sell_email = ? AND sell_password = ?");
        $stmt->execute([$email, $password]);
        $seller = $stmt->fetch();
        
        if ($seller) {
            // Login successful
            $_SESSION['seller_id'] = $seller['sell_id'];
            $_SESSION['seller_name'] = $seller['sell_name'];
            
            header("Location: Items.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } catch (PDOException $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Login - Mobile Shop</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="assets/lib/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/styles.css" />
  </head>
  <body class="bg-light">
    <div class="container">
      <div class="row justify-content-center mt-5">
        <div class="col-md-6">
          <div class="card shadow">
            <div class="card-header bg-warning text-center">
              <h3>Mobile Shop Login</h3>
            </div>
            <div class="card-body">
              <?php if($error): ?>
              <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
              </div>
              <?php endif; ?>
              
              <form method="POST" action="login.php">
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                  <button type="submit" class="btn btn-primary">Login</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
<?php
session_start();

// Establish PDO connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=MobileShop_byme", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle Delete Request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM sellers WHERE sell_id = ?");
        $stmt->execute([$id]);
        header("Location: sellers.php");
        exit();
    } catch (PDOException $e) {
        die("Delete failed: " . $e->getMessage());
    }
}

// Initialize variables for edit form
$editMode = false;
$editData = [
    'sell_id' => '',
    'sell_name' => '',
    'sell_gender' => '',
    'sell_phone' => '',
    'sell_adress' => '',
    'sell_email' => '',
    'sell_password' => ''
];

// Handle Edit Request
if (isset($_GET['edit'])) {
    $editMode = true;
    $id = $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM sellers WHERE sell_id = ?");
        $stmt->execute([$id]);
        $editData = $stmt->fetch();
        if (!$editData) {
            $editMode = false;
        }
    } catch (PDOException $e) {
        die("Edit fetch failed: " . $e->getMessage());
    }
}

// Fetch sellers data
try {
    $stmt = $pdo->query("SELECT * FROM sellers");
    $sellers = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching sellers: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Sellers</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    
    <link rel="stylesheet" href="assets/lib/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/styles.css" />
  </head>
  <body>
    <?php require_once 'Functions.php'; ?>

    <div class="container-fluid">
      <div class="row" style="height: 695px">
        <div class="col-md-2" style="background-color: #198754">
          <ul class="nav flex-column">
            <div class="row">
              <div class="col">
                <img src="assets/images/phone_photo.png" alt="" height="110px">
              </div>
            </div>
            <li class="nav-item">
              <a class="nav-link text-light" href="Items.php">Items</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-light" href="categories.php">Categories</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-light" href="sellers.php">Sellers</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-light" href="billing.php">Billing</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-light" href="logout.php">Logout</a>
            </li>
          </ul>
        </div>
        <div class="col-md-10">
          <div class="row">
            <div class="col">
              <div class="row">
                <div class="col bg-warning">
                  <h3 class="text-center"><?= $editMode ? 'Edit Seller' : 'Manage Sellers' ?></h3>
                </div>
              </div>
              <div class="row">
                <!-- Enter Details Here -->
                <form class="row g-3" action="Functions.php" method="POST">
                  <?php if($editMode): ?>
                  <input type="hidden" name="SellerId" value="<?= htmlspecialchars($editData['sell_id']) ?>">
                  <?php endif; ?>
                  
                  <div class="col-md-4">
                    <label for="SNameTb" class="form-label">Seller Name</label>
                    <input type="text" class="form-control" name="SNameTb" placeholder="Enter Seller's Name" value="<?= $editMode ? htmlspecialchars($editData['sell_name']) : '' ?>" required />
                  </div>
                  <div class="col-md-4">
                    <label for="inputState" class="form-label">Gender</label>
                    <select name="SGenCb" class="form-select">
                      <option <?= (!$editMode || $editData['sell_gender'] == 'Your Gender') ? 'selected' : '' ?>>Your Gender</option>
                      <option <?= ($editMode && $editData['sell_gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                      <option <?= ($editMode && $editData['sell_gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label for="inputEmail4" class="form-label">Seller's Phone</label>
                    <input type="text" class="form-control" name="SPhoneTb" placeholder="Enter Seller's Phone" value="<?= $editMode ? htmlspecialchars($editData['sell_phone']) : '' ?>" />
                  </div>
                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Address</label>
                    <input type="text" class="form-control" name="SAddressTb" placeholder="Enter Seller's Address" value="<?= $editMode ? htmlspecialchars($editData['sell_adress']) : '' ?>" required />
                  </div>
                  <div class="col-md-6">
                    <label for="SNameTb" class="form-label">Seller Email</label>
                    <input type="email" class="form-control" name="SEmailTb" placeholder="Enter Seller's Email" value="<?= $editMode ? htmlspecialchars($editData['sell_email']) : '' ?>" required />
                  </div>
                  <div class="col-md-6">
                    <label for="PasswordTb" class="form-label">Seller Password</label>
                    <input type="password" class="form-control" name="SPasswordTb" placeholder="<?= $editMode ? 'Leave blank to keep current password' : 'Enter Seller\'s Password' ?>" <?= $editMode ? '' : 'required' ?> />
                  </div>
                  <div class="col-6 d-grid">
                    <button type="submit" name="<?= $editMode ? 'Update' : 'Save' ?>" class="btn btn-warning"><?= $editMode ? 'Update Seller' : 'Add New Seller' ?></button>
                  </div>
                  <?php if($editMode): ?>
                  <div class="col-6 d-grid">
                    <a href="sellers.php" class="btn btn-secondary">Cancel</a>
                  </div>
                  <?php endif; ?>
                </form>
              </div>
              <div class="row">
                <!-- Display List Here -->
                <table class="table table-bordered mt-2">
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Email</th>
                    <th>Actions</th>
                  </tr>
                  <?php foreach ($sellers as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['sell_id']) ?></td>
                    <td><?= htmlspecialchars($row['sell_name']) ?></td>
                    <td><?= htmlspecialchars($row['sell_gender']) ?></td>
                    <td><?= htmlspecialchars($row['sell_phone']) ?></td>
                    <td><?= htmlspecialchars($row['sell_adress']) ?></td>
                    <td><?= htmlspecialchars($row['sell_email']) ?></td>
                    <td>
                      <a href="sellers.php?edit=<?= htmlspecialchars($row['sell_id']) ?>" class="btn btn-success">Edit</a>
                      <a href="sellers.php?delete=<?= htmlspecialchars($row['sell_id']) ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this seller?')">Delete</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>


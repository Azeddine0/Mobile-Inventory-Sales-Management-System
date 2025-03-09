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
        // Check if category is used in items table
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE it_cat = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error = "Cannot delete category because it is used by $count item(s)";
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE cat_id = ?");
            $stmt->execute([$id]);
            header("Location: Categories.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Delete failed: " . $e->getMessage());
    }
}

// Initialize variables for edit form
$editMode = false;
$editData = [
    'cat_id' => '',
    'cat_name' => '',
    'cat_desc' => ''
];

// Handle Edit Request
if (isset($_GET['edit'])) {
    $editMode = true;
    $id = $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE cat_id = ?");
        $stmt->execute([$id]);
        $editData = $stmt->fetch();
        if (!$editData) {
            $editMode = false;
        }
    } catch (PDOException $e) {
        die("Edit fetch failed: " . $e->getMessage());
    }
}

// Handle Save/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['CatNameTb'];
    $desc = $_POST['CatDescTb'];
    
    try {
        if (isset($_POST['Update'])) {
            // Update existing category
            $id = $_POST['CatId'];
            $stmt = $pdo->prepare("UPDATE categories SET cat_name = ?, cat_desc = ? WHERE cat_id = ?");
            $stmt->execute([$name, $desc, $id]);
        } else {
            // Get the next available ID
            $stmt = $pdo->query("SELECT MAX(cat_id) FROM categories");
            $maxId = $stmt->fetchColumn();
            $newId = ($maxId === null) ? 1 : $maxId + 1;
            
            // Add new category
            $stmt = $pdo->prepare("INSERT INTO categories (cat_id, cat_name, cat_desc) VALUES (?, ?, ?)");
            $stmt->execute([$newId, $name, $desc]);
        }
        header("Location: Categories.php");
        exit();
    } catch (PDOException $e) {
        die("Operation failed: " . $e->getMessage());
    }
}

// Fetch categories data
try {
    $stmt = $pdo->query("SELECT * FROM categories");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Categories</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="assets/lib/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/styles.css" />
  </head>
  <body>
    <div class="container-fluid">
      <div class="row" style="height: 695px">
        <div class="col-md-2" style="background-color: #198754">
          <ul class="nav flex-column">
            <div class="row">
              <div class="col">
                <img src="assets/images/phone_photo.png" style="height: 100px"/>
              </div>
            </div>
            <li class="nav-item">
              <a class="nav-link text-light" href="Items.php">Items</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-light" href="Categories.php">Categories</a>
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
                  <h3 class="text-center"><?= $editMode ? 'Edit Category' : 'Manage Categories' ?></h3>
                </div>
              </div>
              
              <?php if(isset($error)): ?>
              <div class="alert alert-danger mt-2">
                <?= htmlspecialchars($error) ?>
              </div>
              <?php endif; ?>
              
              <div class="row mt-3">
                <!-- Enter Details Here -->
                <form class="row g-3" method="POST" action="Categories.php">
                  <?php if($editMode): ?>
                  <input type="hidden" name="CatId" value="<?= htmlspecialchars($editData['cat_id']) ?>">
                  <?php endif; ?>
                  
                  <div class="col-md-4">
                    <label for="CatNameTb" class="form-label">Category Name</label>
                    <input type="text" class="form-control" name="CatNameTb" 
                           placeholder="Enter Category's Name" 
                           value="<?= $editMode ? htmlspecialchars($editData['cat_name']) : '' ?>" 
                           required />
                  </div>
                  
                  <div class="col-8">
                    <label for="CatDescTb" class="form-label">Description</label>
                    <input type="text" class="form-control" name="CatDescTb" 
                           placeholder="Enter Category's Description" 
                           value="<?= $editMode ? htmlspecialchars($editData['cat_desc']) : '' ?>" 
                           required />
                  </div>

                  <div class="col-6 d-grid">
                    <button type="submit" name="<?= $editMode ? 'Update' : 'Save' ?>" class="btn btn-warning">
                      <?= $editMode ? 'Update Category' : 'Add New Category' ?>
                    </button>
                  </div>
                  
                  <?php if($editMode): ?>
                  <div class="col-6 d-grid">
                    <a href="Categories.php" class="btn btn-secondary">Cancel</a>
                  </div>
                  <?php endif; ?>
                </form>
              </div>
              <div class="row mt-4">
                <!-- Display List Here -->
                <table class="table table-bordered">
                  <thead class="table-dark">
                    <tr>
                      <th>ID</th>
                      <th>Category Name</th>
                      <th>Description</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($categories as $row): ?>
                    <tr>
                      <td><?= htmlspecialchars($row['cat_id']) ?></td>
                      <td><?= htmlspecialchars($row['cat_name']) ?></td>
                      <td><?= htmlspecialchars($row['cat_desc']) ?></td>
                      <td>
                        <a href="Categories.php?edit=<?= htmlspecialchars($row['cat_id']) ?>" class="btn btn-success btn-sm">Edit</a>
                        <a href="Categories.php?delete=<?= htmlspecialchars($row['cat_id']) ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>

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
        // Check if item is used in any bills (you might need to adjust this if you have a bill_items table)
        $stmt = $pdo->prepare("DELETE FROM items WHERE it_id = ?");
        $stmt->execute([$id]);
        header("Location: Items.php");
        exit();
    } catch (PDOException $e) {
        die("Delete failed: " . $e->getMessage());
    }
}

// Initialize variables for edit form
$editMode = false;
$editData = [
    'it_id' => '',
    'it_name' => '',
    'it_cat' => '',
    'it_qty' => '',
    'it_price' => ''
];

// Handle Edit Request
if (isset($_GET['edit'])) {
    $editMode = true;
    $id = $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM items WHERE it_id = ?");
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
    $name = $_POST['ItNameTb'];
    $category = $_POST['CatCb'];
    $quantity = $_POST['ItQtyTb'];
    $price = $_POST['ItPriceTb'];
    
    try {
        if (isset($_POST['Update'])) {
            // Update existing item
            $id = $_POST['ItemId'];
            $stmt = $pdo->prepare("UPDATE items SET it_name = ?, it_cat = ?, it_qty = ?, it_price = ? WHERE it_id = ?");
            $stmt->execute([$name, $category, $quantity, $price, $id]);
        } else {
            // Get the next available ID
            $stmt = $pdo->query("SELECT MAX(it_id) FROM items");
            $maxId = $stmt->fetchColumn();
            $newId = ($maxId === null) ? 1 : $maxId + 1;
            
            // Add new item
            $stmt = $pdo->prepare("INSERT INTO items (it_id, it_name, it_cat, it_qty, it_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$newId, $name, $category, $quantity, $price]);
        }
        header("Location: Items.php");
        exit();
    } catch (PDOException $e) {
        die("Operation failed: " . $e->getMessage());
    }
}

// Fetch items data with category names
try {
    $stmt = $pdo->query("SELECT i.*, c.cat_name 
                         FROM items i 
                         JOIN categories c ON i.it_cat = c.cat_id");
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching items: " . $e->getMessage());
}

// Fetch categories for dropdown
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
    <title>Items</title>
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
                <img
                  src="assets/images/phone_photo.png"
                  alt=""
                  height="110px"
                />
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
                  <h3 class="text-center"><?= $editMode ? 'Edit Item' : 'Manage Shop\'s Items' ?></h3>
                </div>
              </div>
              <div class="row mt-3">
                <!-- Enter Details Here -->
                <form class="row g-3" method="POST" action="Items.php">
                  <?php if($editMode): ?>
                  <input type="hidden" name="ItemId" value="<?= htmlspecialchars($editData['it_id']) ?>">
                  <?php endif; ?>
                  
                  <div class="col-md-3">
                    <label for="ItNameTb" class="form-label">Item's Name</label>
                    <input
                      type="text"
                      class="form-control"
                      name="ItNameTb"
                      placeholder="Enter Item's Name"
                      value="<?= $editMode ? htmlspecialchars($editData['it_name']) : '' ?>"
                      required
                    />
                  </div>
                  <div class="col-md-3">
                    <label for="CatCb" class="form-label">Category</label>
                    <select name="CatCb" class="form-select" required>
                      <option value="">Select Category</option>
                      <?php foreach ($categories as $category): ?>
                      <option value="<?= htmlspecialchars($category['cat_id']) ?>" 
                              <?= ($editMode && $editData['it_cat'] == $category['cat_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['cat_name']) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label for="ItQtyTb" class="form-label">Item's Quantity</label>
                    <input
                      type="number"
                      class="form-control"
                      name="ItQtyTb"
                      placeholder="Enter Quantity"
                      value="<?= $editMode ? htmlspecialchars($editData['it_qty']) : '' ?>"
                      required
                      min="0"
                    />
                  </div>
                  <div class="col-3">
                    <label for="ItPriceTb" class="form-label">Price</label>
                    <input
                      type="number"
                      class="form-control"
                      name="ItPriceTb"
                      placeholder="Enter Price"
                      value="<?= $editMode ? htmlspecialchars($editData['it_price']) : '' ?>"
                      required
                      min="0"
                    />
                  </div>
                  <div class="col-6 d-grid">
                    <button type="submit" name="<?= $editMode ? 'Update' : 'Save' ?>" class="btn btn-warning">
                      <?= $editMode ? 'Update Item' : 'Add New Item' ?>
                    </button>
                  </div>
                  
                  <?php if($editMode): ?>
                  <div class="col-6 d-grid">
                    <a href="Items.php" class="btn btn-secondary">Cancel</a>
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
                      <th>Item Name</th>
                      <th>Category</th>
                      <th>Quantity</th>
                      <th>Price</th>
                      <th>Total Value</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($items as $row): ?>
                    <tr>
                      <td><?= htmlspecialchars($row['it_id']) ?></td>
                      <td><?= htmlspecialchars($row['it_name']) ?></td>
                      <td><?= htmlspecialchars($row['cat_name']) ?></td>
                      <td><?= htmlspecialchars($row['it_qty']) ?></td>
                      <td>$<?= htmlspecialchars($row['it_price']) ?></td>
                      <td>$<?= htmlspecialchars($row['it_qty'] * $row['it_price']) ?></td>
                      <td>
                        <a href="Items.php?edit=<?= htmlspecialchars($row['it_id']) ?>" class="btn btn-success btn-sm">Edit</a>
                        <a href="Items.php?delete=<?= htmlspecialchars($row['it_id']) ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="5" class="text-end fw-bold">Total Inventory Value:</td>
                      <td colspan="2" class="fw-bold">
                        $<?php
                          $total = 0;
                          foreach ($items as $item) {
                            $total += $item['it_qty'] * $item['it_price'];
                          }
                          echo htmlspecialchars($total);
                        ?>
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>

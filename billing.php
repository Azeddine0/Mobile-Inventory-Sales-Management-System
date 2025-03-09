<?php
session_start();

// Check if user is logged in (adjust this based on your authentication system)
if (!isset($_SESSION['seller_id'])) {
    header("Location: login.php");
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

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding item to cart
if (isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    
    // Get item details
    $stmt = $pdo->prepare("SELECT * FROM items WHERE it_id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
    
    if ($item) {
        // Check if quantity is available
        if ($quantity > $item['it_qty']) {
            $error = "Only " . $item['it_qty'] . " items available in stock";
        } else {
            // Add to cart
            if (isset($_SESSION['cart'][$item_id])) {
                $_SESSION['cart'][$item_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$item_id] = [
                    'id' => $item['it_id'],
                    'name' => $item['it_name'],
                    'price' => $item['it_price'],
                    'quantity' => $quantity
                ];
            }
        }
    }
}

// Handle removing item from cart
if (isset($_GET['remove'])) {
    $item_id = $_GET['remove'];
    if (isset($_SESSION['cart'][$item_id])) {
        unset($_SESSION['cart'][$item_id]);
    }
    header("Location: Billing.php");
    exit();
}

// Handle checkout
if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Get the next available bill ID
        $stmt = $pdo->query("SELECT MAX(b_number) FROM bill");
        $maxId = $stmt->fetchColumn();
        $billId = ($maxId === null) ? 1 : $maxId + 1;
        
        // Calculate total amount
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Create new bill
        $date = date('Y-m-d');
        $sellerId = $_SESSION['seller_id'];
        
        $stmt = $pdo->prepare("INSERT INTO bill (b_number, b_date, seller, amount) VALUES (?, ?, ?, ?)");
        $stmt->execute([$billId, $date, $sellerId, $total]);
        
        // Update inventory
        foreach ($_SESSION['cart'] as $item) {
            $stmt = $pdo->prepare("UPDATE items SET it_qty = it_qty - ? WHERE it_id = ?");
            $stmt->execute([$item['quantity'], $item['id']]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Clear cart
        $_SESSION['cart'] = [];
        $success = "Bill #$billId created successfully!";
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $error = "Checkout failed: " . $e->getMessage();
    }
}

// Fetch all items for selection
try {
    $stmt = $pdo->query("SELECT i.*, c.cat_name 
                         FROM items i 
                         JOIN categories c ON i.it_cat = c.cat_id
                         WHERE i.it_qty > 0
                         ORDER BY i.it_name");
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching items: " . $e->getMessage());
}

// Fetch recent bills
try {
    $stmt = $pdo->query("SELECT b.*, s.sell_name 
                         FROM bill b 
                         JOIN sellers s ON b.seller = s.sell_id
                         ORDER BY b.b_number DESC
                         LIMIT 10");
    $bills = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching bills: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Billing</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="assets/lib/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/styles.css" />
  </head>
  <body>
    <div class="container-fluid">
      <div class="row" style="min-height: 695px">
        <div class="col-md-2" style="background-color: #198754">
          <ul class="nav flex-column">
            <div class="row">
              <div class="col">
                <img src="assets/images/4058000_dollar_finance_money_icon.png" style="height: 100px"/>
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
                  <h3 class="text-center">Billing System</h3>
                </div>
              </div>
              
              <?php if(isset($error)): ?>
              <div class="alert alert-danger mt-2">
                <?= htmlspecialchars($error) ?>
              </div>
              <?php endif; ?>
              
              <?php if(isset($success)): ?>
              <div class="alert alert-success mt-2">
                <?= htmlspecialchars($success) ?>
              </div>
              <?php endif; ?>
              
              <div class="row mt-3">
                <div class="col-md-6">
                  <!-- Add Items to Cart -->
                  <div class="card">
                    <div class="card-header bg-primary text-white">
                      <h5>Add Items to Bill</h5>
                    </div>
                    <div class="card-body">
                      <form method="POST" action="Billing.php">
                        <div class="mb-3">
                          <label for="item_id" class="form-label">Select Item</label>
                          <select name="item_id" class="form-select" required>
                            <option value="">Choose an item</option>
                            <?php foreach ($items as $item): ?>
                            <option value="<?= htmlspecialchars($item['it_id']) ?>">
                              <?= htmlspecialchars($item['it_name']) ?> - 
                              $<?= htmlspecialchars($item['it_price']) ?> 
                              (<?= htmlspecialchars($item['it_qty']) ?> in stock)
                            </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label for="quantity" class="form-label">Quantity</label>
                          <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                      </form>
                    </div>
                  </div>
                  
                  <!-- Recent Bills -->
                  <div class="card mt-3">
                    <div class="card-header bg-info text-white">
                      <h5>Recent Bills</h5>
                    </div>
                    <div class="card-body">
                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th>Bill #</th>
                            <th>Date</th>
                            <th>Seller</th>
                            <th>Amount</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($bills as $bill): ?>
                          <tr>
                            <td><?= htmlspecialchars($bill['b_number']) ?></td>
                            <td><?= htmlspecialchars($bill['b_date']) ?></td>
                            <td><?= htmlspecialchars($bill['sell_name']) ?></td>
                            <td>$<?= htmlspecialchars($bill['amount']) ?></td>
                          </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <!-- Current Cart -->
                  <div class="card">
                    <div class="card-header bg-success text-white">
                      <h5>Current Bill</h5>
                    </div>
                    <div class="card-body">
                      <?php if (empty($_SESSION['cart'])): ?>
                      <p class="text-center">Your cart is empty</p>
                      <?php else: ?>
                      <table class="table">
                        <thead>
                          <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
                          $cartTotal = 0;
                          foreach ($_SESSION['cart'] as $cartItem): 
                            $itemTotal = $cartItem['price'] * $cartItem['quantity'];
                            $cartTotal += $itemTotal;
                          ?>
                          <tr>
                            <td><?= htmlspecialchars($cartItem['name']) ?></td>
                            <td>$<?= htmlspecialchars($cartItem['price']) ?></td>
                            <td><?= htmlspecialchars($cartItem['quantity']) ?></td>
                            <td>$<?= htmlspecialchars($itemTotal) ?></td>
                            <td>
                              <a href="Billing.php?remove=<?= htmlspecialchars($cartItem['id']) ?>" 
                                 class="btn btn-danger btn-sm">Remove</a>
                            </td>
                          </tr>
                          <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                          <tr>
                            <td colspan="3" class="text-end fw-bold">Total:</td>
                            <td colspan="2" class="fw-bold">$<?= htmlspecialchars($cartTotal) ?></td>
                          </tr>
                        </tfoot>
                      </table>
                      
                      <form method="POST" action="Billing.php" class="mt-3">
                        <button type="submit" name="checkout" class="btn btn-success w-100">
                          Complete Sale
                        </button>
                      </form>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
<?php
include '../includes/connection.php';
session_start();

// Check if the user is logged in and is a customer
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'customer') {
    echo '<script type="text/javascript">
        alert("Access denied. Customers only.");
        window.location = "../login.php";
        </script>';
    exit();
}

// Check if the customer is verified
$userID = $_SESSION['userID'];
$customerQuery = "SELECT isVerified FROM customers WHERE userID = $userID";
$customerResult = $conn->query($customerQuery);
$customer = $customerResult->fetch_assoc();
$isVerified = $customer['isVerified'];

// Fetch available products
$productsQuery = "SELECT * FROM product";
$productsResult = $conn->query($productsQuery);

// Process the order
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productID = $_POST['productID'];
    $quantity = $_POST['quantity'];
    $deliveryDate = $_POST['delivery_date'];
    $paymentMethod = $_POST['payment_method'];
    $gcashReceipt = '';

    // Check if quantity is within limits
    if ($quantity < 6 || $quantity > 100) {
        echo '<script type="text/javascript">
            alert("Quantity must be between 6 and 100.");
            window.location = "products.php";
            </script>';
        exit();
    }

    // Handle GCash receipt upload if payment method is GCash
    if ($paymentMethod == 'GCash') {
        if (isset($_FILES['gcash_receipt']) && $_FILES['gcash_receipt']['error'] == 0) {
            $target_dir = "../uploads/receipts/";

            // Create uploads/receipts directory if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $target_file = $target_dir . basename($_FILES["gcash_receipt"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Allow only image file types
            if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                if (move_uploaded_file($_FILES["gcash_receipt"]["tmp_name"], $target_file)) {
                    $gcashReceipt = $target_file;
                } else {
                    echo "Sorry, there was an error uploading your receipt.";
                    exit();
                }
            } else {
                echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                exit();
            }
        } else {
            echo "GCash receipt is required.";
            exit();
        }
    }

    // Fetch the unit price of the product
    $productQuery = "SELECT Price FROM product WHERE ProductID = $productID";
    $productResult = $conn->query($productQuery);
    $product = $productResult->fetch_assoc();
    $unitPrice = $product['Price'];
    $totalAmount = $unitPrice * $quantity;

    // Insert order into database
    $orderQuery = "INSERT INTO orders (customerID, order_date, total_amount, status, payment_method, gcash_receipt) VALUES ($userID, NOW(), $totalAmount, 'pending', '$paymentMethod', '$gcashReceipt')";
    if ($conn->query($orderQuery) === TRUE) {
        $orderID = $conn->insert_id;

        // Insert order items with unit price
        $orderItemQuery = "INSERT INTO order_items (orderID, productID, quantity, unit_price) VALUES ($orderID, $productID, $quantity, $unitPrice)";
        if ($conn->query($orderItemQuery) === TRUE) {
            // Insert delivery information
            $deliveryQuery = "INSERT INTO deliveries (orderID, delivery_date, userID) VALUES ('$orderID', '$deliveryDate', '$userID')";
            if ($conn->query($deliveryQuery) === TRUE) {
                // Create notification message
                $message = "You've placed an order successfully!";

                // Insert notification
                $notificationQuery = "INSERT INTO notifications (userID, message) VALUES (?, ?)";
                $stmt = $conn->prepare($notificationQuery);
                $stmt->bind_param("is", $userID, $message);
                $stmt->execute();
                $stmt->close();

                echo '<script type="text/javascript">
                    alert("Order placed successfully.");
                    window.location = "order_history.php";
                    </script>';
            } else {
                echo "Error: " . $deliveryQuery . "<br>" . $conn->error;
            }
        } else {
            echo "Error: " . $orderItemQuery . "<br>" . $conn->error;
        }
    } else {
        echo "Error: " . $orderQuery . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order | De Chavez Waterhaus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Designs/custom-style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #f8f9fa;
            padding-top: 20px;
            transition: width 0.3s;
        }
        .sidebar.close {
            width: 80px;
        }
        .sidebar .logo {
            display: flex;
            align-items: center;
            padding: 0 20px;
            margin-bottom: 20px;
        }
        .sidebar .logo img {
            width: 40px;
            border-radius: 50%;
        }
        .sidebar .logo .logo_name {
            font-size: 22px;
            font-weight: 600;
            margin-left: 10px;
            transition: opacity 0.3s;
        }
        .sidebar.close .logo .logo_name {
            opacity: 0;
        }
        .sidebar .nav-links {
            list-style: none;
            padding: 0;
        }
        .sidebar .nav-links li {
            width: 100%;
        }
        .sidebar .nav-links li a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.3s;
        }
        .sidebar .nav-links li a:hover {
            background-color: #e2e6ea;
        }
        .sidebar .nav-links li a i {
            font-size: 24px;
            min-width: 45px;
        }
        .sidebar .nav-links li a .link-name {
            font-size: 18px;
            transition: opacity 0.3s;
        }
        .sidebar.close .nav-links li a .link-name {
            opacity: 0;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
        }
        .sidebar.close ~ .content {
            margin-left: 80px;
        }
        .sidebar-toggle {
            font-size: 26px;
            cursor: pointer;
        }
        .product {
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px;
            border-radius: 5px;
        }
        .product img {
            max-width: 100px;
            margin-bottom: 10px;
        }
        .order-form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .order-form select,
        .order-form input {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        <div class="logo">
            <img src="../images/logo.png" alt="Logo">
            <span class="logo_name">De Chavez</span>
        </div>

        <ul class="nav-links">
            <li><a href="customer_dashboard.php">
                <i class="bi bi-house-door"></i>
                <span class="link-name">Home</span>
            </a></li>
            <li><a href="products.php">
                <i class="bi bi-box"></i>
                <span class="link-name">Products</span>
            </a></li>
            <li><a href="orders.php">
                <i class="bi bi-cart"></i>
                <span class="link-name">Orders</span>
            </a></li>
            <li><a href="order_history.php">
                <i class="bi bi-clock-history"></i>
                <span class="link-name">Order History</span>
            </a></li>
            <li><a href="ticket.php">
                <i class="bi bi-inbox"></i>
                <span class="link-message ">Tickets</span>
            </a></li>
            <li><a href="notification.php">
                <i class="bi bi-bell"></i>
                <span class="link-name">Notification</span>
            </a></li>
            <li><a href="profile.php">
                <i class="bi bi-person"></i>
                <span class="link-name">Profile</span>
            </a></li>
            <li><a href="../logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span class="link-name">Logout</span>
            </a></li>
        </ul>
    </nav>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <i class="bi bi-list sidebar-toggle"></i>
            <h1>Order</h1>
        </div>

        <h2>Products</h2>
        <div class="row">
            <?php while ($product = $productsResult->fetch_assoc()) { ?>
                <div class="col-md-4">
                    <div class="card">
                        <img src="<?php echo $product['ImageURL']; ?>" class="card-img-top" alt="<?php echo $product['ProductName']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $product['ProductName']; ?></h5>
                            <p class="card-text"><?php echo $product['Description']; ?></p>
                            <p class="card-text"><strong>Price: </strong>₱<?php echo $product['Price']; ?></p>
                            <?php if ($isVerified) { ?>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal" 
                                        data-productid="<?php echo $product['ProductID']; ?>" 
                                        data-productname="<?php echo $product['ProductName']; ?>">
                                    Buy Now
                                </button>
                            <?php } else { ?>
                                <p class="text-danger">Verify your account to order.</p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- Order Modal -->
        <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="orderForm" action="products.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="orderModalLabel">Place Order</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="productID" id="productID">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" name="quantity" id="quantity" min="6" max="100" required>
                                <div id="quantityWarning" class="alert alert-danger mt-2" style="display: none;">Quantity must be between 6 and 100.</div>
                            </div>
                            <div class="mb-3">
                                <label for="delivery_option" class="form-label">Delivery Option</label>
                                <select class="form-select" name="delivery_option" id="delivery_option" required>
                                    <option value="today">Today</option>
                                    <option value="scheduled">Scheduled</option>
                                </select>
                            </div>
                            <div class="mb-3" id="deliveryDateContainer" style="display: none;">
                                <label for="delivery_date" class="form-label">Delivery Date</label>
                                <input type="date" class="form-control" name="delivery_date" id="delivery_date" min="<?php echo date('Y-m-d'); ?>">
                                <div id="dateWarning" class="alert alert-danger mt-2" style="display: none;">You can't schedule a delivery in the past!</div>
                            </div>
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method" id="payment_method" required>
                                    <option value="COD">COD (Cash on Delivery)</option>
                                    <option value="GCash">GCash</option>
                                </select>
                            </div>
                            <div class="mb-3" id="gcashDetails" style="display: none;">
                                <p>GCash Number: 09502001713<br>Name: Romeo E.</p>
                                <label for="gcash_receipt" class="form-label">Upload GCash Receipt</label>
                                <input type="file" class="form-control" name="gcash_receipt" id="gcash_receipt" accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="place_order">Place Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        const sidebar = document.querySelector(".sidebar");
        const toggle = document.querySelector(".sidebar-toggle");

        toggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
        });

        // Handle modal data population
        const orderModal = document.getElementById('orderModal');
        orderModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const productID = button.getAttribute('data-productid');
            const productName = button.getAttribute('data-productname');
            
            const modalTitle = orderModal.querySelector('.modal-title');
            const modalProductID = orderModal.querySelector('#productID');
            
            modalTitle.textContent = `Place Order for ${productName}`;
            modalProductID.value = productID;
        });

        // Toggle delivery date input
        const deliveryOption = document.getElementById('delivery_option');
        const deliveryDateContainer = document.getElementById('deliveryDateContainer');
        deliveryOption.addEventListener('change', function() {
            if (this.value === 'scheduled') {
                deliveryDateContainer.style.display = 'block';
            } else {
                deliveryDateContainer.style.display = 'none';
            }
        });

        // Toggle GCash details input
        const paymentMethod = document.getElementById('payment_method');
        const gcashDetails = document.getElementById('gcashDetails');
        paymentMethod.addEventListener('change', function() {
            if (this.value === 'GCash') {
                gcashDetails.style.display = 'block';
            } else {
                gcashDetails.style.display = 'none';
            }
        });

        // Quantity validation
        const quantityInput = document.getElementById('quantity');
        const quantityWarning = document.getElementById('quantityWarning');
        quantityInput.addEventListener('input', function() {
            if (this.value < 6 || this.value > 100) {
                quantityWarning.style.display = 'block';
            } else {
                quantityWarning.style.display = 'none';
            }
        });

        // Date validation
        const deliveryDateInput = document.getElementById('delivery_date');
        const dateWarning = document.getElementById('dateWarning');
        deliveryDateInput.addEventListener('input', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                dateWarning.style.display = 'block';
            } else {
                dateWarning.style.display = 'none';
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

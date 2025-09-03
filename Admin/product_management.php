<?php
include '../includes/connection.php';
session_start();

// Check if the user is logged in and is admin
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    echo '<script type="text/javascript">
        alert("Access denied. Admins only.");
        window.location = "../login.php";
        </script>';
    exit();
}

// Handle file upload and product addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['productImage'])) {
    $target_dir = "../uploads/";

    // Create uploads directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["productImage"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["productImage"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["productImage"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "jpeg") {
        echo "Sorry, only JPG, JPEG files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // Generate a unique name for the uploaded file to avoid conflicts
        $new_file_name = $target_dir . "photo" . time() . "." . $imageFileType;

        if (move_uploaded_file($_FILES["productImage"]["tmp_name"], $new_file_name)) {
            $productName = $_POST['productName'];
            $productPrice = $_POST['productPrice'];
            $productDescription = $_POST['productDescription'];
            $imageUrl = $new_file_name;

            $insertQuery = "INSERT INTO product (ProductName, Price, Description, ImageURL) VALUES ('$productName', '$productPrice', '$productDescription', '$imageUrl')";
            if ($conn->query($insertQuery) === TRUE) {
                echo "The file ". htmlspecialchars(basename($_FILES["productImage"]["name"])). " has been uploaded.";
                
                // Fetch all users to send notification
                $usersQuery = "SELECT userID FROM customers";
                $usersResult = $conn->query($usersQuery);

                // Create a notification message
                $message = "New product: $productName is now available for ₱$productPrice. Order now!";

                // Insert notification for each user
                while ($user = $usersResult->fetch_assoc()) {
                    $userID = $user['userID'];
                    $notificationQuery = "INSERT INTO notifications (userID, message) VALUES ('$userID', '$message')";
                    $conn->query($notificationQuery);
                }

                echo "Product added and notification sent.";
            } else {
                echo "Error: " . $insertQuery . "<br>" . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Handle product editing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editProductID'])) {
    $productID = $_POST['editProductID'];
    $productName = $_POST['editProductName'];
    $productPrice = $_POST['editProductPrice'];
    $productDescription = $_POST['editProductDescription'];

    $updateQuery = "UPDATE product SET ProductName='$productName', Price='$productPrice', Description='$productDescription' WHERE ProductID='$productID'";
    if ($conn->query($updateQuery) === TRUE) {
        echo "Product updated successfully.";
    } else {
        echo "Error: " . $updateQuery . "<br>" . $conn->error;
    }
}

// Fetch products
$productsQuery = "SELECT * FROM product";
$productsResult = $conn->query($productsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management | De Chavez Waterhaus</title>
    <link rel="stylesheet" href="../Designs/admin-style.css">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <!-- Custom CSS for Product Display -->
    <link rel="stylesheet" href="../Designs/style.css">
</head>
<body>
    <nav>
        <div class="logo-name">
            <div class="logo-image">
               <img src="../images/logo.png" alt="Logo">
            </div>
            <span class="logo_name">De Chavez</span>
        </div>

        <div class="menu-items">
            <ul class="nav-links">
                <li><a href="admin_dashboard.php">
                    <i class="bi bi-house-door"></i>
                    <span class="link-name">Dashboard</span>
                </a></li>
                <li><a href="order_management.php">
                    <i class="bi bi-cart"></i>
                    <span class="link-name">Orders</span>
                </a></li>
                <li><a href="delivery_management.php">
                    <i class="bi bi-truck"></i>
                    <span class="link-name">Delivery</span>
                </a></li>
                <li><a href="product_management.php">
                    <i class="bi bi-box"></i>
                    <span class="link-name">Products</span>
                </a></li>
                <li><a href="customer_management.php">
                    <i class="bi bi-people"></i>
                    <span class="link-name">Customers</span>
                </a></li>
                <li><a href="manage_ticket.php">
                    <i class="bi bi-inbox"></i>
                    <span class="link-name">Tickets</span>
                </a></li>
            </ul>
            
            <ul class="logout-mode">
                <li><a href="../logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="link-name">Logout</span>
                </a></li>
            </ul>
        </div>
    </nav>

    <section class="dashboard">
        <div class="top">
            <i class="bi bi-list sidebar-toggle"></i>
        </div>

        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="bi bi-box"></i>
                    <span class="text">Product Management</span>
                </div>

                <!-- Add New Product Box -->
                <div class="col-md-3 mb-3">
                    <div class="card h-100" id="addProductCard" data-toggle="modal" data-target="#addProductModal">
                        <div class="card-body d-flex align-items-center justify-content-center" style="cursor: pointer;">
                            <h5 class="card-title mb-0"><i class="bi bi-plus-circle"></i> Add New Product</h5>
                        </div>
                    </div>
                </div>

                <!-- Add Product Modal -->
                <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="product_management.php" method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="productName">Product Name</label>
                                        <input type="text" class="form-control" id="productName" name="productName" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="productPrice">Product Price</label>
                                        <input type="text" class="form-control" id="productPrice" name="productPrice" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="productDescription">Product Description</label>
                                        <textarea class="form-control" id="productDescription" name="productDescription" rows="3" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="productImage">Product Image (JPEG only)</label>
                                        <input type="file" class="form-control-file" id="productImage" name="productImage" accept="image/jpeg" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Add Product</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Product Modal -->
                <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="product_management.php" method="post">
                                    <input type="hidden" id="editProductID" name="editProductID">
                                    <div class="form-group">
                                        <label for="editProductName">Product Name</label>
                                        <input type="text" class="form-control" id="editProductName" name="editProductName" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="editProductPrice">Product Price</label>
                                        <input type="text" class="form-control" id="editProductPrice" name="editProductPrice" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="editProductDescription">Product Description</label>
                                        <textarea class="form-control" id="editProductDescription" name="editProductDescription" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="small-container mt-5">
                    <div class="row">
                        <?php while ($row = $productsResult->fetch_assoc()) { ?>
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <img src="<?php echo $row['ImageURL']; ?>" class="card-img-top" alt="<?php echo $row['ProductName']; ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $row['ProductName']; ?></h5>
                                    <p class="card-text"><strong><?php echo $row['Price']; ?></strong></p>
                                    <p class="card-text"><?php echo $row['Description']; ?></p>
                                    <button class="btn btn-warning edit-btn" data-id="<?php echo $row['ProductID']; ?>" data-name="<?php echo $row['ProductName']; ?>" data-price="<?php echo $row['Price']; ?>" data-description="<?php echo $row['Description']; ?>" data-toggle="modal" data-target="#editProductModal">Edit</button>
                                    <a href="delete_product.php?productID=<?php echo $row['ProductID']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        let sidebar = document.querySelector("nav");
        let closeBtn = document.querySelector(".sidebar-toggle");

        closeBtn.addEventListener("click", () => {
            sidebar.classList.toggle("close");
        });

        // JavaScript to populate the edit product form with current product data
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById("editProductID").value = this.getAttribute('data-id');
                document.getElementById("editProductName").value = this.getAttribute('data-name');
                document.getElementById("editProductPrice").value = this.getAttribute('data-price');
                document.getElementById("editProductDescription").value = this.getAttribute('data-description');
            });
        });
    </script>
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

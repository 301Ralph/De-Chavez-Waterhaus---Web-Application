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

$productID = $_GET['productID'];
$productQuery = "SELECT * FROM product WHERE ProductID = $productID";
$productResult = $conn->query($productQuery);
$product = $productResult->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = $_POST['productName'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $imageURL = $_POST['imageURL'];

    $updateProductQuery = "UPDATE product SET ProductName = '$productName', Description = '$description', Price = '$price', ImageURL = '$imageURL' WHERE ProductID = $productID";
    
    if ($conn->query($updateProductQuery) === TRUE) {
        echo '<script type="text/javascript">
            alert("Product updated successfully.");
            window.location = "product_management.php";
            </script>';
    } else {
        echo "Error: " . $updateProductQuery . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | De Chavez Waterhaus</title>
    <link rel="stylesheet" href="../Designs/admin-style.css">
    <!-- Bootstrap Icons CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
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
                <li><a href="product_management.php">
                    <i class="bi bi-box"></i>
                    <span class="link-name">Products</span>
                </a></li>
                <li><a href="customer_management.php">
                    <i class="bi bi-people"></i>
                    <span class="link-name">Customers</span>
                </a></li>
                <li><a href="reports_analytics.php">
                    <i class="bi bi-graph-up"></i>
                    <span class="link-name">Reports</span>
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
                    <span class="text">Edit Product</span>
                </div>

                <form action="edit_product.php?productID=<?php echo $productID; ?>" method="POST">
                    <div class="input-group">
                        <label for="productName">Product Name</label>
                        <input type="text" name="productName" value="<?php echo $product['ProductName']; ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="description">Description</label>
                        <textarea name="description" required><?php echo $product['Description']; ?></textarea>
                    </div>
                    <div class="input-group">
                        <label for="price">Price</label>
                        <input type="text" name="price" value="<?php echo $product['Price']; ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="imageURL">Image URL</label>
                        <input type="text" name="imageURL" value="<?php echo $product['ImageURL']; ?>" required>
                    </div>
                    <button type="submit" class="btn">Update Product</button>
                </form>
            </div>
        </div>
    </section>
    <script>
        let sidebar = document.querySelector("nav");
        let closeBtn = document.querySelector(".sidebar-toggle");

        closeBtn.addEventListener("click", () => {
            sidebar.classList.toggle("close");
        });
    </script>
</body>
</html>

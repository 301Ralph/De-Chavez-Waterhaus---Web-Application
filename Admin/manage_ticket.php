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

$searchTerm = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $searchTerm = $_POST['searchTerm'];
}

$ticketsQuery = "SELECT tickets.*, customers.Firstname, customers.Lastname 
                 FROM tickets 
                 LEFT JOIN customers ON tickets.customerID = customers.userID
                 WHERE customers.Firstname LIKE '%$searchTerm%' OR customers.Lastname LIKE '%$searchTerm%'";
$ticketsResult = $conn->query($ticketsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tickets | De Chavez Waterhaus</title>
    <link rel="stylesheet" href="../Designs/admin-style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: cyan;
        }
        .table {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        .table th, .table td {
            padding: 1rem;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .search-bar {
            max-width: 300px;
            margin-bottom: 20px;
        }
    </style>
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
            <div class="title">
                <i class="bi bi-ticket"></i>
                <span class="text">Manage Tickets</span>
            </div>

            <form method="POST" action="manage_ticket.php" class="search-bar">
                <input type="text" name="searchTerm" class="form-control" placeholder="Search by name..." value="<?php echo $searchTerm; ?>">
                <button type="submit" name="search" class="btn btn-primary mt-2">Search</button>
            </form>

            <div class="ticket-list">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Subject</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $ticketsResult->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['Firstname'] . ' ' . $row['Lastname']; ?></td>
                                <td><?php echo $row['subject']; ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <a href="view_ticket.php?ticketID=<?php echo $row['ticketID']; ?>" class="btn btn-info btn-sm">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

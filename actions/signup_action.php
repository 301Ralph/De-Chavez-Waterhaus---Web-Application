<?php
include '../includes/connection.php';

$firstname = htmlspecialchars($_POST['firstname']);
$lastname = htmlspecialchars($_POST['lastname']);
$email = htmlspecialchars($_POST['email']);
$password = htmlspecialchars($_POST['password']);
$contact = htmlspecialchars($_POST['contact']);
$address = htmlspecialchars($_POST['address']);

// Hash the password
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT INTO customers (Firstname, Lastname, Email, Password, ContactNumber, Address, Role) VALUES ('$firstname', '$lastname', '$email', '$passwordHash', '$contact', '$address', 'customer')";
if ($conn->query($sql) === true) {
    echo '<script type="text/javascript">
        alert("Registration Successful");
        window.location = "../signin.php";
        </script>';
    exit();
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>

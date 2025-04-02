<?php
session_start();
include('connection.php');
$login_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $un = $_POST["username"];
    $ph = $_POST["phone"];
    
    // Check if the provided information matches any record
    $stmt = $con->prepare("SELECT * FROM User WHERE U_ID = ? AND UserName = ? AND Phone = ?");
    $stmt->bind_param("iss", $id, $un, $ph);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        if (isset($_POST["new_password"])) {
            // Update the password in the database
            $new_password = $_POST["new_password"];
            $update_stmt = $con->prepare("UPDATE User SET Password = ? WHERE U_ID = ?");
            $update_stmt->bind_param("si", $new_password, $id);
            
            if ($update_stmt->execute()) {
                // Redirect to index.php after a successful update
                header("Location: index.php");
                exit();
            } else {
                $login_error = "Error updating password";
            }
        } else {
            // Display the password input field
            echo '<form method="post">
                    <input type="hidden" name="id" value="'.$id.'">
                    <input type="hidden" name="username" value="'.$un.'">
                    <input type="hidden" name="phone" value="'.$ph.'">
                    <input type="password" name="new_password" placeholder="Enter new password" required><br>
                    <input type="submit" value="Update Password">
                </form>';
        }
    } else {
        $login_error = "Error: User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100vh;
        }

        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
            margin: auto;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            padding: 10px 20px;
            background-color: #3498db;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
        }

        a.recover-email {
            display: block;
            margin: 20px 0;
            padding: 10px;
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        a.recover-email:hover {
            background-color: #c0392b;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }

        .footer {
            background-color: #007BFF;
            color: white;
            padding: 20px;
            text-align: center;
            position: absolute;
            bottom: 0;
            width: 100%;
            font-size: 14px;
        }

        .footer a {
            color: white;
            margin: 0 10px;
            text-decoration: none;
            font-weight: bold;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forget Password</h2>
        <form method="post">
            <input type="text" name="id" placeholder="Enter your ID" required><br>
            <input type="text" name="username" placeholder="Enter your username" required><br>
            <input type="text" name="phone" placeholder="Enter your mobile number" required><br>
            <input type="submit" value="Submit">
        </form>
        <a href="forget_password.php" class="recover-email">Recover by email?</a>
        <?php if (!empty($login_error)): ?>
            <div class="error-message"><?php echo $login_error; ?></div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>&copy; 2024 Prochestha. All Rights Reserved. |
            <a href="terms.php">Terms of Service</a> |
            <a href="privacy.php">Privacy Policy</a> |
            <a href="contact.php">Contact Us</a>
        </p>
    </div>
</body>
</html>

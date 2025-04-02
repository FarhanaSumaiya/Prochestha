<?php
include('connection.php');
$token = $_GET['token'];
$error_message = "";

// Check if token is valid and not expired
$stmt = $con->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];

        if ($new_password == $confirm_password) {
            $reset_data = $result->fetch_assoc();
            $email = $reset_data['email'];

            // Update the user's password
            $stmt = $con->prepare("UPDATE User SET Password = ? WHERE Email = ?");
            $stmt->bind_param("ss", $new_password, $email);
            if ($stmt->execute()) {
                // Delete the token after successful reset
                $stmt = $con->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();

                // Redirect to login page
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Error updating password.";
            }
        } else {
            $error_message = "Passwords do not match.";
        }
    }
} else {
    $error_message = "Invalid or expired token.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>
    <form method="post">
        <input type="password" name="new_password" placeholder="Enter new password" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm new password" required><br>
        <input type="submit" value="Reset Password">
    </form>
    <?php if ($error_message): ?>
        <div><?php echo $error_message; ?></div>
    <?php endif; ?>
</body>
</html>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'path_to_phpmailer/PHPMailer/src/Exception.php';
require 'path_to_phpmailer/PHPMailer/src/PHPMailer.php';
require 'path_to_phpmailer/PHPMailer/src/SMTP.php';

include('connection.php');
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // Check if email exists
    $stmt = $con->prepare("SELECT * FROM User WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token expires in 1 hour

        // Store the token in the database
        $stmt = $con->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires_at);
        $stmt->execute();

        // Send the reset email using PHPMailer
        $reset_link = "http://yourwebsite.com/reset_password.php?token=" . $token;
        
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';                // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@gmail.com';      // SMTP username (your Gmail)
            $mail->Password = 'your-email-password';       // SMTP password (your Gmail password or app-specific password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('your-email@gmail.com', 'Your Website');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset';
            $mail->Body    = 'Click the following link to reset your password: <a href="' . $reset_link . '">Reset Password</a>';

            $mail->send();
            echo 'A password reset link has been sent to your email.';
        } catch (Exception $e) {
            $error_message = "Error sending email: {$mail->ErrorInfo}";
        }
    } else {
        $error_message = "No account found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <form method="post">
        <input type="email" name="email" placeholder="Enter your email" required><br>
        <input type="submit" value="Send Reset Link">
    </form>
    <?php if ($error_message): ?>
        <div><?php echo $error_message; ?></div>
    <?php endif; ?>
</body>
</html>

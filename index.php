<?php
session_start();
include('connection.php');  

$login_error = "";  

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $_POST["un"];
    $password = $_POST["pw"];

    $stmt = $con->prepare("SELECT * FROM User WHERE UserName = ? AND Password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['first_name'] = $user['FirstName'];
		$_SESSION['user_id'] = $user['U_ID'];
        header("Location: User.php");
        exit();
    } else {
        $login_error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #d89beb;
			background-image: url('back (1).png');
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex: 1; /* Ensures that container expands */
            padding: 20px;
        }

        .left-side {
            width: 50%;
            height: 100%;
            background-image: url('slider.png');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: slideInLeft 1.5s ease-in-out;
        }

        .left-side::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(93, 146, 196, 0.5);
            z-index: 1;
        }

        .motivational-quotes {
            position: relative;
            z-index: 2;
            color: #211adb;
            text-align: center;
            font-family: 'Georgia', serif;
            font-size: 24px;
            padding: 20px;
            line-height: 1.5;
            max-width: 80%;
            animation: fadeInQuotes 2s ease-in-out infinite alternate;
        }

        .right-side {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: slideInRight 1.5s ease-in-out;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            width: 100%;
            animation: fadeInUp 1.5s ease-in-out forwards;
            opacity: 0;
        }

        .login-container h2 {
            margin-top: 0;
            font-family: 'Arial', sans-serif;
        }

        .login-form input {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .login-form input:focus {
            transform: scale(1.05);
            border-color: #3498db;
        }

        .login-form input[type="submit"] {
            padding: 12px 25px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .login-form input[type="submit"]:hover {
            background-color: #2980b9;
            transform: scale(1.1);
        }

        .login-link {
            margin-top: 20px;
            animation: fadeIn 2s ease-in-out;
            font-size: 14px;
        }

        .login-link a {
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #2980b9;
        }

        .login-link span {
            margin: 0 10px;
            color: #888;
        }

        .forget-link {
            font-weight: normal;
            color: #e74c3c;
            transition: color 0.3s ease, transform 0.2s ease;
        }

        .forget-link:hover {
            color: #c0392b;
            transform: scale(1.1);
        }

        .error-message {
            color: red;
            margin-bottom: 10px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeInQuotes {
            from {
                opacity: 0.7;
                transform: scale(1);
            }
            to {
                opacity: 1;
                transform: scale(1.05);
            }
        }

        .footer {
            background-color: #d89beb;
            color: white;
            padding: 10px;
            text-align: center;
            width: 100%;
            font-size: 14px;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.2);
        }

        .footer a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: #ccc;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="left-side">
        <div class="motivational-quotes">
            <p>"Believe you can and you're halfway there."</p>
            <p>"Success is not final, failure is not fatal: It is the courage to continue that counts."</p>
            <p>"Don't watch the clock; do what it does. Keep going."</p>
            <p>"The only way to do great work is to love what you do."</p>
        </div>
    </div>
    <div class="right-side">
        <div class="login-container">
            <h2>Login</h2>
            <?php if ($login_error): ?>
                <p class="error-message"><?php echo $login_error; ?></p>
            <?php endif; ?>
            <form action="" method="post" class="login-form">
                <input type="text" name="un" placeholder="User Name" required>
                <input type="password" name="pw" placeholder="Password" required>
                <input type="submit" name="login" value="Login">
            </form>
            <div class="login-link">
                <a href="Signup.php">Sign up!</a>
                <span>|</span>
                <a href="Forget.php" class="forget-link">Forget Password?</a>
            </div>
        </div>
    </div>
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

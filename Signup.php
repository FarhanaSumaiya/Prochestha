<?php
session_start();
include('connection.php');

$success_message = "";
$error_message = "";
$user_id = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $first_name = $_POST["fn"];
    $last_name = $_POST["ln"];
    $username = $_POST["un"];
	$email = $_POST["em"];
    $gender = $_POST["gender"];
    $phone_number = $_POST["number"];
    $password = $_POST["pw"];

    mysqli_begin_transaction($con);

    try {
        
        $sql_check_username = "SELECT * FROM User WHERE UserName='$username'";
        $result_check_username = mysqli_query($con, $sql_check_username);

        if (mysqli_num_rows($result_check_username) > 0) {
            throw new Exception("Username already exists. Please choose a unique username.");
        }

        if (!empty($phone_number)) {
            $sql_check_phone = "SELECT * FROM User WHERE Phone='$phone_number'";
            $result_check_phone = mysqli_query($con, $sql_check_phone);

            if (mysqli_num_rows($result_check_phone) > 0) {
                throw new Exception("Phone number already exists. Please choose a unique phone number.");
            }
        }
		
		if (!empty($emil)) {
            $sql_check_email = "SELECT * FROM User WHERE Email='$email'";
            $result_check_email = mysqli_query($con, $sql_check_email);

            if (mysqli_num_rows($result_check_email) > 0) {
                throw new Exception("This email address already exists. Please choose a unique Email.");
            }
        }
		
        $sql = "INSERT INTO User (FirstName, LastName, UserName, Email, Gender, Phone, Password) VALUES ('$first_name', '$last_name', '$username', '$email', '$gender', '$phone_number', '$password')";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            throw new Exception("Error occurred: " . mysqli_error($con));
        } else {
            $user_id = mysqli_insert_id($con);
            mysqli_commit($con);

            $success_message = "Signup successful! Dear, your User ID is: $user_id Remember your ID for further use!";
        }
    } catch (Exception $e) {
        mysqli_rollback($con);
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup Page</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
            margin: 0;
        }
        .navbar {
            width: 100%;
            background-color: #3498db;
            padding: 5px;
            box-sizing: border-box;
            position: relative;
            display: flex;
            align-items: center;
        }
        .navbar a {
            color: #fff;
            font-size: 20px;
            text-decoration: none;
            padding: 10px;
        }
        .signup-container {
            width: 400px;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }
        .signup-container h2 {
            text-align: center;
        }
        .signup-container form {
            display: flex;
            flex-direction: column;
        }
        .signup-container label {
            display: block;
            margin-bottom: 10px;
        }
        .signup-container input[type="text"], .signup-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        .signup-container button {
            width: 100%;
            padding: 10px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .signup-container button:hover {
            background-color: #2980b9;
        }
        .success-message, .error-message {
            text-align: center;
            margin-bottom: 20px;
        }
        .success-message {
            color: green;
        }
        .error-message {
            color: red;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="index.php">Login</a>
    </div>

    <div class="signup-container">
        <h2>Signup</h2>
        <?php if ($success_message): ?>
            <p class="success-message"><?php echo $success_message; ?></p>
            <a href="index.php">Go to Login</a>
        <?php else: ?>
            <?php if ($error_message): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form action="" method="post">
                <label for="first_name">First Name:</label>
                <input type="text" id="fname" name="fn" required>
                
                <label for="last_name">Last Name:</label>
                <input type="text" id="lname" name="ln" required>
                
                <label for="username">User Name:</label>
                <input type="text" id="uname" name="un" required placeholder="Should be Unique, can't change!!">
                
				<label for="Email">Email:</label>
                <input type="text" id="email" name="em" required placeholder="Should be Unique, can't change!!">
                
                <b>Select your Gender</b>  
                Male <input type="radio" name="gender" value="Male" required>  
                Female <input type="radio" name="gender" value="Female" required>  
                <br><br>
                <label for="phone_numbers">Phone Numbers:</label>
                <input type="text" id="phone_numbers" name="number" placeholder="Enter phone numbers">
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="pw" required>
                
                <button type="submit">Signup</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

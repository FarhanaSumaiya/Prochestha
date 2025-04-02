<?php
session_start();
include 'connection.php';

$habit_id = isset($_GET['habit_id']) ? intval($_GET['habit_id']) : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // Ensure user is logged in
$today = date('Y-m-d');

// Fetch habit count and last click date
$stmt = $con->prepare("SELECT Count, Last_Click_Date FROM Habits WHERE ID = ? AND User_ID = ?");
$stmt->bind_param('ii', $habit_id, $user_id);
$stmt->execute();
$stmt->bind_result($click_count, $last_click_date);
$stmt->fetch();
$stmt->close();

// Fetch habit title and details
$stmt = $con->prepare("SELECT Title, Details FROM Habits WHERE ID = ? AND User_ID = ?");
$stmt->bind_param('ii', $habit_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $details);
$stmt->fetch();
$stmt->close();

$hasClickedToday = false;

$isNewDay = $last_click_date != $today;
$isYesterday = $last_click_date == date('Y-m-d', strtotime('-1 day', strtotime($today)));

// If it's a new day and not clicked yesterday, reset count
if ($isNewDay) {
    if (!$isYesterday) {
        // Reset count to 0 if not clicked yesterday
        $click_count = 0;
        $stmt = $con->prepare("UPDATE Habits SET Count = 0 WHERE ID = ? AND User_ID = ?");
        $stmt->bind_param('ii', $habit_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    $dayNumber = $click_count + 1; 
} else {
    $dayNumber = $click_count; 
    $hasClickedToday = true;
}

// Handle button click
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isNewDay) {
        // Update the click count for today
        $click_count++;
        $stmt = $con->prepare("UPDATE Habits SET Count = ?, Last_Click_Date = ? WHERE ID = ? AND User_ID = ?");
        $stmt->bind_param('isii', $click_count, $today, $habit_id, $user_id);

        if ($stmt->execute()) {
            echo "<script>alert('Click count updated: $click_count');</script>";
        } else {
            echo "<script>alert('Error updating click count');</script>";
        }
        $stmt->close();
        $hasClickedToday = true;
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Habit Tracker</title>
    <style>
        /* General page styling */
         /* General page styling */
body {
    margin: 0;
    padding: 0;
    font-family: 'Roboto', sans-serif;
    background-color: #f3f4f6;
    display: flex;
    flex-direction: column; /* Stack elements vertically */
    min-height: 100vh; /* Ensure body takes up full viewport height */
}

.container {
    text-align: center;
    background-color: #ffffff;
    padding: 40px 50px;
    border-radius: 20px;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
    max-width: 1000px; 
    width: 100%;
    margin: 40px auto; /* Center the container vertically */
}

.footer {
    background-color: #007BFF;
    color: white;
    padding: 10px;
    text-align: center;
    width: 100%;
    margin-top: auto; /* Push the footer to the bottom */
}

.footer a {
    color: white;
    margin: 0 10px;
    transition: color 0.3s ease;
}

.footer a:hover {
    color: #ccc;
}

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .back-button:hover {
            color: #2980b9;
        }

        h1 {
            font-size: 32px;
            margin-bottom: 20px;
            color: #333333;
        }

        .message {
            margin-bottom: 30px;
            font-size: 16px;
            color: #666666;
        }

        .days-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
        }

        .day-box {
            width: 150px;
            height: 100px;
            border-radius: 10px;
            background-color: #f0f0f0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            color: #333333;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .day-box.completed {
            background-color: #3498db;
            color: #ffffff;
        }
		
        #dailyButton {
            background-color: #3498db;
            color: #ffffff;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 20px;
            outline: none;
        }

        #dailyButton:hover {
            background-color: #2980b9;
            transform: scale(1.05);
        }

        #dailyButton[disabled] {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
		

    </style>
</head>

<body>
    <div class="container">
        <a href="user.php" class="back-button">&larr; Back to Dashboard</a>
        <h1>Daily Habit Tracker</h1>
        <p class="message">Stay consistent by clicking the button each day you complete this habit!</p>
        
        <h3>Habit: <?php echo htmlspecialchars($title); ?></h3>
        <p>Details: <?php echo htmlspecialchars($details); ?></p>

        <div class="days-container">
            <?php 
            for ($i = 1; $i <= 21; $i++) :
                $habitDate = date('M d, Y', strtotime("+".($i-1)." days", strtotime($today))); // Calculate future dates
            ?>
                <div class="day-box <?php echo ($i <= $click_count) ? 'completed' : ''; ?>">
                    Day <?php echo $i; ?><br>
                    <small><?php echo $habitDate; ?></small>
                </div>
            <?php endfor; ?>
        </div>

        <form method="POST">
            <button id="dailyButton" <?php echo ($hasClickedToday) ? 'disabled="disabled"' : ''; ?>>
                <?php echo ($hasClickedToday) ? 'Completed Today' : 'Mark Today'; ?>
            </button>
        </form>
    </div>


    <script>
        // Reload the page at midnight (12:00 AM)
        function reloadAtMidnight() {
            var now = new Date();
            var midnight = new Date();
            midnight.setHours(24, 0, 0, 0);  // Set to 12:00 AM

            var timeUntilMidnight = midnight.getTime() - now.getTime();
            setTimeout(function () {
                location.reload();
            }, timeUntilMidnight);
        }

        reloadAtMidnight();
    </script>
	
	
    <div class="footer">
        <p>&copy; 2024 Prochestha. All Rights Reserved. | 
            <a href="terms.php">Terms of Service</a> | 
            <a href="privacy.php">Privacy Policy</a> | 
            <a href="contact.php">Contact Us</a>
        </p>
    </div>
</body>

</html>

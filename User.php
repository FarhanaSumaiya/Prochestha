<?php
session_start();
include 'connection.php';

$first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Guest';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['habitTitle']) && isset($_POST['habitDetails'])) {
        // Add a new habit
        $title = htmlspecialchars($_POST['habitTitle']);
        $details = htmlspecialchars($_POST['habitDetails']);
        
        $stmt = $con->prepare("INSERT INTO Habits (User_ID, Title, Details) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $user_id, $title, $details);

        if ($stmt->execute()) {
            echo "<script>alert('Habit added successfully!');</script>";
        } else {
            echo "<script>alert('Error adding habit');</script>";
        }
        $stmt->close();
    } elseif (isset($_POST['deleteHabitID'])) {
        // Delete a habit
        $habit_id = intval($_POST['deleteHabitID']);
        
        $stmt = $con->prepare("DELETE FROM Habits WHERE ID = ? AND User_ID = ?");
        $stmt->bind_param('ii', $habit_id, $user_id);

        if ($stmt->execute()) {
            echo "<script>alert('Habit deleted successfully!');</script>";
        } else {
            echo "<script>alert('Error deleting habit');</script>";
        }
        $stmt->close();
    }
}

$habits = [];
if ($user_id > 0) {
    $stmt = $con->prepare("SELECT ID, Title, Details, Created_at FROM Habits WHERE User_ID = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $habits[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
       body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0f4f8;
    display: flex;
    flex-direction: column;  /* Stack elements vertically */
    justify-content: space-between; /* Push footer to the bottom */
    min-height: 100vh; /* Ensure body takes up full viewport height */
    box-sizing: border-box;
}

.container {
    background-color: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    width: 100%;
    text-align: center;
    margin: 20px auto;
    flex: 1; /* Take up remaining space in flexbox */
    position: relative;
}

.logout-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background-color: #e74c3c;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.logout-btn:hover {
    background-color: #c0392b;
}

h1 {
    font-size: 28px;
    color: #333;
    margin-bottom: 10px;
}

.add-habit-btn {
    background-color: #2ecc71;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 12px 20px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: 16px;
}

.add-habit-btn:hover {
    background-color: #27ae60;
}

.habit-form {
    display: none;
    margin-top: 20px;
}

.habit-form input[type="text"],
.habit-form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.habit-form button {
    padding: 12px 20px;
    background-color: #3498db;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.habit-form button:hover {
    background-color: #2980b9;
}

.habit-list {
    margin-top: 30px;
}

.habit {
    background-color: #fafafa;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 10px;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
    text-align: left;
}

.habit a {
    text-decoration: none;
    color: #3498db;
    font-weight: bold;
    transition: color 0.3s ease;
}

.habit a:hover {
    color: #2980b9;
}

.habit h3 {
    margin: 0;
    margin-bottom: 8px;
}

.habit small {
    color: #aaa;
}

.delete-habit-btn {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
    transition: background-color 0.3s ease;
}

.delete-habit-btn:hover {
    background-color: #c0392b;
}

.footer {
    background-color: #007BFF;
    color: white;
    padding: 10px;
    text-align: center;
    width: 100%;
    margin-top: auto; /* Ensures footer is at the bottom */
}

.footer a {
    color: white;
    margin: 0 10px;
    transition: color 0.3s ease;
}

.footer a:hover {
    color: #ccc;
}

    </style>
</head>
<body>
    <div class="container">
        <button class="logout-btn" onclick="window.location.href='index.php'">Logout</button>
        <h1>Welcome, <?php echo htmlspecialchars($first_name); ?>!</h1>
        <p>Ready to build a new habit?</p>
        <button class="add-habit-btn" onclick="showHabitForm()">Add a New Habit</button>

        <div class="habit-form" id="habitForm">
            <h3>New Habit</h3>
            <form method="POST">
                <input type="text" name="habitTitle" id="habitTitle" placeholder="Habit Title" required>
                <textarea name="habitDetails" id="habitDetails" placeholder="Habit Details" rows="4" required></textarea>
                <button type="submit">Submit Habit</button>
            </form>
        </div>

        <div class="habit-list" id="habitList">
            <h3>Your Habits</h3>
            <?php if (count($habits) > 0): ?>
                <?php foreach ($habits as $habit): ?>
                    <div class="habit">
                        <a href="habit_tracker.php?habit_id=<?php echo $habit['ID']; ?>">
                            <h3><?php echo htmlspecialchars($habit['Title']); ?></h3>
                        </a>
                        <p><?php echo htmlspecialchars($habit['Details']); ?></p>
                        <small>Created on: <?php echo $habit['Created_at']; ?></small>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this habit?');">
                            <input type="hidden" name="deleteHabitID" value="<?php echo $habit['ID']; ?>">
                            <button type="submit" class="delete-habit-btn">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No habits found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showHabitForm() {
            document.getElementById('habitForm').style.display = 'block';
        }
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

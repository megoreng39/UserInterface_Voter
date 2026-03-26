<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "voter");

if (!$conn) { die("Connection failed"); }
if (!isset($_SESSION['student_id'])) { header("Location: login.php"); exit(); }

$sid = $_SESSION['student_id'];

// Check if already voted
$check = mysqli_query($conn, "SELECT has_voted FROM users WHERE student_id = '$sid'");
$user = mysqli_fetch_assoc($check);
if ($user['has_voted'] == 1) {
    die("You have already voted. <a href='logout.php'>Logout</a>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $voted_names = []; // To store names for the summary window
    $all_ids = [];

    // Positions to collect
    $positions = ['President', 'Vice_President', 'Governor', 'Vice_Governor', 'Board_Member'];
    
    foreach ($positions as $pos) {
        if (isset($_POST[$pos])) {
            $id = $_POST[$pos];
            $all_ids[] = $id;
            // Get name for summary
            $res = mysqli_query($conn, "SELECT name FROM candidates WHERE id = '$id'");
            $row = mysqli_fetch_assoc($res);
            $voted_names[] = $row['name'];
        }
    }

    // Handle Senators (Checkboxes)
    if (isset($_POST['senators'])) {
        foreach ($_POST['senators'] as $s_id) {
            $all_ids[] = $s_id;
            $res = mysqli_query($conn, "SELECT name FROM candidates WHERE id = '$s_id'");
            $row = mysqli_fetch_assoc($res);
            $voted_names[] = $row['name'];
        }
    }

    // UPDATE DATABASE (The reflection part)
    foreach ($all_ids as $c_id) {
        mysqli_query($conn, "UPDATE candidates SET vote_count = vote_count + 1 WHERE id = '$c_id'");
    }
    
    // Mark user as voted
    mysqli_query($conn, "UPDATE users SET has_voted = 1 WHERE student_id = '$sid'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vote Confirmed</title>
    <style>
        body { font-family: sans-serif; background: rgba(0,0,0,0.1); display: flex; justify-content: center; padding-top: 50px; }
        .window { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 400px; text-align: center; border-top: 8px solid #800000; }
        .summary-list { text-align: left; background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .logout-btn { display: inline-block; padding: 12px 25px; background: #800000; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="window">
        <h2 style="color: green;">✔ Vote Recorded!</h2>
        <p>Your choices have been successfully updated in the database.</p>
        
        <div class="summary-list">
            <strong>Your Ballot Summary:</strong>
            <ul style="font-size: 0.9em; line-height: 1.6;">
                <?php foreach ($voted_names as $name) echo "<li>$name</li>"; ?>
            </ul>
        </div>

        <a href="logout.php" class="logout-btn">Finish & Logout</a>
    </div>
</body>
</html>

<?php
} // End if POST
?>
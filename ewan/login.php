<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "voter");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sid = mysqli_real_escape_string($conn, $_POST['student_id']);
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE student_id = '$sid' AND password = '$pass'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['student_id'] = $user['student_id'];
        $_SESSION['student_name'] = $user['name'];
        $_SESSION['student_program'] = $user['program'];
        header("Location: index.php");
    } else {
        $error = "Invalid Credentials!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Voting Login</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 300px; }
        input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #800000; color: white; border: none; cursor: pointer; width: 100%; }
    </style>
</head>
<body>
    <div class="box">
        <h2 style="text-align:center; color:#800000;">Student Login</h2>
        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="student_id" placeholder="Student ID" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
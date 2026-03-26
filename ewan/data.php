<?php
header('Content-Type: application/json');

// Database credentials
$host = "localhost";
$user = "root";
$pass = "";
$db   = "voter"; // Your database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

// Fetch all candidates and their current vote counts
$query = "SELECT name, vote_count FROM candidates";
$result = $conn->query($query);

$data = [];
while($row = $result->fetch_assoc()) {
    $data[] = [
        'name' => $row['name'],
        'votes' => (int)$row['vote_count']
    ];
}

echo json_encode($data);
$conn->close();
?>
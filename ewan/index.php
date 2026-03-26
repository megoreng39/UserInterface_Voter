<?php
session_start();
// Security: Redirect to login if not authenticated
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "voter");
$sid = $_SESSION['student_id'];
$user_program = $_SESSION['student_program'];
$user_name = $_SESSION['student_name'];

// Double Check: Has user already voted?
$status_check = mysqli_query($conn, "SELECT has_voted FROM users WHERE student_id = '$sid'");
$status = mysqli_fetch_assoc($status_check);

if ($status['has_voted'] == 1) {
    echo "<div style='text-align:center; padding:100px; font-family:sans-serif;'>
            <h2>Vote Already Recorded</h2>
            <p>You have already participated in this election.</p>
            <a href='logout.php' style='color:#800000; font-weight:bold;'>Logout</a>
          </div>";
    exit();
}

// Helper function to render candidates
function renderOptions($conn, $pos, $prog, $type = "radio") {
    $sql = "SELECT * FROM candidates WHERE position = '$pos' AND program = '$prog'";
    $result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($result)) {
        $nameAttr = ($type == "checkbox") ? "senators[]" : str_replace(' ', '_', $pos);
        // Using a unique class for senators to handle JS limit
        $checkClass = ($pos == 'Senator') ? 'sen-check' : '';
        echo "
        <div class='candidate-option'>
            <input type='$type' name='$nameAttr' value='{$row['id']}' class='$checkClass' id='c_{$row['id']}'>
            <label for='c_{$row['id']}'>{$row['name']}</label>
        </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Ballot | <?php echo $user_program; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
        .ballot-container { max-width: 650px; background: white; margin: 0 auto; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); overflow: hidden; }
        
        /* FIXED HEADER CSS */
        .ballot-header { 
            background: #800000; 
            color: white; 
            padding: 15px 25px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; /* This centers items vertically */
        }
        .ballot-header h2 { margin: 0; font-size: 1.3em; }
        .ballot-header small { opacity: 0.8; }
        
        .user-info-wrapper { 
            display: flex; 
            align-items: center; 
            gap: 15px; /* Adds space between text and button */
        }
        .user-text { text-align: right; font-size: 0.9em; line-height: 1.2; }
        
        .logout-link { 
            color: #ffc107; 
            text-decoration: none; 
            font-weight: bold; 
            border: 2px solid #ffc107; 
            padding: 6px 12px; 
            border-radius: 6px;
            white-space: nowrap; /* Prevents button text from wrapping */
            transition: 0.3s;
        }
        .logout-link:hover { background: #ffc107; color: #800000; }

        /* FORM STYLING */
        .section { padding: 20px; border-bottom: 1px solid #eee; }
        .section-title { color: #800000; text-transform: uppercase; font-size: 0.85em; font-weight: bold; margin-bottom: 15px; display: block; letter-spacing: 1px; }
        .candidate-option { padding: 10px; margin: 5px 0; border: 1px solid #f0f0f0; border-radius: 6px; transition: 0.2s; }
        .candidate-option:hover { background: #fffcfc; border-color: #800000; }
        label { cursor: pointer; padding-left: 8px; font-weight: 500; }

        .submit-area { padding: 30px; text-align: center; background: #fdfdfd; }
        .btn-submit { background: #800000; color: white; border: none; padding: 15px 40px; font-size: 1.1em; border-radius: 30px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; }
        .btn-submit:hover { background: #5a0000; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(128,0,0,0.3); }
    </style>
</head>
<body>

<div class="ballot-container">
    <div class="ballot-header">
        <div>
            <h2>Official Election Ballot</h2>
            <small>BulSU Academic Year 2026-2027</small>
        </div>
        
        <div class="user-info-wrapper">
            <div class="user-text">
                Welcome, <strong><?php echo $user_name; ?></strong><br>
                <span><?php echo $user_program; ?> Program</span>
            </div>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <form action="submit_vote.php" method="POST" id="mainBallot">
        
        <div class="section">
            <span class="section-title">University Level (Executive)</span>
            <p><strong>President</strong></p>
            <?php renderOptions($conn, 'President', 'General'); ?>

            <p><strong>Vice President</strong></p>
            <?php renderOptions($conn, 'Vice President', 'General'); ?>
        </div>

        <div class="section">
            <span class="section-title">Legislative Council</span>
            <p><strong>Senators (Vote for up to 7)</strong></p>
            <?php renderOptions($conn, 'Senator', 'General', 'checkbox'); ?>
        </div>

        <div class="section">
            <span class="section-title">Provincial Council</span>
            <p><strong>Governor</strong></p>
            <?php renderOptions($conn, 'Governor', 'General'); ?>

            <p><strong>Vice Governor</strong></p>
            <?php renderOptions($conn, 'Vice Governor', 'General'); ?>
        </div>

        <div class="section" style="background: #fff9f9;">
            <span class="section-title"><?php echo $user_program; ?> Local Council</span>
            <p><strong>Board Member</strong></p>
            <?php renderOptions($conn, 'Board Member', $user_program); ?>
        </div>

        <div class="submit-area">
            <button type="submit" class="btn-submit">Submit Final Vote</button>
            <p style="font-size: 0.8em; color: #777; margin-top: 15px;">Please review your choices carefully before submitting.</p>
        </div>
    </form>
</div>

<script>
    // Senator Limit Logic
    const senChecks = document.querySelectorAll('.sen-check');
    senChecks.forEach(box => {
        box.onclick = () => {
            const count = document.querySelectorAll('.sen-check:checked').length;
            if (count > 7) {
                box.checked = false;
                alert("Maximum of 7 Senators only.");
            }
        };
    });

    // Submit Confirmation
    document.getElementById('mainBallot').onsubmit = function() {
        return confirm("Ready to cast your official vote for the <?php echo $user_program; ?> council?");
    };
</script>

</body>
</html>

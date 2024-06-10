<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header('Location: login.php');
    exit;
}

$task_id = isset($_GET['task_id']) ? $_GET['task_id'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    $user_id = $_SESSION['user_id'];
    $target_dir = "uploads/";
    $file_name = basename($_FILES["file"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file already exists, and rename it
    if (file_exists($target_file)) {
        $file_name = time() . "_" . $file_name; // Rename the file
        $target_file = $target_dir . $file_name;
    }

    // Check file size
    if ($_FILES["file"]["size"] > 500000) { // 500KB limit
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg"
    && $fileType != "gif" && $fileType != "pdf" && $fileType != "doc" && $fileType != "docx") {
        echo "Sorry, only JPG, JPEG, PNG, GIF, PDF, DOC, & DOCX files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $stmt = $pdo->prepare("INSERT INTO submissions (task_id, user_id, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$task_id, $user_id, $target_file]);
            echo "The file ". htmlspecialchars($file_name). " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kirim Tugas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .submit-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        .submit-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .submit-container input[type="file"] {
            margin: 20px 0;
        }
        .submit-container input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
            transition: background-color 0.3s ease;
        }
        .submit-container input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .navbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        .navbar a {
            text-decoration: none;
            color: #007bff;
            margin: 0 10px;
            transition: color 0.3s ease;
        }
        .navbar a:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="submit-container">
        <h2>Kirim Tugas</h2>
        <div class="navbar">
            <a href="user_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
        <form method="POST" action="submit_task.php" enctype="multipart/form-data">
            <input type="hidden" name="task_id" value="<?= htmlspecialchars($task_id) ?>">
            <input type="file" name="file" required><br>
            <input type="submit" value="Kirim">
        </form>
    </div>
</body>
</html>

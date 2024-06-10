<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $assigned_to = $_POST['assigned_to']; // User ID to whom the task is assigned
    $assigned_by = $_SESSION['user_id']; // Admin ID

    $stmt = $pdo->prepare("INSERT INTO tasks (title, description, due_date, category, priority, assigned_to, assigned_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $due_date, $category, $priority, $assigned_to, $assigned_by]);
    header('Location: admin_dashboard.php');
    exit;
}

// Fetch users to assign tasks
$users_stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'user'");
$users = $users_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah List Tugas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        form input[type="text"],
        form textarea,
        form input[type="datetime-local"],
        form select {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px auto;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        form input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        form input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
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
    <div class="container">
        <h1>Silahkan Input Data</h1>
        <div class="navbar">
            <a href="admin_dashboard.php">Dashboard Admin</a>
            <a href="logout.php">Logout</a>
        </div>
        <form method="POST" action="add_task.php">
            <input type="text" name="title" placeholder="Judul Tugas" required><br>
            <textarea name="description" placeholder="Deskripsi" required></textarea><br>
            <input type="datetime-local" name="due_date" required><br>
            <input type="text" name="category" placeholder="Macam Tugas" required><br>
            <select name="priority" required>
                <option value="" disabled selected>Skala Prioritas</option>
                <option value="2">High</option>
                <option value="1">Middle</option>
                <option value="0">Low</option>
            </select><br>
            <select name="assigned_to" required>
                <option value="" disabled selected>Assign To</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                <?php endforeach; ?>
            </select><br>
            <input type="submit" value="Add Tugas">
        </form>
    </div>
</body>
</html>

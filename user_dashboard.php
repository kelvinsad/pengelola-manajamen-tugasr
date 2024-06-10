<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

$query = "SELECT * FROM tasks WHERE assigned_to = ? ";
$params = [$user_id];

if ($from_date && $to_date) {
    $query .= "AND due_date BETWEEN ? AND ? ";
    $params[] = $from_date . " 00:00:00";
    $params[] = $to_date . " 23:59:59";
} elseif ($from_date) {
    $query .= "AND due_date >= ? ";
    $params[] = $from_date . " 00:00:00";
} elseif ($to_date) {
    $query .= "AND due_date <= ? ";
    $params[] = $to_date . " 23:59:59";
}

$query .= "ORDER BY due_date ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Priority mapping array
$priority_mapping = [
    0 => 'Low',
    1 => 'Middle',
    2 => 'High'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            width: 80%;
            max-width: 1000px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        .filter-form {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .filter-form label {
            margin-right: 10px;
        }
        .filter-form input[type="date"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }
        .filter-form input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .filter-form input[type="submit"]:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hi, <?= htmlspecialchars($user['username']) ?>!</h1>
        <div class="navbar">
            <a href="logout.php">Keluar</a>
        </div>
        <form class="filter-form" method="GET" action="user_dashboard.php">
            <label for="from_date">Dari: </label>
            <input type="date" name="from_date" id="from_date" value="<?= htmlspecialchars($from_date) ?>">
            <label for="to_date">Ke: </label>
            <input type="date" name="to_date" id="to_date" value="<?= htmlspecialchars($to_date) ?>">
            <input type="submit" value="Saring">
        </form>
        <table>
            <tr>
                <th>Judul Tugas</th>
                <th>Deskripsi</th>
                <th>Tenggat Waktu</th>
                <th>Macam Tugas</th>
                <th>Skala Prioritas</th>
                <th>Kemajuan</th>
                <th>Penyerahan</th>
            </tr>
            <?php if (count($tasks) > 0): ?>
                <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['title']) ?></td>
                    <td><?= htmlspecialchars($task['description']) ?></td>
                    <td><?= htmlspecialchars($task['due_date']) ?></td>
                    <td><?= htmlspecialchars($task['category']) ?></td>
                    <td><?= htmlspecialchars($priority_mapping[$task['priority']]) ?></td>
                    <td><?= htmlspecialchars($task['progress']) ?>%</td>
                    <td><a href="submit_task.php?task_id=<?= $task['id'] ?>">Submit</a></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Tidak ditemukan tugas untuk rentang tanggal yang dipilih.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>

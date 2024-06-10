<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch all users for admin
$users_stmt = $pdo->query("SELECT id, username, email, role FROM users");
$users = $users_stmt->fetchAll();

// Fetch tasks (admin can see all tasks)
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

$query = "SELECT * FROM tasks ";
$params = [];

if ($from_date && $to_date) {
    $query .= "WHERE due_date BETWEEN ? AND ? ";
    $params[] = $from_date . " 00:00:00";
    $params[] = $to_date . " 23:59:59";
} elseif ($from_date) {
    $query .= "WHERE due_date >= ? ";
    $params[] = $from_date . " 00:00:00";
} elseif ($to_date) {
    $query .= "WHERE due_date <= ? ";
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

// Fetch submissions
$submissions_query = "
    SELECT submissions.id, tasks.title AS task_title, users.username AS user_name, submissions.file_path, submissions.status
    FROM submissions
    JOIN tasks ON submissions.task_id = tasks.id
    JOIN users ON submissions.user_id = users.id
";
$submissions_stmt = $pdo->prepare($submissions_query);
$submissions_stmt->execute();
$submissions = $submissions_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 80%;
            max-width: 1200px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            font-size: 2em;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .navbar a {
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .navbar a:hover {
            background-color: #0056b3;
        }
        .filter-form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .filter-form label {
            margin: 0 10px;
            font-weight: bold;
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
            transition: background-color 0.3s ease;
        }
        .filter-form input[type="submit"]:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .actions a {
            text-decoration: none;
            color: #007bff;
            margin: 0 5px;
            transition: color 0.3s ease;
        }
        .actions a:hover {
            color: #0056b3;
        }
        h2 {
            color: #333;
            margin-bottom: 10px;
            border-bottom: 2px solid #007bff;
            display: inline-block;
            padding-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hi, Admin <?= htmlspecialchars($user['username']) ?>!</h1>
        <div class="navbar">
            <a href="add_task.php">Add Task</a>
            <a href="logout.php">Logout</a>
        </div>
        <form class="filter-form" method="GET" action="admin_dashboard.php">
            <label for="from_date">From:</label>
            <input type="date" name="from_date" id="from_date" value="<?= htmlspecialchars($from_date) ?>">
            <label for="to_date">To:</label>
            <input type="date" name="to_date" id="to_date" value="<?= htmlspecialchars($to_date) ?>">
            <input type="submit" value="Filter">
        </form>
        <h2>Tasks</h2>
        <table>
            <tr>
                <th>Judul Tugas</th>
                <th>Deskripsi</th>
                <th>Tenggat Waktu</th>
                <th>Macam Tugas</th>
                <th>Skala Prioritas</th>
                <th>Kemajuan</th>
                <th>Aksi</th>
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
                    <td class="actions">
                        <a href="edit_task.php?id=<?= $task['id'] ?>">Edit</a>
                        <a href="delete_task.php?id=<?= $task['id'] ?>" onclick="return confirm('Are you sure you want to delete this task?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No tasks found for the selected date range.</td>
                </tr>
            <?php endif; ?>
        </table>
        <h2>Manage Users</h2>
        <table>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td class="actions">
                        <a href="edit_user.php?id=<?= $user['id'] ?>">Edit</a>
                        <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <h2>Submissions</h2>
        <table>
            <tr>
                <th>Task Title</th>
                <th>Username</th>
                <th>File</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php if (count($submissions) > 0): ?>
                <?php foreach ($submissions as $submission): ?>
                <tr>
                    <td><?= htmlspecialchars($submission['task_title']) ?></td>
                    <td><?= htmlspecialchars($submission['user_name']) ?></td>
                    <td><a href="<?= htmlspecialchars($submission['file_path']) ?>">View File</a></td>
                    <td><?= htmlspecialchars($submission['status']) ?></td>
                    <td class="actions">
                        <a href="accept_submission.php?id=<?= $submission['id'] ?>">Accept</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No submissions found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>

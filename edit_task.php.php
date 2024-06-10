<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();

    if (!$task) {
        die('Task not found.');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $task_id = $_POST['task_id'];

    $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ?, category = ?, priority = ? WHERE id = ?");
    $stmt->execute([$title, $description, $due_date, $category, $priority, $task_id]);

    header('Location: admin_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Tugas</title>
    <style>
        /* Style yang sudah ada */
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Tugas</h1>
        <form method="POST" action="edit_task.php">
            <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id']) ?>">
            <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" required><br>
            <textarea name="description" required><?= htmlspecialchars($task['description']) ?></textarea><br>
            <input type="datetime-local" name="due_date" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($task['due_date']))) ?>" required><br>
            <input type="text" name="category" value="<?= htmlspecialchars($task['category']) ?>" required><br>
            <select name="priority" required>
                <option value="2" <?= $task['priority'] == 2 ? 'selected' : '' ?>>High</option>
                <option value="1" <?= $task['priority'] == 1 ? 'selected' : '' ?>>Middle</option>
                <option value="0" <?= $task['priority'] == 0 ? 'selected' : '' ?>>Low</option>
            </select><br>
            <input type="submit" value="Update Tugas">
        </form>
    </div>
</body>
</html>

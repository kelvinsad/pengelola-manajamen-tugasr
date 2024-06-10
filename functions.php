<?php
require 'config.php';

function register_user($email, $username, $password) {
    global $conn;
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $sql = "INSERT INTO users (email, username, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed: " . $conn->error;
        return false;
    }
    $stmt->bind_param("sss", $email, $username, $password_hash);
    if (!$stmt->execute()) {
        echo "Execute failed: " . $stmt->error;
        return false;
    }
    return true;
}
function authenticate_user($username, $password) {
    global $conn;
    $sql = "SELECT id, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password_hash);
        $stmt->fetch();
        if (password_verify($password, $password_hash)) {
            return $id;
        }
    }
    return false;
}

function add_task($user_id, $title, $description, $due_date, $category, $priority) {
    global $conn;
    $sql = "INSERT INTO tasks (user_id, title, description, due_date, category, priority) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssi", $user_id, $title, $description, $due_date, $category, $priority);
    return $stmt->execute();
}

function get_user_tasks($user_id) {
    global $conn;
    $sql = "SELECT id, title, description, due_date, category, priority FROM tasks WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}
?>

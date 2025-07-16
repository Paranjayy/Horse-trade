<?php
include('includes/db.php');
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

// Get current user
$current_user_query = "SELECT * FROM users WHERE email = ?";
$current_user_stmt = $conn->prepare($current_user_query);
$current_user_stmt->bind_param("s", $_SESSION['email']);
$current_user_stmt->execute();
$current_user = $current_user_stmt->get_result()->fetch_assoc();

$errors = [];
$success = false;

// Sanitize and validate input
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone'] ?? '');
$location = trim($_POST['location'] ?? '');
$user_type = $_POST['user_type'];

// Validation
if (empty($name)) {
    $errors[] = "Name is required.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email is required.";
}

// Check if email is taken by another user
if ($email !== $current_user['email']) {
    $email_check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
    $email_check_stmt = $conn->prepare($email_check_query);
    $email_check_stmt->bind_param("si", $email, $current_user['id']);
    $email_check_stmt->execute();
    if ($email_check_stmt->get_result()->num_rows > 0) {
        $errors[] = "Email is already in use by another account.";
    }
}

if (!in_array($user_type, ['buyer', 'seller', 'both'])) {
    $errors[] = "Invalid user type selected.";
}

// Update profile if no errors
if (empty($errors)) {
    $update_query = "UPDATE users SET name = ?, email = ?, phone = ?, location = ?, user_type = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssssi", $name, $email, $phone, $location, $user_type, $current_user['id']);
    
    if ($update_stmt->execute()) {
        // Update session email if it changed
        $_SESSION['email'] = $email;
        $success = true;
    } else {
        $errors[] = "Failed to update profile. Please try again.";
    }
}

// Redirect back to dashboard with messages
if ($success) {
    header("Location: dashboard.php?updated=1");
} else {
    $error_string = implode('|', $errors);
    header("Location: dashboard.php?errors=" . urlencode($error_string));
}
exit();
?> 
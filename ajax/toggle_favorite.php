<?php
include('../includes/db.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['horse_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing horse ID']);
    exit();
}

$horse_id = (int)$input['horse_id'];

// Get user ID
$user_query = "SELECT id FROM users WHERE email = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $_SESSION['email']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Check if already favorited
$check_query = "SELECT id FROM favorites WHERE user_id = ? AND horse_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $user['id'], $horse_id);
$check_stmt->execute();
$existing = $check_stmt->get_result()->fetch_assoc();

if ($existing) {
    // Remove favorite
    $delete_query = "DELETE FROM favorites WHERE user_id = ? AND horse_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("ii", $user['id'], $horse_id);
    $delete_stmt->execute();
    
    echo json_encode([
        'success' => true,
        'is_favorite' => false,
        'message' => 'Removed from favorites'
    ]);
} else {
    // Add favorite
    $insert_query = "INSERT INTO favorites (user_id, horse_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ii", $user['id'], $horse_id);
    
    if ($insert_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'is_favorite' => true,
            'message' => 'Added to favorites'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add favorite']);
    }
}
?> 
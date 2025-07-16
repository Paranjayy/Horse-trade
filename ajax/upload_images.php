<?php
include('../includes/db.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if (!isset($_POST['horse_id']) || !isset($_FILES['images'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

$horse_id = (int)$_POST['horse_id'];
$user_query = "SELECT id FROM users WHERE email = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $_SESSION['email']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Verify horse belongs to user
$horse_query = "SELECT id FROM horses WHERE id = ? AND user_id = ?";
$horse_stmt = $conn->prepare($horse_query);
$horse_stmt->bind_param("ii", $horse_id, $user['id']);
$horse_stmt->execute();
if (!$horse_stmt->get_result()->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => 'Horse not found or access denied']);
    exit();
}

$upload_dir = '../uploads/';
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB
$uploaded_files = [];

foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
    if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
        continue;
    }
    
    $file_size = $_FILES['images']['size'][$key];
    $file_type = $_FILES['images']['type'][$key];
    $original_name = $_FILES['images']['name'][$key];
    
    // Validate file
    if (!in_array($file_type, $allowed_types)) {
        continue;
    }
    
    if ($file_size > $max_size) {
        continue;
    }
    
    // Generate unique filename
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    $filename = 'horse_' . $horse_id . '_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($tmp_name, $filepath)) {
        // Insert into database
        $is_primary = count($uploaded_files) === 0 ? 1 : 0; // First image is primary
        $insert_query = "INSERT INTO horse_images (horse_id, image_path, is_primary) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $image_path = 'uploads/' . $filename;
        $insert_stmt->bind_param("isi", $horse_id, $image_path, $is_primary);
        
        if ($insert_stmt->execute()) {
            $uploaded_files[] = [
                'id' => $conn->insert_id,
                'path' => $image_path,
                'is_primary' => $is_primary
            ];
        }
    }
}

echo json_encode([
    'success' => true,
    'message' => count($uploaded_files) . ' images uploaded successfully',
    'files' => $uploaded_files
]);
?> 
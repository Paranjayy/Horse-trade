<?php
include('includes/db.php');
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$horse_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$horse_id) {
    header("Location: dashboard.php");
    exit();
}

// Get user ID
$user_query = "SELECT id FROM users WHERE email = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $_SESSION['email']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Get horse details and verify ownership
$horse_query = "SELECT * FROM horses WHERE id = ? AND user_id = ?";
$horse_stmt = $conn->prepare($horse_query);
$horse_stmt->bind_param("ii", $horse_id, $user['id']);
$horse_stmt->execute();
$horse = $horse_stmt->get_result()->fetch_assoc();

if (!$horse) {
    header("Location: dashboard.php");
    exit();
}

$errors = [];
$success = '';

if (isset($_POST['update_horse'])) {
    // Sanitize and validate input
    $name = trim($_POST['name']);
    $breed = trim($_POST['breed']);
    $category_id = $_POST['category_id'];
    $age = (int)$_POST['age'];
    $gender = $_POST['gender'];
    $color = trim($_POST['color']);
    $height = (float)$_POST['height'];
    $price = (float)$_POST['price'];
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $training_level = $_POST['training_level'];
    $disciplines = isset($_POST['disciplines']) ? implode(',', $_POST['disciplines']) : '';
    $health_status = trim($_POST['health_status']);
    $vaccinations_current = isset($_POST['vaccinations_current']) ? 1 : 0;
    $registration_papers = isset($_POST['registration_papers']) ? 1 : 0;
    $status = $_POST['status'];

    // Validation
    if (empty($name)) $errors[] = "Horse name is required.";
    if (empty($breed)) $errors[] = "Breed is required.";
    if ($age <= 0 || $age > 50) $errors[] = "Please enter a valid age.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if ($height <= 0 || $height > 20) $errors[] = "Please enter a valid height.";
    if ($price <= 0) $errors[] = "Please enter a valid price.";
    if (empty($location)) $errors[] = "Location is required.";
    if (empty($description)) $errors[] = "Description is required.";

    // Update horse if no errors
    if (empty($errors)) {
        $update_query = "UPDATE horses SET name=?, breed=?, category_id=?, age=?, gender=?, color=?, height=?, price=?, location=?, description=?, training_level=?, disciplines=?, health_status=?, vaccinations_current=?, registration_papers=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=? AND user_id=?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssisssdssssssiisii", $name, $breed, $category_id, $age, $gender, $color, $height, $price, $location, $description, $training_level, $disciplines, $health_status, $vaccinations_current, $registration_papers, $status, $horse_id, $user['id']);
        
        if ($update_stmt->execute()) {
            $success = "Horse listing updated successfully! <a href='horse_detail.php?id=$horse_id'>View listing</a>";
            
            // Refresh horse data
            $horse_stmt->execute();
            $horse = $horse_stmt->get_result()->fetch_assoc();
        } else {
            $errors[] = "Failed to update listing. Please try again.";
        }
    }
}

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Get existing disciplines
$existing_disciplines = !empty($horse['disciplines']) ? explode(',', $horse['disciplines']) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo htmlspecialchars($horse['name']); ?> - HorseTrader</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include('includes/navbar.php'); ?>

<div class="add-horse-page">
    <div class="container">
        <div class="page-header">
            <h1>Edit Horse Listing</h1>
            <p>Update your horse's information</p>
        </div>

        <div class="form-container">
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="add-horse-form">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3>Basic Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Horse Name *</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?php echo htmlspecialchars($horse['name']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="breed">Breed *</label>
                            <input type="text" id="breed" name="breed" required 
                                   value="<?php echo htmlspecialchars($horse['breed']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                <?php 
                                $categories->data_seek(0);
                                while ($category = $categories->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $horse['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="age">Age (years) *</label>
                            <input type="number" id="age" name="age" min="1" max="50" required 
                                   value="<?php echo $horse['age']; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo $horse['gender'] == 'male' ? 'selected' : ''; ?>>Stallion</option>
                                <option value="female" <?php echo $horse['gender'] == 'female' ? 'selected' : ''; ?>>Mare</option>
                                <option value="gelding" <?php echo $horse['gender'] == 'gelding' ? 'selected' : ''; ?>>Gelding</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="color">Color</label>
                            <input type="text" id="color" name="color" 
                                   value="<?php echo htmlspecialchars($horse['color']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="height">Height (hands) *</label>
                            <input type="number" id="height" name="height" step="0.1" min="10" max="20" required 
                                   value="<?php echo $horse['height']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="price">Price (USD) *</label>
                            <input type="number" id="price" name="price" min="1" step="0.01" required 
                                   value="<?php echo $horse['price']; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="location">Location *</label>
                            <input type="text" id="location" name="location" placeholder="City, State" required 
                                   value="<?php echo htmlspecialchars($horse['location']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="available" <?php echo $horse['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="pending" <?php echo $horse['status'] == 'pending' ? 'selected' : ''; ?>>Pending Sale</option>
                                <option value="sold" <?php echo $horse['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Training and Disciplines -->
                <div class="form-section">
                    <h3>Training & Disciplines</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="training_level">Training Level</label>
                            <select id="training_level" name="training_level">
                                <option value="untrained" <?php echo $horse['training_level'] == 'untrained' ? 'selected' : ''; ?>>Untrained</option>
                                <option value="basic" <?php echo $horse['training_level'] == 'basic' ? 'selected' : ''; ?>>Basic</option>
                                <option value="intermediate" <?php echo $horse['training_level'] == 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="advanced" <?php echo $horse['training_level'] == 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                <option value="professional" <?php echo $horse['training_level'] == 'professional' ? 'selected' : ''; ?>>Professional</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Disciplines (check all that apply)</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="disciplines[]" value="dressage" 
                                       <?php echo in_array('dressage', $existing_disciplines) ? 'checked' : ''; ?>>
                                Dressage
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="disciplines[]" value="jumping" 
                                       <?php echo in_array('jumping', $existing_disciplines) ? 'checked' : ''; ?>>
                                Jumping
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="disciplines[]" value="racing" 
                                       <?php echo in_array('racing', $existing_disciplines) ? 'checked' : ''; ?>>
                                Racing
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="disciplines[]" value="western" 
                                       <?php echo in_array('western', $existing_disciplines) ? 'checked' : ''; ?>>
                                Western
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="disciplines[]" value="trail" 
                                       <?php echo in_array('trail', $existing_disciplines) ? 'checked' : ''; ?>>
                                Trail Riding
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="disciplines[]" value="breeding" 
                                       <?php echo in_array('breeding', $existing_disciplines) ? 'checked' : ''; ?>>
                                Breeding
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-section">
                    <h3>Description</h3>
                    <div class="form-group">
                        <label for="description">Detailed Description *</label>
                        <textarea id="description" name="description" rows="6" required 
                                  placeholder="Describe your horse's temperament, training, experience, and any other important details..."><?php echo htmlspecialchars($horse['description']); ?></textarea>
                    </div>
                </div>

                <!-- Health Information -->
                <div class="form-section">
                    <h3>Health & Documentation</h3>
                    <div class="form-group">
                        <label for="health_status">Health Status</label>
                        <textarea id="health_status" name="health_status" rows="3" 
                                  placeholder="Any health conditions, recent vet checks, etc."><?php echo htmlspecialchars($horse['health_status']); ?></textarea>
                    </div>

                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="vaccinations_current" value="1" 
                                   <?php echo $horse['vaccinations_current'] ? 'checked' : ''; ?>>
                            Vaccinations are current
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="registration_papers" value="1" 
                                   <?php echo $horse['registration_papers'] ? 'checked' : ''; ?>>
                            Has registration papers
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update_horse" class="btn btn-primary">Update Listing</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    <a href="horse_detail.php?id=<?php echo $horse_id; ?>" class="btn btn-success">View Listing</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

</body>
</html> 
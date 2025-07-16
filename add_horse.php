<?php
include('includes/db.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get user ID
$user_query = "SELECT id FROM users WHERE email = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $_SESSION['email']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

$errors = [];
$success = '';

if (isset($_POST['add_horse'])) {
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

    // Validation
    if (empty($name)) $errors[] = "Horse name is required.";
    if (empty($breed)) $errors[] = "Breed is required.";
    if ($age <= 0 || $age > 50) $errors[] = "Please enter a valid age.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if ($height <= 0 || $height > 20) $errors[] = "Please enter a valid height.";
    if ($price <= 0) $errors[] = "Please enter a valid price.";
    if (empty($location)) $errors[] = "Location is required.";
    if (empty($description)) $errors[] = "Description is required.";

    // Insert horse if no errors
    if (empty($errors)) {
        $insert_query = "INSERT INTO horses (user_id, name, breed, category_id, age, gender, color, height, price, location, description, training_level, disciplines, health_status, vaccinations_current, registration_papers) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("issiissddsssssii", $user['id'], $name, $breed, $category_id, $age, $gender, $color, $height, $price, $location, $description, $training_level, $disciplines, $health_status, $vaccinations_current, $registration_papers);
        
        if ($insert_stmt->execute()) {
            $horse_id = $conn->insert_id;
            
            // Handle image uploads
            if (isset($_FILES['images']) && !empty($_FILES['images']['tmp_name'][0])) {
                $upload_dir = 'uploads/';
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024; // 5MB
                $uploaded_count = 0;
                
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK || $uploaded_count >= 5) {
                        continue;
                    }
                    
                    $file_size = $_FILES['images']['size'][$key];
                    $file_type = $_FILES['images']['type'][$key];
                    $original_name = $_FILES['images']['name'][$key];
                    
                    // Validate file
                    if (!in_array($file_type, $allowed_types) || $file_size > $max_size) {
                        continue;
                    }
                    
                    // Generate unique filename
                    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
                    $filename = 'horse_' . $horse_id . '_' . uniqid() . '.' . $extension;
                    $filepath = $upload_dir . $filename;
                    
                    if (move_uploaded_file($tmp_name, $filepath)) {
                        // Insert into database
                        $is_primary = $uploaded_count === 0 ? 1 : 0; // First image is primary
                        $image_insert_query = "INSERT INTO horse_images (horse_id, image_path, is_primary) VALUES (?, ?, ?)";
                        $image_insert_stmt = $conn->prepare($image_insert_query);
                        $image_path = $filepath;
                        $image_insert_stmt->bind_param("isi", $horse_id, $image_path, $is_primary);
                        $image_insert_stmt->execute();
                        $uploaded_count++;
                    }
                }
            }
            
            $success = "Horse listing created successfully! <a href='horse_detail.php?id=$horse_id'>View your listing</a>";
            
            // Reset form
            $_POST = array();
        } else {
            $errors[] = "Failed to create listing. Please try again.";
        }
    }
}

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Your Horse - HorseTrader</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include('includes/navbar.php'); ?>

<div class="add-horse-page">
    <div class="container">
        <div class="page-header">
            <h1>List Your Horse for Sale</h1>
            <p>Reach thousands of potential buyers by listing your horse on HorseTrader</p>
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

            <form method="POST" class="add-horse-form" enctype="multipart/form-data">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3>Basic Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Horse Name *</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="breed">Breed *</label>
                            <input type="text" id="breed" name="breed" required 
                                   value="<?php echo isset($_POST['breed']) ? htmlspecialchars($_POST['breed']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                <?php while ($category = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="age">Age (years) *</label>
                            <input type="number" id="age" name="age" min="1" max="50" required 
                                   value="<?php echo isset($_POST['age']) ? $_POST['age'] : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'selected' : ''; ?>>Stallion</option>
                                <option value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'selected' : ''; ?>>Mare</option>
                                <option value="gelding" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'gelding') ? 'selected' : ''; ?>>Gelding</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="color">Color</label>
                            <input type="text" id="color" name="color" 
                                   value="<?php echo isset($_POST['color']) ? htmlspecialchars($_POST['color']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="height">Height (hands) *</label>
                            <input type="number" id="height" name="height" step="0.1" min="10" max="20" required 
                                   value="<?php echo isset($_POST['height']) ? $_POST['height'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="price">Price (USD) *</label>
                            <input type="number" id="price" name="price" min="1" step="0.01" required 
                                   value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" placeholder="City, State" required 
                               value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                    </div>
                </div>

                <!-- Training and Disciplines -->
                <div class="form-section">
                    <h3>Training & Disciplines</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="training_level">Training Level</label>
                            <select id="training_level" name="training_level">
                                <option value="untrained" <?php echo (isset($_POST['training_level']) && $_POST['training_level'] == 'untrained') ? 'selected' : ''; ?>>Untrained</option>
                                <option value="basic" <?php echo (isset($_POST['training_level']) && $_POST['training_level'] == 'basic') ? 'selected' : ''; ?>>Basic</option>
                                <option value="intermediate" <?php echo (isset($_POST['training_level']) && $_POST['training_level'] == 'intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="advanced" <?php echo (isset($_POST['training_level']) && $_POST['training_level'] == 'advanced') ? 'selected' : ''; ?>>Advanced</option>
                                <option value="professional" <?php echo (isset($_POST['training_level']) && $_POST['training_level'] == 'professional') ? 'selected' : ''; ?>>Professional</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Disciplines (check all that apply)</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="disciplines[]" value="dressage" 
                                       <?php echo (isset($_POST['disciplines']) && in_array('dressage', $_POST['disciplines'])) ? 'checked' : ''; ?>>
                                Dressage
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="disciplines[]" value="jumping" 
                                       <?php echo (isset($_POST['disciplines']) && in_array('jumping', $_POST['disciplines'])) ? 'checked' : ''; ?>>
                                Jumping
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="disciplines[]" value="racing" 
                                       <?php echo (isset($_POST['disciplines']) && in_array('racing', $_POST['disciplines'])) ? 'checked' : ''; ?>>
                                Racing
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="disciplines[]" value="western" 
                                       <?php echo (isset($_POST['disciplines']) && in_array('western', $_POST['disciplines'])) ? 'checked' : ''; ?>>
                                Western
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="disciplines[]" value="trail" 
                                       <?php echo (isset($_POST['disciplines']) && in_array('trail', $_POST['disciplines'])) ? 'checked' : ''; ?>>
                                Trail Riding
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="disciplines[]" value="breeding" 
                                       <?php echo (isset($_POST['disciplines']) && in_array('breeding', $_POST['disciplines'])) ? 'checked' : ''; ?>>
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
                                  placeholder="Describe your horse's temperament, training, experience, and any other important details..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>
                </div>

                <!-- Health Information -->
                <div class="form-section">
                    <h3>Health & Documentation</h3>
                    <div class="form-group">
                        <label for="health_status">Health Status</label>
                        <textarea id="health_status" name="health_status" rows="3" 
                                  placeholder="Any health conditions, recent vet checks, etc."><?php echo isset($_POST['health_status']) ? htmlspecialchars($_POST['health_status']) : ''; ?></textarea>
                    </div>

                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="vaccinations_current" value="1" 
                                   <?php echo (isset($_POST['vaccinations_current'])) ? 'checked' : ''; ?>>
                            Vaccinations are current
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="registration_papers" value="1" 
                                   <?php echo (isset($_POST['registration_papers'])) ? 'checked' : ''; ?>>
                            Has registration papers
                        </label>
                    </div>
                </div>

                <!-- Image Upload Section -->
                <div class="form-section">
                    <h3>Horse Images</h3>
                    <div class="form-group">
                        <label for="images">Upload Photos (Max 5 images, 5MB each)</label>
                        <input type="file" id="images" name="images[]" multiple accept="image/*" class="image-upload">
                        <div class="upload-help">
                            <p>📸 Upload high-quality photos showing your horse from different angles</p>
                            <p>💡 The first image will be used as the main photo</p>
                        </div>
                    </div>
                    <div id="imagePreview" class="image-preview"></div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="add_horse" class="btn btn-primary">List My Horse</button>
                    <a href="horses.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<style>
/* Add Horse Form Styles */
.add-horse-page {
    padding: 40px 0;
    background: #f8f9fa;
    min-height: 100vh;
}

.page-header {
    text-align: center;
    margin-bottom: 50px;
}

.page-header h1 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.page-header p {
    color: #7f8c8d;
    font-size: 1.1rem;
}

.form-container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e1e8ed;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section h3 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.3rem;
}

.checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: normal;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.form-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #e1e8ed;
}

.add-horse-form textarea {
    resize: vertical;
    min-height: 100px;
}

.error-messages {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.error {
    margin-bottom: 8px;
}

.error:last-child {
    margin-bottom: 0;
}

.success-message {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    text-align: center;
}

@media (max-width: 768px) {
    .form-container {
        margin: 0 20px;
        padding: 20px;
    }
    
    .checkbox-group {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: center;
    }
}

.upload-help {
    margin-top: 10px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.upload-help p {
    margin: 5px 0;
    color: #555;
    font-size: 14px;
}

.image-upload {
    margin-bottom: 15px;
}

.upload-progress {
    margin-top: 15px;
    padding: 15px;
    background: #e3f2fd;
    border-radius: 8px;
    text-align: center;
    display: none;
}
</style>

<script>
// Image preview functionality
document.getElementById('images').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    const files = Array.from(e.target.files).slice(0, 5); // Limit to 5 images
    
    files.forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'image-preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}">
                    <div class="image-info">
                        <small>${file.name}</small>
                        <span class="primary-badge" style="${index === 0 ? '' : 'display: none;'}">Main Photo</span>
                    </div>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    });
});

// Form validation
document.querySelector('.add-horse-form').addEventListener('submit', function(e) {
    const requiredFields = ['name', 'breed', 'age', 'gender', 'height', 'price', 'location', 'description'];
    let hasErrors = false;
    
    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.value.trim()) {
            input.style.borderColor = '#e74c3c';
            hasErrors = true;
        } else {
            input.style.borderColor = '#e1e8ed';
        }
    });
    
    if (hasErrors) {
        e.preventDefault();
        alert('Please fill in all required fields.');
    }
});
</script>

</body>
</html> 
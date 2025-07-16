<?php
include('includes/db.php');
session_start();

$horse_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$horse_id) {
    header("Location: horses.php");
    exit();
}

// Get horse details
$query = "SELECT h.*, c.name as category_name, u.name as seller_name, u.email as seller_email, u.phone as seller_phone, u.location as seller_location
          FROM horses h 
          LEFT JOIN categories c ON h.category_id = c.id 
          LEFT JOIN users u ON h.user_id = u.id 
          WHERE h.id = ? AND h.status = 'available'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $horse_id);
$stmt->execute();
$horse = $stmt->get_result()->fetch_assoc();

if (!$horse) {
    header("Location: horses.php");
    exit();
}

// Get horse images
$images_query = "SELECT * FROM horse_images WHERE horse_id = ? ORDER BY is_primary DESC, id ASC";
$images_stmt = $conn->prepare($images_query);
$images_stmt->bind_param("i", $horse_id);
$images_stmt->execute();
$images = $images_stmt->get_result();

// Handle contact form submission
$contact_success = '';
$contact_error = '';
if (isset($_POST['send_inquiry']) && isset($_SESSION['email'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $buyer_query = "SELECT id FROM users WHERE email = ?";
        $buyer_stmt = $conn->prepare($buyer_query);
        $buyer_stmt->bind_param("s", $_SESSION['email']);
        $buyer_stmt->execute();
        $buyer = $buyer_stmt->get_result()->fetch_assoc();
        
        if ($buyer) {
            $inquiry_query = "INSERT INTO inquiries (horse_id, buyer_id, seller_id, message) VALUES (?, ?, ?, ?)";
            $inquiry_stmt = $conn->prepare($inquiry_query);
            $inquiry_stmt->bind_param("iiis", $horse_id, $buyer['id'], $horse['user_id'], $message);
            
            if ($inquiry_stmt->execute()) {
                $contact_success = "Your inquiry has been sent to the seller!";
            } else {
                $contact_error = "Failed to send inquiry. Please try again.";
            }
        }
    } else {
        $contact_error = "Please enter a message.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($horse['name']); ?> - Horse Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include('includes/navbar.php'); ?>

<div class="horse-detail-page">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="index.php">Home</a> > 
            <a href="horses.php">Horses</a> > 
            <span><?php echo htmlspecialchars($horse['name']); ?></span>
        </nav>

        <div class="horse-detail-content">
            <!-- Image Gallery -->
            <div class="image-gallery">
                <div class="main-image">
                    <?php 
                    $images->data_seek(0);
                    $first_image = $images->fetch_assoc();
                    ?>
                    <img id="mainImage" src="<?php echo $first_image ? htmlspecialchars($first_image['image_path']) : 'horse_photo.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($horse['name']); ?>">
                </div>
                
                <?php if ($images->num_rows > 1): ?>
                    <div class="image-thumbnails">
                        <?php 
                        $images->data_seek(0);
                        while ($image = $images->fetch_assoc()): 
                        ?>
                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($image['caption']); ?>"
                                 onclick="changeMainImage(this.src)"
                                 class="thumbnail">
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Horse Information -->
            <div class="horse-info-section">
                <div class="horse-header">
                    <h1><?php echo htmlspecialchars($horse['name']); ?></h1>
                    <div class="price-badge">$<?php echo number_format($horse['price'], 2); ?></div>
                </div>

                <div class="horse-meta">
                    <div class="meta-grid">
                        <div class="meta-item">
                            <strong>Breed:</strong> <?php echo htmlspecialchars($horse['breed']); ?>
                        </div>
                        <div class="meta-item">
                            <strong>Age:</strong> <?php echo $horse['age']; ?> years
                        </div>
                        <div class="meta-item">
                            <strong>Gender:</strong> <?php echo ucfirst($horse['gender']); ?>
                        </div>
                        <div class="meta-item">
                            <strong>Color:</strong> <?php echo htmlspecialchars($horse['color']); ?>
                        </div>
                        <div class="meta-item">
                            <strong>Height:</strong> <?php echo $horse['height']; ?> hands
                        </div>
                        <div class="meta-item">
                            <strong>Training Level:</strong> <?php echo ucfirst($horse['training_level']); ?>
                        </div>
                        <div class="meta-item">
                            <strong>Location:</strong> <?php echo htmlspecialchars($horse['location']); ?>
                        </div>
                        <?php if ($horse['disciplines']): ?>
                            <div class="meta-item">
                                <strong>Disciplines:</strong> <?php echo ucwords(str_replace(',', ', ', $horse['disciplines'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="horse-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($horse['description'])); ?></p>
                </div>

                <?php if ($horse['health_status']): ?>
                    <div class="health-info">
                        <h3>Health Information</h3>
                        <p><?php echo nl2br(htmlspecialchars($horse['health_status'])); ?></p>
                        <div class="health-badges">
                            <?php if ($horse['vaccinations_current']): ?>
                                <span class="badge badge-success">✓ Vaccinations Current</span>
                            <?php endif; ?>
                            <?php if ($horse['registration_papers']): ?>
                                <span class="badge badge-success">✓ Registration Papers</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Seller Information and Contact -->
        <div class="seller-contact-section">
            <div class="seller-info">
                <h3>Seller Information</h3>
                <div class="seller-details">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($horse['seller_name']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($horse['seller_location']); ?></p>
                    <?php if ($horse['seller_phone']): ?>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($horse['seller_phone']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="contact-form">
                <h3>Contact Seller</h3>
                <?php if (isset($_SESSION['email'])): ?>
                    <?php if ($contact_success): ?>
                        <div class="success-message"><?php echo $contact_success; ?></div>
                    <?php endif; ?>
                    <?php if ($contact_error): ?>
                        <div class="error-message"><?php echo $contact_error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" class="inquiry-form">
                        <textarea name="message" placeholder="Hi, I'm interested in <?php echo htmlspecialchars($horse['name']); ?>. Could you please provide more information?" required></textarea>
                        <button type="submit" name="send_inquiry" class="btn btn-primary">Send Inquiry</button>
                    </form>
                <?php else: ?>
                    <p>Please <a href="login.php">login</a> to contact the seller.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Horses -->
        <div class="related-horses-section">
            <h3>Similar Horses</h3>
            <div class="related-horses-grid">
                <?php
                $related_query = "SELECT h.*, 
                                 (SELECT image_path FROM horse_images WHERE horse_id = h.id AND is_primary = 1 LIMIT 1) as primary_image
                                 FROM horses h 
                                 WHERE h.id != ? AND h.status = 'available' 
                                 AND (h.breed = ? OR h.category_id = ?) 
                                 ORDER BY RAND() 
                                 LIMIT 4";
                $related_stmt = $conn->prepare($related_query);
                $related_stmt->bind_param("isi", $horse_id, $horse['breed'], $horse['category_id']);
                $related_stmt->execute();
                $related_horses = $related_stmt->get_result();

                while ($related = $related_horses->fetch_assoc()):
                ?>
                    <div class="horse-card">
                        <div class="horse-image">
                            <img src="<?php echo $related['primary_image'] ? htmlspecialchars($related['primary_image']) : 'horse_photo.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>">
                        </div>
                        <div class="horse-info">
                            <h4><?php echo htmlspecialchars($related['name']); ?></h4>
                            <p class="breed"><?php echo htmlspecialchars($related['breed']); ?></p>
                            <p class="price">$<?php echo number_format($related['price'], 2); ?></p>
                            <a href="horse_detail.php?id=<?php echo $related['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<style>
/* Horse Detail Page Styles */
.horse-detail-page {
    padding: 20px 0 60px;
}

.breadcrumb {
    margin-bottom: 30px;
    color: #666;
}

.breadcrumb a {
    color: #3498db;
    text-decoration: none;
}

.horse-detail-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-bottom: 60px;
}

.image-gallery {
    position: sticky;
    top: 100px;
}

.main-image {
    margin-bottom: 20px;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.main-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
}

.image-thumbnails {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 10px;
}

.thumbnail {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color 0.3s ease;
}

.thumbnail:hover {
    border-color: #3498db;
}

.horse-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.horse-header h1 {
    color: #2c3e50;
    margin: 0;
}

.price-badge {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    font-size: 1.5rem;
    font-weight: bold;
}

.meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.meta-item {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.horse-description, .health-info {
    margin-bottom: 30px;
}

.horse-description h3, .health-info h3 {
    color: #2c3e50;
    margin-bottom: 15px;
}

.health-badges {
    margin-top: 15px;
}

.badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 14px;
    margin-right: 10px;
}

.badge-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.seller-contact-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 60px;
}

.seller-details p {
    margin-bottom: 10px;
}

.inquiry-form textarea {
    width: 100%;
    height: 120px;
    padding: 15px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    margin-bottom: 15px;
    resize: vertical;
}

.related-horses-section h3 {
    color: #2c3e50;
    margin-bottom: 30px;
}

.related-horses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.success-message {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}

.error-message {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .horse-detail-content,
    .seller-contact-section {
        grid-template-columns: 1fr;
    }
    
    .image-gallery {
        position: static;
    }
    
    .horse-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .meta-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
}
</script>

</body>
</html> 
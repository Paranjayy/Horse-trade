<?php
include('includes/db.php');
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get user info
$user_query = "SELECT * FROM users WHERE email = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $_SESSION['email']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Get user's horses
$horses_query = "SELECT h.*, 
                 (SELECT image_path FROM horse_images WHERE horse_id = h.id AND is_primary = 1 LIMIT 1) as primary_image,
                 (SELECT COUNT(*) FROM inquiries WHERE horse_id = h.id) as inquiry_count
                 FROM horses h 
                 WHERE h.user_id = ? 
                 ORDER BY h.created_at DESC";
$horses_stmt = $conn->prepare($horses_query);
$horses_stmt->bind_param("i", $user['id']);
$horses_stmt->execute();
$user_horses = $horses_stmt->get_result();

// Get user's favorites
$favorites_query = "SELECT h.*, c.name as category_name,
                   (SELECT image_path FROM horse_images WHERE horse_id = h.id AND is_primary = 1 LIMIT 1) as primary_image
                   FROM favorites f
                   JOIN horses h ON f.horse_id = h.id
                   LEFT JOIN categories c ON h.category_id = c.id
                   WHERE f.user_id = ? AND h.status = 'available'
                   ORDER BY f.created_at DESC";
$favorites_stmt = $conn->prepare($favorites_query);
$favorites_stmt->bind_param("i", $user['id']);
$favorites_stmt->execute();
$favorites = $favorites_stmt->get_result();

// Get recent inquiries
$inquiries_query = "SELECT i.*, h.name as horse_name, u.name as buyer_name, u.email as buyer_email
                   FROM inquiries i
                   JOIN horses h ON i.horse_id = h.id
                   JOIN users u ON i.buyer_id = u.id
                   WHERE i.seller_id = ?
                   ORDER BY i.created_at DESC
                   LIMIT 10";
$inquiries_stmt = $conn->prepare($inquiries_query);
$inquiries_stmt->bind_param("i", $user['id']);
$inquiries_stmt->execute();
$inquiries = $inquiries_stmt->get_result();

// Get statistics
$stats_query = "SELECT 
                (SELECT COUNT(*) FROM horses WHERE user_id = ? AND status = 'available') as active_listings,
                (SELECT COUNT(*) FROM horses WHERE user_id = ? AND status = 'sold') as sold_horses,
                (SELECT COUNT(*) FROM favorites WHERE user_id = ?) as favorite_count,
                (SELECT COUNT(*) FROM inquiries WHERE seller_id = ? AND status = 'pending') as pending_inquiries";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("iiii", $user['id'], $user['id'], $user['id'], $user['id']);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HorseTrader</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include('includes/navbar.php'); ?>

<div class="dashboard-page">
    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
            <p>Manage your horse listings and track your activity</p>
            
            <?php if (isset($_GET['updated'])): ?>
                <div class="success-message">Profile updated successfully!</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['errors'])): ?>
                <div class="error-messages">
                    <?php 
                    $errors = explode('|', urldecode($_GET['errors']));
                    foreach ($errors as $error): 
                    ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">🐴</div>
                <div class="stat-content">
                    <h3><?php echo $stats['active_listings']; ?></h3>
                    <p>Active Listings</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <h3><?php echo $stats['sold_horses']; ?></h3>
                    <p>Horses Sold</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">❤️</div>
                <div class="stat-content">
                    <h3><?php echo $stats['favorite_count']; ?></h3>
                    <p>Favorites</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📧</div>
                <div class="stat-content">
                    <h3><?php echo $stats['pending_inquiries']; ?></h3>
                    <p>New Inquiries</p>
                </div>
            </div>
        </div>

        <!-- Dashboard Tabs -->
        <div class="dashboard-tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="showTab('listings')">My Listings</button>
                <button class="tab-btn" onclick="showTab('favorites')">Favorites</button>
                <button class="tab-btn" onclick="showTab('inquiries')">Inquiries</button>
                <button class="tab-btn" onclick="showTab('profile')">Profile</button>
            </div>

            <!-- My Listings Tab -->
            <div id="listings" class="tab-content active">
                <div class="tab-header">
                    <h2>My Horse Listings</h2>
                    <a href="add_horse.php" class="btn btn-primary">+ Add New Horse</a>
                </div>
                
                <?php if ($user_horses->num_rows > 0): ?>
                    <div class="listings-grid">
                        <?php while ($horse = $user_horses->fetch_assoc()): ?>
                            <div class="listing-card">
                                <div class="listing-image">
                                    <img src="<?php echo $horse['primary_image'] ? htmlspecialchars($horse['primary_image']) : 'horse_photo.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($horse['name']); ?>">
                                    <span class="status-badge status-<?php echo $horse['status']; ?>">
                                        <?php echo ucfirst($horse['status']); ?>
                                    </span>
                                </div>
                                <div class="listing-info">
                                    <h3><?php echo htmlspecialchars($horse['name']); ?></h3>
                                    <p class="breed"><?php echo htmlspecialchars($horse['breed']); ?></p>
                                    <p class="price">$<?php echo number_format($horse['price'], 2); ?></p>
                                    <div class="listing-stats">
                                        <span>📧 <?php echo $horse['inquiry_count']; ?> inquiries</span>
                                        <span>📅 <?php echo date('M j, Y', strtotime($horse['created_at'])); ?></span>
                                    </div>
                                    <div class="listing-actions">
                                        <a href="horse_detail.php?id=<?php echo $horse['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        <a href="edit_horse.php?id=<?php echo $horse['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">🐴</div>
                        <h3>No horses listed yet</h3>
                        <p>Start by adding your first horse for sale</p>
                        <a href="add_horse.php" class="btn btn-primary">List Your First Horse</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Favorites Tab -->
            <div id="favorites" class="tab-content">
                <div class="tab-header">
                    <h2>Favorite Horses</h2>
                </div>
                
                <?php if ($favorites->num_rows > 0): ?>
                    <div class="favorites-grid">
                        <?php while ($horse = $favorites->fetch_assoc()): ?>
                            <div class="horse-card">
                                <div class="horse-image">
                                    <img src="<?php echo $horse['primary_image'] ? htmlspecialchars($horse['primary_image']) : 'horse_photo.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($horse['name']); ?>">
                                </div>
                                <div class="horse-info">
                                    <h3><?php echo htmlspecialchars($horse['name']); ?></h3>
                                    <p class="breed"><?php echo htmlspecialchars($horse['breed']); ?></p>
                                    <p class="price">$<?php echo number_format($horse['price'], 2); ?></p>
                                    <div class="card-actions">
                                        <a href="horse_detail.php?id=<?php echo $horse['id']; ?>" class="btn btn-primary">View Details</a>
                                        <button class="btn btn-secondary favorite-btn active" data-horse-id="<?php echo $horse['id']; ?>">♥</button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">❤️</div>
                        <h3>No favorites yet</h3>
                        <p>Browse horses and add them to your favorites</p>
                        <a href="horses.php" class="btn btn-primary">Browse Horses</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Inquiries Tab -->
            <div id="inquiries" class="tab-content">
                <div class="tab-header">
                    <h2>Recent Inquiries</h2>
                </div>
                
                <?php if ($inquiries->num_rows > 0): ?>
                    <div class="inquiries-list">
                        <?php while ($inquiry = $inquiries->fetch_assoc()): ?>
                            <div class="inquiry-card">
                                <div class="inquiry-header">
                                    <h4><?php echo htmlspecialchars($inquiry['buyer_name']); ?></h4>
                                    <span class="inquiry-date"><?php echo date('M j, Y g:i A', strtotime($inquiry['created_at'])); ?></span>
                                </div>
                                <div class="inquiry-horse">
                                    <strong>Horse:</strong> <?php echo htmlspecialchars($inquiry['horse_name']); ?>
                                </div>
                                <div class="inquiry-message">
                                    <p><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></p>
                                </div>
                                <div class="inquiry-actions">
                                    <a href="mailto:<?php echo htmlspecialchars($inquiry['buyer_email']); ?>" class="btn btn-primary">Reply via Email</a>
                                    <span class="inquiry-status status-<?php echo $inquiry['status']; ?>">
                                        <?php echo ucfirst($inquiry['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📧</div>
                        <h3>No inquiries yet</h3>
                        <p>Inquiries about your horses will appear here</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Profile Tab -->
            <div id="profile" class="tab-content">
                <div class="tab-header">
                    <h2>Profile Settings</h2>
                </div>
                
                <div class="profile-form">
                    <form method="POST" action="update_profile.php">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="user_type">Account Type</label>
                            <select id="user_type" name="user_type">
                                <option value="buyer" <?php echo $user['user_type'] == 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                                <option value="seller" <?php echo $user['user_type'] == 'seller' ? 'selected' : ''; ?>>Seller</option>
                                <option value="both" <?php echo $user['user_type'] == 'both' ? 'selected' : ''; ?>>Both</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<style>
/* Dashboard Styles */
.dashboard-page {
    padding: 40px 0;
    background: #f8f9fa;
    min-height: 100vh;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 40px;
}

.dashboard-header h1 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.dashboard-header p {
    color: #7f8c8d;
    font-size: 1.1rem;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    font-size: 2.5rem;
}

.stat-content h3 {
    font-size: 2rem;
    color: #2c3e50;
    margin-bottom: 5px;
}

.stat-content p {
    color: #7f8c8d;
    margin: 0;
}

.dashboard-tabs {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

.tab-buttons {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #e1e8ed;
}

.tab-btn {
    flex: 1;
    padding: 15px 20px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.tab-btn.active {
    background: white;
    color: #3498db;
    border-bottom: 2px solid #3498db;
}

.tab-content {
    display: none;
    padding: 30px;
}

.tab-content.active {
    display: block;
}

.tab-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.tab-header h2 {
    color: #2c3e50;
    margin: 0;
}

.listings-grid, .favorites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.listing-card {
    background: #f8f9fa;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.listing-card:hover {
    transform: translateY(-5px);
}

.listing-image {
    position: relative;
    height: 200px;
}

.listing-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
}

.status-available { background: #27ae60; color: white; }
.status-sold { background: #e74c3c; color: white; }
.status-pending { background: #f39c12; color: white; }

.listing-info {
    padding: 20px;
}

.listing-info h3 {
    margin-bottom: 5px;
    color: #2c3e50;
}

.listing-stats {
    display: flex;
    gap: 15px;
    margin: 10px 0;
    font-size: 14px;
    color: #7f8c8d;
}

.listing-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 14px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #7f8c8d;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.inquiries-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.inquiry-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    border-left: 4px solid #3498db;
}

.inquiry-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.inquiry-header h4 {
    color: #2c3e50;
    margin: 0;
}

.inquiry-date {
    color: #7f8c8d;
    font-size: 14px;
}

.inquiry-horse {
    margin-bottom: 15px;
    color: #555;
}

.inquiry-message {
    background: white;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.inquiry-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.inquiry-status {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-responded { background: #d1ecf1; color: #0c5460; }
.status-closed { background: #f8d7da; color: #721c24; }

.profile-form {
    max-width: 600px;
}

@media (max-width: 768px) {
    .tab-buttons {
        flex-direction: column;
    }
    
    .tab-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .stats-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .listings-grid, .favorites-grid {
        grid-template-columns: 1fr;
    }
    
    .inquiry-actions {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }
}
</style>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName).classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

// Favorite functionality
document.querySelectorAll('.favorite-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const horseId = this.dataset.horseId;
        
        fetch('ajax/toggle_favorite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({horse_id: horseId})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (!data.is_favorite) {
                    // Remove from favorites view
                    this.closest('.horse-card').style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => {
                        this.closest('.horse-card').remove();
                    }, 300);
                }
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>

</body>
</html> 
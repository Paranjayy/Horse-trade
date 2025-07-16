<?php include('includes/navbar.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HorseTrader - Buy and Sell Horses Online</title>
    <meta name="description" content="The premier marketplace for buying and selling horses. Find your perfect horse or list your horse for sale.">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <h1>Find Your Perfect Horse</h1>
        <p>The world's leading marketplace for buying and selling horses</p>
        <div class="hero-actions">
            <a href="horses.php" class="btn btn-primary">Browse Horses</a>
            <a href="register.php" class="btn btn-success">Sell Your Horse</a>
        </div>
    </div>
</section>

<!-- Quick Search Section -->
<section class="quick-search-section">
    <div class="container">
        <h2>Start Your Search</h2>
        <form action="horses.php" method="GET" class="quick-search-form">
            <div class="search-inputs">
                <input type="text" name="search" placeholder="Search by breed, name, or description...">
                <select name="category">
                    <option value="">All Breeds</option>
                    <option value="1">Arabian</option>
                    <option value="2">Thoroughbred</option>
                    <option value="3">Quarter Horse</option>
                    <option value="4">Warmblood</option>
                </select>
                <input type="number" name="max_price" placeholder="Max Price">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <h2 class="text-center">Why Choose HorseTrader?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3>Advanced Search</h3>
                <p>Find horses by breed, age, training level, location, and more with our powerful search filters.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <h3>Secure Platform</h3>
                <p>Safe and secure transactions with verified sellers and comprehensive horse information.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Easy Listing</h3>
                <p>List your horse in minutes with our user-friendly interface and reach thousands of buyers.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🤝</div>
                <h3>Expert Support</h3>
                <p>Get help from our team of horse experts throughout your buying or selling journey.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Horses Section -->
<section class="featured-horses-section">
    <div class="container">
        <h2 class="text-center">Featured Horses</h2>
        <div class="featured-horses-grid">
            <?php
            include('includes/db.php');
            $featured_query = "SELECT h.*, c.name as category_name,
                              (SELECT image_path FROM horse_images WHERE horse_id = h.id AND is_primary = 1 LIMIT 1) as primary_image
                              FROM horses h 
                              LEFT JOIN categories c ON h.category_id = c.id 
                              WHERE h.featured = 1 AND h.status = 'available' 
                              ORDER BY h.created_at DESC 
                              LIMIT 6";
            $featured_result = $conn->query($featured_query);
            
            if ($featured_result && $featured_result->num_rows > 0):
                while ($horse = $featured_result->fetch_assoc()):
            ?>
                <div class="horse-card">
                    <div class="horse-image">
                        <?php if ($horse['primary_image']): ?>
                            <img src="<?php echo htmlspecialchars($horse['primary_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($horse['name']); ?>">
                        <?php else: ?>
                            <img src="horse_photo.jpg" alt="Horse image">
                        <?php endif; ?>
                        <span class="featured-badge">Featured</span>
                    </div>
                    <div class="horse-info">
                        <h3><?php echo htmlspecialchars($horse['name']); ?></h3>
                        <p class="breed"><?php echo htmlspecialchars($horse['breed']); ?></p>
                        <p class="price">$<?php echo number_format($horse['price'], 2); ?></p>
                        <a href="horse_detail.php?id=<?php echo $horse['id']; ?>" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="no-featured">
                    <p>No featured horses available at the moment.</p>
                    <a href="horses.php" class="btn btn-primary">Browse All Horses</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <?php
            $total_horses = $conn->query("SELECT COUNT(*) as count FROM horses WHERE status = 'available'")->fetch_assoc()['count'];
            $total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
            $total_sold = $conn->query("SELECT COUNT(*) as count FROM horses WHERE status = 'sold'")->fetch_assoc()['count'];
            ?>
            <div class="stat-item">
                <h3><?php echo number_format($total_horses); ?></h3>
                <p>Horses Available</p>
            </div>
            <div class="stat-item">
                <h3><?php echo number_format($total_users); ?></h3>
                <p>Registered Members</p>
            </div>
            <div class="stat-item">
                <h3><?php echo number_format($total_sold); ?></h3>
                <p>Successful Sales</p>
            </div>
            <div class="stat-item">
                <h3>15+</h3>
                <p>Years Experience</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Get Started?</h2>
            <p>Join thousands of horse enthusiasts buying and selling on HorseTrader</p>
            <div class="cta-buttons">
                <?php if (!isset($_SESSION['email'])): ?>
                    <a href="register.php" class="btn btn-primary">Join Now</a>
                    <a href="horses.php" class="btn btn-secondary">Browse Horses</a>
                <?php else: ?>
                    <a href="add_horse.php" class="btn btn-primary">List Your Horse</a>
                    <a href="horses.php" class="btn btn-secondary">Find Horses</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include('includes/footer.php'); ?>

<style>
/* Additional styles for homepage */
.quick-search-section {
    background: white;
    padding: 60px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.quick-search-section h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #2c3e50;
}

.quick-search-form {
    max-width: 800px;
    margin: 0 auto;
}

.search-inputs {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 15px;
}

.search-inputs input, .search-inputs select {
    padding: 15px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 16px;
}

.featured-horses-section {
    background: #f8f9fa;
    padding: 80px 0;
}

.featured-horses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 50px;
}

.stats-section {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    color: white;
    padding: 80px 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 40px;
}

.stat-item {
    text-align: center;
}

.stat-item h3 {
    font-size: 3rem;
    margin-bottom: 10px;
    color: #f39c12;
}

.cta-section {
    background: white;
    padding: 80px 0;
}

.cta-content {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.cta-content h2 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: #2c3e50;
}

.cta-content p {
    font-size: 1.2rem;
    margin-bottom: 30px;
    color: #7f8c8d;
}

.cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
}

.hero-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-top: 30px;
}

.no-featured {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
}

@media (max-width: 768px) {
    .search-inputs {
        grid-template-columns: 1fr;
    }
    
    .hero-actions, .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
}
</style>

</body>
</html>

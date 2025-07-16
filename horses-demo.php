<?php
session_start();
require_once 'demo-mode.php';

// Demo horses data
$horses = getDemoHorses();
$categories = getDemoCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Horses - Horse Trading Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include('includes/navbar.php'); ?>

    <div class="container">
        <div class="horses-header">
            <h1>🐎 Browse Horses</h1>
            <p class="demo-notice">✨ Demo Mode - Showing sample horses</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <div class="search-row">
                    <input type="text" name="search" placeholder="Search horses..." class="search-input">
                    <select name="category" class="filter-select">
                        <option value="">All Breeds</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="search-btn">Search</button>
                </div>
                
                <div class="filter-row">
                    <input type="number" name="min_price" placeholder="Min Price" class="price-input">
                    <input type="number" name="max_price" placeholder="Max Price" class="price-input">
                    <select name="gender" class="filter-select">
                        <option value="">Any Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="gelding">Gelding</option>
                    </select>
                    <input type="text" name="location" placeholder="Location" class="location-input">
                </div>
            </form>
        </div>

        <!-- Horses Grid -->
        <div class="horses-grid">
            <?php foreach($horses as $horse): ?>
                <div class="horse-card">
                    <div class="horse-image">
                        <img src="<?php echo htmlspecialchars($horse['image']); ?>" 
                             alt="<?php echo htmlspecialchars($horse['name']); ?>">
                        <div class="price-tag">$<?php echo number_format($horse['price']); ?></div>
                    </div>
                    
                    <div class="horse-details">
                        <h3><?php echo htmlspecialchars($horse['name']); ?></h3>
                        <p class="breed"><?php echo htmlspecialchars($horse['breed']); ?></p>
                        <div class="horse-info">
                            <span class="age"><?php echo $horse['age']; ?> years</span>
                            <span class="gender"><?php echo ucfirst($horse['gender']); ?></span>
                        </div>
                        <p class="location">📍 <?php echo htmlspecialchars($horse['location']); ?></p>
                        <p class="description"><?php echo htmlspecialchars(substr($horse['description'], 0, 100)) . '...'; ?></p>
                        
                        <div class="card-actions">
                            <a href="horse-detail-demo.php?id=<?php echo $horse['id']; ?>" class="view-btn">View Details</a>
                            <button class="favorite-btn" onclick="toggleFavorite(<?php echo $horse['id']; ?>)">
                                ❤️ Save
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="demo-info">
            <h3>🚀 Demo Mode Features</h3>
            <ul>
                <li>✅ Beautiful responsive design</li>
                <li>✅ Search and filter interface</li>
                <li>✅ Horse card layouts</li>
                <li>✅ Professional styling</li>
                <li>⏳ Full functionality with database setup</li>
            </ul>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script>
        function toggleFavorite(horseId) {
            alert('Demo mode: Favorites feature available with database setup!');
        }
    </script>

    <style>
        .demo-notice {
            background: linear-gradient(45deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
            color: #333;
            padding: 10px 20px;
            border-radius: 25px;
            text-align: center;
            margin: 20px 0;
            font-weight: 500;
        }

        .demo-info {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            margin: 40px 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .demo-info h3 {
            color: #fff;
            margin-bottom: 20px;
        }

        .demo-info ul {
            list-style: none;
            padding: 0;
        }

        .demo-info li {
            color: #fff;
            padding: 8px 0;
            font-size: 16px;
        }
    </style>
</body>
</html> 
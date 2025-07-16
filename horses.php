<?php
session_start();
include('includes/db.php');

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$training_level = isset($_GET['training_level']) ? $_GET['training_level'] : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Horses per page
$offset = ($page - 1) * $limit;

// Build the WHERE clause
$where_conditions = ["status = 'available'"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(h.name LIKE ? OR h.breed LIKE ? OR h.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($category)) {
    $where_conditions[] = "h.category_id = ?";
    $params[] = (int)$category;
}

if (!empty($min_price)) {
    $where_conditions[] = "h.price >= ?";
    $params[] = (float)$min_price;
}

if (!empty($max_price)) {
    $where_conditions[] = "h.price <= ?";
    $params[] = (float)$max_price;
}

if (!empty($gender)) {
    $where_conditions[] = "h.gender = ?";
    $params[] = $gender;
}

if (!empty($training_level)) {
    $where_conditions[] = "h.training_level = ?";
    $params[] = $training_level;
}

if (!empty($location)) {
    $where_conditions[] = "h.location LIKE ?";
    $params[] = "%$location%";
}

$where_clause = implode(' AND ', $where_conditions);

// Count total horses for pagination
$count_sql = "SELECT COUNT(*) as total FROM horses h WHERE $where_clause";
if ($conn && !isDemoMode()) {
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $count_stmt->execute($params);
    } else {
        $count_stmt->execute();
    }
    $total_horses = $count_stmt->fetch()['total'];
} else {
    $total_horses = 5; // Demo mode
}

$total_pages = ceil($total_horses / $limit);

// Get horses with pagination
$sql = "SELECT h.*, c.name as category_name, u.name as seller_name, u.location as seller_location 
        FROM horses h 
        LEFT JOIN categories c ON h.category_id = c.id 
        LEFT JOIN users u ON h.user_id = u.id 
        WHERE $where_clause 
        ORDER BY h.featured DESC, h.created_at DESC 
        LIMIT ? OFFSET ?";

$horses = [];
if ($conn && !isDemoMode()) {
    $all_params = array_merge($params, [$limit, $offset]);
    $stmt = $conn->prepare($sql);
    $stmt->execute($all_params);
    $horses = $stmt->fetchAll();
} else {
    // Demo mode data
    $horses = [
        [
            'id' => 1,
            'name' => 'Thunder',
            'breed' => 'Thoroughbred',
            'age' => 5,
            'gender' => 'male',
            'color' => 'Bay',
            'height' => 16.2,
            'price' => 25000.00,
            'location' => 'Kentucky, USA',
            'description' => 'Beautiful thoroughbred stallion with excellent racing bloodlines.',
            'category_name' => 'Thoroughbred',
            'seller_name' => 'John Smith',
            'seller_location' => 'Kentucky, USA',
            'featured' => 1
        ],
        [
            'id' => 2,
            'name' => 'Midnight Star',
            'breed' => 'Arabian',
            'age' => 8,
            'gender' => 'female',
            'color' => 'Black',
            'height' => 15.1,
            'price' => 18000.00,
            'location' => 'Kentucky, USA',
            'description' => 'Stunning black Arabian mare with incredible endurance.',
            'category_name' => 'Arabian',
            'seller_name' => 'John Smith',
            'seller_location' => 'Kentucky, USA',
            'featured' => 0
        ]
    ];
}

// Get categories for filter dropdown
$categories = [];
if ($conn && !isDemoMode()) {
    $cat_stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
    $cat_stmt->execute();
    $categories = $cat_stmt->fetchAll();
} else {
    // Demo categories
    $categories = [
        ['id' => 1, 'name' => 'Sport Horse'],
        ['id' => 2, 'name' => 'Draft Horse'],
        ['id' => 3, 'name' => 'Pony'],
        ['id' => 4, 'name' => 'Arabian'],
        ['id' => 5, 'name' => 'Quarter Horse'],
        ['id' => 6, 'name' => 'Thoroughbred']
    ];
}
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

<div class="horses-page">
    <!-- Search and Filters Section -->
    <div class="search-section">
        <div class="container">
            <h1>Find Your Perfect Horse</h1>
            <form method="GET" class="search-form">
                <div class="search-row">
                    <div class="search-input-group">
                        <input type="text" name="search" placeholder="Search horses by name, breed..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-btn">Search</button>
                    </div>
                </div>
                
                <div class="filters-row">
                    <select name="category">
                        <option value="">All Breeds</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="gender">
                        <option value="">Any Gender</option>
                        <option value="male" <?php echo $gender == 'male' ? 'selected' : ''; ?>>Stallion</option>
                        <option value="female" <?php echo $gender == 'female' ? 'selected' : ''; ?>>Mare</option>
                        <option value="gelding" <?php echo $gender == 'gelding' ? 'selected' : ''; ?>>Gelding</option>
                    </select>
                    
                    <select name="training_level">
                        <option value="">Any Training Level</option>
                        <option value="untrained" <?php echo $training_level == 'untrained' ? 'selected' : ''; ?>>Untrained</option>
                        <option value="basic" <?php echo $training_level == 'basic' ? 'selected' : ''; ?>>Basic</option>
                        <option value="intermediate" <?php echo $training_level == 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                        <option value="advanced" <?php echo $training_level == 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                        <option value="professional" <?php echo $training_level == 'professional' ? 'selected' : ''; ?>>Professional</option>
                    </select>
                    
                    <input type="text" name="location" placeholder="Location" 
                           value="<?php echo htmlspecialchars($location); ?>">
                    
                    <input type="number" name="min_price" placeholder="Min Price" 
                           value="<?php echo htmlspecialchars($min_price); ?>">
                    
                    <input type="number" name="max_price" placeholder="Max Price" 
                           value="<?php echo htmlspecialchars($max_price); ?>">
                    
                    <button type="submit" class="filter-btn">Filter</button>
                    <a href="horses.php" class="clear-btn">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div class="results-section">
        <div class="container">
            <div class="results-header">
                <h2><?php echo $total_horses; ?> Horses Found</h2>
                <?php if (isset($_SESSION['email'])): ?>
                    <a href="add_horse.php" class="btn btn-primary">+ List Your Horse</a>
                <?php endif; ?>
            </div>

            <?php if (!empty($horses)): ?>
                <div class="horses-grid">
                    <?php foreach ($horses as $horse): ?>
                        <div class="horse-card">
                            <div class="horse-image">
                                <?php if ($horse['primary_image']): ?>
                                    <img src="<?php echo htmlspecialchars($horse['primary_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($horse['name']); ?>">
                                <?php else: ?>
                                    <img src="horse_photo.jpg" alt="Default horse image">
                                <?php endif; ?>
                                <?php if ($horse['featured']): ?>
                                    <span class="featured-badge">Featured</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="horse-info">
                                <h3><?php echo htmlspecialchars($horse['name']); ?></h3>
                                <p class="breed"><?php echo htmlspecialchars($horse['breed']); ?></p>
                                <div class="horse-details">
                                    <span class="age"><?php echo $horse['age']; ?> years</span>
                                    <span class="gender"><?php echo ucfirst($horse['gender']); ?></span>
                                    <span class="height"><?php echo $horse['height']; ?> hands</span>
                                </div>
                                <p class="location">📍 <?php echo htmlspecialchars($horse['location']); ?></p>
                                <p class="price">$<?php echo number_format($horse['price'], 2); ?></p>
                                <div class="card-actions">
                                    <a href="horse_detail.php?id=<?php echo $horse['id']; ?>" class="btn btn-primary">View Details</a>
                                    <?php if (isset($_SESSION['email'])): ?>
                                        <button class="btn btn-secondary favorite-btn" data-horse-id="<?php echo $horse['id']; ?>">♡</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-btn">← Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                               class="page-btn <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-btn">Next →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results">
                    <h3>No horses found</h3>
                    <p>Try adjusting your search criteria or <a href="horses.php">browse all horses</a>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script>
// Favorite functionality
document.querySelectorAll('.favorite-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const horseId = this.dataset.horseId;
        // Add AJAX call to toggle favorite
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
                this.textContent = data.is_favorite ? '♥' : '♡';
                this.classList.toggle('active');
            }
        });
    });
});
</script>

</body>
</html>

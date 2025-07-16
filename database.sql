
-- Drop tables if they exist
DROP TABLE IF EXISTS inquiries;
DROP TABLE IF EXISTS favorites;  
DROP TABLE IF EXISTS horse_images;
DROP TABLE IF EXISTS horses;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Users table with enhanced fields
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  location VARCHAR(100),
  user_type ENUM('buyer', 'seller', 'both') DEFAULT 'buyer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Horse categories/breeds
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT
);

-- Main horses table with comprehensive fields
CREATE TABLE horses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  breed VARCHAR(100),
  category_id INT,
  age INT,
  gender ENUM('male', 'female', 'gelding') NOT NULL,
  color VARCHAR(50),
  height DECIMAL(4,2), -- in hands
  price DECIMAL(10,2) NOT NULL,
  location VARCHAR(100),
  description TEXT,
  training_level ENUM('untrained', 'basic', 'intermediate', 'advanced', 'professional'),
  disciplines SET('dressage', 'jumping', 'racing', 'western', 'trail', 'breeding', 'endurance', 'other'),
  health_status TEXT,
  vaccinations_current BOOLEAN DEFAULT FALSE,
  registration_papers BOOLEAN DEFAULT FALSE,
  status ENUM('available', 'sold', 'pending') DEFAULT 'available',
  featured BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Horse images table
CREATE TABLE horse_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  horse_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  is_primary BOOLEAN DEFAULT FALSE,
  caption VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (horse_id) REFERENCES horses(id) ON DELETE CASCADE
);

-- Favorites/watchlist
CREATE TABLE favorites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  horse_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (horse_id) REFERENCES horses(id) ON DELETE CASCADE,
  UNIQUE KEY unique_favorite (user_id, horse_id)
);

-- Contact inquiries
CREATE TABLE inquiries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  horse_id INT NOT NULL,
  buyer_id INT NOT NULL,
  seller_id INT NOT NULL,
  message TEXT NOT NULL,
  status ENUM('pending', 'responded', 'closed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (horse_id) REFERENCES horses(id) ON DELETE CASCADE,
  FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Arabian', 'Known for their intelligence, spirit and endurance'),
('Thoroughbred', 'Famous for racing and athletic ability'),
('Quarter Horse', 'Versatile American breed, excellent for western riding'),
('Warmblood', 'Sport horses bred for dressage and jumping'),
('Draft Horse', 'Large, strong horses bred for heavy work'),
('Pony', 'Smaller horses, great for children and driving'),
('Paint Horse', 'Colorful horses with distinctive markings'),
('Appaloosa', 'Known for their spotted coat patterns');

-- Insert sample users (with hashed passwords)
INSERT INTO users (name, email, password, phone, location, user_type) VALUES
('John Smith', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0123', 'Kentucky, USA', 'seller'),
('Mary Johnson', 'mary@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0124', 'Texas, USA', 'both'),
('David Wilson', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0125', 'California, USA', 'buyer');

-- Insert sample horses
INSERT INTO horses (user_id, name, breed, category_id, age, gender, color, height, price, location, description, training_level, disciplines, health_status, vaccinations_current, registration_papers) VALUES
(1, 'Thunder', 'Arabian', 1, 8, 'gelding', 'Bay', 15.2, 15000.00, 'Kentucky, USA', 'Beautiful Arabian gelding with excellent bloodlines. Perfect for trail riding and endurance.', 'intermediate', 'trail,endurance', 'Excellent health, recent vet check', TRUE, TRUE),
(1, 'Lightning Bolt', 'Thoroughbred', 2, 5, 'male', 'Chestnut', 16.1, 25000.00, 'Kentucky, USA', 'Young stallion with racing potential. Great conformation and athletic ability.', 'basic', 'racing', 'Excellent health', TRUE, TRUE),
(2, 'Sweet Dreams', 'Quarter Horse', 3, 12, 'female', 'Palomino', 15.0, 8000.00, 'Texas, USA', 'Gentle mare perfect for beginners. Great with children and very calm temperament.', 'advanced', 'western,trail', 'Good health, minor arthritis managed', TRUE, FALSE),
(2, 'Star Dancer', 'Warmblood', 4, 7, 'female', 'Black', 16.3, 35000.00, 'Texas, USA', 'Competition-ready mare excelling in dressage. Multiple show wins.', 'professional', 'dressage,jumping', 'Excellent health', TRUE, TRUE),
(1, 'Midnight', 'Paint Horse', 7, 6, 'gelding', 'Black and White', 15.1, 12000.00, 'Kentucky, USA', 'Stunning paint gelding with great ground manners and under-saddle training.', 'intermediate', 'western,trail', 'Good health', TRUE, TRUE);

-- Insert sample horse images (placeholder paths)
INSERT INTO horse_images (horse_id, image_path, is_primary, caption) VALUES
(1, 'uploads/thunder_1.jpg', TRUE, 'Thunder in the pasture'),
(1, 'uploads/thunder_2.jpg', FALSE, 'Thunder under saddle'),
(2, 'uploads/lightning_1.jpg', TRUE, 'Lightning Bolt racing'),
(3, 'uploads/sweet_dreams_1.jpg', TRUE, 'Sweet Dreams with rider'),
(4, 'uploads/star_dancer_1.jpg', TRUE, 'Star Dancer in competition'),
(5, 'uploads/midnight_1.jpg', TRUE, 'Midnight showing his markings');

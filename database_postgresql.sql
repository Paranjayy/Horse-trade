-- PostgreSQL version of the horse trading database schema

-- Drop tables if they exist
DROP TABLE IF EXISTS inquiries CASCADE;
DROP TABLE IF EXISTS favorites CASCADE;  
DROP TABLE IF EXISTS horse_images CASCADE;
DROP TABLE IF EXISTS horses CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Create ENUM types for PostgreSQL
CREATE TYPE user_type_enum AS ENUM ('buyer', 'seller', 'both');
CREATE TYPE gender_enum AS ENUM ('male', 'female', 'gelding');
CREATE TYPE training_level_enum AS ENUM ('untrained', 'basic', 'intermediate', 'advanced', 'professional');
CREATE TYPE status_enum AS ENUM ('available', 'sold', 'pending');

-- Users table with enhanced fields
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  location VARCHAR(100),
  user_type user_type_enum DEFAULT 'buyer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Horse categories/breeds
CREATE TABLE categories (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT
);

-- Main horses table with comprehensive fields
CREATE TABLE horses (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL,
  name VARCHAR(100) NOT NULL,
  breed VARCHAR(100),
  category_id INTEGER,
  age INTEGER,
  gender gender_enum NOT NULL,
  color VARCHAR(50),
  height DECIMAL(4,2), -- in hands
  price DECIMAL(10,2) NOT NULL,
  location VARCHAR(100),
  description TEXT,
  training_level training_level_enum,
  disciplines TEXT[], -- PostgreSQL array instead of MySQL SET
  health_status TEXT,
  vaccinations_current BOOLEAN DEFAULT FALSE,
  registration_papers BOOLEAN DEFAULT FALSE,
  status status_enum DEFAULT 'available',
  featured BOOLEAN DEFAULT FALSE,
  views INTEGER DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Horse images table
CREATE TABLE horse_images (
  id SERIAL PRIMARY KEY,
  horse_id INTEGER NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  is_primary BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (horse_id) REFERENCES horses(id) ON DELETE CASCADE
);

-- User favorites
CREATE TABLE favorites (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL,
  horse_id INTEGER NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (horse_id) REFERENCES horses(id) ON DELETE CASCADE,
  UNIQUE(user_id, horse_id)
);

-- Inquiries/Contact requests
CREATE TABLE inquiries (
  id SERIAL PRIMARY KEY,
  horse_id INTEGER NOT NULL,
  buyer_email VARCHAR(100) NOT NULL,
  buyer_name VARCHAR(100) NOT NULL,
  buyer_phone VARCHAR(20),
  message TEXT NOT NULL,
  status VARCHAR(20) DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (horse_id) REFERENCES horses(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Sport Horse', 'Horses bred and trained for competitive sports'),
('Draft Horse', 'Large, strong horses bred for heavy work'),
('Pony', 'Smaller horses, typically under 14.2 hands'),
('Arabian', 'Ancient breed known for endurance and intelligence'),
('Quarter Horse', 'American breed known for speed and agility'),
('Thoroughbred', 'Breed developed for horse racing'),
('Warmblood', 'Sport horses with mixed breeding for athletic ability');

-- Insert sample users (passwords are hashed versions of 'password123')
INSERT INTO users (name, email, password, phone, location, user_type) VALUES
('John Smith', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0101', 'Kentucky, USA', 'seller'),
('Sarah Johnson', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0102', 'California, USA', 'both'),
('Mike Wilson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0103', 'Texas, USA', 'buyer');

-- Insert sample horses
INSERT INTO horses (user_id, name, breed, category_id, age, gender, color, height, price, location, description, training_level, disciplines, health_status, vaccinations_current, registration_papers, featured) VALUES
(1, 'Thunder', 'Thoroughbred', 6, 5, 'male', 'Bay', 16.2, 25000.00, 'Kentucky, USA', 'Beautiful thoroughbred stallion with excellent racing bloodlines. Well-trained and ready for breeding or continued racing career.', 'advanced', ARRAY['racing', 'breeding'], 'Excellent health, recent vet check completed', true, true, true),
(1, 'Midnight Star', 'Arabian', 4, 8, 'female', 'Black', 15.1, 18000.00, 'Kentucky, USA', 'Stunning black Arabian mare with incredible endurance and gentle temperament. Perfect for trail riding or endurance competitions.', 'intermediate', ARRAY['endurance', 'trail'], 'Perfect health, all vaccinations current', true, true, false),
(2, 'Golden Dream', 'Quarter Horse', 5, 4, 'female', 'Palomino', 15.3, 15000.00, 'California, USA', 'Beautiful palomino quarter horse with western training. Great for ranch work or pleasure riding.', 'intermediate', ARRAY['western', 'trail'], 'Excellent health and condition', true, false, true),
(2, 'Storm Chaser', 'Warmblood', 1, 6, 'gelding', 'Grey', 16.8, 35000.00, 'California, USA', 'Impressive warmblood gelding trained in dressage and jumping. Competition ready with lots of potential.', 'advanced', ARRAY['dressage', 'jumping'], 'Excellent health, competition fit', true, true, false),
(1, 'Sweet Pea', 'Pony', 3, 12, 'female', 'Chestnut', 13.2, 8000.00, 'Kentucky, USA', 'Gentle and reliable pony perfect for children or small adults. Well-trained and very safe.', 'basic', ARRAY['trail', 'other'], 'Good health for age, regular vet care', true, false, false);

-- Insert sample horse images (you'll need to update these paths based on your actual images)
INSERT INTO horse_images (horse_id, image_path, is_primary) VALUES
(1, 'uploads/thunder1.jpg', true),
(1, 'uploads/thunder2.jpg', false),
(2, 'uploads/midnight1.jpg', true),
(2, 'uploads/midnight2.jpg', false),
(3, 'uploads/golden1.jpg', true),
(4, 'uploads/storm1.jpg', true),
(5, 'uploads/sweetpea1.jpg', true); 
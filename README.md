# 🐎 Professional Horse Trading Platform

A modern, full-featured horse trading website built with PHP and MySQL. Features advanced search, user management, image galleries, and responsive design.

![Horse Trading Platform](horse_photo.jpg)

## ✨ Features

- 🔐 **Secure Authentication** - Password hashing, session management
- 🔍 **Advanced Search & Filters** - Breed, price range, location, training level
- 📸 **Image Upload System** - Multiple photos per horse with galleries
- 👤 **User Dashboard** - Manage listings, favorites, and profile
- ❤️ **AJAX Favorites** - Real-time favorites without page reload
- 📱 **Responsive Design** - Works perfectly on all devices
- 🎨 **Modern UI** - Beautiful gradients, animations, and card layouts
- 🛡️ **Security Features** - XSS protection, prepared statements

## 🚀 Live Demo

**Local Development:** `http://localhost:8000`
**Production:** [Deploy to Vercel](#deployment)

## 🛠️ Tech Stack

- **Backend:** PHP 8.4+
- **Database:** MySQL 9.3+
- **Frontend:** HTML5, CSS3, JavaScript (AJAX)
- **Security:** Password hashing, prepared statements, XSS protection
- **Design:** Modern responsive layout with CSS Grid/Flexbox

## 📋 Requirements

- PHP 8.0+ with MySQLi extension
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx) or PHP built-in server

## 🏃‍♂️ Quick Start

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/horse-trading-platform.git
   cd horse-trading-platform
   ```

2. **Set up database**
   ```bash
   mysql -u root -e "CREATE DATABASE horse_trading;"
   mysql -u root horse_trading < database.sql
   ```

3. **Start PHP server**
   ```bash
   php -S localhost:8000
   ```

4. **Visit** `http://localhost:8000`

### 🔐 Test Accounts
- **Email:** `john@example.com` **Password:** `password123`
- **Email:** `mary@example.com` **Password:** `password123`
- **Email:** `david@example.com` **Password:** `password123`

## 🌐 Deployment

### Deploy to Vercel

1. **Push to GitHub** (see instructions below)
2. **Connect to Vercel**
   - Visit [vercel.com](https://vercel.com)
   - Import from GitHub
   - Configure environment variables
3. **Set up database** (PlanetScale, Railway, or similar)
4. **Configure environment variables:**
   ```
   DB_HOST=your-database-host
   DB_NAME=horse_trading
   DB_USER=your-username
   DB_PASS=your-password
   ```

### Deploy to Other Platforms
- **Railway:** Direct GitHub integration
- **Render:** PHP/MySQL support
- **DigitalOcean App Platform:** Full-stack deployment

## 📂 Project Structure

```
horse-trading-platform/
├── 📄 index.php              # Homepage
├── 🐎 horses.php             # Horse listings with filters
├── 📋 horse_detail.php       # Individual horse pages
├── ➕ add_horse.php          # Add new horse listings
├── ✏️ edit_horse.php         # Edit existing horses
├── 👤 dashboard.php          # User management dashboard
├── 🔐 login.php & register.php # Authentication
├── 📁 includes/
│   ├── db.php                # Database connection
│   ├── navbar.php            # Navigation component
│   └── footer.php            # Footer component
├── 📁 ajax/                  # AJAX endpoints
├── 📁 uploads/               # Horse images
├── 🎨 style.css              # Modern responsive styling
├── 🗄️ database.sql           # Complete database schema
├── ⚙️ vercel.json            # Vercel deployment config
└── 📖 README.md              # This file
```

## �� Design Highlights

- **Modern Gradient Background** with subtle patterns
- **Card-based Layouts** with hover effects
- **Responsive Grid System** that adapts to all screen sizes
- **Professional Typography** with Inter font family
- **Smooth Animations** and transitions
- **Accessible Color Scheme** with proper contrast
- **Mobile-first Design** approach

## 🔧 Configuration

### Database Connection
The app automatically detects environment:
- **Local:** Uses localhost MySQL
- **Production:** Uses environment variables

### File Uploads
- Max file size: 5MB per image
- Supported formats: JPG, PNG, GIF
- Automatic resize and optimization

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🙏 Acknowledgments

- Built with modern PHP practices
- Responsive design inspired by leading e-commerce platforms
- Security best practices implemented throughout
- Professional UI/UX design principles

---

**Made with ❤️ for horse enthusiasts worldwide** 🐎 
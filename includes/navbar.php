<?php
session_start();
?>
<nav class="navbar">
    <div class="container">
        <a href="index.php" class="logo">🐎 HorseTrader</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="horses.php">Browse Horses</a>
            <?php if (isset($_SESSION['email'])): ?>
                <a href="add_horse.php">Sell Horse</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
  .navbar {
    background-color: #333;
    padding: 10px;
    text-align: center;
  }

  .navbar a {
    color: #fff;
    text-decoration: none;
    margin: 0 10px;
    font-weight: bold;
    font-family: Arial, sans-serif;
  }

  .navbar a:hover {
    color: #FFD700;
    text-decoration: underline;
  }

  hr {
    margin: 0;
    border: none;
    border-top: 2px solid #ddd;
  }
</style>

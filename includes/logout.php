<?php
include '../includes/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear and destroy the session
$_SESSION = [];
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Logout Successful</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
  <style>
    /* Base reset */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Roboto', sans-serif;
      background: #eef2f5;
      color: #333;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .logout-card {
      background: #fff;
      padding: 2.5rem 1.5rem;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.08);
      text-align: center;
      max-width: 360px;
      width: 100%;
    }
    .logout-card .icon {
      font-size: 3rem;
      color: #27ae60;
      margin-bottom: 1rem;
    }
    .logout-card h1 {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
    }
    .logout-card p {
      font-size: 1rem;
      color: #555;
      margin-bottom: 1.5rem;
      line-height: 1.4;
    }
    .logout-card .btn-home {
      display: inline-block;
      background: linear-gradient(135deg, #3498db, #2980b9);
      color: #fff;
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 6px;
      font-size: 0.95rem;
      text-decoration: none;
      transition: background 0.3s, transform 0.2s;
    }
    .logout-card .btn-home:hover {
      background: linear-gradient(135deg, #2980b9, #2471a3);
      transform: translateY(-2px);
    }
  </style>
</head>
<body>
  <div class="logout-card">
    <i class="bi bi-check-circle-fill icon"></i>
    <h1>Logged Out</h1>
    <p>You have successfully signed out of your account.</p>
    <a href="../index.php" class="btn-home">Go to Homepage</a>
  </div>
</body>
</html>

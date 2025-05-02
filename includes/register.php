<?php
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: ../account.php');
        exit;
    }

    // Check if email already exists
    $stmt = $connexion->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['error'] = 'Email already registered.';
        header('Location: ../account.php');
        exit;
    }

    // Register user
    $role = str_contains(strtolower($email), 'admin') ? 'admin' : 'client';
    $stmt = $connexion->prepare('INSERT INTO users (firstName, LastName, email, password, role) VALUES (?, ?, ?, ?, ?)');
    
    try {
        $stmt->execute([$username, $username, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        $_SESSION['success'] = 'Registration successful! Please log in.';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Registration failed. Please try again.';
    }
    
    header('Location: ../account.php');
    exit;
}

$_SESSION['error'] = 'Invalid request method.';
header('Location: ../account.php');
exit;
?>
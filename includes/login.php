<?php
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: ../account.php');
        exit;
    }

    // Check if user exists
    $stmt = $connexion->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Start session and set session variables
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['firstName'];
        $_SESSION['logged_in'] = true;
        $_SESSION['email'] = $user['email'];
        
        // Redirect to homepage
        header('Location: ../index.php');
        exit;
    } else {
        $_SESSION['error'] = 'Invalid email or password.';
        header('Location: ../account.php');
        exit;
    }
} else {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../account.php');
    exit;
}
?>
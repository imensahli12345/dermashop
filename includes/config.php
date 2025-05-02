<?php
// Only start session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session for authentication
}

$PARAM_hote = 'localhost:3306'; // MySQL host
$PARAM_nom_bd = 'dermashop'; // Database name
$PARAM_utilisateur = 'root'; // MySQL username
$PARAM_mot_passe = ''; // MySQL password

try {
    $connexion = new PDO(
        'mysql:host=' . $PARAM_hote . ';dbname=' . $PARAM_nom_bd . ';charset=utf8',
        $PARAM_utilisateur,
        $PARAM_mot_passe,
        [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]
    );
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Unable to connect to the database. Please try again later.");
}
?>
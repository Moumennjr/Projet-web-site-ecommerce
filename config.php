<?php
$host = 'localhost';
$dbname = 'site_ecommerce1';
$username = 'root';
$password = '';

try {
    $conn = mysqli_connect($host, $username, $password, $dbname);
    mysqli_set_charset($conn, 'utf8');
} catch (Exception $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}
?>
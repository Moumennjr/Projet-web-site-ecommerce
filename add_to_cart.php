<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Veuillez vous connecter pour ajouter au panier.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['item_id']) || !is_numeric($_POST['item_id']) || !isset($_POST['quantity']) || !is_numeric($_POST['quantity'])) {
    $response['message'] = 'Données invalides.';
    echo json_encode($response);
    exit;
}

$user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
$item_id = mysqli_real_escape_string($conn, $_POST['item_id']);
$quantity = (int)$_POST['quantity'];

// Vérifier le stock
$stock_query = "SELECT stock FROM item WHERE id = '$item_id'";
$stock_result = mysqli_query($conn, $stock_query);
if (mysqli_num_rows($stock_result) == 0) {
    $response['message'] = 'Article non trouvé.';
    echo json_encode($response);
    exit;
}

$stock_row = mysqli_fetch_assoc($stock_result);
$stock = $stock_row['stock'];

if ($quantity <= 0 || $quantity > $stock) {
    $response['message'] = 'Quantité invalide ou stock insuffisant.';
    echo json_encode($response);
    exit;
}

// Vérifier si l'article est déjà dans le panier
$check_query = "SELECT quantite FROM panier WHERE id_user = '$user_id' AND id_item = '$item_id'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    // Mettre à jour la quantité
    $current_quantity = mysqli_fetch_assoc($check_result)['quantite'];
    $new_quantity = $current_quantity + $quantity;
    if ($new_quantity > $stock) {
        $response['message'] = 'La quantité totale dépasse le stock disponible.';
        echo json_encode($response);
        exit;
    }
    $update_query = "UPDATE panier SET quantite = '$new_quantity' WHERE id_user = '$user_id' AND id_item = '$item_id'";
    if (mysqli_query($conn, $update_query)) {
        $response['success'] = true;
    } else {
        $response['message'] = 'Erreur lors de la mise à jour du panier.';
    }
} else {
    // Insérer une nouvelle ligne
    $insert_query = "INSERT INTO panier (id_user, id_item, quantite) VALUES ('$user_id', '$item_id', '$quantity')";
    if (mysqli_query($conn, $insert_query)) {
        $response['success'] = true;
    } else {
        $response['message'] = 'Erreur lors de l\'ajout au panier.';
    }
}

echo json_encode($response);
?>
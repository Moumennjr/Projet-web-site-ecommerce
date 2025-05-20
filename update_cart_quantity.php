<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Veuillez vous connecter.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['cart_id']) || !is_numeric($_POST['cart_id']) || !isset($_POST['quantity']) || !is_numeric($_POST['quantity'])) {
    $response['message'] = 'Données invalides.';
    echo json_encode($response);
    exit;
}

$cart_id = (int)$_POST['cart_id'];
$new_quantity = (int)$_POST['quantity'];

if ($new_quantity < 1) {
    $response['message'] = 'La quantité doit être au moins 1.';
    echo json_encode($response);
    exit;
}

// Vérifier si le panier appartient à l'utilisateur connecté
$stmt_check = mysqli_prepare($conn, "SELECT id_user, id_item FROM panier WHERE id = ?");
mysqli_stmt_bind_param($stmt_check, 'i', $cart_id);
mysqli_stmt_execute($stmt_check);
$result = mysqli_stmt_get_result($stmt_check);
$row = mysqli_fetch_assoc($result);

if (!$row || $row['id_user'] !== $_SESSION['user_id']) {
    $response['message'] = 'Accès non autorisé ou panier non trouvé.';
    echo json_encode($response);
    exit;
}

$id_item = $row['id_item'];
mysqli_stmt_close($stmt_check);

// Vérifier le stock disponible
$stmt_stock = mysqli_prepare($conn, "SELECT stock FROM item WHERE id = ?");
mysqli_stmt_bind_param($stmt_stock, 'i', $id_item);
mysqli_stmt_execute($stmt_stock);
$result_stock = mysqli_stmt_get_result($stmt_stock);
$row_stock = mysqli_fetch_assoc($result_stock);

if (!$row_stock) {
    $response['message'] = 'Article non trouvé.';
    echo json_encode($response);
    exit;
}

$stock = $row_stock['stock'];
mysqli_stmt_close($stmt_stock);

if ($new_quantity > $stock) {
    $response['message'] = 'Quantité demandée supérieure au stock disponible (' . $stock . ').';
    echo json_encode($response);
    exit;
}

// Mettre à jour la quantité dans le panier
$query = "UPDATE panier SET quantite = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $new_quantity, $cart_id);

if (mysqli_stmt_execute($stmt)) {
    $response['success'] = true;
} else {
    $response['message'] = 'Erreur lors de la mise à jour de la quantité : ' . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
echo json_encode($response);
?>
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

if (!isset($_POST['cart_id'])) {
    $response['message'] = 'Données invalides.';
    echo json_encode($response);
    exit;
}

$cart_id = (int)$_POST['cart_id'];

// Vérifier si le panier appartient à l'utilisateur connecté
$stmt_check = mysqli_prepare($conn, "SELECT id_user FROM panier WHERE id = ?");
mysqli_stmt_bind_param($stmt_check, 'i', $cart_id);
mysqli_stmt_execute($stmt_check);
$result = mysqli_stmt_get_result($stmt_check);
$row = mysqli_fetch_assoc($result);

if (!$row || $row['id_user'] !== $_SESSION['user_id']) {
    $response['message'] = 'Accès non autorisé ou panier non trouvé.';
    echo json_encode($response);
    exit;
}
mysqli_stmt_close($stmt_check);

// Appeler la procédure stockée
$stmt = mysqli_prepare($conn, "CALL valider_commande_panier(?)");
mysqli_stmt_bind_param($stmt, 'i', $cart_id);

if (mysqli_stmt_execute($stmt)) {
    $response['success'] = true;
} else {
    $response['message'] = 'Erreur lors de la validation de la commande : ' . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
echo json_encode($response);
?>
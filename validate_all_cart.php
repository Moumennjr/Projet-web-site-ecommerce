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

if (!isset($_POST['user_id'])) {
    $response['message'] = 'Données invalides.';
    echo json_encode($response);
    exit;
}

$user_id = (int)$_POST['user_id'];

if ($_SESSION['user_id'] !== $user_id) {
    $response['message'] = 'Accès non autorisé.';
    echo json_encode($response);
    exit;
}

// Récupérer tous les articles du panier
$query = "SELECT id FROM panier WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$cart_ids = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cart_ids[] = $row['id'];
}
mysqli_stmt_close($stmt);

if (empty($cart_ids)) {
    $response['message'] = 'Le panier est vide.';
    echo json_encode($response);
    exit;
}

// Valider chaque article
$success = true;
foreach ($cart_ids as $cart_id) {
    $stmt = mysqli_prepare($conn, "CALL valider_commande_panier(?)");
    mysqli_stmt_bind_param($stmt, 'i', $cart_id);
    if (!mysqli_stmt_execute($stmt)) {
        $success = false;
        $response['message'] = 'Erreur lors de la validation d\'un article : ' . mysqli_error($conn);
        break;
    }
    mysqli_stmt_close($stmt);
}

if ($success) {
    $response['success'] = true;
}

echo json_encode($response);
?>
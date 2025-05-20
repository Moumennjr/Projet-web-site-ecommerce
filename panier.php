<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_items = [];
$total_items = 0;
$total_price = 0;

// Récupérer les articles du panier avec leur ID
$query = "
    SELECT p.id, p.id_item, p.quantite, i.nom, i.prix, i.image
    FROM panier p
    JOIN item i ON p.id_item = i.id
    WHERE p.id_user = ?
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $row['prix_total'] = $row['prix'] * $row['quantite'];
    $cart_items[] = $row;
    $total_items += $row['quantite'];
    $total_price += $row['prix_total'];
}
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - ElectroShop</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="logo">ElectroShop</div>
        <nav class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="panier.php">Panier</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Déconnexion</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php">Admin</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php">Connexion</a>
            <?php endif; ?>
        </nav>
        <form class="search-form" method="GET" action="index.php">
            <input type="text" name="search" placeholder="Rechercher...">
            <select name="categorie">
                <option value="">Catégorie</option>
                <?php
                $categories_query = "SELECT DISTINCT categorie FROM item WHERE categorie IS NOT NULL";
                $categories_result = mysqli_query($conn, $categories_query);
                while ($row = mysqli_fetch_assoc($categories_result)) {
                    echo '<option value="' . htmlspecialchars($row['categorie']) . '">' . htmlspecialchars($row['categorie']) . '</option>';
                }
                ?>
            </select>
            <input type="number" name="prix_min" placeholder="Prix min">
            <input type="number" name="prix_max" placeholder="Prix max">
            <button type="submit">Filtrer</button>
        </form>
    </header>

    <main class="main-content">
        <h2>Votre Panier</h2>
        <?php if (empty($cart_items)): ?>
            <p>Votre panier est vide.</p>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Nom</th>
                        <th>Prix Unitaire</th>
                        <th>Quantité</th>
                        <th>Prix Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['nom']); ?>" class="cart-item-image"></td>
                            <td><a href="item.php?id=<?php echo $item['id_item']; ?>"><?php echo htmlspecialchars($item['nom']); ?></a></td>
                            <td><?php echo number_format($item['prix'], 2); ?> €</td>
                            <td><?php echo $item['quantite']; ?></td>
                            <td><?php echo number_format($item['prix_total'], 2); ?> €</td>
                            <td>
                                <button class="btn btn-validate" data-cart-id="<?php echo $item['id']; ?>">Valider</button>
                                <button class="btn btn-delete" data-cart-id="<?php echo $item['id']; ?>">Supprimer</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cart-summary">
                <p><strong>Total articles :</strong> <?php echo $total_items; ?></p>
                <p><strong>Prix total :</strong> <?php echo number_format($total_price, 2); ?> €</p>
                <div class="cart-actions">
                    <button class="btn btn-validate-all" data-user-id="<?php echo $user_id; ?>">Valider tout (<?php echo $total_items; ?>)</button>
                    <button class="btn btn-delete-all" data-user-id="<?php echo $user_id; ?>">Supprimer tout</button>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>© 2025 ElectroShop. Tous droits réservés.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
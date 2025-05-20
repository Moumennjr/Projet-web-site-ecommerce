<?php
session_start();
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID invalide.');
}

$item_id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM item WHERE id = '$item_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    die('Article non trouvé.');
}

$item = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['nom']); ?> - ElectroShop</title>
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
            <input type="text" name="search" placeholder="Rechercher..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <select name="categorie">
                <option value="">Catégorie</option>
                <?php
                $categories_query = "SELECT DISTINCT categorie FROM item WHERE categorie IS NOT NULL";
                $categories_result = mysqli_query($conn, $categories_query);
                while ($row = mysqli_fetch_assoc($categories_result)) {
                    echo '<option value="' . htmlspecialchars($row['categorie']) . '" ' . (isset($_GET['categorie']) && $_GET['categorie'] === $row['categorie'] ? 'selected' : '') . '>' . htmlspecialchars($row['categorie']) . '</option>';
                }
                ?>
            </select>
            <input type="number" name="prix_min" placeholder="Prix min" value="<?php echo isset($_GET['prix_min']) ? htmlspecialchars($_GET['prix_min']) : ''; ?>">
            <input type="number" name="prix_max" placeholder="Prix max" value="<?php echo isset($_GET['prix_max']) ? htmlspecialchars($_GET['prix_max']) : ''; ?>">
            <button type="submit">Filtrer</button>
        </form>
    </header>

    <main class="main-content">
        <div class="item-details">
            <div class="item-image">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['nom']); ?>">
            </div>
            <div class="item-info">
                <h2><?php echo htmlspecialchars($item['nom']); ?></h2>
                <p class="price"><?php echo number_format($item['prix'], 2); ?> €</p>
                <p><strong>Catégorie :</strong> <?php echo htmlspecialchars($item['categorie'] ?: 'Non spécifiée'); ?></p>
                <p><strong>Stock :</strong> <?php echo htmlspecialchars($item['stock']); ?> unité(s)</p>
                <p><?php echo htmlspecialchars($item['description']); ?></p>
                <button class="btn add-to-cart" data-id="<?php echo $item['id']; ?>">Ajouter au panier</button>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>© 2025 ElectroShop. Tous droits réservés.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
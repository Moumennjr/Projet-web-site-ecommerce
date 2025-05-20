<?php
session_start();
require_once 'config.php';

// Récupérer les catégories pour le filtre
$categories_query = "SELECT DISTINCT categorie FROM item WHERE categorie IS NOT NULL";
$categories_result = mysqli_query($conn, $categories_query);
$categories = [];
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row['categorie'];
}

// Récupérer les items avec filtre éventuel
$where = [];
if (isset($_GET['categorie']) && !empty($_GET['categorie'])) {
    $categorie = mysqli_real_escape_string($conn, $_GET['categorie']);
    $where[] = "categorie = '$categorie'";
}
if (isset($_GET['prix_min']) && is_numeric($_GET['prix_min'])) {
    $prix_min = mysqli_real_escape_string($conn, $_GET['prix_min']);
    $where[] = "prix >= $prix_min";
}
if (isset($_GET['prix_max']) && is_numeric($_GET['prix_max'])) {
    $prix_max = mysqli_real_escape_string($conn, $_GET['prix_max']);
    $where[] = "prix <= $prix_max";
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where[] = "nom LIKE '%$search%' OR description LIKE '%$search%'";
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$query = "SELECT * FROM item $where_clause LIMIT 5"; // Limiter à 5 items pour correspondre à l'image
$result = mysqli_query($conn, $query);
$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}
$featured_item = !empty($items) ? array_shift($items) : null; // Premier item comme produit mis en avant
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElectroShop</title>
    <link rel="stylesheet" href="style.css">
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
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo isset($_GET['categorie']) && $_GET['categorie'] === $cat ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="prix_min" placeholder="Prix min" value="<?php echo isset($_GET['prix_min']) ? htmlspecialchars($_GET['prix_min']) : ''; ?>">
            <input type="number" name="prix_max" placeholder="Prix max" value="<?php echo isset($_GET['prix_max']) ? htmlspecialchars($_GET['prix_max']) : ''; ?>">
            <button type="submit">Filtrer</button>
        </form>
    </header>

    <main class="main-content">
        <div class="featured-product">
            <?php if ($featured_item): ?>
                <div class="featured-image">
                    <img src="<?php echo htmlspecialchars($featured_item['image']); ?>" alt="<?php echo htmlspecialchars($featured_item['nom']); ?>">
                </div>
                <div class="featured-details">
                    <h2><?php echo htmlspecialchars($featured_item['nom']); ?></h2>
                    <p class="price"><?php echo number_format($featured_item['prix'], 2); ?> €</p>
                    <p><?php echo htmlspecialchars($featured_item['description']); ?></p>
                    <button class="btn add-to-cart" data-id="<?php echo $featured_item['id']; ?>">Ajouter au panier</button>
                </div>
            <?php else: ?>
                <p>Aucun produit disponible pour le moment.</p>
            <?php endif; ?>
        </div>

        <div class="products-grid">
            <?php foreach ($items as $item): ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['nom']); ?>">
                    <h3><?php echo htmlspecialchars($item['nom']); ?></h3>
                    <p class="price"><?php echo number_format($item['prix'], 2); ?> €</p>
                    <button class="btn add-to-cart" data-id="<?php echo $item['id']; ?>">Ajouter au panier</button>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer class="footer">
        <p>© 2025 ElectroShop. Tous droits réservés.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
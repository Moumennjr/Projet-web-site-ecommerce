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
$query = "SELECT * FROM item $where_clause";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique Électronique</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">ElectroShop</div>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="panier.php">Panier</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">Déconnexion</a></li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin.php">Admin</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="search-filter">
            <form method="GET" action="index.php">
                <input type="text" name="search" placeholder="Rechercher un produit..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <select name="categorie">
                    <option value="">Toutes les catégories</option>
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
        </section>

        <section class="items-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($item = mysqli_fetch_assoc($result)): ?>
                    <div class="item-card">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['nom']); ?>">
                        <h3><?php echo htmlspecialchars($item['nom']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>...</p>
                        <p>Catégorie : <?php echo htmlspecialchars($item['categorie'] ?: 'Non spécifiée'); ?></p>
                        <p class="price"><?php echo number_format($item['prix'], 2); ?> €</p>
                        <a href="item.php?id=<?php echo $item['id']; ?>" class="btn">Voir détails</a>
                        <button class="btn add-to-cart" data-id="<?php echo $item['id']; ?>">Ajouter au panier</button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Aucun produit trouvé.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>© 2025 ElectroShop. Tous droits réservés.</p>
    </footer>

    <script src="./script.js"></script>
</body>
</html>
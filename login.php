<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = trim(isset($_POST['password']) ? $_POST['password'] : '');
    // Validation
    if (empty($email) || empty($password)) {
        $errors[] = 'Tous les champs sont requis.';
    }

    if (empty($errors)) {
        // Vérifier les identifiants
        $query = "SELECT id, mot_de_passe, role FROM user WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['mot_de_passe'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Email ou mot de passe incorrect.';
            }
        } else {
            $errors[] = 'Email ou mot de passe incorrect.';
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ElectroShop</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header class="header">
        <div class="logo">ElectroShop</div>
        <nav class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="panier.php">Panier</a>
            <a href="register.php">Inscription</a>
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
        <div class="login-form">
            <h2>Connexion</h2>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email :</label>
                    <input type="email" id="email" name="email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe :</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Se connecter</button>
            </form>
            <p>Pas de compte ? <a href="register.php">Inscrivez-vous</a></p>
        </div>
    </main>

    <footer class="footer">
        <p>© 2025 ElectroShop. Tous droits réservés.</p>
    </footer>

    <script src="script.js"></script>
</body>

</html>
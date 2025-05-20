<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim(isset($_POST['nom']) ? $_POST['nom'] : '');
    $prenom = trim(isset($_POST['prenom']) ? $_POST['prenom'] : '');
    $num_tel = trim(isset($_POST['num_tel']) ? $_POST['num_tel'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = trim(isset($_POST['password']) ? $_POST['password'] : '');
    $confirm_password = trim(isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '');

    // Validation
    if (empty($nom) || empty($prenom) || empty($num_tel) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = 'Tous les champs sont requis.';
    }

    if (strlen($nom) > 50) {
        $errors[] = 'Le nom ne doit pas dépasser 50 caractères.';
    }

    if (strlen($prenom) > 50) {
        $errors[] = 'Le prénom ne doit pas dépasser 50 caractères.';
    }

    if (!preg_match('/^[0-9]{10}$/', $num_tel)) {
        $errors[] = 'Le numéro de téléphone doit contenir exactement 10 chiffres.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errors[] = 'L\'adresse email est invalide ou trop longue (max 100 caractères).';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    if (empty($errors)) {
        // Vérifier si l'email existe déjà
        $check_query = "SELECT id FROM user WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'Cet email est déjà utilisé.';
        }
        mysqli_stmt_close($stmt);
    }

    if (empty($errors)) {
        // Hacher le mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insérer l'utilisateur avec le rôle 'client'
        $insert_query = "INSERT INTO user (nom, prenom, num_tel, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?, 'client')";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'sssss', $nom, $prenom, $num_tel, $email, $hashed_password);

        if (mysqli_stmt_execute($stmt)) {
            $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
            header('Refresh: 2; URL=login.php');
        } else {
            $errors[] = 'Une erreur s\'est produite lors de l\'inscription.';
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
    <title>Inscription - ElectroShop</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header class="header">
        <div class="logo">ElectroShop</div>
        <nav class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="panier.php">Panier</a>
            <a href="login.php">Connexion</a>
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
        <div class="register-form">
            <h2>Inscription</h2>
            <?php if ($success): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="register.php" id="register-form">
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom"
                        value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom :</label>
                    <input type="text" id="prenom" name="prenom"
                        value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="num_tel">Numéro de téléphone :</label>
                    <input type="text" id="num_tel" name="num_tel"
                        value="<?php echo isset($_POST['num_tel']) ? htmlspecialchars($_POST['num_tel']) : ''; ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="email">Email :</label>
                    <input type="email" id="email" name="email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe :</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe :</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn">S'inscrire</button>
            </form>
            <p>Déjà un compte ? <a href="login.php">Connectez-vous</a></p>
        </div>
    </main>

    <footer class="footer">
        <p>© 2025 ElectroShop. Tous droits réservés.</p>
    </footer>

    <script src="script.js"></script>
</body>

</html>
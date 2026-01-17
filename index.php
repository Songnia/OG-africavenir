<?php
require_once 'src/Controllers/AuthController.php';
$authController = new \App\Controllers\AuthController();
$error = $authController->login();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page de connexion pour AfricAvenir">
    <title>AfricAvenir | Login</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="auth-container">
        <header class="auth-header">
            <img src="assets/logo-africavenir.png" alt="Image décorative AfricAvenir" class="decorative-image">
        </header>

        <main class="auth-main">
            <!--<a href="login.html" class="back-link">< Back</a>-->
            <h1>Login</h1>
            <p class="form-instruction">Entrez vos Informations</p>
            <?php if ($error): ?>
                <div style="color: red; text-align: center; margin-bottom: 10px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form id="loginForm" action="" method="post">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur ou Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="motdepasse">Mot de passe</label>
                    <input type="password" id="motdepasse" name="motdepasse" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Valider</button>
            </form>
            <div class="auth-links" style="display: flex; justify-content: space-between; margin-top: 15px;">
                <a href="forgot-password.php" style="font-size: 0.9em;">Mot de passe oublié ?</a>
                <p style="margin: 0;">Pas encore de compte ? <a href="signup.php">Inscrivez-vous ici</a></p>
            </div>
        </main>
    </div>

    <footer class="site-footer auth-footer">
        <p>©2025 AfricAvenir tous droits réservés</p>
    </footer>

    <!--<script src="js/main.js"></script>-->
</body>
</html>
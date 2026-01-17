<?php
/**
 * Script pour créer un utilisateur administrateur WordPress
 * Nom d'utilisateur: Admin1
 * Mot de passe: 123456789
 */

// Ajustez ce chemin selon votre installation WordPress
// Options possibles:
// '/var/www/html/wordpress/wp-load.php'
// '/var/www/html/africavenir-international/wp-load.php'

$wp_load_paths = [
    '/var/www/html/wordpress/wp-load.php',
    '/var/www/html/africavenir-international/wp-load.php',
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        echo "✅ WordPress chargé depuis: $path\n\n";
        break;
    }
}

if (!$wp_loaded) {
    die("❌ Erreur: Impossible de trouver wp-load.php\nVeuillez ajuster le chemin dans ce script.\n");
}

// Informations du nouvel utilisateur
$username = 'Admin1';
$password = '123456789';
$email = 'admin@africavenir.org'; // Vous pouvez changer cet email

// Vérifier si l'utilisateur existe déjà
$user_id = username_exists($username);

if ($user_id) {
    echo "⚠️  L'utilisateur '$username' existe déjà (ID: $user_id)\n";
    echo "Mise à jour du mot de passe...\n";
    
    // Mettre à jour le mot de passe
    wp_set_password($password, $user_id);
    
    // S'assurer qu'il est administrateur
    $user = new WP_User($user_id);
    $user->set_role('administrator');
    
    echo "✅ Mot de passe mis à jour et rôle défini à administrateur\n";
} else {
    echo "Création du nouvel utilisateur administrateur...\n";
    
    // Créer le nouvel utilisateur
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        die("❌ Erreur lors de la création: " . $user_id->get_error_message() . "\n");
    }
    
    // Définir le rôle administrateur
    $user = new WP_User($user_id);
    $user->set_role('administrator');
    
    echo "✅ Utilisateur créé avec succès!\n";
}

// Afficher les informations de connexion
echo "\n" . str_repeat("=", 50) . "\n";
echo "INFORMATIONS DE CONNEXION\n";
echo str_repeat("=", 50) . "\n";
echo "URL de connexion: " . wp_login_url() . "\n";
echo "Nom d'utilisateur: $username\n";
echo "Mot de passe: $password\n";
echo "Email: $email\n";
echo "Rôle: Administrateur\n";
echo str_repeat("=", 50) . "\n";

// Lister tous les utilisateurs administrateurs
echo "\n📋 Liste des administrateurs WordPress:\n";
$admins = get_users(array('role' => 'administrator'));
foreach ($admins as $admin) {
    echo "  - {$admin->user_login} (ID: {$admin->ID}, Email: {$admin->user_email})\n";
}

echo "\n✅ Script terminé avec succès!\n";
?>

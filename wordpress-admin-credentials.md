# Utilisateur WordPress Créé

## ✅ Informations de Connexion

**URL de connexion:** http://localhost/wordpress/wp-login.php

**Nom d'utilisateur:** Admin1  
**Mot de passe:** 123456789  
**Email:** admin@africavenir.org  
**Rôle:** Administrateur

## 📋 Détails

- **ID utilisateur:** 31
- **Droits:** Accès administrateur complet
- **Installation WordPress:** `/var/www/html/wordpress/`

## 🔒 Recommandations de Sécurité

> [!WARNING]
> Le mot de passe actuel (`123456789`) est très simple et **non sécurisé** pour un environnement de production.

**Pour la production, il est fortement recommandé de:**
1. Changer le mot de passe pour un mot de passe fort (12+ caractères, lettres, chiffres, symboles)
2. Utiliser une adresse email valide
3. Activer l'authentification à deux facteurs (2FA)

## 🔄 Pour Changer le Mot de Passe

**Méthode 1 - Depuis WordPress:**
1. Se connecter avec Admin1
2. Aller dans **Utilisateurs** → **Profil**
3. Faire défiler jusqu'à "Gestion du compte"
4. Cliquer sur "Générer un mot de passe" ou entrer un nouveau mot de passe

**Méthode 2 - Réexécuter le script:**
Le script [`create-wordpress-admin.php`](file:///var/www/html/OG-afrcavenir/create-wordpress-admin.php) peut être utilisé pour réinitialiser le mot de passe si besoin.

## 📝 Script Utilisé

Le script de création est disponible ici: [`create-wordpress-admin.php`](file:///var/www/html/OG-afrcavenir/create-wordpress-admin.php)

Vous pouvez le réutiliser ou le modifier pour créer d'autres utilisateurs.

<?php
require_once 'includes/admin-check.php';
require_once 'src/Helpers/RoleHelper.php';
require_once 'src/Controllers/MemberController.php';

use App\Helpers\RoleHelper;

// Strict Super Admin Check
if (!RoleHelper::isSuperAdmin($_SESSION['user_id'])) {
    header('Location: dashboard.php?error=unauthorized');
    exit;
}

$memberController = new \App\Controllers\MemberController();
$user = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $isEdit = true;
    $response = $memberController->getMember($_GET['id']);
    if ($response['success']) {
        $user = $response['data'];
    } else {
        header('Location: dashboard-users.php?error=notfound');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<?php include('includes/meta-data.php'); ?>
<body>
    <div class="page-container">
        <?php include('includes/header.php'); ?>

        <div class="main-content">
            <?php include('includes/filter-bare.php'); ?>

            <main>
                <section class="page-title-section">
                    <h2><?php echo $isEdit ? 'Modifier Utilisateur' : 'Créer Utilisateur'; ?></h2>
                    <a href="dashboard-users.php" class="btn btn-secondary">
                        < Retour
                    </a>
                </section>

                <section class="card form-card" id="formSection">
                    <form id="userForm" action="user-action-handler.php" method="POST">
                        <input type="hidden" name="action" value="<?php echo $isEdit ? 'update' : 'create'; ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?php echo $user['ID']; ?>">
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom">Nom</label>
                                <input type="text" id="nom" name="nom" value="<?php echo $isEdit ? htmlspecialchars($user['last_name'] ?? '') : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="prenom">Prénom</label>
                                <input type="text" id="prenom" name="prenom" value="<?php echo $isEdit ? htmlspecialchars($user['first_name'] ?? '') : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo $isEdit ? htmlspecialchars($user['user_email']) : ''; ?>" required>
                        </div>

                        <?php if (!$isEdit): ?>
                        <div class="alert alert-info">
                            Un mot de passe sera généré automatiquement et envoyé par email (ou affiché).
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="role">Rôle</label>
                            <select id="role" name="role" required>
                                <?php 
                                $currentRole = $isEdit ? RoleHelper::getUserLevel($user['ID']) : RoleHelper::LEVEL_MEMBER;
                                ?>
                                <option value="<?php echo RoleHelper::LEVEL_MEMBER; ?>" <?php echo $currentRole === RoleHelper::LEVEL_MEMBER ? 'selected' : ''; ?>>Membre</option>
                                <option value="<?php echo RoleHelper::LEVEL_ADMIN_MTM; ?>" <?php echo $currentRole === RoleHelper::LEVEL_ADMIN_MTM ? 'selected' : ''; ?>>Admin MTM</option>
                                <option value="<?php echo RoleHelper::LEVEL_SUPERADMIN; ?>" <?php echo $currentRole === RoleHelper::LEVEL_SUPERADMIN ? 'selected' : ''; ?>>Super Admin</option>
                            </select>
                        </div>

                        <div class="form-group" id="categoryGroup" style="<?php echo $currentRole === RoleHelper::LEVEL_MEMBER ? '' : 'display:none;'; ?>">
                            <label for="categorie">Catégorie (si Membre)</label>
                            <select id="categorie" name="categorie">
                                <?php 
                                $currentCat = $isEdit ? ($user['categorie'] ?? '') : '';
                                ?>
                                <option value="member-ships" <?php echo $currentCat === 'member-ships' ? 'selected' : ''; ?>>Member-ships</option>
                                <option value="alumni" <?php echo $currentCat === 'alumni' ? 'selected' : ''; ?>>Alumni</option>
                                <option value="cercle-amis" <?php echo $currentCat === 'cercle-amis' ? 'selected' : ''; ?>>Cercle d'amis</option>
                                <option value="mecene" <?php echo $currentCat === 'mecene' ? 'selected' : ''; ?>>Mécène</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </section>

                <!-- Zone d'affichage des identifiants (uniquement pour création) -->
                <div id="credentialsDisplay" style="display: none; margin: 30px auto; max-width: 500px;">
                    <div style="background-color: var(--bg-secondary); border: 2px solid var(--africavenir-yellow); border-radius: 8px; padding: 25px; text-align: left;">
                        <h4 style="margin-top: 0; color: var(--africavenir-yellow); text-align: center;">🔐 Identifiants Générés</h4>
                        <p style="text-align: center; color: var(--text-muted); font-size: 0.9em; margin-bottom: 20px;">
                            Veuillez communiquer ces informations à l'utilisateur.
                        </p>
                        
                        <div style="margin: 15px 0;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px; color: var(--text-primary);">Nom d'utilisateur:</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="text" id="displayUsername" readonly style="flex: 1; padding: 10px; background-color: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 4px; font-family: monospace; font-size: 1.1em;">
                                <button type="button" onclick="copyToClipboard('displayUsername', this)" class="btn btn-secondary" style="padding: 10px 15px;">
                                    📋 Copier
                                </button>
                            </div>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px; color: var(--text-primary);">Mot de passe:</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="text" id="displayPassword" readonly style="flex: 1; padding: 10px; background-color: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 4px; font-family: monospace; font-size: 1.1em;">
                                <button type="button" onclick="copyToClipboard('displayPassword', this)" class="btn btn-secondary" style="padding: 10px 15px;">
                                    📋 Copier
                                </button>
                            </div>
                        </div>
                        
                        <div style="background-color: var(--bg-warning-light); border-left: 4px solid var(--color-warning); padding: 15px; margin-top: 20px; border-radius: 4px;">
                            <p style="margin: 0; font-size: 0.9em; color: var(--text-primary);">
                                <strong>⚠️ Important:</strong> Ces identifiants ne seront affichés qu'une seule fois. 
                                Copiez-les maintenant.
                            </p>
                        </div>
                        
                        <div style="text-align: center; margin-top: 20px;">
                            <a href="dashboard-users.php" class="btn btn-primary" style="display: inline-block; padding: 12px 24px; text-decoration: none;">
                                Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="site-footer main-footer">
                <p>©2025 AfricAvenir tous droits réservés</p>
            </footer>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        // Fonction pour copier dans le presse-papier
        function copyToClipboard(inputId, button) {
            const input = document.getElementById(inputId);
            input.select();
            input.setSelectionRange(0, 99999); // Pour mobile
            
            try {
                document.execCommand('copy');
                const originalText = button.textContent;
                button.textContent = '✓ Copié!';
                button.style.backgroundColor = 'var(--color-success)';
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.style.backgroundColor = '';
                }, 2000);
            } catch (err) {
                alert('Erreur lors de la copie');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const categoryGroup = document.getElementById('categoryGroup');
            const formSection = document.getElementById('formSection');
            const credentialsDisplay = document.getElementById('credentialsDisplay');

            roleSelect.addEventListener('change', function() {
                if (this.value === '<?php echo RoleHelper::LEVEL_MEMBER; ?>') {
                    categoryGroup.style.display = 'block';
                } else {
                    categoryGroup.style.display = 'none';
                }
            });

            const form = document.getElementById('userForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(form);

                fetch('user-action-handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.credentials) {
                            // Show credentials UI
                            document.getElementById('displayUsername').value = data.credentials.username;
                            document.getElementById('displayPassword').value = data.credentials.password;
                            
                            formSection.style.display = 'none';
                            credentialsDisplay.style.display = 'block';
                            
                            // Change page title
                            document.querySelector('.page-title-section h2').textContent = 'Utilisateur Créé';
                        } else {
                            alert('Utilisateur enregistré avec succès');
                            window.location.href = 'dashboard-users.php';
                        }
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Une erreur est survenue.');
                });
            });
        });
    </script>
</body>
</html>

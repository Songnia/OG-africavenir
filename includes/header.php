<aside class="sidebar">
    <div class="sidebar-header">
        <img src="assets/logo-africavenir.png" alt="Logo AfricAvenir" class="logo">

        <!-- L'email de l'admin est repris de l'OCR -->
        <div class="admin-info">
            <span>emai@admistrateur.com</span>
        </div>
    </div>
    <nav class="main-nav">
        <ul>
            <li class="active"> <a href="dashboard-admin.php"> <img src="assets/icons/dashboard.svg" alt="" class="nav-icon"> Dashboard </a> </li>
            <li> <a href="dashboard-list-member.php"> <img src="assets/icons/members.svg" alt="" class="nav-icon"> Membres</a> </li>
            <li> <a href="dashboard-list-paiement.php"> <img src="assets/icons/payment.svg" alt="" class="nav-icon"> Paiement </a> </li>
            <?php 
            require_once __DIR__ . '/../src/Helpers/RoleHelper.php';
            use App\Helpers\RoleHelper;
            if (isset($_SESSION['user_id']) && RoleHelper::isSuperAdmin($_SESSION['user_id'])): 
            ?>
            <li> <a href="dashboard-users.php"> <img src="assets/icons/users.svg" alt="" class="nav-icon"> Administrateur </a> </li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php" class="signout-link"> <img src="assets/icons/signout.svg" alt="" class="nav-icon"> Sign Out </a>
    </div>
</aside>
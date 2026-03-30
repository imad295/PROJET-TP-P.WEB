<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul>
            <li><a href="dashboard_admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_admin.php' ? 'active' : ''; ?>">
                <span class="icon">📊</span> Tableau de bord
            </a></li>
            <li><a href="etudiants.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'etudiants.php' ? 'active' : ''; ?>">
                <span class="icon">👨‍🎓</span> Gestion des étudiants
            </a></li>
            <li><a href="enseignants.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'enseignants.php' ? 'active' : ''; ?>">
                <span class="icon">👨‍🏫</span> Gestion des enseignants
            </a></li>
            <li><a href="admins.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admins.php' ? 'active' : ''; ?>">
                <span class="icon">👑</span> Gestion des administrateurs
            </a></li>
            <li><a href="modules.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'modules.php' ? 'active' : ''; ?>">
                <span class="icon">📚</span> Gestion des modules
            </a></li>
            <li><a href="notes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notes.php' ? 'active' : ''; ?>">
                <span class="icon">📝</span> Gestion des notes
            </a></li>
            <li><a href="statistiques.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'statistiques.php' ? 'active' : ''; ?>">
                <span class="icon">📈</span> Statistiques
            </a></li>
        </ul>
    </nav>
</aside>
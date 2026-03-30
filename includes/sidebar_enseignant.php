<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul>
            <li><a href="dashboard_enseignant.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_enseignant.php' ? 'active' : ''; ?>">
                <span class="icon">📊</span> Tableau de bord
            </a></li>
            <li><a href="notes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notes.php' ? 'active' : ''; ?>">
                <span class="icon">📝</span> Gestion des notes
            </a></li>
            <li><a href="releve.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'releve.php' ? 'active' : ''; ?>">
                <span class="icon">📄</span> Relevés de notes
            </a></li>
        </ul>
    </nav>
</aside>
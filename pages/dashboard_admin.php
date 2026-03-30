<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

// Statistiques
$stmt = $pdo->query("SELECT COUNT(*) as total FROM etudiant");
$total_etudiants = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM enseignant");
$total_enseignants = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM module");
$total_modules = $stmt->fetch()['total'];

include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Tableau de bord Administrateur</h1>
    </div>
    
    <?php displayMessage(); ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👨‍🎓</div>
            <div class="stat-info">
                <h3><?php echo $total_etudiants; ?></h3>
                <p>Étudiants</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👨‍🏫</div>
            <div class="stat-info">
                <h3><?php echo $total_enseignants; ?></h3>
                <p>Enseignants</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
                <h3><?php echo $total_modules; ?></h3>
                <p>Modules</p>
            </div>
        </div>
    </div>
    
    <div class="quick-actions">
        <h2>Actions rapides</h2>
        <div class="actions-grid">
            <a href="etudiants.php" class="action-btn">Gérer les étudiants</a>
            <a href="enseignants.php" class="action-btn">Gérer les enseignants</a>
            <a href="modules.php" class="action-btn">Gérer les modules</a>
            <a href="notes.php" class="action-btn">Gérer les notes</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

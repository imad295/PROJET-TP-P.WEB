<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

$stmt = $pdo->query("SELECT COUNT(*) FROM etudiant");
$total_etudiants = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM enseignant");
$total_enseignants = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM module");
$total_modules = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT * FROM etudiant ORDER BY id_etudiant DESC LIMIT 5");
$derniers_etudiants = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-tachometer-alt"></i> Tableau de bord Administrateur</h1>
                <p>Bienvenue, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></p>
            </div>
        </div>

        <!-- Cartes statistiques avec animation -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="stat-card-animated">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #4361ee, #3b82f6); border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-users" style="font-size: 28px; color: white;"></i>
                </div>
                <div style="font-size: 36px; font-weight: 700; color: #1a1a2e;"><?php echo $total_etudiants; ?></div>
                <div style="color: #6c757d; font-size: 14px; margin-top: 5px;">Étudiants inscrits</div>
            </div>
            <div class="stat-card-animated">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #28a745, #20c997); border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-chalkboard-user" style="font-size: 28px; color: white;"></i>
                </div>
                <div style="font-size: 36px; font-weight: 700; color: #1a1a2e;"><?php echo $total_enseignants; ?></div>
                <div style="color: #6c757d; font-size: 14px; margin-top: 5px;">Enseignants</div>
            </div>
            <div class="stat-card-animated">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #fd7e14, #ffc107); border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-book" style="font-size: 28px; color: white;"></i>
                </div>
                <div style="font-size: 36px; font-weight: 700; color: #1a1a2e;"><?php echo $total_modules; ?></div>
                <div style="color: #6c757d; font-size: 14px; margin-top: 5px;">Modules</div>
            </div>
        </div>

        <!-- Boutons actions avec animation -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <a href="etudiants.php" class="action-btn">
                <i class="fas fa-users"></i> Étudiants
            </a>
            <a href="enseignants.php" class="action-btn" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <i class="fas fa-chalkboard-user"></i> Enseignants
            </a>
            <a href="admins.php" class="action-btn" style="background: linear-gradient(135deg, #fd7e14, #ffc107);">
                <i class="fas fa-crown"></i> Administrateurs
            </a>
            <a href="modules.php" class="action-btn" style="background: linear-gradient(135deg, #6f42c1, #8b5cf6);">
                <i class="fas fa-book"></i> Modules
            </a>
            <a href="notes.php" class="action-btn" style="background: linear-gradient(135deg, #e83e8c, #f06595);">
                <i class="fas fa-pen-fancy"></i> Notes
            </a>
            <a href="statistiques.php" class="action-btn" style="background: linear-gradient(135deg, #17a2b8, #20c997);">
                <i class="fas fa-chart-line"></i> Statistiques
            </a>
        </div>

        <!-- Derniers étudiants -->
        <div style="background: #f8f9fa; border-radius: 16px; padding: 20px;">
            <h3 style="margin-bottom: 15px; font-size: 16px;"><i class="fas fa-clock"></i> Derniers étudiants inscrits</h3>
            <?php if(count($derniers_etudiants) > 0): ?>
                <?php foreach($derniers_etudiants as $e): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #e9ecef;">
                    <span style="font-weight: 600;"><?php echo htmlspecialchars($e['matricule']); ?></span>
                    <span><?php echo htmlspecialchars($e['prenom'] . ' ' . $e['nom']); ?></span>
                    <span style="color: #6c757d; font-size: 13px;"><?php echo date('d/m/Y', strtotime($e['date_naissance'])); ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #6c757d; text-align: center; padding: 20px;">Aucun étudiant inscrit</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
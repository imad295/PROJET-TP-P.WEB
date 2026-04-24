<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('enseignant')) {
    redirect('../index.php');
}

$stmt = $pdo->prepare("SELECT * FROM enseignant WHERE id_enseignant = ?");
$stmt->execute([$_SESSION['user_id']]);
$enseignant = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM module WHERE id_enseignant = ?");
$stmt->execute([$_SESSION['user_id']]);
$modules = $stmt->fetchAll();

$total_etudiants = $pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();

include '../includes/header.php';
?>

<div style="max-width: 1000px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-chalkboard-teacher"></i> Tableau de bord Enseignant</h1>
                <p>Bienvenue, <strong><?php echo htmlspecialchars($enseignant['prenom'] . ' ' . $enseignant['nom']); ?></strong></p>
            </div>
        </div>

        <!-- Cartes statistiques avec animation -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="stat-card-animated" style="background: white; border-radius: 16px; padding: 25px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e9ecef; transition: all 0.3s cubic-bezier(0.4,0,0.2,1);">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #4361ee, #3b82f6); border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-book" style="font-size: 28px; color: white;"></i>
                </div>
                <div style="font-size: 36px; font-weight: 700; color: #1a1a2e;"><?php echo count($modules); ?></div>
                <div style="color: #6c757d; font-size: 14px; margin-top: 5px;">Mes modules</div>
            </div>
            <div class="stat-card-animated" style="background: white; border-radius: 16px; padding: 25px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e9ecef; transition: all 0.3s cubic-bezier(0.4,0,0.2,1);">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #28a745, #20c997); border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-users" style="font-size: 28px; color: white;"></i>
                </div>
                <div style="font-size: 36px; font-weight: 700; color: #1a1a2e;"><?php echo $total_etudiants; ?></div>
                <div style="color: #6c757d; font-size: 14px; margin-top: 5px;">Étudiants total</div>
            </div>
        </div>

        <!-- Mes modules avec animation au survol -->
        <h3 style="margin-bottom: 20px; font-size: 18px;"><i class="fas fa-book-open"></i> Mes modules</h3>
        
        <?php if(count($modules) > 0): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php foreach($modules as $module): ?>
                <div class="module-card" style="background: white; border-radius: 16px; padding: 20px; border: 1px solid #e9ecef; transition: all 0.3s cubic-bezier(0.4,0,0.2,1);">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #4361ee, #3b82f6); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                        <i class="fas fa-chalkboard" style="font-size: 24px; color: white;"></i>
                    </div>
                    <h4 style="font-size: 18px; margin-bottom: 8px;"><?php echo htmlspecialchars($module['nom_module']); ?></h4>
                    <p style="color: #6c757d; font-size: 13px; margin-bottom: 15px;">Coefficient: <?php echo $module['coefficient']; ?></p>
                    <a href="notes.php?module=<?php echo $module['id_module']; ?>" class="action-btn" style="background: linear-gradient(135deg, #4361ee, #3b82f6); color: white; padding: 8px 16px; border-radius: 10px; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s cubic-bezier(0.4,0,0.2,1);">
                        <i class="fas fa-pen-fancy"></i> Gérer les notes
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="background: #f8f9fa; border-radius: 16px; padding: 40px; text-align: center;">
                <i class="fas fa-book" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px;"></i>
                <p style="color: #6c757d;">Aucun module assigné pour le moment</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Animations pour le dashboard enseignant */
.stat-card-animated:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.module-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.action-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(67,97,238,0.3);
}
</style>

<?php include '../includes/footer.php'; ?>
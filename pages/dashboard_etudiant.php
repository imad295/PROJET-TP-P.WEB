<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('etudiant')) {
    redirect('../index.php');
}

// Récupérer les informations de l'étudiant avec son groupe, section, spécialité, niveau
$stmt = $pdo->prepare("
    SELECT e.*, 
           g.nom_groupe, 
           s.nom_section, 
           sp.nom_specialite, 
           n.nom_niveau,
           n.ordre as niveau_ordre
    FROM etudiant e
    LEFT JOIN `groupe` g ON e.id_groupe = g.id_groupe
    LEFT JOIN section s ON g.id_section = s.id_section
    LEFT JOIN specialite sp ON s.id_specialite = sp.id_specialite
    LEFT JOIN niveau n ON sp.id_niveau = n.id_niveau
    WHERE e.id_etudiant = ?
");
$stmt->execute([$_SESSION['user_id']]);
$etudiant = $stmt->fetch();

if(!$etudiant) {
    redirect('../index.php');
}

// Récupérer les notes de l'étudiant
$stmt = $pdo->prepare("
    SELECT m.nom_module, m.coefficient, n.note_cc, n.note_examen, n.note_ratrapage,
    (COALESCE(n.note_cc, 0) + COALESCE(n.note_examen, 0))/2 as moyenne_module
    FROM note n
    JOIN module m ON n.id_module = m.id_module
    WHERE n.id_etudiant = ? AND n.session = 'Normale'
    ORDER BY m.nom_module
");
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll();

// Calcul de la moyenne générale
$total_coeff = 0;
$total_moyenne = 0;
foreach($notes as $note) {
    if($note['moyenne_module'] !== null) {
        $total_coeff += $note['coefficient'];
        $total_moyenne += $note['moyenne_module'] * $note['coefficient'];
    }
}
$moyenne_generale = $total_coeff > 0 ? $total_moyenne / $total_coeff : 0;

include '../includes/header.php';
?>

<div style="max-width: 1000px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-tachometer-alt"></i> Tableau de bord Étudiant</h1>
                <p>Bienvenue, <strong><?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?></strong></p>
            </div>
        </div>

        <!-- ========== FICHE D'IDENTITÉ ÉTUDIANT ========== -->
        <div style="background: linear-gradient(135deg, #4361ee, #3b82f6); border-radius: 20px; padding: 25px; margin-bottom: 25px; color: white;">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-id-card"></i> Mon identité académique</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <div style="font-size: 12px; opacity: 0.8;">Matricule</div>
                    <div style="font-size: 18px; font-weight: bold;"><?php echo htmlspecialchars($etudiant['matricule']); ?></div>
                </div>
                <div>
                    <div style="font-size: 12px; opacity: 0.8;">Niveau</div>
                    <div style="font-size: 18px; font-weight: bold;">
                        <?php echo htmlspecialchars($etudiant['nom_niveau'] ?? 'Non assigné'); ?>
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; opacity: 0.8;">Spécialité</div>
                    <div style="font-size: 18px; font-weight: bold;">
                        <?php echo htmlspecialchars($etudiant['nom_specialite'] ?? 'Non assigné'); ?>
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; opacity: 0.8;">Section</div>
                    <div style="font-size: 18px; font-weight: bold;">
                        <?php echo htmlspecialchars($etudiant['nom_section'] ?? 'Non assigné'); ?>
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; opacity: 0.8;">Groupe</div>
                    <div style="font-size: 18px; font-weight: bold;">
                        <?php echo htmlspecialchars($etudiant['nom_groupe'] ?? 'Non assigné'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== MOYENNE GÉNÉRALE ========== -->
        <div style="background: linear-gradient(135deg, #1a1a2e, #16213e); border-radius: 20px; padding: 25px; margin-bottom: 25px; text-align: center; color: white;">
            <div style="font-size: 14px; opacity: 0.8; margin-bottom: 5px;">Moyenne Générale</div>
            <div style="font-size: 48px; font-weight: 700;"><?php echo number_format($moyenne_generale, 2); ?>/20</div>
            <div style="margin-top: 10px;">
                <span style="background: <?php echo $moyenne_generale >= 10 ? '#10b981' : '#ef4444'; ?>; padding: 5px 20px; border-radius: 30px; font-size: 14px;">
                    <?php echo $moyenne_generale >= 10 ? '✅ Admis' : '❌ Non admis'; ?>
                </span>
            </div>
        </div>

        <!-- ========== MES NOTES ========== -->
        <div style="background: #f8f9fa; border-radius: 16px; padding: 20px;">
            <h3 style="margin-bottom: 15px;"><i class="fas fa-list"></i> Mes notes</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr style="background: #e8f0fe;">
                            <th>Module</th>
                            <th>Coefficient</th>
                            <th>CC (/20)</th>
                            <th>Examen (/20)</th>
                            <th>Moyenne</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($notes) > 0): ?>
                            <?php foreach($notes as $note): 
                                $moy = $note['moyenne_module'];
                                $valide = $moy >= 10;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($note['nom_module']); ?></strong></td>
                                <td align="center"><?php echo $note['coefficient']; ?></td>
                                <td align="center"><?php echo $note['note_cc'] ? number_format($note['note_cc'], 2) : '-'; ?></td>
                                <td align="center"><?php echo $note['note_examen'] ? number_format($note['note_examen'], 2) : '-'; ?></td>
                                <td align="center">
                                    <strong style="color: <?php echo $valide ? '#28a745' : '#dc3545'; ?>;">
                                        <?php echo number_format($moy, 2); ?>/20
                                    </strong>
                                </td>
                                <td align="center">
                                    <?php echo $valide ? '✅ Validé' : '❌ Non validé'; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Aucune note enregistrée</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Bouton Voir mon relevé complet -->
            <div style="text-align: center; margin-top: 25px;">
                <a href="releve.php" class="btn-releve">
                    <i class="fas fa-file-alt"></i> Voir mon relevé complet
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.btn-releve {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 14px 32px;
    border-radius: 50px;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(40,167,69,0.3);
}

.btn-releve:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(40,167,69,0.4);
    gap: 15px;
}

.btn-releve:active {
    transform: scale(0.98);
}

.btn-releve i:first-child {
    font-size: 18px;
}

.btn-releve i:last-child {
    transition: transform 0.3s ease;
}

.btn-releve:hover i:last-child {
    transform: translateX(5px);
}
</style>

<?php include '../includes/footer.php'; ?>
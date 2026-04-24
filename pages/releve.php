<?php
require_once '../includes/config.php';

if(!isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$selected_etudiant = null;
$etudiant = null;
$notes = [];
$total_coeff = 0;
$total_moyenne = 0;

// Si l'utilisateur est un étudiant, récupérer son ID directement
if(hasRole('etudiant')) {
    $selected_etudiant = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT e.*, g.nom_groupe, s.nom_section, sp.nom_specialite, n.nom_niveau
        FROM etudiant e
        LEFT JOIN `groupe` g ON e.id_groupe = g.id_groupe
        LEFT JOIN section s ON g.id_section = s.id_section
        LEFT JOIN specialite sp ON s.id_specialite = sp.id_specialite
        LEFT JOIN niveau n ON sp.id_niveau = n.id_niveau
        WHERE e.id_etudiant = ?
    ");
    $stmt->execute([$selected_etudiant]);
    $etudiant = $stmt->fetch();
} else {
    // Pour admin et enseignant : recherche par matricule
    $search_matricule = isset($_GET['search_matricule']) ? trim($_GET['search_matricule']) : '';
    
    if($search_matricule != '') {
        $stmt = $pdo->prepare("
            SELECT e.*, g.nom_groupe, s.nom_section, sp.nom_specialite, n.nom_niveau
            FROM etudiant e
            LEFT JOIN `groupe` g ON e.id_groupe = g.id_groupe
            LEFT JOIN section s ON g.id_section = s.id_section
            LEFT JOIN specialite sp ON s.id_specialite = sp.id_specialite
            LEFT JOIN niveau n ON sp.id_niveau = n.id_niveau
            WHERE e.matricule = ?
        ");
        $stmt->execute([$search_matricule]);
        $etudiant = $stmt->fetch();
        
        if($etudiant) {
            $selected_etudiant = $etudiant['id_etudiant'];
        } else {
            $error = "Aucun étudiant trouvé avec le matricule : " . htmlspecialchars($search_matricule);
        }
    } elseif(isset($_GET['etudiant'])) {
        $selected_etudiant = $_GET['etudiant'];
        $stmt = $pdo->prepare("
            SELECT e.*, g.nom_groupe, s.nom_section, sp.nom_specialite, n.nom_niveau
            FROM etudiant e
            LEFT JOIN `groupe` g ON e.id_groupe = g.id_groupe
            LEFT JOIN section s ON g.id_section = s.id_section
            LEFT JOIN specialite sp ON s.id_specialite = sp.id_specialite
            LEFT JOIN niveau n ON sp.id_niveau = n.id_niveau
            WHERE e.id_etudiant = ?
        ");
        $stmt->execute([$selected_etudiant]);
        $etudiant = $stmt->fetch();
    }
}

// Récupération des notes si un étudiant est sélectionné
if($etudiant) {
    $stmt = $pdo->prepare("
        SELECT m.nom_module, m.coefficient, n.note_cc, n.note_examen, n.note_ratrapage,
        ROUND((COALESCE(n.note_cc, 0) + COALESCE(n.note_examen, 0))/2, 2) as moyenne_module
        FROM note n 
        JOIN module m ON n.id_module = m.id_module
        WHERE n.id_etudiant = ? AND n.session = 'Normale'
        ORDER BY m.nom_module
    ");
    $stmt->execute([$etudiant['id_etudiant']]);
    $notes = $stmt->fetchAll();
    
    foreach($notes as $n) {
        if($n['moyenne_module'] !== null) {
            $total_coeff += $n['coefficient'];
            $total_moyenne += $n['moyenne_module'] * $n['coefficient'];
        }
    }
}

$moyenne_generale = $total_coeff > 0 ? round($total_moyenne / $total_coeff, 2) : 0;

if($moyenne_generale >= 16) $mention = "Très bien";
elseif($moyenne_generale >= 14) $mention = "Bien";
elseif($moyenne_generale >= 12) $mention = "Assez bien";
elseif($moyenne_generale >= 10) $mention = "Passable";
elseif($moyenne_generale > 0) $mention = "Insuffisant";
else $mention = "Non disponible";

include '../includes/header.php';
?>

<div style="max-width: 1000px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-file-alt"></i> Relevé de notes</h1>
                <p>
                    <?php if(hasRole('etudiant')): ?>
                    Consultez votre relevé de notes personnel
                    <?php else: ?>
                    Consulter le relevé de notes d'un étudiant
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Barre de recherche par matricule (visible uniquement pour admin et enseignant) -->
        <?php if(!hasRole('etudiant')): ?>
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search_matricule" placeholder="🔍 Rechercher un étudiant par MATRICULE..." value="<?php echo isset($search_matricule) ? htmlspecialchars($search_matricule) : ''; ?>">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> Rechercher</button>
                <?php if(isset($search_matricule) && $search_matricule != ''): ?>
                <a href="releve.php" class="btn-clear"><i class="fas fa-times"></i> Effacer</a>
                <?php endif; ?>
            </form>
            <?php if($error): ?>
            <p style="color: #dc3545; font-size: 13px; margin-top: 10px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if($etudiant): ?>
        <!-- En-tête du relevé -->
        <div style="background: linear-gradient(135deg, #4361ee, #3b82f6); border-radius: 20px; padding: 25px; margin-bottom: 25px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <i class="fas fa-university" style="font-size: 40px;"></i>
                    <h2 style="margin-top: 10px;">USTHB</h2>
                    <p>Faculté d'Informatique</p>
                </div>
                <div style="text-align: center;">
                    <h3>RELEVÉ DE NOTES</h3>
                    <p>Année universitaire 2025/2026</p>
                </div>
                <div>
                    <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y'); ?></p>
                </div>
            </div>
        </div>

        <!-- Informations étudiant complètes -->
        <div style="background: #f8f9fa; border-radius: 16px; padding: 20px; margin-bottom: 20px;">
            <h4 style="margin-bottom: 15px; color: #4361ee;"><i class="fas fa-id-card"></i> Informations étudiant</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div><span style="color: #6c757d;">Matricule:</span><br><strong><?php echo htmlspecialchars($etudiant['matricule']); ?></strong></div>
                <div><span style="color: #6c757d;">Nom:</span><br><strong><?php echo htmlspecialchars($etudiant['nom']); ?></strong></div>
                <div><span style="color: #6c757d;">Prénom:</span><br><strong><?php echo htmlspecialchars($etudiant['prenom']); ?></strong></div>
                <div><span style="color: #6c757d;">Date naissance:</span><br><strong><?php echo date('d/m/Y', strtotime($etudiant['date_naissance'])); ?></strong></div>
                <div><span style="color: #6c757d;">Niveau:</span><br><strong><?php echo htmlspecialchars($etudiant['nom_niveau'] ?? '-'); ?></strong></div>
                <div><span style="color: #6c757d;">Spécialité:</span><br><strong><?php echo htmlspecialchars($etudiant['nom_specialite'] ?? '-'); ?></strong></div>
                <div><span style="color: #6c757d;">Section:</span><br><strong><?php echo htmlspecialchars($etudiant['nom_section'] ?? '-'); ?></strong></div>
                <div><span style="color: #6c757d;">Groupe:</span><br><strong><?php echo htmlspecialchars($etudiant['nom_groupe'] ?? '-'); ?></strong></div>
            </div>
        </div>

        <!-- Tableau des notes -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th>Module</th>
                        <th>Coefficient</th>
                        <th>CC (/20)</th>
                        <th>Examen (/20)</th>
                        <th>Rattrapage</th>
                        <th>Moyenne</th>
                        <th>Validation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($notes) > 0): ?>
                        <?php foreach($notes as $n): 
                            $moy_module = ($n['note_cc'] + $n['note_examen']) / 2;
                            $moy_finale = $moy_module >= 10 ? $moy_module : ($n['note_ratrapage'] ?: $moy_module);
                            $valide = $moy_finale >= 10;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($n['nom_module']); ?></strong></td>
                            <td align="center"><?php echo $n['coefficient']; ?></td>
                            <td align="center"><?php echo $n['note_cc'] ? number_format($n['note_cc'], 2) : '-'; ?></td>
                            <td align="center"><?php echo $n['note_examen'] ? number_format($n['note_examen'], 2) : '-'; ?></td>
                            <td align="center"><?php echo $n['note_ratrapage'] ? number_format($n['note_ratrapage'], 2) : '-'; ?></td>
                            <td align="center">
                                <strong style="color: <?php echo $valide ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo number_format($moy_finale, 2); ?>/20
                                </strong>
                            </td>
                            <td align="center"><?php echo $valide ? '✅ Validé' : '❌ Non validé'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" align="center" style="padding: 40px;">
                                <i class="fas fa-info-circle"></i> Aucune note enregistrée pour cet étudiant
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Moyenne générale et mention -->
        <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 15px; background: #e8f0fe; border-radius: 16px; padding: 20px; margin-top: 20px;">
            <div>
                <span style="color: #6c757d;">Moyenne générale:</span><br>
                <strong style="font-size: 20px; color: <?php echo $moyenne_generale >= 10 ? '#28a745' : '#dc3545'; ?>;">
                    <?php echo $moyenne_generale; ?>/20
                </strong>
            </div>
            <div>
                <span style="color: #6c757d;">Mention:</span><br>
                <strong><?php echo $mention; ?></strong>
            </div>
            <div>
                <span style="color: #6c757d;">Résultat:</span><br>
                <strong style="color: <?php echo $moyenne_generale >= 10 ? '#28a745' : '#dc3545'; ?>;">
                    <?php echo $moyenne_generale >= 10 ? 'ADMIS' : 'NON ADMIS'; ?>
                </strong>
            </div>
        </div>

        <!-- Bouton impression -->
        <div style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" class="action-btn" style="background: linear-gradient(135deg, #28a745, #20c997); padding: 12px 25px; border: none; border-radius: 10px; color: white; cursor: pointer; transition: all 0.3s;">
                <i class="fas fa-print"></i> Imprimer le relevé
            </button>
        </div>
        
        <?php elseif(!hasRole('etudiant') && isset($search_matricule) && $search_matricule != '' && !$etudiant): ?>
        <div class="alert alert-error" style="text-align: center;">
            <i class="fas fa-search"></i> Aucun étudiant trouvé avec le matricule "<strong><?php echo htmlspecialchars($search_matricule); ?></strong>"
        </div>
        <?php elseif(hasRole('etudiant') && !$etudiant): ?>
        <div class="alert alert-error" style="text-align: center;">
            <i class="fas fa-exclamation-circle"></i> Impossible de charger votre relevé. Veuillez contacter l'administrateur.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
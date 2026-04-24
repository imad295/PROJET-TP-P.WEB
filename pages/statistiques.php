<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

// Statistiques générales
$total_etudiants = $pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();
$total_enseignants = $pdo->query("SELECT COUNT(*) FROM enseignant")->fetchColumn();
$total_modules = $pdo->query("SELECT COUNT(*) FROM module")->fetchColumn();

// Moyennes par module
$stmt = $pdo->query("
    SELECT 
        m.nom_module, 
        COUNT(n.id_note) as nb_notes,
        ROUND(AVG((COALESCE(n.note_cc, 0) + COALESCE(n.note_examen, 0))/2), 2) as moyenne_module
    FROM module m
    LEFT JOIN note n ON m.id_module = n.id_module
    GROUP BY m.id_module
    ORDER BY moyenne_module DESC
");
$stats_modules = $stmt->fetchAll();

// Classement des étudiants
$stmt = $pdo->query("
    SELECT 
        e.id_etudiant, 
        e.matricule, 
        e.nom, 
        e.prenom,
        SUM(m.coefficient) as total_coeff,
        SUM((COALESCE(n.note_cc, 0) + COALESCE(n.note_examen, 0))/2 * m.coefficient) as total_moyenne
    FROM etudiant e
    LEFT JOIN note n ON e.id_etudiant = n.id_etudiant AND n.session = 'Normale'
    LEFT JOIN module m ON n.id_module = m.id_module
    GROUP BY e.id_etudiant
");
$classement = [];
foreach($stmt->fetchAll() as $e) {
    $moyenne = ($e['total_coeff'] > 0) ? round($e['total_moyenne'] / $e['total_coeff'], 2) : 0;
    $classement[] = [
        'matricule' => $e['matricule'],
        'nom' => $e['nom'],
        'prenom' => $e['prenom'],
        'moyenne' => $moyenne
    ];
}
usort($classement, fn($a, $b) => $b['moyenne'] <=> $a['moyenne']);

include '../includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-chart-line"></i> Statistiques</h1>
                <p>Analyse des performances académiques</p>
            </div>
        </div>

        <!-- Cartes statistiques -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="stat-card-animated">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #4361ee, #3b82f6); border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-users" style="font-size: 28px; color: white;"></i>
                </div>
                <div style="font-size: 36px; font-weight: 700; color: #1a1a2e;"><?php echo $total_etudiants; ?></div>
                <div style="color: #6c757d; font-size: 14px; margin-top: 5px;">Étudiants</div>
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

        <!-- Moyennes par module -->
        <div style="background: #f8f9fa; border-radius: 16px; padding: 20px; margin-bottom: 25px;">
            <h3 style="margin-bottom: 15px; font-size: 18px;"><i class="fas fa-chart-simple"></i> Moyennes par module</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Nombre de notes</th>
                            <th>Moyenne</th>
                            <th>Appréciation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($stats_modules as $s): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($s['nom_module']); ?></strong></span>
                            <td align="center"><?php echo $s['nb_notes']; ?></span>
                            <td align="center">
                                <?php if($s['moyenne_module'] > 0): ?>
                                <strong style="color: <?php echo $s['moyenne_module'] >= 10 ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo $s['moyenne_module']; ?>/20
                                </strong>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td align="center">
                                <?php 
                                if($s['moyenne_module'] >= 14) echo '<span style="color:#28a745;">Excellent</span>';
                                elseif($s['moyenne_module'] >= 12) echo '<span style="color:#20c997;">Très bien</span>';
                                elseif($s['moyenne_module'] >= 10) echo '<span style="color:#4361ee;">Bien</span>';
                                elseif($s['moyenne_module'] > 0) echo '<span style="color:#dc3545;">Insuffisant</span>';
                                else echo '-';
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Classement des étudiants -->
        <div style="background: #f8f9fa; border-radius: 16px; padding: 20px;">
            <h3 style="margin-bottom: 15px; font-size: 18px;"><i class="fas fa-trophy"></i> Classement des étudiants</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Matricule</th>
                            <th>Nom et prénom</th>
                            <th>Moyenne</th>
                            <th>Mention</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rang = 1;
                        foreach($classement as $c): 
                        ?>
                        <tr>
                            <td align="center">
                                <?php 
                                if($rang == 1) echo '🥇 ' . $rang;
                                elseif($rang == 2) echo '🥈 ' . $rang;
                                elseif($rang == 3) echo '🥉 ' . $rang;
                                else echo $rang;
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($c['matricule']); ?></td>
                            <td><?php echo htmlspecialchars($c['prenom'] . ' ' . $c['nom']); ?></td>
                            <td align="center">
                                <strong style="color: <?php echo $c['moyenne'] >= 10 ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo $c['moyenne'] > 0 ? $c['moyenne'] . '/20' : '-'; ?>
                                </strong>
                            </td>
                            <td align="center">
                                <?php 
                                if($c['moyenne'] >= 16) echo '🏅 Très bien';
                                elseif($c['moyenne'] >= 14) echo '📘 Bien';
                                elseif($c['moyenne'] >= 12) echo '📗 Assez bien';
                                elseif($c['moyenne'] >= 10) echo '📙 Passable';
                                elseif($c['moyenne'] > 0) echo '⚠️ Insuffisant';
                                else echo '-';
                                ?>
                            </td>
                        </tr>
                        <?php 
                        $rang++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
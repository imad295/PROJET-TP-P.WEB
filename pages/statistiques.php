<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

// Statistiques générales
$stmt = $pdo->query("SELECT COUNT(*) as total FROM etudiant");
$total_etudiants = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM enseignant");
$total_enseignants = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM module");
$total_modules = $stmt->fetch()['total'];

// Répartition par module des notes
$stmt = $pdo->query("
    SELECT 
        m.nom_module, 
        COUNT(n.id_note) as nb_notes,
        AVG((COALESCE(n.note_cc, 0) + COALESCE(n.note_examen, 0))/2) as moyenne_module
    FROM module m
    LEFT JOIN note n ON m.id_module = n.id_module
    GROUP BY m.id_module
    ORDER BY moyenne_module DESC
");
$stats_modules = $stmt->fetchAll();

// Liste des étudiants avec moyenne générale - Version corrigée
$stmt = $pdo->query("
    SELECT 
        e.id_etudiant, 
        e.matricule, 
        e.nom, 
        e.prenom,
        SUM(m.coefficient) as total_coeff,
        SUM((COALESCE(n.note_cc, 0) + COALESCE(n.note_examen, 0))/2 * m.coefficient) as total_moyenne_ponderee
    FROM etudiant e
    LEFT JOIN note n ON e.id_etudiant = n.id_etudiant AND n.session = 'Normale'
    LEFT JOIN module m ON n.id_module = m.id_module
    GROUP BY e.id_etudiant
");
$classement_temp = $stmt->fetchAll();

// Calculer la moyenne pour chaque étudiant
$classement = [];
foreach($classement_temp as $etudiant) {
    $moyenne = ($etudiant['total_coeff'] > 0) ? $etudiant['total_moyenne_ponderee'] / $etudiant['total_coeff'] : 0;
    $classement[] = [
        'id_etudiant' => $etudiant['id_etudiant'],
        'matricule' => $etudiant['matricule'],
        'nom' => $etudiant['nom'],
        'prenom' => $etudiant['prenom'],
        'moyenne' => $moyenne,
        'total_coeff' => $etudiant['total_coeff']
    ];
}

// Trier le classement par moyenne décroissante
usort($classement, function($a, $b) {
    return $b['moyenne'] <=> $a['moyenne'];
});

include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>📊 Statistiques</h1>
    </div>
    
    <!-- Statistiques globales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👨‍🎓</div>
            <div class="stat-info">
                <h3><?php echo $total_etudiants; ?></h3>
                <p>Étudiants inscrits</p>
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
    
    <!-- Moyennes par module -->
    <div class="section">
        <h2>📈 Moyennes par module</h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr bgcolor="#f0f0f0">
                        <th>Module</th>
                        <th>Nombre de notes</th>
                        <th>Moyenne générale</th>
                        <th>Appréciation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($stats_modules) > 0): ?>
                        <?php foreach($stats_modules as $stat): ?>
                        <tr>
                            <td><b><?php echo htmlspecialchars($stat['nom_module']); ?></b></td>
                            <td align="center"><?php echo $stat['nb_notes']; ?></td>
                            <td align="center">
                                <?php 
                                if($stat['moyenne_module'] && $stat['moyenne_module'] > 0) {
                                    $moy = number_format($stat['moyenne_module'], 2);
                                    echo $moy . '/20';
                                    if($moy >= 14) echo " 👍";
                                    elseif($moy >= 10) echo " 👌";
                                    else echo " 👎";
                                } else {
                                    echo "N/A";
                                }
                                ?>
                            </td>
                            <td align="center">
                                <?php 
                                if($stat['moyenne_module'] >= 14) echo "Excellent";
                                elseif($stat['moyenne_module'] >= 12) echo "Très bien";
                                elseif($stat['moyenne_module'] >= 10) echo "Bien";
                                elseif($stat['moyenne_module'] > 0) echo "Insuffisant";
                                else echo "-";
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" align="center">Aucune donnée disponible</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Classement des étudiants -->
    <div class="section" style="margin-top: 30px;">
        <h2>🏆 Classement des étudiants</h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr bgcolor="#f0f0f0">
                        <th>#</th>
                        <th>Matricule</th>
                        <th>Nom et prénom</th>
                        <th>Moyenne générale</th>
                        <th>Mention</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rang = 1;
                    $affiche = false;
                    foreach($classement as $etudiant): 
                        if($etudiant['total_coeff'] > 0) {
                            $affiche = true;
                            $moyenne = $etudiant['moyenne'];
                        } else {
                            $moyenne = 0;
                        }
                    ?>
                    <tr>
                        <td align="center">
                            <?php 
                            if($rang == 1) echo "🥇 $rang";
                            elseif($rang == 2) echo "🥈 $rang";
                            elseif($rang == 3) echo "🥉 $rang";
                            else echo $rang;
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($etudiant['matricule']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?></td>
                        <td align="center">
                            <?php 
                            if($moyenne > 0) {
                                echo number_format($moyenne, 2) . '/20';
                                if($moyenne >= 10) {
                                    echo " ✅";
                                } else {
                                    echo " ❌";
                                }
                            } else {
                                echo "-";
                            }
                            ?>
                        </td>
                        <td align="center">
                            <?php 
                            if($moyenne >= 16) echo "<font color='gold'>🏅 Très bien</font>";
                            elseif($moyenne >= 14) echo "<font color='blue'>📘 Bien</font>";
                            elseif($moyenne >= 12) echo "<font color='green'>📗 Assez bien</font>";
                            elseif($moyenne >= 10) echo "<font color='orange'>📙 Passable</font>";
                            elseif($moyenne > 0) echo "<font color='red'>⚠️ Insuffisant</font>";
                            else echo "-";
                            ?>
                        </td>
                        <td align="center">
                            <?php 
                            if($moyenne >= 10) {
                                echo "<font color='green'><b>ADMIS</b></font>";
                            } elseif($moyenne > 0) {
                                echo "<font color='red'><b>NON ADMIS</b></font>";
                            } else {
                                echo "-";
                            }
                            ?>
                        </td>
                    </tr>
                    <?php 
                    $rang++;
                    endforeach; 
                    ?>
                    <?php if(!$affiche): ?>
                    <tr>
                        <td colspan="6" align="center">Aucune note enregistrée</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    border: 1px solid #e0e0e0;
}

.stat-icon {
    font-size: 2.5rem;
}

.stat-info h3 {
    font-size: 2rem;
    color: #1e3c72;
    margin: 0;
}

.stat-info p {
    color: #666;
    margin: 5px 0 0;
}

.section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.section h2 {
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.data-table th {
    background: #f8f9fa;
    font-weight: 600;
}

.data-table tr:hover {
    background: #f9f9f9;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .data-table th,
    .data-table td {
        padding: 8px;
        font-size: 12px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
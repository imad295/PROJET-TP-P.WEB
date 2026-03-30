<?php
require_once '../includes/config.php';

if(!isLoggedIn()) {
    redirect('../index.php');
}

// Récupérer l'étudiant
if(hasRole('etudiant')) {
    $id_etudiant = $_SESSION['user_id'];
} elseif(hasRole('enseignant') && isset($_GET['etudiant'])) {
    $id_etudiant = $_GET['etudiant'];
} elseif(hasRole('admin') && isset($_GET['etudiant'])) {
    $id_etudiant = $_GET['etudiant'];
} else {
    // Pour admin/enseignant, afficher un formulaire de sélection
    $id_etudiant = null;
}

// Récupération des étudiants pour la sélection (admin/enseignant)
if(!hasRole('etudiant')) {
    $stmt = $pdo->query("SELECT * FROM etudiant ORDER BY nom");
    $etudiants = $stmt->fetchAll();
}

// Récupération des notes si un étudiant est sélectionné
$etudiant = null;
$notes = [];
$total_coeff = 0;
$total_moyenne_ponderee = 0;

if($id_etudiant) {
    $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_etudiant = ?");
    $stmt->execute([$id_etudiant]);
    $etudiant = $stmt->fetch();
    
    if($etudiant) {
        $stmt = $pdo->prepare("
            SELECT m.nom_module, m.coefficient, 
                   n.note_cc, n.note_examen, n.note_ratrapage, n.session,
                   ((n.note_cc + n.note_examen)/2) as moyenne_module
            FROM note n
            JOIN module m ON n.id_module = m.id_module
            WHERE n.id_etudiant = ? AND n.session = 'Normale'
            ORDER BY m.nom_module
        ");
        $stmt->execute([$id_etudiant]);
        $notes = $stmt->fetchAll();
        
        foreach($notes as $note) {
            if($note['moyenne_module'] !== null) {
                $coeff = $note['coefficient'];
                $total_coeff += $coeff;
                $total_moyenne_ponderee += $note['moyenne_module'] * $coeff;
            }
        }
    }
}

$moyenne_generale = ($total_coeff > 0) ? $total_moyenne_ponderee / $total_coeff : 0;

// Déterminer la mention
$mention = '';
if($moyenne_generale >= 16) $mention = 'Très bien';
elseif($moyenne_generale >= 14) $mention = 'Bien';
elseif($moyenne_generale >= 12) $mention = 'Assez bien';
elseif($moyenne_generale >= 10) $mention = 'Passable';
elseif($moyenne_generale > 0) $mention = 'Insuffisant';

include '../includes/header.php';

if(hasRole('admin')) {
    include '../includes/sidebar_admin.php';
} elseif(hasRole('enseignant')) {
    include '../includes/sidebar_enseignant.php';
}
?>

<main class="main-content">
    <div class="page-header">
        <h1>Relevé de notes</h1>
    </div>
    
    <?php if(!hasRole('etudiant')): ?>
    <!-- Formulaire de sélection pour admin/enseignant -->
    <div class="form-container">
        <form method="GET" action="">
            <div class="form-group">
                <label>Sélectionner un étudiant</label>
                <select name="etudiant" required onchange="this.form.submit()">
                    <option value="">-- Choisir un étudiant --</option>
                    <?php foreach($etudiants as $e): ?>
                    <option value="<?php echo $e['id_etudiant']; ?>" <?php echo $id_etudiant == $e['id_etudiant'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($e['matricule'] . ' - ' . $e['prenom'] . ' ' . $e['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <?php if($etudiant): ?>
    <!-- Relevé de notes -->
    <div class="releve-container">
        <div class="releve-header" style="text-align: center; margin-bottom: 30px;">
            <h2>Université des Sciences et de la Technologie Houari Boumediene</h2>
            <h3>Faculté d'Informatique</h3>
            <h4>RELEVÉ DE NOTES</h4>
            <hr>
        </div>
        
        <div class="etudiant-info">
            <table width="100%" border="0" cellpadding="5">
                <tr>
                    <td width="30%"><b>Matricule :</b></td>
                    <td><?php echo htmlspecialchars($etudiant['matricule']); ?></td>
                    <td width="30%"><b>Date de naissance :</b></td>
                    <td><?php echo date('d/m/Y', strtotime($etudiant['date_naissance'])); ?></td>
                </tr>
                <tr>
                    <td><b>Nom :</b></td>
                    <td><?php echo htmlspecialchars($etudiant['nom']); ?></td>
                    <td><b>Email :</b></td>
                    <td><?php echo htmlspecialchars($etudiant['email']); ?></td>
                </tr>
                <tr>
                    <td><b>Prénom :</b></td>
                    <td><?php echo htmlspecialchars($etudiant['prenom']); ?></td>
                    <td><b>Année universitaire :</b></td>
                    <td>2025/2026</td>
                </tr>
            </table>
        </div>
        
        <br>
        
        <div class="notes-table">
            <table width="100%" border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;">
                <thead>
                    <tr bgcolor="#f0f0f0">
                        <th>Module</th>
                        <th>Coefficient</th>
                        <th>Note CC</th>
                        <th>Note Examen</th>
                        <th>Note Rattrapage</th>
                        <th>Moyenne</th>
                        <th>Validation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($notes) > 0): ?>
                        <?php foreach($notes as $note): ?>
                        <?php 
                        $moyenne_module = $note['moyenne_module'];
                        $moyenne_finale = ($moyenne_module >= 10) ? $moyenne_module : ($note['note_ratrapage'] ?: $moyenne_module);
                        $valide = $moyenne_finale >= 10;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($note['nom_module']); ?></td>
                            <td align="center"><?php echo $note['coefficient']; ?></td>
                            <td align="center"><?php echo $note['note_cc'] ? number_format($note['note_cc'], 2) : '-'; ?></td>
                            <td align="center"><?php echo $note['note_examen'] ? number_format($note['note_examen'], 2) : '-'; ?></td>
                            <td align="center"><?php echo $note['note_ratrapage'] ? number_format($note['note_ratrapage'], 2) : '-'; ?></td>
                            <td align="center">
                                <b><?php echo number_format($moyenne_finale, 2); ?>/20</b>
                            </td>
                            <td align="center">
                                <?php if($valide): ?>
                                    <font color="green">✅ Validé</font>
                                <?php else: ?>
                                    <font color="red">❌ Non validé</font>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" align="center">Aucune note enregistrée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <br>
        
        <div class="moyenne-generale" style="background: #e3f2fd; padding: 15px; border-radius: 5px;">
            <table width="100%" border="0">
                <tr>
                    <td width="70%">
                        <b>Moyenne générale :</b>
                    </td>
                    <td>
                        <font size="4">
                            <?php echo number_format($moyenne_generale, 2); ?>/20
                        </font>
                    </td>
                </tr>
                <tr>
                    <td><b>Mention :</b></td>
                    <td>
                        <?php if($mention): ?>
                            <font size="4">
                                <?php 
                                if($mention == 'Très bien') echo "🏅 " . $mention;
                                elseif($mention == 'Bien') echo "📘 " . $mention;
                                elseif($mention == 'Assez bien') echo "📗 " . $mention;
                                elseif($mention == 'Passable') echo "📙 " . $mention;
                                else echo "⚠️ " . $mention;
                                ?>
                            </font>
                        <?php else: ?>
                            Non disponible
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><b>Résultat :</b></td>
                    <td>
                        <?php if($moyenne_generale >= 10): ?>
                            <font color="green" size="4"><b>ADMIS</b></font>
                        <?php elseif($moyenne_generale > 0): ?>
                            <font color="red" size="4"><b>NON ADMIS</b></font>
                        <?php else: ?>
                            Non disponible
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="footer-releve" style="margin-top: 30px; text-align: center; font-size: 12px;">
            <hr>
            <p>Fait à Alger, le <?php echo date('d/m/Y'); ?></p>
            <p>Cachet et signature du chef de département</p>
        </div>
    </div>
    
    <div class="print-button" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="background-color: #1e3c72; color: white; padding: 10px 20px; border: none; cursor: pointer;">🖨️ Imprimer le relevé</button>
    </div>
    
    <?php elseif($id_etudiant): ?>
        <div class="alert alert-error">Étudiant non trouvé</div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
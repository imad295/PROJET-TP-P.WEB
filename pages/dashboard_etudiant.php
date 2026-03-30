<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('etudiant')) {
    redirect('../index.php');
}

// Récupérer les informations de l'étudiant
$stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_etudiant = ?");
$stmt->execute([$_SESSION['user_id']]);
$etudiant = $stmt->fetch();

// Récupérer les notes avec les informations des modules
$stmt = $pdo->prepare("
    SELECT 
        m.nom_module, 
        m.coefficient,
        n.note_cc, 
        n.note_examen, 
        n.note_ratrapage,
        n.session
    FROM note n
    INNER JOIN module m ON n.id_module = m.id_module
    WHERE n.id_etudiant = ? AND n.session = 'Normale'
    ORDER BY m.nom_module
");
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll();

// Calcul de la moyenne générale
$total_coeff = 0;
$total_moyenne_ponderee = 0;
$notes_valides = [];

foreach($notes as $note) {
    // Vérifier que les notes existent
    $note_cc = isset($note['note_cc']) ? floatval($note['note_cc']) : null;
    $note_examen = isset($note['note_examen']) ? floatval($note['note_examen']) : null;
    $note_ratrapage = isset($note['note_ratrapage']) ? floatval($note['note_ratrapage']) : null;
    
    // Calculer la moyenne du module
    $moyenne_module = null;
    
    if($note_cc !== null && $note_examen !== null) {
        $moyenne_module = ($note_cc + $note_examen) / 2;
        
        // Si la moyenne est < 10 et qu'il y a une note de rattrapage, utiliser celle-ci
        if($moyenne_module < 10 && $note_ratrapage !== null) {
            $moyenne_module = $note_ratrapage;
        }
        
        $notes_valides[] = [
            'nom_module' => $note['nom_module'],
            'coefficient' => $note['coefficient'],
            'note_cc' => $note_cc,
            'note_examen' => $note_examen,
            'note_ratrapage' => $note_ratrapage,
            'moyenne_module' => $moyenne_module
        ];
        
        $total_coeff += $note['coefficient'];
        $total_moyenne_ponderee += $moyenne_module * $note['coefficient'];
    }
}

$moyenne_generale = ($total_coeff > 0) ? $total_moyenne_ponderee / $total_coeff : 0;

// Déterminer la mention
if($moyenne_generale >= 16) $mention = "Très bien";
elseif($moyenne_generale >= 14) $mention = "Bien";
elseif($moyenne_generale >= 12) $mention = "Assez bien";
elseif($moyenne_generale >= 10) $mention = "Passable";
elseif($moyenne_generale > 0) $mention = "Insuffisant";
else $mention = "Non disponible";

include '../includes/header.php';
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr valign="top">
        <!-- Sidebar -->
        <td width="250" bgcolor="#f0f0f0" style="padding: 20px;">
            <h3>👨‍🎓 Menu Étudiant</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 10px 0;"><a href="dashboard_etudiant.php" style="text-decoration: none; color: #333; font-weight: bold;">📊 Tableau de bord</a></li>
                <li style="margin: 10px 0;"><a href="releve.php" style="text-decoration: none; color: #333;">📄 Mon relevé de notes</a></li>
            </ul>
            <hr>
            <p><a href="logout.php" style="color: #f44336; text-decoration: none;">🔓 Déconnexion</a></p>
         </td>
        
        <!-- Main Content -->
        <td style="padding: 20px;">
            <h2>📊 Tableau de bord Étudiant</h2>
            <p>Bienvenue, <b><?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?></b></p>
            <p>Matricule: <b><?php echo htmlspecialchars($etudiant['matricule']); ?></b></p>
            
            <?php displayMessage(); ?>
            
            <!-- Moyenne générale -->
            <table width="100%" bgcolor="#e3f2fd" style="border-radius: 5px; margin-bottom: 25px;">
                <tr>
                    <td align="center" style="padding: 25px;">
                        <font size="4"><b>📈 Moyenne Générale</b></font><br>
                        <font size="6">
                            <?php 
                            if($moyenne_generale > 0) {
                                echo number_format($moyenne_generale, 2) . '/20';
                                if($moyenne_generale >= 10) {
                                    echo " ✅";
                                } else {
                                    echo " ❌";
                                }
                            } else {
                                echo "Non disponible";
                            }
                            ?>
                        </font>
                        <br>
                        <?php if($moyenne_generale >= 10 && $moyenne_generale > 0): ?>
                            <font color="green" size="3"><b>Admis - <?php echo $mention; ?></b></font>
                        <?php elseif($moyenne_generale > 0): ?>
                            <font color="red" size="3"><b>Non admis - <?php echo $mention; ?></b></font>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <!-- Mes dernières notes -->
            <h3>📝 Mes notes</h3>
            <table width="100%" border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;">
                <thead>
                    <tr bgcolor="#f0f0f0">
                        <th>Module</th>
                        <th>Coefficient</th>
                        <th>CC (/20)</th>
                        <th>Examen (/20)</th>
                        <th>Rattrapage</th>
                        <th>Moyenne</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($notes_valides) > 0): ?>
                        <?php foreach($notes_valides as $note): ?>
                            <?php 
                            $moyenne = $note['moyenne_module'];
                            $valide = $moyenne >= 10;
                            ?>
                            <tr>
                                <td><b><?php echo htmlspecialchars($note['nom_module']); ?></b></td>
                                <td align="center"><?php echo $note['coefficient']; ?></td>
                                <td align="center">
                                    <?php 
                                    if($note['note_cc'] !== null) {
                                        echo number_format($note['note_cc'], 2);
                                    } else {
                                        echo "<font color='gray'>-</font>";
                                    }
                                    ?>
                                </td>
                                <td align="center">
                                    <?php 
                                    if($note['note_examen'] !== null) {
                                        echo number_format($note['note_examen'], 2);
                                    } else {
                                        echo "<font color='gray'>-</font>";
                                    }
                                    ?>
                                </td>
                                <td align="center">
                                    <?php 
                                    if($note['note_ratrapage'] !== null) {
                                        echo number_format($note['note_ratrapage'], 2);
                                    } else {
                                        echo "<font color='gray'>-</font>";
                                    }
                                    ?>
                                </td>
                                <td align="center">
                                    <b>
                                        <?php 
                                        if($moyenne !== null) {
                                            echo number_format($moyenne, 2);
                                            if($valide) {
                                                echo " ✅";
                                            } else {
                                                echo " ❌";
                                            }
                                        } else {
                                            echo "-";
                                        }
                                        ?>
                                    </b>
                                </td>
                                <td align="center">
                                    <?php 
                                    if($moyenne !== null) {
                                        if($valide) {
                                            echo "<font color='green'>Validé</font>";
                                        } else {
                                            echo "<font color='red'>Non validé</font>";
                                        }
                                    } else {
                                        echo "-";
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" align="center">
                                <font color="gray">Aucune note enregistrée pour le moment</font>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <br>
            <div align="center">
                <a href="releve.php" style="background-color: #1e3c72; color: white; padding: 10px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    📄 Voir mon relevé complet
                </a>
            </div>
        </td>
    </tr>
</table>

<?php include '../includes/footer.php'; ?>
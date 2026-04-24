<?php
require_once '../includes/config.php';

if(!isLoggedIn()) {
    redirect('../index.php');
}
if(hasRole('etudiant')) {
    redirect('dashboard_etudiant.php');
}

$error = '';
$success = '';

// Récupération des étudiants
$search_etudiant = isset($_GET['search_etudiant']) ? trim($_GET['search_etudiant']) : '';
if($search_etudiant != '') {
    $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE matricule LIKE ? ORDER BY nom");
    $stmt->execute(["%$search_etudiant%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM etudiant ORDER BY nom");
}
$etudiants = $stmt->fetchAll();

// Récupération des valeurs sélectionnées
$selected_etudiant = isset($_GET['etudiant']) ? $_GET['etudiant'] : null;

// Traitement du formulaire d'ajout/modification des notes
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_notes'])) {
    $id_etudiant = $_POST['id_etudiant'];
    $id_module = $_POST['id_module'];
    $note_cc = $_POST['note_cc'] != '' ? $_POST['note_cc'] : null;
    $note_examen = $_POST['note_examen'] != '' ? $_POST['note_examen'] : null;
    $note_ratrapage = $_POST['note_ratrapage'] != '' ? $_POST['note_ratrapage'] : null;
    $session = $_POST['session'];
    
    $stmt = $pdo->prepare("SELECT * FROM note WHERE id_etudiant = ? AND id_module = ? AND session = ?");
    $stmt->execute([$id_etudiant, $id_module, $session]);
    $existing = $stmt->fetch();
    
    if($existing) {
        $stmt = $pdo->prepare("UPDATE note SET note_cc=?, note_examen=?, note_ratrapage=? WHERE id_note=?");
        $stmt->execute([$note_cc, $note_examen, $note_ratrapage, $existing['id_note']]);
        $success = "✅ Notes mises à jour avec succès";
    } else {
        $stmt = $pdo->prepare("INSERT INTO note (id_etudiant, id_module, note_cc, note_examen, note_ratrapage, session) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$id_etudiant, $id_module, $note_cc, $note_examen, $note_ratrapage, $session]);
        $success = "✅ Notes ajoutées avec succès";
    }
    
    header("Location: notes.php?etudiant=$id_etudiant");
    exit();
}

// Récupérer les modules avec notes pour l'étudiant sélectionné
$modules_avec_notes = [];
if($selected_etudiant) {
    // Pour enseignant : ne voir que ses modules
    if(hasRole('enseignant')) {
        $stmt = $pdo->prepare("
            SELECT m.id_module, m.nom_module, m.coefficient, n.note_cc, n.note_examen, n.note_ratrapage,
            (COALESCE(n.note_cc,0) + COALESCE(n.note_examen,0))/2 as moyenne
            FROM note n
            JOIN module m ON n.id_module = m.id_module
            WHERE n.id_etudiant = ? AND n.session = 'Normale' AND m.id_enseignant = ?
            ORDER BY m.nom_module
        ");
        $stmt->execute([$selected_etudiant, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("
            SELECT m.id_module, m.nom_module, m.coefficient, n.note_cc, n.note_examen, n.note_ratrapage,
            (COALESCE(n.note_cc,0) + COALESCE(n.note_examen,0))/2 as moyenne
            FROM note n
            JOIN module m ON n.id_module = m.id_module
            WHERE n.id_etudiant = ? AND n.session = 'Normale'
            ORDER BY m.nom_module
        ");
        $stmt->execute([$selected_etudiant]);
    }
    $modules_avec_notes = $stmt->fetchAll();
}

// Récupérer le module sélectionné pour modification
$selected_module = isset($_GET['module']) ? $_GET['module'] : null;
$notes_existantes = null;
if($selected_etudiant && $selected_module) {
    $stmt = $pdo->prepare("SELECT * FROM note WHERE id_etudiant = ? AND id_module = ? AND session = 'Normale'");
    $stmt->execute([$selected_etudiant, $selected_module]);
    $notes_existantes = $stmt->fetch();
}

include '../includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-pen-fancy"></i> Gestion des Notes</h1>
                <p><?php echo hasRole('enseignant') ? 'Gérer les notes de vos modules' : 'Saisir et modifier les notes des étudiants'; ?></p>
            </div>
        </div>

        <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Barre de recherche étudiant par MATRICULE -->
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search_etudiant" placeholder="🔍 Rechercher un étudiant par MATRICULE..." value="<?php echo htmlspecialchars($search_etudiant); ?>">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> Rechercher</button>
                <?php if($search_etudiant != ''): ?>
                <a href="notes.php" class="btn-clear"><i class="fas fa-times"></i> Effacer</a>
                <?php endif; ?>
            </form>
            <?php if($search_etudiant != '' && count($etudiants) == 0): ?>
            <p style="color: #dc3545; font-size: 13px; margin-top: 10px;">
                <i class="fas fa-exclamation-circle"></i> Aucun étudiant trouvé avec le matricule "<strong><?php echo htmlspecialchars($search_etudiant); ?></strong>"
            </p>
            <?php endif; ?>
        </div>

        <!-- Sélection de l'étudiant -->
        <div style="background: #f8f9fa; border-radius: 16px; padding: 20px; margin-bottom: 20px;">
            <h3 style="margin-bottom: 15px;"><i class="fas fa-user-graduate"></i> 1. Sélectionner un étudiant</h3>
            <form method="GET" action="">
                <div class="form-group">
                    <select name="etudiant" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd;" onchange="this.form.submit()">
                        <option value="">-- Choisir un étudiant --</option>
                        <?php foreach($etudiants as $e): ?>
                        <option value="<?php echo $e['id_etudiant']; ?>" <?php echo $selected_etudiant == $e['id_etudiant'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($e['matricule'] . ' - ' . $e['prenom'] . ' ' . $e['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if($selected_etudiant): ?>
        
        <!-- Liste des modules avec notes et bouton modifier -->
        <div style="background: #f8f9fa; border-radius: 16px; padding: 20px;">
            <h3 style="margin-bottom: 15px;"><i class="fas fa-list"></i> 2. Notes de l'étudiant</h3>
            
            <?php if(count($modules_avec_notes) > 0): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #e8f0fe; border-radius: 10px;">
                            <th style="padding: 12px; text-align: left;">Module</th>
                            <th style="padding: 12px; text-align: center;">Coefficient</th>
                            <th style="padding: 12px; text-align: center;">CC (/20)</th>
                            <th style="padding: 12px; text-align: center;">Examen (/20)</th>
                            <th style="padding: 12px; text-align: center;">Rattrapage</th>
                            <th style="padding: 12px; text-align: center;">Moyenne</th>
                            <th style="padding: 12px; text-align: center;">Statut</th>
                            <th style="padding: 12px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_coeff = 0;
                        $total_moyenne_ponderee = 0;
                        foreach($modules_avec_notes as $m): 
                            $moyenne = ($m['note_cc'] + $m['note_examen']) / 2;
                            $moyenne_finale = $moyenne;
                            if($moyenne < 10 && $m['note_ratrapage']) {
                                $moyenne_finale = $m['note_ratrapage'];
                            }
                            $valide = $moyenne_finale >= 10;
                            $total_coeff += $m['coefficient'];
                            $total_moyenne_ponderee += $moyenne_finale * $m['coefficient'];
                        ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px;"><strong><?php echo htmlspecialchars($m['nom_module']); ?></strong></td>
                            <td style="padding: 12px; text-align: center;"><?php echo $m['coefficient']; ?></td>
                            <td style="padding: 12px; text-align: center;"><?php echo $m['note_cc'] ? number_format($m['note_cc'], 2) : '-'; ?></td>
                            <td style="padding: 12px; text-align: center;"><?php echo $m['note_examen'] ? number_format($m['note_examen'], 2) : '-'; ?></td>
                            <td style="padding: 12px; text-align: center;"><?php echo $m['note_ratrapage'] ? number_format($m['note_ratrapage'], 2) : '-'; ?></td>
                            <td style="padding: 12px; text-align: center;">
                                <strong style="color: <?php echo $valide ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo number_format($moyenne_finale, 2); ?>/20
                                </strong>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <?php echo $valide ? '✅ Validé' : '❌ Non validé'; ?>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <a href="notes.php?etudiant=<?php echo $selected_etudiant; ?>&module=<?php echo $m['id_module']; ?>" class="btn-action btn-edit" style="padding: 6px 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php if($total_coeff > 0): 
                        $moyenne_generale = $total_moyenne_ponderee / $total_coeff;
                    ?>
                    <tfoot>
                        <tr style="background: #e8f0fe; font-weight: bold;">
                            <td style="padding: 12px;"><strong>Moyenne générale</strong></td>
                            <td style="padding: 12px; text-align: center;"><?php echo $total_coeff; ?></td>
                            <td colspan="4" style="padding: 12px; text-align: center;">
                                <strong style="font-size: 16px; color: <?php echo $moyenne_generale >= 10 ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo number_format($moyenne_generale, 2); ?>/20
                                </strong>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <?php echo $moyenne_generale >= 10 ? '✅ Admis' : '❌ Non admis'; ?>
                            </td>
                            <td style="padding: 12px; text-align: center;"></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
            <?php else: ?>
            <p style="color: #6c757d; text-align: center; padding: 30px;">
                <i class="fas fa-info-circle"></i> Aucune note enregistrée pour cet étudiant
            </p>
            <?php endif; ?>
        </div>

        <?php endif; ?>

        <!-- Formulaire de modification des notes -->
        <?php if($selected_etudiant && $selected_module && $notes_existantes): 
            // Récupérer les infos de l'étudiant
            $info_etudiant = null;
            foreach($etudiants as $e) {
                if($e['id_etudiant'] == $selected_etudiant) {
                    $info_etudiant = $e;
                    break;
                }
            }
            // Récupérer les infos du module
            $info_module = null;
            $stmt = $pdo->prepare("SELECT * FROM module WHERE id_module = ?");
            $stmt->execute([$selected_module]);
            $info_module = $stmt->fetch();
        ?>
        <div style="background: #f8f9fa; border-radius: 16px; padding: 20px; margin-top: 20px;">
            <h3 style="margin-bottom: 15px;"><i class="fas fa-edit"></i> Modifier les notes</h3>
            
            <div style="background: #e8f0fe; border-radius: 12px; padding: 15px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <span style="color: #6c757d;">Étudiant :</span><br>
                        <strong><?php echo htmlspecialchars($info_etudiant['prenom'] . ' ' . $info_etudiant['nom']); ?></strong><br>
                        <span style="font-size: 12px; color: #6c757d;">Matricule: <?php echo htmlspecialchars($info_etudiant['matricule']); ?></span>
                    </div>
                    <div>
                        <span style="color: #6c757d;">Module :</span><br>
                        <strong><?php echo htmlspecialchars($info_module['nom_module']); ?></strong><br>
                        <span style="font-size: 12px; color: #6c757d;">Coefficient: <?php echo $info_module['coefficient']; ?></span>
                    </div>
                </div>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="id_etudiant" value="<?php echo $selected_etudiant; ?>">
                <input type="hidden" name="id_module" value="<?php echo $selected_module; ?>">
                <input type="hidden" name="save_notes" value="1">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Note CC (0-20)</label>
                        <input type="number" step="0.25" min="0" max="20" name="note_cc" value="<?php echo $notes_existantes['note_cc']; ?>">
                        <small style="color: #6c757d;">Laissez vide si non disponible</small>
                    </div>
                    <div class="form-group">
                        <label>Note Examen (0-20)</label>
                        <input type="number" step="0.25" min="0" max="20" name="note_examen" value="<?php echo $notes_existantes['note_examen']; ?>">
                        <small style="color: #6c757d;">Laissez vide si non disponible</small>
                    </div>
                    <div class="form-group">
                        <label>Note Rattrapage (0-20)</label>
                        <input type="number" step="0.25" min="0" max="20" name="note_ratrapage" value="<?php echo $notes_existantes['note_ratrapage']; ?>">
                        <small style="color: #6c757d;">Laissez vide si non disponible</small>
                    </div>
                    <div class="form-group">
                        <label>Session</label>
                        <select name="session" required>
                            <option value="Normale" <?php echo $notes_existantes['session'] == 'Normale' ? 'selected' : ''; ?>>Normale</option>
                            <option value="Rattrapage" <?php echo $notes_existantes['session'] == 'Rattrapage' ? 'selected' : ''; ?>>Rattrapage</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="action-btn" style="background: linear-gradient(135deg, #4361ee, #3b82f6); padding: 12px 25px;">
                        <i class="fas fa-save"></i> Mettre à jour les notes
                    </button>
                    <a href="notes.php?etudiant=<?php echo $selected_etudiant; ?>" class="btn-action" style="background: #6c757d; text-decoration: none;">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
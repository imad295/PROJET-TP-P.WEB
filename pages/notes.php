<?php
require_once '../includes/config.php';

if(!isLoggedIn()) {
    redirect('../index.php');
}

// Vérifier l'accès selon le rôle
if(hasRole('etudiant')) {
    redirect('dashboard_etudiant.php');
}

$error = '';
$success = '';

// Récupérer la liste des étudiants
$stmt = $pdo->query("SELECT * FROM etudiant ORDER BY nom");
$etudiants = $stmt->fetchAll();

// Récupérer la liste des modules
if(hasRole('enseignant')) {
    $stmt = $pdo->prepare("SELECT * FROM module WHERE id_enseignant = ? ORDER BY nom_module");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->query("SELECT * FROM module ORDER BY nom_module");
}
$modules = $stmt->fetchAll();

// Traitement du formulaire
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_etudiant = $_POST['id_etudiant'];
    $id_module = $_POST['id_module'];
    $note_cc = $_POST['note_cc'] ?: null;
    $note_examen = $_POST['note_examen'] ?: null;
    $note_ratrapage = $_POST['note_ratrapage'] ?: null;
    $session = $_POST['session'];
    
    // Validation des notes
    if(($note_cc && ($note_cc < 0 || $note_cc > 20)) || 
       ($note_examen && ($note_examen < 0 || $note_examen > 20)) || 
       ($note_ratrapage && ($note_ratrapage < 0 || $note_ratrapage > 20))) {
        $error = "Les notes doivent être comprises entre 0 et 20";
    } else {
        // Vérifier si une note existe déjà
        $stmt = $pdo->prepare("SELECT * FROM note WHERE id_etudiant = ? AND id_module = ? AND session = ?");
        $stmt->execute([$id_etudiant, $id_module, $session]);
        $existing = $stmt->fetch();
        
        if($existing) {
            // Mise à jour
            $stmt = $pdo->prepare("UPDATE note SET note_cc=?, note_examen=?, note_ratrapage=? WHERE id_note=?");
            if($stmt->execute([$note_cc, $note_examen, $note_ratrapage, $existing['id_note']])) {
                $success = "Notes mises à jour avec succès";
            }
        } else {
            // Insertion
            $stmt = $pdo->prepare("INSERT INTO note (id_etudiant, id_module, note_cc, note_examen, note_ratrapage, session) VALUES (?, ?, ?, ?, ?, ?)");
            if($stmt->execute([$id_etudiant, $id_module, $note_cc, $note_examen, $note_ratrapage, $session])) {
                $success = "Notes ajoutées avec succès";
            }
        }
    }
}

// Récupérer les notes existantes si un étudiant et un module sont sélectionnés
$selected_etudiant = isset($_GET['etudiant']) ? $_GET['etudiant'] : null;
$selected_module = isset($_GET['module']) ? $_GET['module'] : null;
$notes_existantes = null;

if($selected_etudiant && $selected_module) {
    $stmt = $pdo->prepare("SELECT * FROM note WHERE id_etudiant = ? AND id_module = ?");
    $stmt->execute([$selected_etudiant, $selected_module]);
    $notes_existantes = $stmt->fetch();
}

include '../includes/header.php';

// Sidebar selon le rôle
if(hasRole('admin')) {
    include '../includes/sidebar_admin.php';
} else {
    include '../includes/sidebar_enseignant.php';
}
?>

<main class="main-content">
    <div class="page-header">
        <h1>Gestion des Notes</h1>
    </div>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <!-- Formulaire de sélection -->
    <div class="form-container">
        <form method="GET" action="">
            <div class="form-row">
                <div class="form-group">
                    <label>Étudiant</label>
                    <select name="etudiant" required onchange="this.form.submit()">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($etudiants as $etudiant): ?>
                        <option value="<?php echo $etudiant['id_etudiant']; ?>" <?php echo $selected_etudiant == $etudiant['id_etudiant'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($etudiant['matricule'] . ' - ' . $etudiant['prenom'] . ' ' . $etudiant['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Module</label>
                    <select name="module" required onchange="this.form.submit()">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($modules as $module): ?>
                        <option value="<?php echo $module['id_module']; ?>" <?php echo $selected_module == $module['id_module'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($module['nom_module'] . ' (Coeff: ' . $module['coefficient'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
    
    <?php if($selected_etudiant && $selected_module): ?>
    <!-- Formulaire de saisie des notes -->
    <div class="form-container" style="margin-top: 20px;">
        <h3>Saisie des notes</h3>
        <form method="POST" action="">
            <input type="hidden" name="id_etudiant" value="<?php echo $selected_etudiant; ?>">
            <input type="hidden" name="id_module" value="<?php echo $selected_module; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Note CC (0-20)</label>
                    <input type="number" step="0.25" min="0" max="20" name="note_cc" value="<?php echo $notes_existantes ? $notes_existantes['note_cc'] : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Note Examen (0-20)</label>
                    <input type="number" step="0.25" min="0" max="20" name="note_examen" value="<?php echo $notes_existantes ? $notes_existantes['note_examen'] : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Note Rattrapage (0-20)</label>
                    <input type="number" step="0.25" min="0" max="20" name="note_ratrapage" value="<?php echo $notes_existantes ? $notes_existantes['note_ratrapage'] : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Session</label>
                    <select name="session" required>
                        <option value="Normale" <?php echo $notes_existantes && $notes_existantes['session'] == 'Normale' ? 'selected' : ''; ?>>Normale</option>
                        <option value="Rattrapage" <?php echo $notes_existantes && $notes_existantes['session'] == 'Rattrapage' ? 'selected' : ''; ?>>Rattrapage</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Enregistrer les notes</button>
            </div>
        </form>
        
        <?php if($notes_existantes && ($notes_existantes['note_cc'] || $notes_existantes['note_examen'])): ?>
        <div class="note-info" style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;">
            <h4>Moyenne calculée</h4>
            <?php
            $moyenne = ($notes_existantes['note_cc'] + $notes_existantes['note_examen']) / 2;
            $moyenne_finale = $moyenne >= 10 ? $moyenne : ($notes_existantes['note_ratrapage'] ?: $moyenne);
            ?>
            <p>Moyenne normale: <b><?php echo number_format($moyenne, 2); ?>/20</b></p>
            <p>Moyenne finale: <b><?php echo number_format($moyenne_finale, 2); ?>/20</b></p>
            <?php if($moyenne_finale >= 10): ?>
                <font color="green">✅ Validé</font>
            <?php else: ?>
                <font color="red">❌ Non validé</font>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
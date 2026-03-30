<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $nom_module = trim($_POST['nom_module']);
                $coefficient = $_POST['coefficient'];
                $id_enseignant = $_POST['id_enseignant'] ?: null;
                
                $stmt = $pdo->prepare("INSERT INTO module (nom_module, coefficient, id_enseignant) VALUES (?, ?, ?)");
                if($stmt->execute([$nom_module, $coefficient, $id_enseignant])) {
                    $_SESSION['message'] = "Module ajouté avec succès";
                    $_SESSION['message_type'] = "success";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $nom_module = trim($_POST['nom_module']);
                $coefficient = $_POST['coefficient'];
                $id_enseignant = $_POST['id_enseignant'] ?: null;
                
                $stmt = $pdo->prepare("UPDATE module SET nom_module=?, coefficient=?, id_enseignant=? WHERE id_module=?");
                if($stmt->execute([$nom_module, $coefficient, $id_enseignant, $id])) {
                    $_SESSION['message'] = "Module modifié avec succès";
                    $_SESSION['message_type'] = "success";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM module WHERE id_module=?");
                if($stmt->execute([$id])) {
                    $_SESSION['message'] = "Module supprimé avec succès";
                    $_SESSION['message_type'] = "success";
                }
                break;
        }
        redirect('modules.php');
    }
}

// Récupération des modules avec nom enseignant
$stmt = $pdo->query("
    SELECT m.*, e.nom as enseignant_nom, e.prenom as enseignant_prenom 
    FROM module m 
    LEFT JOIN enseignant e ON m.id_enseignant = e.id_enseignant 
    ORDER BY m.nom_module
");
$modules = $stmt->fetchAll();

// Récupération des enseignants pour le select
$stmt = $pdo->query("SELECT * FROM enseignant ORDER BY nom");
$enseignants = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Gestion des Modules</h1>
        <button class="btn-primary" onclick="openModal('addModal')">+ Ajouter module</button>
    </div>
    
    <?php displayMessage(); ?>
    
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Coefficient</th>
                    <th>Enseignant responsable</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($modules) > 0): ?>
                    <?php foreach($modules as $module): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($module['nom_module']); ?></td>
                        <td align="center"><?php echo $module['coefficient']; ?></td>
                        <td><?php echo $module['enseignant_nom'] ? htmlspecialchars($module['enseignant_prenom'] . ' ' . $module['enseignant_nom']) : 'Non assigné'; ?></td>
                        <td class="actions">
                            <button class="btn-edit" onclick="editModule(<?php echo $module['id_module']; ?>, '<?php echo htmlspecialchars($module['nom_module']); ?>', <?php echo $module['coefficient']; ?>, <?php echo $module['id_enseignant'] ?: 'null'; ?>)">Modifier</button>
                            <button class="btn-delete" onclick="deleteModule(<?php echo $module['id_module']; ?>, '<?php echo htmlspecialchars($module['nom_module']); ?>')">Supprimer</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Aucun module trouvé</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Ajout -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Ajouter un module</h2>
            <span class="close" onclick="closeModal('addModal')">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Nom du module *</label>
                <input type="text" name="nom_module" required>
            </div>
            <div class="form-group">
                <label>Coefficient *</label>
                <input type="number" step="0.5" min="0.5" name="coefficient" required>
            </div>
            <div class="form-group">
                <label>Enseignant responsable</label>
                <select name="id_enseignant">
                    <option value="">-- Aucun --</option>
                    <?php foreach($enseignants as $enseignant): ?>
                    <option value="<?php echo $enseignant['id_enseignant']; ?>">
                        <?php echo htmlspecialchars($enseignant['prenom'] . ' ' . $enseignant['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <button type="button" class="btn-secondary" onclick="closeModal('addModal')">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Modification -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Modifier le module</h2>
            <span class="close" onclick="closeModal('editModal')">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label>Nom du module *</label>
                <input type="text" name="nom_module" id="edit_nom_module" required>
            </div>
            <div class="form-group">
                <label>Coefficient *</label>
                <input type="number" step="0.5" min="0.5" name="coefficient" id="edit_coefficient" required>
            </div>
            <div class="form-group">
                <label>Enseignant responsable</label>
                <select name="id_enseignant" id="edit_id_enseignant">
                    <option value="">-- Aucun --</option>
                    <?php foreach($enseignants as $enseignant): ?>
                    <option value="<?php echo $enseignant['id_enseignant']; ?>">
                        <?php echo htmlspecialchars($enseignant['prenom'] . ' ' . $enseignant['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Modifier</button>
                <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Suppression -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirmer la suppression</h2>
            <span class="close" onclick="closeModal('deleteModal')">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="delete_id">
            <p>Êtes-vous sûr de vouloir supprimer le module <strong id="delete_name"></strong> ?</p>
            <div class="form-actions">
                <button type="submit" class="btn-delete">Supprimer</button>
                <button type="button" class="btn-secondary" onclick="closeModal('deleteModal')">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function editModule(id, nom, coefficient, enseignantId) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nom_module').value = nom;
    document.getElementById('edit_coefficient').value = coefficient;
    if(enseignantId) {
        document.getElementById('edit_id_enseignant').value = enseignantId;
    }
    openModal('editModal');
}

function deleteModule(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    openModal('deleteModal');
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
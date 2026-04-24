<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO module (nom_module, coefficient, id_enseignant) VALUES (?,?,?)");
                $stmt->execute([$_POST['nom_module'], $_POST['coefficient'], $_POST['id_enseignant'] ?: null]);
                $_SESSION['message'] = "Module ajouté avec succès";
                break;
            case 'edit':
                $stmt = $pdo->prepare("UPDATE module SET nom_module=?, coefficient=?, id_enseignant=? WHERE id_module=?");
                $stmt->execute([$_POST['nom_module'], $_POST['coefficient'], $_POST['id_enseignant'] ?: null, $_POST['id']]);
                $_SESSION['message'] = "Module modifié avec succès";
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM module WHERE id_module=?");
                $stmt->execute([$_POST['id']]);
                $_SESSION['message'] = "Module supprimé avec succès";
                break;
        }
        header('Location: modules.php');
        exit();
    }
}

// Recherche de modules
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if($search) {
    $stmt = $pdo->prepare("
        SELECT m.*, e.nom as enom, e.prenom as eprenom 
        FROM module m 
        LEFT JOIN enseignant e ON m.id_enseignant = e.id_enseignant 
        WHERE m.nom_module LIKE ? 
        ORDER BY m.nom_module
    ");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $pdo->query("
        SELECT m.*, e.nom as enom, e.prenom as eprenom 
        FROM module m 
        LEFT JOIN enseignant e ON m.id_enseignant = e.id_enseignant 
        ORDER BY m.nom_module
    ");
}
$modules = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM enseignant ORDER BY nom");
$enseignants = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-book"></i> Gestion des Modules</h1>
                <p>Ajouter, modifier ou supprimer des modules</p>
            </div>
            <button class="action-btn" onclick="openModal('addModal')" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <i class="fas fa-plus"></i> Ajouter un module
            </button>
        </div>

        <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <!-- Barre de recherche -->
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="🔍 Rechercher un module..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> Rechercher</button>
                <?php if($search): ?>
                <a href="modules.php" class="btn-clear"><i class="fas fa-times"></i> Effacer</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if($search && count($modules) == 0): ?>
        <div class="alert alert-error" style="text-align: center;">
            <i class="fas fa-search"></i> Aucun module trouvé pour "<strong><?php echo htmlspecialchars($search); ?></strong>"
        </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th>Module</th>
                        <th>Coefficient</th>
                        <th>Enseignant responsable</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($modules as $m): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($m['nom_module']); ?></strong></td>
                        <td align="center"><?php echo $m['coefficient']; ?></td>
                        <td><?php echo $m['enom'] ? htmlspecialchars($m['eprenom'].' '.$m['enom']) : '<span style="color:#6c757d;">Non assigné</span>'; ?></td>
                        <td class="actions">
                            <button class="btn-action btn-edit" onclick='editModule(<?php echo json_encode($m); ?>)'>
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteModule(<?php echo $m['id_module']; ?>, '<?php echo addslashes($m['nom_module']); ?>')">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modals (same as before) -->
<div id="addModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-plus"></i> Ajouter un module</h2><span class="close" onclick="closeModal('addModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="add">
        <div class="form-group"><label>Nom du module</label><input type="text" name="nom_module" required></div>
        <div class="form-group"><label>Coefficient</label><input type="number" step="0.5" name="coefficient" required></div>
        <div class="form-group"><label>Enseignant responsable</label>
            <select name="id_enseignant"><option value="">-- Aucun --</option>
            <?php foreach($enseignants as $e): ?>
            <option value="<?php echo $e['id_enseignant']; ?>"><?php echo htmlspecialchars($e['prenom'].' '.$e['nom']); ?></option>
            <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Enregistrer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('addModal')">Annuler</button></div>
    </form>
</div></div>

<div id="editModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-edit"></i> Modifier le module</h2><span class="close" onclick="closeModal('editModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="edit_id">
        <div class="form-group"><label>Nom du module</label><input type="text" name="nom_module" id="edit_nom" required></div>
        <div class="form-group"><label>Coefficient</label><input type="number" step="0.5" name="coefficient" id="edit_coeff" required></div>
        <div class="form-group"><label>Enseignant responsable</label>
            <select name="id_enseignant" id="edit_ens"><option value="">-- Aucun --</option>
            <?php foreach($enseignants as $e): ?>
            <option value="<?php echo $e['id_enseignant']; ?>"><?php echo htmlspecialchars($e['prenom'].' '.$e['nom']); ?></option>
            <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Modifier</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('editModal')">Annuler</button></div>
    </form>
</div></div>

<div id="deleteModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-trash-alt"></i> Confirmer la suppression</h2><span class="close" onclick="closeModal('deleteModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" id="delete_id">
        <p>Supprimer le module <strong id="delete_name"></strong> ?</p>
        <div class="form-actions"><button type="submit" class="btn-action btn-delete">Supprimer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('deleteModal')">Annuler</button></div>
    </form>
</div></div>

<script>
function openModal(id){ document.getElementById(id).style.display='block'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }
function editModule(e){
    document.getElementById('edit_id').value=e.id_module;
    document.getElementById('edit_nom').value=e.nom_module;
    document.getElementById('edit_coeff').value=e.coefficient;
    if(e.id_enseignant) document.getElementById('edit_ens').value=e.id_enseignant;
    openModal('editModal');
}
function deleteModule(id,name){
    document.getElementById('delete_id').value=id;
    document.getElementById('delete_name').innerText=name;
    openModal('deleteModal');
}
window.onclick = function(e){ if(e.target.classList.contains('modal')) e.target.style.display='none'; }
</script>

<?php include '../includes/footer.php'; ?>
<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO specialite (nom_specialite, id_niveau) VALUES (?, ?)");
                $stmt->execute([$_POST['nom_specialite'], $_POST['id_niveau']]);
                $_SESSION['message'] = "Spécialité ajoutée avec succès";
                break;
            case 'edit':
                $stmt = $pdo->prepare("UPDATE specialite SET nom_specialite=?, id_niveau=? WHERE id_specialite=?");
                $stmt->execute([$_POST['nom_specialite'], $_POST['id_niveau'], $_POST['id']]);
                $_SESSION['message'] = "Spécialité modifiée avec succès";
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM specialite WHERE id_specialite=?");
                $stmt->execute([$_POST['id']]);
                $_SESSION['message'] = "Spécialité supprimée avec succès";
                break;
        }
        header('Location: specialites.php');
        exit();
    }
}

$stmt = $pdo->query("
    SELECT s.*, n.nom_niveau 
    FROM specialite s 
    JOIN niveau n ON s.id_niveau = n.id_niveau 
    ORDER BY n.ordre, s.id_specialite
");
$specialites = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM niveau ORDER BY ordre");
$niveaux = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-code-branch"></i> Gestion des Spécialités</h1>
                <p>Ajouter, modifier ou supprimer les spécialités</p>
            </div>
            <button class="action-btn" onclick="openModal('addModal')" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <i class="fas fa-plus"></i> Ajouter une spécialité
            </button>
        </div>

        <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Spécialité</th>
                        <th>Niveau</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($specialites as $s): ?>
                    <tr>
                        <td><?php echo $s['id_specialite']; ?></td>
                        <td><strong><?php echo htmlspecialchars($s['nom_specialite']); ?></strong></td>
                        <td><?php echo htmlspecialchars($s['nom_niveau']); ?></td>
                        <td class="actions">
                            <button class="btn-action btn-edit" onclick='editSpecialite(<?php echo json_encode($s); ?>)'>
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteSpecialite(<?php echo $s['id_specialite']; ?>, '<?php echo addslashes($s['nom_specialite']); ?>')">
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

<!-- Modals -->
<div id="addModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-plus"></i> Ajouter une spécialité</h2><span class="close" onclick="closeModal('addModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="add">
        <div class="form-group"><label>Nom de la spécialité</label><input type="text" name="nom_specialite" required placeholder="Ex: Informatique"></div>
        <div class="form-group"><label>Niveau</label>
            <select name="id_niveau" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach($niveaux as $n): ?>
                <option value="<?php echo $n['id_niveau']; ?>"><?php echo htmlspecialchars($n['nom_niveau']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Enregistrer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('addModal')">Annuler</button></div>
    </form>
</div></div>

<div id="editModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-edit"></i> Modifier la spécialité</h2><span class="close" onclick="closeModal('editModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="edit_id">
        <div class="form-group"><label>Nom de la spécialité</label><input type="text" name="nom_specialite" id="edit_nom" required></div>
        <div class="form-group"><label>Niveau</label>
            <select name="id_niveau" id="edit_niveau" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach($niveaux as $n): ?>
                <option value="<?php echo $n['id_niveau']; ?>"><?php echo htmlspecialchars($n['nom_niveau']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Modifier</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('editModal')">Annuler</button></div>
    </form>
</div></div>

<div id="deleteModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-trash-alt"></i> Confirmer</h2><span class="close" onclick="closeModal('deleteModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" id="delete_id">
        <p>Supprimer la spécialité <strong id="delete_name"></strong> ?</p>
        <div class="form-actions"><button type="submit" class="btn-action btn-delete">Supprimer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('deleteModal')">Annuler</button></div>
    </form>
</div></div>

<script>
function openModal(id){ document.getElementById(id).style.display='block'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }
function editSpecialite(e){
    document.getElementById('edit_id').value=e.id_specialite;
    document.getElementById('edit_nom').value=e.nom_specialite;
    document.getElementById('edit_niveau').value=e.id_niveau;
    openModal('editModal');
}
function deleteSpecialite(id,name){
    document.getElementById('delete_id').value=id;
    document.getElementById('delete_name').innerText=name;
    openModal('deleteModal');
}
window.onclick = function(e){ if(e.target.classList.contains('modal')) e.target.style.display='none'; }
</script>

<?php include '../includes/footer.php'; ?>
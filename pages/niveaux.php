<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO niveau (nom_niveau, ordre) VALUES (?, ?)");
                $stmt->execute([$_POST['nom_niveau'], $_POST['ordre']]);
                $_SESSION['message'] = "Niveau ajouté avec succès";
                break;
            case 'edit':
                $stmt = $pdo->prepare("UPDATE niveau SET nom_niveau=?, ordre=? WHERE id_niveau=?");
                $stmt->execute([$_POST['nom_niveau'], $_POST['ordre'], $_POST['id']]);
                $_SESSION['message'] = "Niveau modifié avec succès";
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM niveau WHERE id_niveau=?");
                $stmt->execute([$_POST['id']]);
                $_SESSION['message'] = "Niveau supprimé avec succès";
                break;
        }
        header('Location: niveaux.php');
        exit();
    }
}

$stmt = $pdo->query("SELECT * FROM niveau ORDER BY ordre");
$niveaux = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-layer-group"></i> Gestion des Niveaux</h1>
                <p>Ajouter, modifier ou supprimer les niveaux (L1, L2, L3)</p>
            </div>
            <button class="action-btn" onclick="openModal('addModal')" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <i class="fas fa-plus"></i> Ajouter un niveau
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
                        <th>Nom du niveau</th>
                        <th>Ordre</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($niveaux as $n): ?>
                    <tr>
                        <td><?php echo $n['id_niveau']; ?></td>
                        <td><strong><?php echo htmlspecialchars($n['nom_niveau']); ?></strong></td>
                        <td align="center"><?php echo $n['ordre']; ?></td>
                        <td class="actions">
                            <button class="btn-action btn-edit" onclick='editNiveau(<?php echo json_encode($n); ?>)'>
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteNiveau(<?php echo $n['id_niveau']; ?>, '<?php echo addslashes($n['nom_niveau']); ?>')">
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
    <div class="modal-header"><h2><i class="fas fa-plus"></i> Ajouter un niveau</h2><span class="close" onclick="closeModal('addModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="add">
        <div class="form-group"><label>Nom du niveau</label><input type="text" name="nom_niveau" required placeholder="Ex: 1ère Année (L1)"></div>
        <div class="form-group"><label>Ordre</label><input type="number" name="ordre" required placeholder="1, 2, 3..."></div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Enregistrer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('addModal')">Annuler</button></div>
    </form>
</div></div>

<div id="editModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-edit"></i> Modifier le niveau</h2><span class="close" onclick="closeModal('editModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="edit_id">
        <div class="form-group"><label>Nom du niveau</label><input type="text" name="nom_niveau" id="edit_nom" required></div>
        <div class="form-group"><label>Ordre</label><input type="number" name="ordre" id="edit_ordre" required></div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Modifier</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('editModal')">Annuler</button></div>
    </form>
</div></div>

<div id="deleteModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-trash-alt"></i> Confirmer</h2><span class="close" onclick="closeModal('deleteModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" id="delete_id">
        <p>Supprimer le niveau <strong id="delete_name"></strong> ?</p>
        <div class="form-actions"><button type="submit" class="btn-action btn-delete">Supprimer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('deleteModal')">Annuler</button></div>
    </form>
</div></div>

<script>
function openModal(id){ document.getElementById(id).style.display='block'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }
function editNiveau(e){
    document.getElementById('edit_id').value=e.id_niveau;
    document.getElementById('edit_nom').value=e.nom_niveau;
    document.getElementById('edit_ordre').value=e.ordre;
    openModal('editModal');
}
function deleteNiveau(id,name){
    document.getElementById('delete_id').value=id;
    document.getElementById('delete_name').innerText=name;
    openModal('deleteModal');
}
window.onclick = function(e){ if(e.target.classList.contains('modal')) e.target.style.display='none'; }
</script>

<?php include '../includes/footer.php'; ?>
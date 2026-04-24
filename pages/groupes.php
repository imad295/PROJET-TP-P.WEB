<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO `groupe` (nom_groupe, id_section) VALUES (?, ?)");
                $stmt->execute([$_POST['nom_groupe'], $_POST['id_section']]);
                $_SESSION['message'] = "Groupe ajouté avec succès";
                break;
            case 'edit':
                $stmt = $pdo->prepare("UPDATE `groupe` SET nom_groupe=?, id_section=? WHERE id_groupe=?");
                $stmt->execute([$_POST['nom_groupe'], $_POST['id_section'], $_POST['id']]);
                $_SESSION['message'] = "Groupe modifié avec succès";
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM `groupe` WHERE id_groupe=?");
                $stmt->execute([$_POST['id']]);
                $_SESSION['message'] = "Groupe supprimé avec succès";
                break;
        }
        header('Location: groupes.php');
        exit();
    }
}

$stmt = $pdo->query("
    SELECT g.*, s.nom_section, sp.nom_specialite, n.nom_niveau 
    FROM `groupe` g
    JOIN section s ON g.id_section = s.id_section
    JOIN specialite sp ON s.id_specialite = sp.id_specialite
    JOIN niveau n ON sp.id_niveau = n.id_niveau
    ORDER BY n.ordre, sp.id_specialite, s.id_section, g.id_groupe
");
$groupes = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT s.*, sp.nom_specialite, n.nom_niveau 
    FROM section s
    JOIN specialite sp ON s.id_specialite = sp.id_specialite
    JOIN niveau n ON sp.id_niveau = n.id_niveau
    ORDER BY n.ordre, sp.id_specialite, s.id_section
");
$sections = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-users"></i> Gestion des Groupes</h1>
                <p>Ajouter, modifier ou supprimer les groupes (36 groupes au total)</p>
            </div>
            <button class="action-btn" onclick="openModal('addModal')" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <i class="fas fa-plus"></i> Ajouter un groupe
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
                        <th>Groupe</th>
                        <th>Section</th>
                        <th>Spécialité</th>
                        <th>Niveau</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($groupes as $g): ?>
                    <tr>
                        <td><?php echo $g['id_groupe']; ?></td>
                        <td><strong><?php echo htmlspecialchars($g['nom_groupe']); ?></strong></td>
                        <td><?php echo htmlspecialchars($g['nom_section']); ?></td>
                        <td><?php echo htmlspecialchars($g['nom_specialite']); ?></td>
                        <td><?php echo htmlspecialchars($g['nom_niveau']); ?></td>
                        <td class="actions">
                            <button class="btn-action btn-edit" onclick='editGroupe(<?php echo json_encode($g); ?>)'>
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteGroupe(<?php echo $g['id_groupe']; ?>, '<?php echo addslashes($g['nom_groupe']); ?>')">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Statistiques -->
        <div style="margin-top: 20px; padding: 15px; background: #e8f0fe; border-radius: 12px;">
            <strong>Total :</strong> <?php echo count($groupes); ?> groupes
        </div>
    </div>
</div>

<!-- Modals -->
<div id="addModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-plus"></i> Ajouter un groupe</h2><span class="close" onclick="closeModal('addModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="add">
        <div class="form-group"><label>Nom du groupe</label><input type="text" name="nom_groupe" required placeholder="Ex: Groupe 1"></div>
        <div class="form-group"><label>Section</label>
            <select name="id_section" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach($sections as $sec): ?>
                <option value="<?php echo $sec['id_section']; ?>"><?php echo htmlspecialchars($sec['nom_section'] . ' - ' . $sec['nom_specialite'] . ' (' . $sec['nom_niveau'] . ')'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Enregistrer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('addModal')">Annuler</button></div>
    </form>
</div></div>

<div id="editModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-edit"></i> Modifier le groupe</h2><span class="close" onclick="closeModal('editModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="edit_id">
        <div class="form-group"><label>Nom du groupe</label><input type="text" name="nom_groupe" id="edit_nom" required></div>
        <div class="form-group"><label>Section</label>
            <select name="id_section" id="edit_section" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach($sections as $sec): ?>
                <option value="<?php echo $sec['id_section']; ?>"><?php echo htmlspecialchars($sec['nom_section'] . ' - ' . $sec['nom_specialite'] . ' (' . $sec['nom_niveau'] . ')'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Modifier</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('editModal')">Annuler</button></div>
    </form>
</div></div>

<div id="deleteModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-trash-alt"></i> Confirmer</h2><span class="close" onclick="closeModal('deleteModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" id="delete_id">
        <p>Supprimer le groupe <strong id="delete_name"></strong> ?</p>
        <div class="form-actions"><button type="submit" class="btn-action btn-delete">Supprimer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('deleteModal')">Annuler</button></div>
    </form>
</div></div>

<script>
function openModal(id){ document.getElementById(id).style.display='block'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }
function editGroupe(e){
    document.getElementById('edit_id').value=e.id_groupe;
    document.getElementById('edit_nom').value=e.nom_groupe;
    document.getElementById('edit_section').value=e.id_section;
    openModal('editModal');
}
function deleteGroupe(id,name){
    document.getElementById('delete_id').value=id;
    document.getElementById('delete_name').innerText=name;
    openModal('deleteModal');
}
window.onclick = function(e){ if(e.target.classList.contains('modal')) e.target.style.display='none'; }
</script>

<?php include '../includes/footer.php'; ?>
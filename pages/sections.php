<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO section (nom_section, id_specialite) VALUES (?, ?)");
                $stmt->execute([$_POST['nom_section'], $_POST['id_specialite']]);
                $_SESSION['message'] = "Section ajoutée avec succès";
                break;
            case 'edit':
                $stmt = $pdo->prepare("UPDATE section SET nom_section=?, id_specialite=? WHERE id_section=?");
                $stmt->execute([$_POST['nom_section'], $_POST['id_specialite'], $_POST['id']]);
                $_SESSION['message'] = "Section modifiée avec succès";
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM section WHERE id_section=?");
                $stmt->execute([$_POST['id']]);
                $_SESSION['message'] = "Section supprimée avec succès";
                break;
        }
        header('Location: sections.php');
        exit();
    }
}

$stmt = $pdo->query("
    SELECT sec.*, sp.nom_specialite, n.nom_niveau 
    FROM section sec
    JOIN specialite sp ON sec.id_specialite = sp.id_specialite
    JOIN niveau n ON sp.id_niveau = n.id_niveau
    ORDER BY n.ordre, sp.id_specialite, sec.id_section
");
$sections = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT sp.*, n.nom_niveau 
    FROM specialite sp
    JOIN niveau n ON sp.id_niveau = n.id_niveau
    ORDER BY n.ordre, sp.id_specialite
");
$specialites = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="max-width: 1000px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-columns"></i> Gestion des Sections</h1>
                <p>Ajouter, modifier ou supprimer les sections (A1, A2, B1, etc.)</p>
            </div>
            <button class="action-btn" onclick="openModal('addModal')" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <i class="fas fa-plus"></i> Ajouter une section
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
                        <th>Section</th>
                        <th>Spécialité</th>
                        <th>Niveau</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sections as $sec): ?>
                    <tr>
                        <td><?php echo $sec['id_section']; ?></td>
                        <td><strong><?php echo htmlspecialchars($sec['nom_section']); ?></strong></td>
                        <td><?php echo htmlspecialchars($sec['nom_specialite']); ?></td>
                        <td><?php echo htmlspecialchars($sec['nom_niveau']); ?></td>
                        <td class="actions">
                            <button class="btn-action btn-edit" onclick='editSection(<?php echo json_encode($sec); ?>)'>
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteSection(<?php echo $sec['id_section']; ?>, '<?php echo addslashes($sec['nom_section']); ?>')">
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
    <div class="modal-header"><h2><i class="fas fa-plus"></i> Ajouter une section</h2><span class="close" onclick="closeModal('addModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="add">
        <div class="form-group"><label>Nom de la section</label><input type="text" name="nom_section" required placeholder="Ex: Section A1"></div>
        <div class="form-group"><label>Spécialité</label>
            <select name="id_specialite" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach($specialites as $sp): ?>
                <option value="<?php echo $sp['id_specialite']; ?>"><?php echo htmlspecialchars($sp['nom_specialite'] . ' - ' . $sp['nom_niveau']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Enregistrer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('addModal')">Annuler</button></div>
    </form>
</div></div>

<div id="editModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-edit"></i> Modifier la section</h2><span class="close" onclick="closeModal('editModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="edit_id">
        <div class="form-group"><label>Nom de la section</label><input type="text" name="nom_section" id="edit_nom" required></div>
        <div class="form-group"><label>Spécialité</label>
            <select name="id_specialite" id="edit_specialite" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach($specialites as $sp): ?>
                <option value="<?php echo $sp['id_specialite']; ?>"><?php echo htmlspecialchars($sp['nom_specialite'] . ' - ' . $sp['nom_niveau']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Modifier</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('editModal')">Annuler</button></div>
    </form>
</div></div>

<div id="deleteModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-trash-alt"></i> Confirmer</h2><span class="close" onclick="closeModal('deleteModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" id="delete_id">
        <p>Supprimer la section <strong id="delete_name"></strong> ?</p>
        <div class="form-actions"><button type="submit" class="btn-action btn-delete">Supprimer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('deleteModal')">Annuler</button></div>
    </form>
</div></div>

<script>
function openModal(id){ document.getElementById(id).style.display='block'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }
function editSection(e){
    document.getElementById('edit_id').value=e.id_section;
    document.getElementById('edit_nom').value=e.nom_section;
    document.getElementById('edit_specialite').value=e.id_specialite;
    openModal('editModal');
}
function deleteSection(id,name){
    document.getElementById('delete_id').value=id;
    document.getElementById('delete_name').innerText=name;
    openModal('deleteModal');
}
window.onclick = function(e){ if(e.target.classList.contains('modal')) e.target.style.display='none'; }
</script>

<?php include '../includes/footer.php'; ?>
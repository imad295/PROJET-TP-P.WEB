<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO enseignant (nom, prenom, email, mot_de_passe) VALUES (?,?,?,?)");
                $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], $password]);
                $_SESSION['message'] = "Enseignant ajouté avec succès";
                break;
            case 'edit':
                $stmt = $pdo->prepare("UPDATE enseignant SET nom=?, prenom=?, email=? WHERE id_enseignant=?");
                $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['id']]);
                $_SESSION['message'] = "Enseignant modifié avec succès";
                break;
            case 'change_password':
                if($_POST['password'] == $_POST['confirm_password'] && strlen($_POST['password']) >= 6) {
                    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE enseignant SET mot_de_passe=? WHERE id_enseignant=?");
                    $stmt->execute([$hashed, $_POST['id']]);
                    $_SESSION['message'] = "Mot de passe modifié avec succès";
                } else {
                    $_SESSION['message'] = "Erreur: mots de passe non identiques ou trop courts";
                }
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM enseignant WHERE id_enseignant=?");
                $stmt->execute([$_POST['id']]);
                $_SESSION['message'] = "Enseignant supprimé avec succès";
                break;
        }
        header('Location: enseignants.php');
        exit();
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if($search) {
    $stmt = $pdo->prepare("SELECT * FROM enseignant WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? ORDER BY nom");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM enseignant ORDER BY nom");
}
$enseignants = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-chalkboard-user"></i> Gestion des Enseignants</h1>
                <p>Ajouter, modifier ou supprimer des enseignants</p>
            </div>
            <button class="action-btn" onclick="openModal('addModal')" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <i class="fas fa-plus"></i> Ajouter un enseignant
            </button>
        </div>

        <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="🔍 Rechercher par nom, prénom ou email..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> Rechercher</button>
                <?php if($search): ?><a href="enseignants.php" class="btn-clear">Effacer</a><?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($enseignants as $e): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($e['nom']); ?></strong></td>
                        <td><?php echo htmlspecialchars($e['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($e['email']); ?></td>
                        <td class="actions">
                            <button class="btn-action btn-edit" onclick='editEnseignant(<?php echo json_encode($e); ?>)'>
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn-action btn-view" onclick='changePwd(<?php echo json_encode($e); ?>)'>
                                <i class="fas fa-key"></i> Mot de passe
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteEnseignant(<?php echo $e['id_enseignant']; ?>, '<?php echo addslashes($e['prenom'].' '.$e['nom']); ?>')">
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
    <div class="modal-header"><h2><i class="fas fa-user-plus"></i> Ajouter un enseignant</h2><span class="close" onclick="closeModal('addModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="add">
        <div class="form-row"><div class="form-group"><label>Nom</label><input type="text" name="nom" required></div><div class="form-group"><label>Prénom</label><input type="text" name="prenom" required></div></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Mot de passe</label><input type="password" name="password" required placeholder="Minimum 6 caractères"></div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Enregistrer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('addModal')">Annuler</button></div>
    </form>
</div></div>

<div id="editModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-edit"></i> Modifier l'enseignant</h2><span class="close" onclick="closeModal('editModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="edit_id">
        <div class="form-row"><div class="form-group"><label>Nom</label><input type="text" name="nom" id="edit_nom" required></div><div class="form-group"><label>Prénom</label><input type="text" name="prenom" id="edit_prenom" required></div></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" id="edit_email" required></div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Modifier</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('editModal')">Annuler</button></div>
    </form>
</div></div>

<div id="pwdModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-key"></i> Changer mot de passe</h2><span class="close" onclick="closeModal('pwdModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="change_password"><input type="hidden" name="id" id="pwd_id">
        <div class="form-group"><label>Enseignant: <strong id="pwd_name"></strong></label></div>
        <div class="form-group"><label>Nouveau mot de passe</label><input type="password" name="password" required placeholder="Minimum 6 caractères"></div>
        <div class="form-group"><label>Confirmer</label><input type="password" name="confirm_password" required></div>
        <div class="form-actions"><button type="submit" class="btn-action btn-view">Changer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('pwdModal')">Annuler</button></div>
    </form>
</div></div>

<div id="deleteModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-trash-alt"></i> Confirmer</h2><span class="close" onclick="closeModal('deleteModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" id="delete_id">
        <p>Supprimer l'enseignant <strong id="delete_name"></strong> ?</p>
        <div class="form-actions"><button type="submit" class="btn-action btn-delete">Supprimer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('deleteModal')">Annuler</button></div>
    </form>
</div></div>

<script>
function openModal(id){ document.getElementById(id).style.display='block'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }
function editEnseignant(e){
    document.getElementById('edit_id').value=e.id_enseignant;
    document.getElementById('edit_nom').value=e.nom;
    document.getElementById('edit_prenom').value=e.prenom;
    document.getElementById('edit_email').value=e.email;
    openModal('editModal');
}
function changePwd(e){
    document.getElementById('pwd_id').value=e.id_enseignant;
    document.getElementById('pwd_name').innerText=e.prenom+' '+e.nom;
    openModal('pwdModal');
}
function deleteEnseignant(id,name){
    document.getElementById('delete_id').value=id;
    document.getElementById('delete_name').innerText=name;
    openModal('deleteModal');
}
window.onclick = function(e){ if(e.target.classList.contains('modal')) e.target.style.display='none'; }
</script>

<?php include '../includes/footer.php'; ?>
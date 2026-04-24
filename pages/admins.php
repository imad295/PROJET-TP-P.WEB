<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                if($_POST['password'] == $_POST['confirm_password'] && strlen($_POST['password']) >= 6) {
                    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO administrateur (login, mot_de_passe) VALUES (?,?)");
                    $stmt->execute([$_POST['login'], $hashed]);
                    $_SESSION['message'] = "Administrateur ajouté avec succès";
                } else {
                    $_SESSION['message'] = "Erreur: mots de passe non identiques";
                }
                break;
            case 'edit':
                $stmt = $pdo->prepare("UPDATE administrateur SET login=? WHERE id_admin=?");
                $stmt->execute([$_POST['login'], $_POST['id']]);
                $_SESSION['message'] = "Administrateur modifié avec succès";
                break;
            case 'change_password':
                if($_POST['password'] == $_POST['confirm_password'] && strlen($_POST['password']) >= 6) {
                    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE administrateur SET mot_de_passe=? WHERE id_admin=?");
                    $stmt->execute([$hashed, $_POST['id']]);
                    $_SESSION['message'] = "Mot de passe modifié avec succès";
                } else {
                    $_SESSION['message'] = "Erreur: mots de passe non identiques";
                }
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM administrateur WHERE id_admin=?");
                $stmt->execute([$_POST['id']]);
                $_SESSION['message'] = "Administrateur supprimé avec succès";
                break;
        }
        header('Location: admins.php');
        exit();
    }
}

$stmt = $pdo->query("SELECT * FROM administrateur ORDER BY id_admin");
$admins = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="max-width: 1000px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-crown"></i> Gestion des Administrateurs</h1>
                <p>Ajouter, modifier ou supprimer des administrateurs</p>
            </div>
            <button class="action-btn" onclick="openModal('addModal')" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <i class="fas fa-plus"></i> Ajouter un administrateur
            </button>
        </div>

        <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($admins as $a): ?>
                    <tr>
                        <td><?php echo $a['id_admin']; ?></td>
                        <td><strong><?php echo htmlspecialchars($a['login']); ?></strong></td>
                        <td><span style="background: #fd7e14; color: white; padding: 3px 10px; border-radius: 20px; font-size: 11px;"><i class="fas fa-shield-alt"></i> Administrateur</span></td>
                        <td class="actions">
                            <button class="btn-action btn-edit" onclick='editAdmin(<?php echo json_encode($a); ?>)'>
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn-action btn-view" onclick='changePwd(<?php echo json_encode($a); ?>)'>
                                <i class="fas fa-key"></i> Mot de passe
                            </button>
                            <?php if($a['login'] != 'admin'): ?>
                            <button class="btn-action btn-delete" onclick="deleteAdmin(<?php echo $a['id_admin']; ?>, '<?php echo addslashes($a['login']); ?>')">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                            <?php endif; ?>
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
    <div class="modal-header"><h2><i class="fas fa-user-plus"></i> Ajouter un administrateur</h2><span class="close" onclick="closeModal('addModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="add">
        <div class="form-group"><label>Nom d'utilisateur</label><input type="text" name="login" required></div>
        <div class="form-group"><label>Mot de passe</label><input type="password" name="password" required placeholder="Minimum 6 caractères"></div>
        <div class="form-group"><label>Confirmer</label><input type="password" name="confirm_password" required></div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Ajouter</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('addModal')">Annuler</button></div>
    </form>
</div></div>

<div id="editModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-edit"></i> Modifier l'administrateur</h2><span class="close" onclick="closeModal('editModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="edit_id">
        <div class="form-group"><label>Nom d'utilisateur</label><input type="text" name="login" id="edit_login" required></div>
        <div class="form-actions"><button type="submit" class="btn-action btn-edit">Modifier</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('editModal')">Annuler</button></div>
    </form>
</div></div>

<div id="pwdModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-key"></i> Changer mot de passe</h2><span class="close" onclick="closeModal('pwdModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="change_password"><input type="hidden" name="id" id="pwd_id">
        <div class="form-group"><label>Administrateur: <strong id="pwd_login"></strong></label></div>
        <div class="form-group"><label>Nouveau mot de passe</label><input type="password" name="password" required placeholder="Minimum 6 caractères"></div>
        <div class="form-group"><label>Confirmer</label><input type="password" name="confirm_password" required></div>
        <div class="form-actions"><button type="submit" class="btn-action btn-view">Changer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('pwdModal')">Annuler</button></div>
    </form>
</div></div>

<div id="deleteModal" class="modal"><div class="modal-content">
    <div class="modal-header"><h2><i class="fas fa-trash-alt"></i> Confirmer</h2><span class="close" onclick="closeModal('deleteModal')">&times;</span></div>
    <form method="POST"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" id="delete_id">
        <p>Supprimer l'administrateur <strong id="delete_name"></strong> ?</p>
        <div class="form-actions"><button type="submit" class="btn-action btn-delete">Supprimer</button><button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('deleteModal')">Annuler</button></div>
    </form>
</div></div>

<script>
function openModal(id){ document.getElementById(id).style.display='block'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }
function editAdmin(e){
    document.getElementById('edit_id').value=e.id_admin;
    document.getElementById('edit_login').value=e.login;
    openModal('editModal');
}
function changePwd(e){
    document.getElementById('pwd_id').value=e.id_admin;
    document.getElementById('pwd_login').innerText=e.login;
    openModal('pwdModal');
}
function deleteAdmin(id,login){
    document.getElementById('delete_id').value=id;
    document.getElementById('delete_name').innerText=login;
    openModal('deleteModal');
}
window.onclick = function(e){ if(e.target.classList.contains('modal')) e.target.style.display='none'; }
</script>

<?php include '../includes/footer.php'; ?>
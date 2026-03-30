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
                $nom = trim($_POST['nom']);
                $prenom = trim($_POST['prenom']);
                $email = trim($_POST['email']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO enseignant (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
                if($stmt->execute([$nom, $prenom, $email, $password])) {
                    $_SESSION['message'] = "Enseignant ajouté avec succès";
                    $_SESSION['message_type'] = "success";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $nom = trim($_POST['nom']);
                $prenom = trim($_POST['prenom']);
                $email = trim($_POST['email']);
                
                $stmt = $pdo->prepare("UPDATE enseignant SET nom=?, prenom=?, email=? WHERE id_enseignant=?");
                if($stmt->execute([$nom, $prenom, $email, $id])) {
                    $_SESSION['message'] = "Enseignant modifié avec succès";
                    $_SESSION['message_type'] = "success";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM enseignant WHERE id_enseignant=?");
                if($stmt->execute([$id])) {
                    $_SESSION['message'] = "Enseignant supprimé avec succès";
                    $_SESSION['message_type'] = "success";
                }
                break;
        }
        redirect('enseignants.php');
    }
}

// Récupération des enseignants
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if($search) {
    $stmt = $pdo->prepare("SELECT * FROM enseignant WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? ORDER BY nom");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM enseignant ORDER BY nom");
}
$enseignants = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Gestion des Enseignants</h1>
        <button class="btn-primary" onclick="openModal('addModal')">+ Ajouter enseignant</button>
    </div>
    
    <?php displayMessage(); ?>
    
    <div class="search-bar">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Rechercher par nom, prénom ou email..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-search">Rechercher</button>
            <?php if($search): ?>
                <a href="enseignants.php" class="btn-clear">Effacer</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($enseignants) > 0): ?>
                    <?php foreach($enseignants as $enseignant): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($enseignant['nom']); ?></td>
                        <td><?php echo htmlspecialchars($enseignant['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($enseignant['email']); ?></td>
                        <td class="actions">
                            <button class="btn-edit" onclick="editEnseignant(<?php echo $enseignant['id_enseignant']; ?>, '<?php echo htmlspecialchars($enseignant['nom']); ?>', '<?php echo htmlspecialchars($enseignant['prenom']); ?>', '<?php echo htmlspecialchars($enseignant['email']); ?>')">Modifier</button>
                            <button class="btn-delete" onclick="deleteEnseignant(<?php echo $enseignant['id_enseignant']; ?>, '<?php echo htmlspecialchars($enseignant['prenom'] . ' ' . $enseignant['nom']); ?>')">Supprimer</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Aucun enseignant trouvé</td>
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
            <h2>Ajouter un enseignant</h2>
            <span class="close" onclick="closeModal('addModal')">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Nom *</label>
                <input type="text" name="nom" required>
            </div>
            <div class="form-group">
                <label>Prénom *</label>
                <input type="text" name="prenom" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Mot de passe *</label>
                <input type="password" name="password" required>
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
            <h2>Modifier l'enseignant</h2>
            <span class="close" onclick="closeModal('editModal')">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label>Nom *</label>
                <input type="text" name="nom" id="edit_nom" required>
            </div>
            <div class="form-group">
                <label>Prénom *</label>
                <input type="text" name="prenom" id="edit_prenom" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" id="edit_email" required>
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
            <p>Êtes-vous sûr de vouloir supprimer l'enseignant <strong id="delete_name"></strong> ?</p>
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

function editEnseignant(id, nom, prenom, email) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nom').value = nom;
    document.getElementById('edit_prenom').value = prenom;
    document.getElementById('edit_email').value = email;
    openModal('editModal');
}

function deleteEnseignant(id, name) {
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
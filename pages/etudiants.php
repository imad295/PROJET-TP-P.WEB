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
                $matricule = trim($_POST['matricule']);
                $nom = trim($_POST['nom']);
                $prenom = trim($_POST['prenom']);
                $date_naissance = $_POST['date_naissance'];
                $email = trim($_POST['email']);
                
                $stmt = $pdo->prepare("INSERT INTO etudiant (matricule, nom, prenom, date_naissance, email) VALUES (?, ?, ?, ?, ?)");
                if($stmt->execute([$matricule, $nom, $prenom, $date_naissance, $email])) {
                    $_SESSION['message'] = "Étudiant ajouté avec succès";
                    $_SESSION['message_type'] = "success";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $matricule = trim($_POST['matricule']);
                $nom = trim($_POST['nom']);
                $prenom = trim($_POST['prenom']);
                $date_naissance = $_POST['date_naissance'];
                $email = trim($_POST['email']);
                
                $stmt = $pdo->prepare("UPDATE etudiant SET matricule=?, nom=?, prenom=?, date_naissance=?, email=? WHERE id_etudiant=?");
                if($stmt->execute([$matricule, $nom, $prenom, $date_naissance, $email, $id])) {
                    $_SESSION['message'] = "Étudiant modifié avec succès";
                    $_SESSION['message_type'] = "success";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM etudiant WHERE id_etudiant=?");
                if($stmt->execute([$id])) {
                    $_SESSION['message'] = "Étudiant supprimé avec succès";
                    $_SESSION['message_type'] = "success";
                }
                break;
        }
        redirect('etudiants.php');
    }
}

// Récupération des étudiants
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if($search) {
    $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE nom LIKE ? OR prenom LIKE ? OR matricule LIKE ? ORDER BY nom");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM etudiant ORDER BY nom");
}
$etudiants = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Gestion des Étudiants</h1>
        <button class="btn-primary" onclick="openModal('addModal')">+ Ajouter étudiant</button>
    </div>
    
    <?php displayMessage(); ?>
    
    <div class="search-bar">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Rechercher par nom, prénom ou matricule..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-search">Rechercher</button>
            <?php if($search): ?>
                <a href="etudiants.php" class="btn-clear">Effacer</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Date naissance</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($etudiants) > 0): ?>
                    <?php foreach($etudiants as $etudiant): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($etudiant['matricule']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['nom']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['prenom']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($etudiant['date_naissance'])); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['email']); ?></td>
                        <td class="actions">
                            <button class="btn-edit" onclick="editEtudiant(<?php echo $etudiant['id_etudiant']; ?>, '<?php echo htmlspecialchars($etudiant['matricule']); ?>', '<?php echo htmlspecialchars($etudiant['nom']); ?>', '<?php echo htmlspecialchars($etudiant['prenom']); ?>', '<?php echo $etudiant['date_naissance']; ?>', '<?php echo htmlspecialchars($etudiant['email']); ?>')">Modifier</button>
                            <button class="btn-delete" onclick="deleteEtudiant(<?php echo $etudiant['id_etudiant']; ?>, '<?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']); ?>')">Supprimer</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Aucun étudiant trouvé</td>
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
            <h2>Ajouter un étudiant</h2>
            <span class="close" onclick="closeModal('addModal')">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Matricule *</label>
                <input type="text" name="matricule" required>
            </div>
            <div class="form-group">
                <label>Nom *</label>
                <input type="text" name="nom" required>
            </div>
            <div class="form-group">
                <label>Prénom *</label>
                <input type="text" name="prenom" required>
            </div>
            <div class="form-group">
                <label>Date de naissance *</label>
                <input type="date" name="date_naissance" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
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
            <h2>Modifier l'étudiant</h2>
            <span class="close" onclick="closeModal('editModal')">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label>Matricule *</label>
                <input type="text" name="matricule" id="edit_matricule" required>
            </div>
            <div class="form-group">
                <label>Nom *</label>
                <input type="text" name="nom" id="edit_nom" required>
            </div>
            <div class="form-group">
                <label>Prénom *</label>
                <input type="text" name="prenom" id="edit_prenom" required>
            </div>
            <div class="form-group">
                <label>Date de naissance *</label>
                <input type="date" name="date_naissance" id="edit_date_naissance" required>
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
            <p>Êtes-vous sûr de vouloir supprimer l'étudiant <strong id="delete_name"></strong> ?</p>
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

function editEtudiant(id, matricule, nom, prenom, date_naissance, email) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_matricule').value = matricule;
    document.getElementById('edit_nom').value = nom;
    document.getElementById('edit_prenom').value = prenom;
    document.getElementById('edit_date_naissance').value = date_naissance;
    document.getElementById('edit_email').value = email;
    openModal('editModal');
}

function deleteEtudiant(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    openModal('deleteModal');
}

// Fermer les modals en cliquant en dehors
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
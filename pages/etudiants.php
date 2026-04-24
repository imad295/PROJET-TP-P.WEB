<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO etudiant (matricule, nom, prenom, date_naissance, id_groupe) VALUES (?,?,?,?,?)");
                $stmt->execute([$_POST['matricule'], $_POST['nom'], $_POST['prenom'], $_POST['date_naissance'], $_POST['id_groupe'] ?: null]);
                $_SESSION['message'] = "Étudiant ajouté avec succès";
                break;
            case 'edit':
                $stmt = $pdo->prepare("UPDATE etudiant SET matricule=?, nom=?, prenom=?, date_naissance=?, id_groupe=? WHERE id_etudiant=?");
                $stmt->execute([$_POST['matricule'], $_POST['nom'], $_POST['prenom'], $_POST['date_naissance'], $_POST['id_groupe'] ?: null, $_POST['id']]);
                $_SESSION['message'] = "Étudiant modifié avec succès";
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM etudiant WHERE id_etudiant=?");
                $stmt->execute([$_POST['id']]);
                $_SESSION['message'] = "Étudiant supprimé avec succès";
                break;
        }
        header('Location: etudiants.php');
        exit();
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if($search) {
    $stmt = $pdo->prepare("
        SELECT e.*, g.nom_groupe, s.nom_section, sp.nom_specialite, n.nom_niveau 
        FROM etudiant e
        LEFT JOIN `groupe` g ON e.id_groupe = g.id_groupe
        LEFT JOIN section s ON g.id_section = s.id_section
        LEFT JOIN specialite sp ON s.id_specialite = sp.id_specialite
        LEFT JOIN niveau n ON sp.id_niveau = n.id_niveau
        WHERE e.nom LIKE ? OR e.prenom LIKE ? OR e.matricule LIKE ? 
        ORDER BY e.nom
    ");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("
        SELECT e.*, g.nom_groupe, s.nom_section, sp.nom_specialite, n.nom_niveau 
        FROM etudiant e
        LEFT JOIN `groupe` g ON e.id_groupe = g.id_groupe
        LEFT JOIN section s ON g.id_section = s.id_section
        LEFT JOIN specialite sp ON s.id_specialite = sp.id_specialite
        LEFT JOIN niveau n ON sp.id_niveau = n.id_niveau
        ORDER BY e.nom
    ");
}
$etudiants = $stmt->fetchAll();

// Récupérer tous les groupes pour le formulaire
$stmt = $pdo->query("
    SELECT g.*, s.nom_section, sp.nom_specialite, n.nom_niveau 
    FROM `groupe` g
    JOIN section s ON g.id_section = s.id_section
    JOIN specialite sp ON s.id_specialite = sp.id_specialite
    JOIN niveau n ON sp.id_niveau = n.id_niveau
    ORDER BY n.ordre, sp.id_specialite, s.id_section, g.id_groupe
");
$groupes = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; width: 100%;">
    <div class="page-card">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-users"></i> Gestion des Étudiants</h1>
                <p>Ajouter, modifier ou supprimer des étudiants</p>
            </div>
            <button class="action-btn" onclick="openModal('addModal')" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <i class="fas fa-plus"></i> Ajouter un étudiant
            </button>
        </div>

        <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="🔍 Rechercher par nom, prénom ou matricule..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> Rechercher</button>
                <?php if($search): ?><a href="etudiants.php" class="btn-clear">Effacer</a><?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Date naissance</th>
                        <th>Niveau</th>
                        <th>Spécialité</th>
                        <th>Section</th>
                        <th>Groupe</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($etudiants as $e): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($e['matricule']); ?></strong></td>
                        <td><?php echo htmlspecialchars($e['nom']); ?></td>
                        <td><?php echo htmlspecialchars($e['prenom']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($e['date_naissance'])); ?></td>
                        <td><?php echo htmlspecialchars($e['nom_niveau'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($e['nom_specialite'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($e['nom_section'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($e['nom_groupe'] ?? '-'); ?></td>
                        <td class="actions">
                            <button class="btn-action btn-edit" onclick='editEtudiant(<?php echo json_encode($e); ?>)'>
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteEtudiant(<?php echo $e['id_etudiant']; ?>, '<?php echo addslashes($e['prenom'].' '.$e['nom']); ?>')">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(count($etudiants) == 0): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">
                            <i class="fas fa-info-circle"></i> Aucun étudiant trouvé
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user-plus"></i> Ajouter un étudiant</h2>
            <span class="close" onclick="closeModal('addModal')">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Matricule</label>
                <input type="text" name="matricule" required placeholder="Ex: 20260001">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" required>
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" required>
                </div>
            </div>
            <div class="form-group">
                <label>Date de naissance</label>
                <input type="date" name="date_naissance" required>
            </div>
            <div class="form-group">
                <label>Groupe</label>
                <select name="id_groupe">
                    <option value="">-- Sélectionner un groupe --</option>
                    <?php foreach($groupes as $g): ?>
                    <option value="<?php echo $g['id_groupe']; ?>">
                        <?php echo htmlspecialchars($g['nom_groupe'] . ' - ' . $g['nom_section'] . ' - ' . $g['nom_specialite'] . ' (' . $g['nom_niveau'] . ')'); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-action btn-edit">Enregistrer</button>
                <button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('addModal')">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-edit"></i> Modifier l'étudiant</h2>
            <span class="close" onclick="closeModal('editModal')">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label>Matricule</label>
                <input type="text" name="matricule" id="edit_matricule" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" id="edit_nom" required>
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" id="edit_prenom" required>
                </div>
            </div>
            <div class="form-group">
                <label>Date de naissance</label>
                <input type="date" name="date_naissance" id="edit_date" required>
            </div>
            <div class="form-group">
                <label>Groupe</label>
                <select name="id_groupe" id="edit_groupe">
                    <option value="">-- Sélectionner un groupe --</option>
                    <?php foreach($groupes as $g): ?>
                    <option value="<?php echo $g['id_groupe']; ?>">
                        <?php echo htmlspecialchars($g['nom_groupe'] . ' - ' . $g['nom_section'] . ' - ' . $g['nom_specialite'] . ' (' . $g['nom_niveau'] . ')'); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-action btn-edit">Modifier</button>
                <button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('editModal')">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-trash-alt"></i> Confirmer la suppression</h2>
            <span class="close" onclick="closeModal('deleteModal')">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="delete_id">
            <p>Êtes-vous sûr de vouloir supprimer l'étudiant <strong id="delete_name"></strong> ?</p>
            <div class="form-actions">
                <button type="submit" class="btn-action btn-delete">Supprimer</button>
                <button type="button" class="btn-action" style="background:#6c757d;" onclick="closeModal('deleteModal')">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { 
    document.getElementById(id).style.display = 'block'; 
}
function closeModal(id) { 
    document.getElementById(id).style.display = 'none'; 
}
function editEtudiant(e) {
    document.getElementById('edit_id').value = e.id_etudiant;
    document.getElementById('edit_matricule').value = e.matricule;
    document.getElementById('edit_nom').value = e.nom;
    document.getElementById('edit_prenom').value = e.prenom;
    document.getElementById('edit_date').value = e.date_naissance;
    if(e.id_groupe) document.getElementById('edit_groupe').value = e.id_groupe;
    openModal('editModal');
}
function deleteEtudiant(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').innerHTML = name;
    openModal('deleteModal');
}
window.onclick = function(e) { 
    if(e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
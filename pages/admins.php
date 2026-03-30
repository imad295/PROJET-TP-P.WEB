<?php
require_once '../includes/config.php';

// Vérifier que l'utilisateur est connecté et est admin
if(!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

$message = '';
$error = '';

// Traitement des actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $login = trim($_POST['login']);
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                
                // Validation
                if(empty($login) || empty($password)) {
                    $error = "Veuillez remplir tous les champs";
                } elseif($password != $confirm_password) {
                    $error = "Les mots de passe ne correspondent pas";
                } elseif(strlen($password) < 6) {
                    $error = "Le mot de passe doit contenir au moins 6 caractères";
                } else {
                    // Vérifier si le login existe déjà
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM administrateur WHERE login = ?");
                    $stmt->execute([$login]);
                    if($stmt->fetchColumn() > 0) {
                        $error = "Ce nom d'utilisateur existe déjà";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO administrateur (login, mot_de_passe) VALUES (?, ?)");
                        if($stmt->execute([$login, $hashed_password])) {
                            $message = "✅ Administrateur <b>$login</b> ajouté avec succès !";
                        } else {
                            $error = "❌ Erreur lors de l'ajout";
                        }
                    }
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $login = trim($_POST['login']);
                
                if(empty($login)) {
                    $error = "Le nom d'utilisateur est obligatoire";
                } else {
                    // Vérifier si le login existe déjà pour un autre admin
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM administrateur WHERE login = ? AND id_admin != ?");
                    $stmt->execute([$login, $id]);
                    if($stmt->fetchColumn() > 0) {
                        $error = "Ce nom d'utilisateur existe déjà";
                    } else {
                        $stmt = $pdo->prepare("UPDATE administrateur SET login = ? WHERE id_admin = ?");
                        if($stmt->execute([$login, $id])) {
                            $message = "✅ Administrateur modifié avec succès";
                        } else {
                            $error = "❌ Erreur lors de la modification";
                        }
                    }
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                
                $stmt = $pdo->prepare("DELETE FROM administrateur WHERE id_admin = ?");
                if($stmt->execute([$id])) {
                    $message = "✅ Administrateur supprimé avec succès";
                } else {
                    $error = "❌ Erreur lors de la suppression";
                }
                break;
                
            case 'change_password':
                $id = $_POST['id'];
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                
                if(empty($password)) {
                    $error = "Le mot de passe est obligatoire";
                } elseif($password != $confirm_password) {
                    $error = "Les mots de passe ne correspondent pas";
                } elseif(strlen($password) < 6) {
                    $error = "Le mot de passe doit contenir au moins 6 caractères";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE administrateur SET mot_de_passe = ? WHERE id_admin = ?");
                    if($stmt->execute([$hashed_password, $id])) {
                        $message = "✅ Mot de passe modifié avec succès";
                    } else {
                        $error = "❌ Erreur lors de la modification du mot de passe";
                    }
                }
                break;
        }
    }
}

// Récupérer la liste des administrateurs
$stmt = $pdo->query("SELECT * FROM administrateur ORDER BY id_admin");
$admins = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Administrateurs - USTHB</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin-bottom: 0;
        }
        
        .header h1 {
            margin-bottom: 5px;
        }
        
        .header p {
            opacity: 0.9;
        }
        
        /* Navigation */
        .nav-bar {
            background: white;
            padding: 12px 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .nav-links a {
            color: #555;
            text-decoration: none;
            padding: 8px 15px;
            margin: 0 5px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .nav-links a:hover {
            background: #f0f0f0;
            color: #1e3c72;
        }
        
        .user-info {
            background: #f0f0f0;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .logout-btn {
            background: #ff6b35;
            color: white;
            padding: 5px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 10px;
        }
        
        /* Main Content */
        .main-content {
            background: white;
            padding: 30px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-header h2 {
            color: #333;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: transform 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        
        .btn-edit {
            background: #4caf50;
            color: white;
            padding: 5px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin: 2px;
        }
        
        .btn-password {
            background: #ff9800;
            color: white;
            padding: 5px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin: 2px;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
            padding: 5px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin: 2px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .actions {
            white-space: nowrap;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            width: 90%;
            max-width: 450px;
            border-radius: 10px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-header h3 {
            margin: 0;
        }
        
        .close {
            font-size: 24px;
            cursor: pointer;
            color: white;
        }
        
        .close:hover {
            color: #ff6b35;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #1e3c72;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .btn-secondary {
            background: #9e9e9e;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .small-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .badge-admin {
            background: #1e3c72;
            color: white;
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 12px;
        }
        
        footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        
        @media (max-width: 768px) {
            .nav-bar {
                flex-direction: column;
                text-align: center;
            }
            
            .page-header {
                flex-direction: column;
                text-align: center;
            }
            
            th, td {
                padding: 8px;
                font-size: 12px;
            }
            
            .btn-edit, .btn-password, .btn-delete {
                padding: 3px 8px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👑 Gestion des Administrateurs</h1>
            <p>Ajouter, modifier et gérer les comptes administrateurs</p>
        </div>
        
        <div class="nav-bar">
            <div class="nav-links">
                <a href="dashboard_admin.php">📊 Tableau de bord</a>
                <a href="etudiants.php">👨‍🎓 Étudiants</a>
                <a href="enseignants.php">👨‍🏫 Enseignants</a>
                <a href="admins.php" style="background: #1e3c72; color: white;">👑 Administrateurs</a>
                <a href="modules.php">📚 Modules</a>
                <a href="notes.php">📝 Notes</a>
                <a href="statistiques.php">📈 Statistiques</a>
            </div>
            <div class="user-info">
                👋 <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                <a href="logout.php" class="logout-btn">Déconnexion</a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="page-header">
                <h2>📋 Liste des administrateurs</h2>
                <button class="btn-primary" onclick="openModal('addModal')">➕ Ajouter un administrateur</button>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($admins) > 0): ?>
                            <?php foreach($admins as $admin): ?>
                            <tr>
                                <td><?php echo $admin['id_admin']; ?></td>
                                <td><b><?php echo htmlspecialchars($admin['login']); ?></b></td>
                                <td><span class="badge-admin">👑 Administrateur</span></td>
                                <td class="actions">
                                    <button class="btn-edit" onclick="editAdmin(<?php echo $admin['id_admin']; ?>, '<?php echo htmlspecialchars($admin['login']); ?>')">✏️ Modifier</button>
                                    <button class="btn-password" onclick="changePassword(<?php echo $admin['id_admin']; ?>, '<?php echo htmlspecialchars($admin['login']); ?>')">🔑 Changer mot de passe</button>
                                    <button class="btn-delete" onclick="deleteAdmin(<?php echo $admin['id_admin']; ?>, '<?php echo htmlspecialchars($admin['login']); ?>')">🗑️ Supprimer</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Aucun administrateur trouvé</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> USTHB - Faculté d'Informatique | Gestion de Scolarité</p>
        </footer>
    </div>
    
    <!-- Modal Ajout -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>➕ Ajouter un administrateur</h3>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Nom d'utilisateur *</label>
                        <input type="text" name="login" required placeholder="ex: admin, gestionnaire, superadmin">
                    </div>
                    <div class="form-group">
                        <label>Mot de passe *</label>
                        <input type="password" name="password" required placeholder="Minimum 6 caractères">
                        <div class="small-text">Le mot de passe doit contenir au moins 6 caractères</div>
                    </div>
                    <div class="form-group">
                        <label>Confirmer le mot de passe *</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">✅ Ajouter</button>
                        <button type="button" class="btn-secondary" onclick="closeModal('addModal')">Annuler</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Modification -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Modifier l'administrateur</h3>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Nom d'utilisateur *</label>
                        <input type="text" name="login" id="edit_login" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">💾 Modifier</button>
                        <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Annuler</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Changer mot de passe -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🔑 Changer le mot de passe</h3>
                <span class="close" onclick="closeModal('passwordModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="id" id="password_id">
                    <div class="form-group">
                        <label>Administrateur : <b id="password_login"></b></label>
                    </div>
                    <div class="form-group">
                        <label>Nouveau mot de passe *</label>
                        <input type="password" name="password" required placeholder="Minimum 6 caractères">
                    </div>
                    <div class="form-group">
                        <label>Confirmer le nouveau mot de passe *</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">🔐 Changer</button>
                        <button type="button" class="btn-secondary" onclick="closeModal('passwordModal')">Annuler</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Suppression -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🗑️ Confirmer la suppression</h3>
                <span class="close" onclick="closeModal('deleteModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Êtes-vous sûr de vouloir supprimer l'administrateur <strong id="delete_name"></strong> ?</p>
                    <p style="color: #f44336;"><b>⚠️ Cette action est irréversible !</b></p>
                    <div class="form-actions">
                        <button type="submit" class="btn-delete">🗑️ Supprimer</button>
                        <button type="button" class="btn-secondary" onclick="closeModal('deleteModal')">Annuler</button>
                    </div>
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
        
        function editAdmin(id, login) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_login').value = login;
            openModal('editModal');
        }
        
        function changePassword(id, login) {
            document.getElementById('password_id').value = id;
            document.getElementById('password_login').textContent = login;
            openModal('passwordModal');
        }
        
        function deleteAdmin(id, name) {
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
</body>
</html>
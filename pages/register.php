<?php
require_once '../includes/config.php';

// Si déjà connecté, rediriger
if(isLoggedIn()) {
    switch($_SESSION['role']) {
        case 'admin': redirect('dashboard_admin.php'); break;
        case 'enseignant': redirect('dashboard_enseignant.php'); break;
        case 'etudiant': redirect('dashboard_etudiant.php'); break;
    }
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $date_naissance = isset($_POST['date_naissance']) ? $_POST['date_naissance'] : null;
    
    // Validation
    if(empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires";
    } elseif($password != $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } elseif(strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide";
    } elseif($role == 'etudiant' && empty($date_naissance)) {
        $error = "La date de naissance est obligatoire pour les étudiants";
    } else {
        // Vérifier si l'email existe déjà
        if($role == 'etudiant') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM etudiant WHERE email = ?");
            $stmt->execute([$email]);
            $email_exists = $stmt->fetchColumn();
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM enseignant WHERE email = ?");
            $stmt->execute([$email]);
            $email_exists = $stmt->fetchColumn();
        }
        
        if($email_exists > 0) {
            $error = "Cet email est déjà utilisé";
        } else {
            // Hash du mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            if($role == 'etudiant') {
                // Générer un matricule unique (année + numéro aléatoire)
                $annee = date('Y');
                $num_aleatoire = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $matricule = $annee . $num_aleatoire;
                
                // Vérifier que le matricule n'existe pas déjà
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM etudiant WHERE matricule = ?");
                $stmt->execute([$matricule]);
                while($stmt->fetchColumn() > 0) {
                    $num_aleatoire = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                    $matricule = $annee . $num_aleatoire;
                    $stmt->execute([$matricule]);
                }
                
                $stmt = $pdo->prepare("INSERT INTO etudiant (matricule, nom, prenom, email, date_naissance) VALUES (?, ?, ?, ?, ?)");
                if($stmt->execute([$matricule, $nom, $prenom, $email, $date_naissance])) {
                    $success = "Compte étudiant créé avec succès !";
                    $success .= "<br>Votre matricule est : <b>" . $matricule . "</b>";
                    $success .= "<br>Votre mot de passe par défaut est votre date de naissance au format AAAAMMJJ (ex: 20000101)";
                } else {
                    $error = "Erreur lors de la création du compte";
                }
            } else {
                // Inscription enseignant
                $stmt = $pdo->prepare("INSERT INTO enseignant (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
                if($stmt->execute([$nom, $prenom, $email, $hashed_password])) {
                    $success = "Compte enseignant créé avec succès !";
                    $success .= "<br>Vous pouvez maintenant vous connecter avec votre email et votre mot de passe.";
                } else {
                    $error = "Erreur lors de la création du compte";
                }
            }
        }
    }
}

include '../includes/header.php';
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr valign="top">
        <td align="center" style="padding: 50px;">
            <table width="500" border="0" cellpadding="0" cellspacing="0" bgcolor="white" style="border: 1px solid #ddd; border-radius: 5px;">
                <!-- En-tête -->
                <tr bgcolor="#1e3c72">
                    <td align="center" style="padding: 20px;">
                        <font color="white" size="4"><b>📝 Créer un compte</b></font><br>
                        <font color="white" size="2">Inscrivez-vous pour accéder à la plateforme</font>
                    </td>
                </tr>
                
                <!-- Corps -->
                <tr>
                    <td style="padding: 30px;">
                        
                        <?php if($error): ?>
                        <table width="100%" bgcolor="#ffebee" style="border: 1px solid #f44336; border-radius: 3px; margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 10px;">
                                    <font color="#f44336"><b>❌ Erreur :</b> <?php echo $error; ?></font>
                                </td>
                            </tr>
                        </table>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                        <table width="100%" bgcolor="#e8f5e9" style="border: 1px solid #4caf50; border-radius: 3px; margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 10px;">
                                    <font color="#4caf50"><b>✅ Succès :</b> <?php echo $success; ?></font>
                                </td>
                            </tr>
                        </table>
                        
                        <div align="center" style="margin-top: 20px;">
                            <a href="login.php" style="background-color: #1e3c72; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">🔐 Se connecter</a>
                        </div>
                        
                        <?php else: ?>
                        
                        <form method="POST" action="" onsubmit="return validateForm()">
                            <!-- Type de compte -->
                            <table width="100%" border="0" cellpadding="8">
                                <tr>
                                    <td width="40%"><b>Type de compte *</b></td>
                                    <td>
                                        <select name="role" id="role" style="width: 100%; padding: 8px; border: 1px solid #ddd;" onchange="toggleDateNaissance()">
                                            <option value="etudiant">👨‍🎓 Étudiant</option>
                                            <option value="enseignant">👨‍🏫 Enseignant</option>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><b>Nom *</b></td>
                                    <td><input type="text" name="nom" required style="width: 100%; padding: 8px; border: 1px solid #ddd;"></td>
                                </tr>
                                
                                <tr>
                                    <td><b>Prénom *</b></td>
                                    <td><input type="text" name="prenom" required style="width: 100%; padding: 8px; border: 1px solid #ddd;"></td>
                                </tr>
                                
                                <tr id="date_naissance_row">
                                    <td><b>Date de naissance *</b></td>
                                    <td><input type="date" name="date_naissance" style="width: 100%; padding: 8px; border: 1px solid #ddd;"></td>
                                </tr>
                                
                                <tr>
                                    <td><b>Email *</b></td>
                                    <td><input type="email" name="email" required style="width: 100%; padding: 8px; border: 1px solid #ddd;"></td>
                                </tr>
                                
                                <tr>
                                    <td><b>Mot de passe *</b></td>
                                    <td><input type="password" name="password" id="password" required style="width: 100%; padding: 8px; border: 1px solid #ddd;"></td>
                                </tr>
                                
                                <tr>
                                    <td><b>Confirmer mot de passe *</b></td>
                                    <td><input type="password" name="confirm_password" id="confirm_password" required style="width: 100%; padding: 8px; border: 1px solid #ddd;"></td>
                                </tr>
                                
                                <tr>
                                    <td colspan="2" align="center" style="padding-top: 20px;">
                                        <button type="submit" style="background-color: #4caf50; color: white; padding: 10px 30px; border: none; cursor: pointer; border-radius: 3px;">✅ S'inscrire</button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                        
                        <hr style="margin: 20px 0;">
                        
                        <div align="center">
                            <font size="2">Déjà un compte ? <a href="login.php" style="color: #1e3c72;">Se connecter</a></font>
                        </div>
                        
                        <?php endif; ?>
                        
                    </td>
                </tr>
                
                <!-- Pied -->
                <tr bgcolor="#f5f5f5">
                    <td align="center" style="padding: 15px;">
                        <font size="1" color="#666">Les champs marqués d'un * sont obligatoires</font>
                    </td>
                </tr>
            </table>
            
            <!-- Informations supplémentaires -->
            <table width="500" border="0" cellpadding="10" cellspacing="0" style="margin-top: 20px;">
                <tr>
                    <td bgcolor="#fff3e0" style="border: 1px solid #ff9800; border-radius: 3px;">
                        <font size="2">
                            <b>📌 Informations importantes :</b><br>
                            • Pour les étudiants, le mot de passe par défaut est votre date de naissance (format AAAAMMJJ)<br>
                            • Vous pourrez modifier votre mot de passe après la première connexion<br>
                            • L'administrateur peut désactiver votre compte en cas de non-respect des règles
                        </font>
                    </td>
                </tr>
            </table>
            
        </td>
    </tr>
</table>

<script>
function toggleDateNaissance() {
    var role = document.getElementById('role').value;
    var dateRow = document.getElementById('date_naissance_row');
    var dateInput = dateRow.querySelector('input');
    
    if(role == 'etudiant') {
        dateRow.style.display = 'table-row';
        dateInput.required = true;
    } else {
        dateRow.style.display = 'none';
        dateInput.required = false;
    }
}

function validateForm() {
    var password = document.getElementById('password').value;
    var confirm = document.getElementById('confirm_password').value;
    var role = document.getElementById('role').value;
    var email = document.querySelector('input[name="email"]').value;
    
    if(password != confirm) {
        alert('Les mots de passe ne correspondent pas !');
        return false;
    }
    
    if(password.length < 6) {
        alert('Le mot de passe doit contenir au moins 6 caractères !');
        return false;
    }
    
    // Validation simple de l'email
    var emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
    if(!emailRegex.test(email)) {
        alert('Veuillez entrer un email valide !');
        return false;
    }
    
    return true;
}

// Initialiser l'affichage
toggleDateNaissance();
</script>

<?php include '../includes/footer.php'; ?>
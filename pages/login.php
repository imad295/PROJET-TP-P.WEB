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

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    if(empty($login) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        // Vérifier dans la table administrateur
        $stmt = $pdo->prepare("SELECT * FROM administrateur WHERE login = ?");
        $stmt->execute([$login]);
        $admin = $stmt->fetch();
        
        if($admin && password_verify($password, $admin['mot_de_passe'])) {
            $_SESSION['user_id'] = $admin['id_admin'];
            $_SESSION['user_name'] = $admin['login'];
            $_SESSION['role'] = 'admin';
            redirect('dashboard_admin.php');
        }
        
        // Vérifier dans la table enseignant
        $stmt = $pdo->prepare("SELECT * FROM enseignant WHERE email = ?");
        $stmt->execute([$login]);
        $enseignant = $stmt->fetch();
        
        if($enseignant && password_verify($password, $enseignant['mot_de_passe'])) {
            $_SESSION['user_id'] = $enseignant['id_enseignant'];
            $_SESSION['user_name'] = $enseignant['prenom'] . ' ' . $enseignant['nom'];
            $_SESSION['role'] = 'enseignant';
            redirect('dashboard_enseignant.php');
        }
        
        // Vérifier dans la table etudiant
        $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE email = ? OR matricule = ?");
        $stmt->execute([$login, $login]);
        $etudiant = $stmt->fetch();
        
        if($etudiant) {
            $default_password = date('Ymd', strtotime($etudiant['date_naissance']));
            if($password == $default_password || ($etudiant['mot_de_passe'] ?? false && password_verify($password, $etudiant['mot_de_passe'] ?? ''))) {
                $_SESSION['user_id'] = $etudiant['id_etudiant'];
                $_SESSION['user_name'] = $etudiant['prenom'] . ' ' . $etudiant['nom'];
                $_SESSION['role'] = 'etudiant';
                redirect('dashboard_etudiant.php');
            }
        }
        
        $error = "Email ou mot de passe incorrect";
    }
}

include '../includes/header.php';
?>

<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle" style="padding: 50px 20px;">
            
            <!-- Boîte de connexion -->
            <table width="450" border="0" cellpadding="0" cellspacing="0" bgcolor="white" style="border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                
                <!-- En-tête avec dégradé -->
                <tr>
                    <td align="center" style="background: linear-gradient(135deg, #1e3c72, #2a5298); border-radius: 10px 10px 0 0; padding: 30px;">
                        <font color="white" size="5"><b>🔐 Connexion</b></font><br>
                        <font color="white" size="2">Accédez à votre espace personnel</font>
                    </td>
                </tr>
                
                <!-- Corps du formulaire -->
                <tr>
                    <td style="padding: 40px 35px;">
                        
                        <!-- Message d'erreur -->
                        <?php if($error): ?>
                        <table width="100%" bgcolor="#ffebee" style="border-left: 4px solid #f44336; border-radius: 3px; margin-bottom: 25px;">
                            <tr>
                                <td style="padding: 12px;">
                                    <font color="#c62828" size="2"><b>❌</b> <?php echo $error; ?></font>
                                </td>
                            </tr>
                        </table>
                        <?php endif; ?>
                        
                        <!-- Formulaire -->
                        <form method="POST" action="">
                            <table width="100%" border="0" cellpadding="8">
                                <tr>
                                    <td>
                                        <font size="2" color="#333"><b>📧 Email ou Matricule</b></font><br>
                                        <input type="text" name="login" required 
                                               style="width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;"
                                               placeholder="exemple@usthb.dz ou 20260001">
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td style="padding-top: 20px;">
                                        <font size="2" color="#333"><b>🔒 Mot de passe</b></font><br>
                                        <input type="password" name="password" required 
                                               style="width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;"
                                               placeholder="••••••••">
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td align="center" style="padding-top: 30px;">
                                        <button type="submit" 
                                                style="background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; padding: 12px 40px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; width: 100%; font-weight: bold;">
                                            Se connecter
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                        
                        <!-- Séparateur -->
                        <hr style="margin: 25px 0; border: none; border-top: 1px solid #eee;">
                        
                        <!-- Lien inscription -->
                        <div align="center">
                            <font size="2" color="#666">
                                Vous n'avez pas de compte ?
                                <a href="register.php" style="color: #1e3c72; text-decoration: none; font-weight: bold;">Créer un compte</a>
                            </font>
                        </div>
                        
                    </td>
                </tr>
                
                <!-- Pied de page -->
                <tr bgcolor="#f9f9f9">
                    <td align="center" style="padding: 15px; border-radius: 0 0 10px 10px;">
                        <font size="1" color="#888">
                            USTHB - Faculté d'Informatique<br>
                            Gestion de Scolarité
                        </font>
                    </td>
                </tr>
                
            </table>
            
        </td>
    </tr>
</table>

<?php include '../includes/footer.php'; ?>
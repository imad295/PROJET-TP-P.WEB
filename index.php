<?php
require_once 'includes/config.php';

// Rediriger vers le dashboard si déjà connecté
if(isLoggedIn()) {
    switch($_SESSION['role']) {
        case 'admin':
            header('Location: pages/dashboard_admin.php');
            exit();
        case 'enseignant':
            header('Location: pages/dashboard_enseignant.php');
            exit();
        case 'etudiant':
            header('Location: pages/dashboard_etudiant.php');
            exit();
    }
}

// Récupérer quelques statistiques pour affichage
try {
    $pdo = new PDO("mysql:host=localhost;dbname=gestion_scolarite", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM etudiant");
    $total_etudiants = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM enseignant");
    $total_enseignants = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM module");
    $total_modules = $stmt->fetch()['total'];
} catch(PDOException $e) {
    $total_etudiants = 0;
    $total_enseignants = 0;
    $total_modules = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USTHB - Gestion Scolarité</title>
</head>
<body>
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#1e3c72">
        <tr>
            <td align="center" style="padding: 30px;">
                <font color="white" size="6"><b>🏛️ USTHB</b></font><br>
                <font color="white" size="3">Faculté d'Informatique</font><br>
                <font color="white" size="2">Gestion de Scolarité</font>
            </td>
        </tr>
    </table>
    
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f0f0f0">
        <tr>
            <td align="center" style="padding: 12px;">
                <a href="index.php" style="margin: 0 20px; color: #1e3c72; text-decoration: none; font-weight: bold;">🏠 Accueil</a>
                <a href="pages/login.php" style="margin: 0 20px; color: #1e3c72; text-decoration: none;">🔐 Connexion</a>
                <a href="pages/register.php" style="margin: 0 20px; color: #1e3c72; text-decoration: none;">📝 Inscription</a>
            </td>
        </tr>
    </table>
    
    <!-- Bannière -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#4a90e2">
        <tr>
            <td align="center" style="padding: 60px 20px;">
                <font color="white" size="5"><b>Bienvenue sur la plateforme de gestion de scolarité</b></font><br><br>
                <font color="white" size="3">Gérez facilement les étudiants, les notes et les relevés</font><br><br>
                <a href="pages/login.php" style="background-color: white; color: #4a90e2; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">🔑 Se connecter</a>
                &nbsp;&nbsp;
                <a href="pages/register.php" style="background-color: transparent; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; border: 2px solid white; font-weight: bold;">📝 Créer un compte</a>
            </td>
        </tr>
    </table>
    
    <!-- Statistiques -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="white">
        <tr>
            <td align="center" style="padding: 40px;">
                <font size="4"><b>📊 Chiffres clés</b></font><br><br>
                <table width="80%" border="0" align="center">
                    <tr align="center">
                        <td width="33%" style="padding: 20px;" bgcolor="#f9f9f9">
                            <font size="6"><b><?php echo $total_etudiants; ?></b></font><br>
                            <font size="2">Étudiants inscrits</font>
                        </td>
                        <td width="33%" style="padding: 20px;" bgcolor="#f9f9f9">
                            <font size="6"><b><?php echo $total_enseignants; ?></b></font><br>
                            <font size="2">Enseignants</font>
                        </td>
                        <td width="33%" style="padding: 20px;" bgcolor="#f9f9f9">
                            <font size="6"><b><?php echo $total_modules; ?></b></font><br>
                            <font size="2">Modules</font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <!-- Fonctionnalités -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f9f9f9">
        <tr>
            <td align="center" style="padding: 40px;">
                <font size="4"><b>✨ Fonctionnalités principales</b></font><br><br>
                <table width="85%" border="0">
                    <tr valign="top">
                        <td width="33%" style="padding: 15px;">
                            <table width="100%" bgcolor="white" style="border: 1px solid #ddd; border-radius: 5px;">
                                <tr><td align="center" style="padding: 20px;">
                                    <font size="4"><b>👨‍🎓 Gestion des étudiants</b></font><br>
                                    <font size="2">Ajout, modification, suppression et consultation des étudiants</font>
                                </td></tr>
                            </table>
                        </td>
                        <td width="33%" style="padding: 15px;">
                            <table width="100%" bgcolor="white" style="border: 1px solid #ddd; border-radius: 5px;">
                                <tr><td align="center" style="padding: 20px;">
                                    <font size="4"><b>📝 Gestion des notes</b></font><br>
                                    <font size="2">Saisie des notes, calcul automatique des moyennes par module</font>
                                </td></tr>
                            </table>
                        </td>
                        <td width="33%" style="padding: 15px;">
                            <table width="100%" bgcolor="white" style="border: 1px solid #ddd; border-radius: 5px;">
                                <tr><td align="center" style="padding: 20px;">
                                    <font size="4"><b>📊 Statistiques</b></font><br>
                                    <font size="2">Génération de rapports et relevés de notes détaillés</font>
                                </td></tr>
                            </table>
                        </td>
                    </tr>
                    <tr valign="top">
                        <td style="padding: 15px;">
                            <table width="100%" bgcolor="white" style="border: 1px solid #ddd; border-radius: 5px;">
                                <tr><td align="center" style="padding: 20px;">
                                    <font size="4"><b>👨‍🏫 Gestion des enseignants</b></font><br>
                                    <font size="2">Gestion complète des professeurs et leurs modules</font>
                                </td></tr>
                            </table>
                        </td>
                        <td style="padding: 15px;">
                            <table width="100%" bgcolor="white" style="border: 1px solid #ddd; border-radius: 5px;">
                                <tr><td align="center" style="padding: 20px;">
                                    <font size="4"><b>🔐 Authentification</b></font><br>
                                    <font size="2">Accès sécurisé selon le rôle (Admin, Enseignant, Étudiant)</font>
                                </td></tr>
                            </table>
                        </td>
                        <td style="padding: 15px;">
                            <table width="100%" bgcolor="white" style="border: 1px solid #ddd; border-radius: 5px;">
                                <tr><td align="center" style="padding: 20px;">
                                    <font size="4"><b>📄 Relevé de notes</b></font><br>
                                    <font size="2">Consultation des relevés et calcul de la moyenne générale</font>
                                </td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <!-- Comment accéder -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="white">
        <tr>
            <td align="center" style="padding: 40px;">
                <font size="4"><b>🔑 Comment accéder ?</b></font><br><br>
                <table width="60%" border="0" bgcolor="#f0f0f0" style="border-radius: 5px;">
                    <tr>
                        <td style="padding: 20px;">
                            <b>👨‍💼 Administrateur :</b><br>
                            Login: admin<br>
                            Mot de passe: admin123<br><br>
                            <b>👨‍🏫 Enseignant :</b><br>
                            Email: mohamed.benali@usthb.dz<br>
                            Mot de passe: enseignant123<br><br>
                            <b>👨‍🎓 Étudiant :</b><br>
                            Email: ahmed.saidi@usthb.dz<br>
                            Mot de passe: 20000115 (date de naissance)
                        </td>
                    </tr>
                </table>
                <br>
                <a href="pages/login.php" style="background-color: #1e3c72; color: white; padding: 10px 30px; text-decoration: none; border-radius: 5px;">Se connecter</a>
            </td>
        </tr>
    </table>
    
    <!-- Pied de page -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#333">
        <tr>
            <td align="center" style="padding: 20px;">
                <font color="white" size="2">
                    &copy; <?php echo date('Y'); ?> USTHB - Faculté d'Informatique<br>
                    Projet Programmation Web | PHP & MySQL | Groupe 38<br>
                    <br>
                    <a href="index.php" style="color: white; text-decoration: none;">Accueil</a> | 
                    <a href="pages/login.php" style="color: white; text-decoration: none;">Connexion</a> | 
                    <a href="pages/register.php" style="color: white; text-decoration: none;">Inscription</a>
                </font>
            </td>
        </tr>
    </table>
</body>
</html>
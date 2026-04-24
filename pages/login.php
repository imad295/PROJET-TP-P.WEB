<?php
require_once '../includes/config.php';

$stmt = $pdo->query("SELECT COUNT(*) FROM administrateur");
$admin_count = $stmt->fetchColumn();

if($admin_count == 0) {
    header('Location: ../create_first_admin.php');
    exit();
}

if(isLoggedIn()) {
    switch($_SESSION['role']) {
        case 'admin': header('Location: dashboard_admin.php'); exit();
        case 'enseignant': header('Location: dashboard_enseignant.php'); exit();
        case 'etudiant': header('Location: dashboard_etudiant.php'); exit();
    }
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    if(empty($login) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        // Vérifier Administrateur
        $stmt = $pdo->prepare("SELECT * FROM administrateur WHERE login = ?");
        $stmt->execute([$login]);
        $admin = $stmt->fetch();
        
        if($admin && password_verify($password, $admin['mot_de_passe'])) {
            $_SESSION['user_id'] = $admin['id_admin'];
            $_SESSION['user_name'] = $admin['login'];
            $_SESSION['role'] = 'admin';
            header('Location: dashboard_admin.php');
            exit();
        }
        
        // Vérifier Enseignant (connexion par email)
        $stmt = $pdo->prepare("SELECT * FROM enseignant WHERE email = ?");
        $stmt->execute([$login]);
        $enseignant = $stmt->fetch();
        
        if($enseignant && password_verify($password, $enseignant['mot_de_passe'])) {
            $_SESSION['user_id'] = $enseignant['id_enseignant'];
            $_SESSION['user_name'] = $enseignant['prenom'] . ' ' . $enseignant['nom'];
            $_SESSION['role'] = 'enseignant';
            header('Location: dashboard_enseignant.php');
            exit();
        }
        
        // Vérifier Étudiant (connexion par matricule)
        $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE matricule = ?");
        $stmt->execute([$login]);
        $etudiant = $stmt->fetch();
        
        if($etudiant) {
            $default_password = date('Ymd', strtotime($etudiant['date_naissance']));
            if($password == $default_password) {
                $_SESSION['user_id'] = $etudiant['id_etudiant'];
                $_SESSION['user_name'] = $etudiant['prenom'] . ' ' . $etudiant['nom'];
                $_SESSION['role'] = 'etudiant';
                header('Location: dashboard_etudiant.php');
                exit();
            }
        }
        
        $error = "Matricule/Email ou mot de passe incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - USTHB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0f0f, #1a1a2e);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-card {
            background: rgba(255,255,255,0.05);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(99,102,241,0.3);
            backdrop-filter: blur(10px);
        }
        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-header .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 15px;
            color: white;
        }
        .auth-header h1 { font-size: 28px; margin-bottom: 8px; color: white; }
        .auth-header p { color: #a1a1aa; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #d4d4d8;
            font-size: 13px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 14px;
        }
        .form-group input:focus { outline: none; border-color: #6366f1; }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 30px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(99,102,241,0.3); }
        .auth-footer { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .auth-footer a { color: #a5b4fc; text-decoration: none; font-size: 13px; }
        .auth-footer a:hover { color: #6366f1; }
        .alert {
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .alert-error { background: rgba(239,68,68,0.2); color: #ef4444; border-left: 3px solid #ef4444; }
        .info-text {
            text-align: center;
            margin-top: 15px;
            font-size: 11px;
            color: #71717a;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
            <h1>Connexion</h1>
            <p>Accédez à votre espace personnel</p>
        </div>
        <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Email ou Matricule</label>
                <input type="text" name="login" required placeholder="exemple@usthb.dz ou 20260001">
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-login">Se connecter</button>
        </form>
        <div class="info-text">
            <small>📌 Étudiants : utilisez votre matricule et votre date de naissance (AAAAMMJJ)</small>
        </div>
        <div class="auth-footer">
            <p><a href="../index.php">← Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>
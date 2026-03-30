<?php
require_once 'includes/config.php';

$message = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if(empty($login) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } elseif($password != $confirm) {
        $error = "Les mots de passe ne correspondent pas";
    } elseif(strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères";
    } else {
        // Vérifier si un admin existe déjà
        $stmt = $pdo->query("SELECT COUNT(*) FROM administrateur");
        if($stmt->fetchColumn() > 0) {
            $error = "Un administrateur existe déjà ! Connectez-vous avec vos identifiants.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO administrateur (login, mot_de_passe) VALUES (?, ?)");
            if($stmt->execute([$login, $hashed])) {
                $message = "✅ Administrateur créé avec succès !<br>";
                $message .= "Login: <b>$login</b><br>";
                $message .= "Mot de passe: <b>$password</b><br>";
                $message .= "<a href='pages/login.php'>Cliquez ici pour vous connecter</a>";
            } else {
                $error = "Erreur lors de la création";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer le premier administrateur</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 450px;
            width: 100%;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .body {
            padding: 30px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30,60,114,0.1);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .info a {
            color: #1e3c72;
            text-decoration: none;
        }
        
        .info a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>👑 Création du premier administrateur</h1>
                <p>Configurez votre compte administrateur</p>
            </div>
            
            <div class="body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if($error): ?>
                    <div class="alert alert-error">❌ <?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php
                // Vérifier si un admin existe déjà
                $stmt = $pdo->query("SELECT COUNT(*) FROM administrateur");
                $admin_exists = $stmt->fetchColumn();
                
                if($admin_exists > 0):
                ?>
                    <div class="alert alert-error">
                        ⚠️ Un administrateur existe déjà dans la base de données !<br><br>
                        <a href="pages/login.php" style="color: #1e3c72;">Cliquez ici pour vous connecter</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Nom d'utilisateur</label>
                            <input type="text" name="login" required placeholder="ex: admin, superadmin">
                        </div>
                        
                        <div class="form-group">
                            <label>Mot de passe</label>
                            <input type="password" name="password" required placeholder="Minimum 6 caractères">
                        </div>
                        
                        <div class="form-group">
                            <label>Confirmer le mot de passe</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn">✅ Créer l'administrateur</button>
                    </form>
                <?php endif; ?>
                
                <div class="info">
                    <p>📌 Après création, vous pourrez vous connecter avec vos identifiants</p>
                    <p><a href="pages/login.php">← Retour à la page de connexion</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
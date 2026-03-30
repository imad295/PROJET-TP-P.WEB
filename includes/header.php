<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Scolarité - USTHB</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <div class="logo">
                    <h1>USTHB</h1>
                    <p>Gestion de Scolarité</p>
                </div>
                <?php if(isLoggedIn()): ?>
                <div class="user-info">
                    <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <span class="role-badge"><?php echo ucfirst($_SESSION['role']); ?></span>
                    <a href="../pages/logout.php" class="logout-btn">Déconnexion</a>
                </div>
                <?php endif; ?>
            </div>
        </header>
        
        <div class="main-wrapper">
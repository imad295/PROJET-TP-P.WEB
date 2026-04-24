<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USTHB - Gestion Scolarité</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            color: #1a1a2e;
        }

        /* Layout principal */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            width: 280px;
            background: #1e293b;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid #334155;
        }

        .logo-sidebar {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-sidebar i {
            font-size: 28px;
            color: #3b82f6;
        }

        .logo-sidebar h3 {
            font-size: 18px;
            color: white;
        }

        .logo-sidebar p {
            font-size: 10px;
            color: #94a3b8;
        }

        .sidebar-nav {
            padding: 20px 15px;
        }

        .nav-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            margin: 4px 0;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            font-size: 14px;
        }

        .nav-btn i {
            width: 20px;
            font-size: 16px;
        }

        .nav-btn:hover {
            background: #334155;
            color: white;
            transform: translateX(5px);
        }

        .nav-btn.active {
            background: #3b82f6;
            color: white;
        }

        .divider {
            height: 1px;
            background: #334155;
            margin: 20px 0;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #0f172a;
            border-radius: 10px;
            margin: 10px 0;
        }

        .user-card i {
            font-size: 32px;
            color: #3b82f6;
        }

        .user-card .user-name {
            font-size: 14px;
            font-weight: 600;
            color: white;
        }

        .user-card .user-role {
            font-size: 11px;
            color: #94a3b8;
        }

        .logout-btn {
            color: #ef4444 !important;
        }

        .logout-btn:hover {
            background: #450a0a !important;
            color: #ef4444 !important;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 25px 30px;
            width: calc(100% - 280px);
            background: #f0f2f5;
            min-height: 100vh;
        }

        /* Cartes */
        .page-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1a1a2e;
        }

        .page-header p {
            color: #6c757d;
            margin-top: 5px;
        }

        /* Animation des cartes statistiques */
        .stat-card-animated {
            background: white;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .stat-card-animated:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        /* Animation des boutons d'action */
        .action-btn {
            background: linear-gradient(135deg, #4361ee, #3b82f6);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }

        .action-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(67,97,238,0.3);
        }

        .action-btn:active {
            transform: scale(0.98);
        }

        /* Boutons d'action dans les tableaux */
        .btn-action {
            padding: 6px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-action:hover {
            transform: scale(1.05);
        }

        .btn-edit {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .btn-view {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            background: white;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        /* Search bar */
        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            font-size: 14px;
        }

        .search-bar input:focus {
            outline: none;
            border-color: #4361ee;
        }

        .btn-search {
            background: #4361ee;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .btn-clear {
            background: #e9ecef;
            color: #495057;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 10px;
        }

        /* Alerts */
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Modals */
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
            max-width: 500px;
            border-radius: 16px;
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
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 16px 16px 0 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 18px;
            margin: 0;
        }

        .close {
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
        }

        .close:hover {
            color: #dc3545;
        }

        .modal-content form {
            padding: 20px;
        }

        /* Forms */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #495057;
            font-size: 13px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4361ee;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .text-center {
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }
            .app-container {
                flex-direction: column;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-sidebar">
                <i class="fas fa-graduation-cap"></i>
                <div>
                    <h3>USTHB</h3>
                    <p>Gestion Scolarité</p>
                </div>
            </div>
        </div>
        
        <div class="sidebar-nav">
            <?php if(hasRole('admin')): ?>
                <a href="dashboard_admin.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_admin.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                </a>
                <a href="etudiants.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'etudiants.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Étudiants
                </a>
                <a href="enseignants.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'enseignants.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chalkboard-user"></i> Enseignants
                </a>
                <a href="admins.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'admins.php' ? 'active' : ''; ?>">
                    <i class="fas fa-crown"></i> Administrateurs
                </a>
                <a href="modules.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'modules.php' ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i> Modules
                </a>
                <!-- Nouveaux liens pour la hiérarchie -->
                <a href="niveaux.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'niveaux.php' ? 'active' : ''; ?>">
                    <i class="fas fa-layer-group"></i> Niveaux
                </a>
                <a href="specialites.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'specialites.php' ? 'active' : ''; ?>">
                    <i class="fas fa-code-branch"></i> Spécialités
                </a>
                <a href="sections.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'sections.php' ? 'active' : ''; ?>">
                    <i class="fas fa-columns"></i> Sections
                </a>
                <a href="groupes.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'groupes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Groupes
                </a>
                <a href="notes.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'notes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-pen-fancy"></i> Notes
                </a>
                <a href="statistiques.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'statistiques.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Statistiques
                </a>
                <a href="releve.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'releve.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> Relevés
                </a>
            <?php elseif(hasRole('enseignant')): ?>
                <a href="dashboard_enseignant.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_enseignant.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                </a>
                <a href="notes.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'notes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-pen-fancy"></i> Notes
                </a>
                <a href="releve.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'releve.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> Relevés
                </a>
            <?php elseif(hasRole('etudiant')): ?>
                <a href="dashboard_etudiant.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_etudiant.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                </a>
                <a href="releve.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) == 'releve.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> Mon relevé
                </a>
            <?php endif; ?>
            
            <div class="divider"></div>
            
            <div class="user-card">
                <i class="fas fa-user-circle"></i>
                <div>
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
                </div>
            </div>
            
            <a href="logout.php" class="nav-btn logout-btn">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
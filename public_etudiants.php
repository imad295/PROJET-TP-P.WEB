<?php
require_once 'includes/config.php';

// Récupérer la liste de tous les étudiants avec leurs informations
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$niveau_filter = isset($_GET['niveau']) ? $_GET['niveau'] : '';
$section_filter = isset($_GET['section']) ? $_GET['section'] : '';

// Requête de base
$sql = "
    SELECT e.matricule, e.nom, e.prenom, 
           g.nom_groupe, 
           s.nom_section, 
           sp.nom_specialite, 
           n.nom_niveau,
           n.ordre as niveau_ordre
    FROM etudiant e
    LEFT JOIN `groupe` g ON e.id_groupe = g.id_groupe
    LEFT JOIN section s ON g.id_section = s.id_section
    LEFT JOIN specialite sp ON s.id_specialite = sp.id_specialite
    LEFT JOIN niveau n ON sp.id_niveau = n.id_niveau
    WHERE 1=1
";

$params = [];

if($search != '') {
    $sql .= " AND (e.matricule LIKE ? OR e.nom LIKE ? OR e.prenom LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if($niveau_filter != '') {
    $sql .= " AND n.id_niveau = ?";
    $params[] = $niveau_filter;
}

if($section_filter != '') {
    $sql .= " AND s.id_section = ?";
    $params[] = $section_filter;
}

$sql .= " ORDER BY n.ordre, sp.id_specialite, s.id_section, g.id_groupe, e.nom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$etudiants = $stmt->fetchAll();

// Récupérer les filtres disponibles
$niveaux = $pdo->query("SELECT * FROM niveau ORDER BY ordre")->fetchAll();
$sections = $pdo->query("
    SELECT s.*, sp.nom_specialite, n.nom_niveau 
    FROM section s
    JOIN specialite sp ON s.id_specialite = sp.id_specialite
    JOIN niveau n ON sp.id_niveau = n.id_niveau
    ORDER BY n.ordre, sp.id_specialite, s.id_section
")->fetchAll();

// Statistiques
$total_etudiants = $pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();
$total_groupes = $pdo->query("SELECT COUNT(*) FROM `groupe`")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annuaire des étudiants - USTHB</title>
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

        /* Navigation */
        .navbar {
            background: #1e293b;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .logo h1 {
            font-size: 20px;
            color: white;
        }

        .logo p {
            font-size: 11px;
            color: #94a3b8;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-links a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: white;
        }

        .btn-login {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            padding: 8px 20px;
            border-radius: 30px;
            color: white !important;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        /* Main content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Header */
        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 32px;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #6c757d;
        }

        /* Stats */
        .stats-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px 30px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            min-width: 150px;
        }

        .stat-card i {
            font-size: 30px;
            color: #3b82f6;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
        }

        .stat-label {
            font-size: 13px;
            color: #6c757d;
        }

        /* Filters */
        .filters-container {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .filters-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-group label {
            display: block;
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .btn-search {
            background: #3b82f6;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-reset {
            background: #e9ecef;
            color: #495057;
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 16px;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
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
            font-size: 13px;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-l1 { background: #e3f2fd; color: #1565c0; }
        .badge-l2 { background: #e8f5e9; color: #2e7d32; }
        .badge-l3 { background: #fff3e0; color: #e65100; }

        /* Info message */
        .info-message {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        /* Footer */
        .footer {
            background: #1e293b;
            color: #94a3b8;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
            }
            .filters-form {
                flex-direction: column;
            }
            .filter-group {
                width: 100%;
            }
            .stats-container {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h1>USTHB</h1>
                    <p>Faculté d'Informatique</p>
                </div>
            </div>
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Accueil</a>
                <a href="public_etudiants.php" class="active" style="color: #3b82f6;"><i class="fas fa-users"></i> Annuaire</a>
                <a href="pages/login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Connexion</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-users"></i> Annuaire des étudiants</h1>
            <p>Consultez la liste des étudiants de la Faculté d'Informatique</p>
        </div>

        <!-- Statistiques -->
        <div class="stats-container">
            <div class="stat-card">
                <i class="fas fa-user-graduate"></i>
                <div class="stat-number"><?php echo $total_etudiants; ?></div>
                <div class="stat-label">Étudiants</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-layer-group"></i>
                <div class="stat-number">3</div>
                <div class="stat-label">Niveaux</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-columns"></i>
                <div class="stat-number">9</div>
                <div class="stat-label">Sections</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-number"><?php echo $total_groupes; ?></div>
                <div class="stat-label">Groupes</div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filters-container">
            <form method="GET" action="" class="filters-form">
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Recherche</label>
                    <input type="text" name="search" placeholder="Nom, prénom ou matricule..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-layer-group"></i> Niveau</label>
                    <select name="niveau">
                        <option value="">Tous les niveaux</option>
                        <?php foreach($niveaux as $n): ?>
                        <option value="<?php echo $n['id_niveau']; ?>" <?php echo $niveau_filter == $n['id_niveau'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($n['nom_niveau']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-columns"></i> Section</label>
                    <select name="section">
                        <option value="">Toutes les sections</option>
                        <?php foreach($sections as $s): ?>
                        <option value="<?php echo $s['id_section']; ?>" <?php echo $section_filter == $s['id_section'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['nom_section'] . ' - ' . $s['nom_specialite']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn-search"><i class="fas fa-filter"></i> Filtrer</button>
                    <a href="public_etudiants.php" class="btn-reset"><i class="fas fa-times"></i> Réinitialiser</a>
                </div>
            </form>
        </div>

        <!-- Tableau des étudiants -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Niveau</th>
                        <th>Spécialité</th>
                        <th>Section</th>
                        <th>Groupe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($etudiants) > 0): ?>
                        <?php foreach($etudiants as $e): 
                            $niveau_class = '';
                            if(strpos($e['nom_niveau'], 'L1') !== false) $niveau_class = 'badge-l1';
                            elseif(strpos($e['nom_niveau'], 'L2') !== false) $niveau_class = 'badge-l2';
                            elseif(strpos($e['nom_niveau'], 'L3') !== false) $niveau_class = 'badge-l3';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($e['matricule']); ?></strong></td>
                            <td><?php echo htmlspecialchars($e['nom']); ?></td>
                            <td><?php echo htmlspecialchars($e['prenom']); ?></td>
                            <td><span class="badge <?php echo $niveau_class; ?>"><?php echo htmlspecialchars($e['nom_niveau'] ?? '-'); ?></span></td>
                            <td><?php echo htmlspecialchars($e['nom_specialite'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($e['nom_section'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($e['nom_groupe'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="info-message">
                                <i class="fas fa-info-circle"></i> Aucun étudiant trouvé
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> USTHB - Faculté d'Informatique. Tous droits réservés.</p>
        <p>Annuaire public des étudiants</p>
    </footer>
</body>
</html>
<?php
require_once '../includes/config.php';

if(!isLoggedIn() || !hasRole('enseignant')) {
    redirect('../index.php');
}

// Récupérer les informations de l'enseignant
$stmt = $pdo->prepare("SELECT * FROM enseignant WHERE id_enseignant = ?");
$stmt->execute([$_SESSION['user_id']]);
$enseignant = $stmt->fetch();

// Récupérer les modules de l'enseignant
$stmt = $pdo->prepare("SELECT * FROM module WHERE id_enseignant = ?");
$stmt->execute([$_SESSION['user_id']]);
$modules = $stmt->fetchAll();

// Récupérer le nombre d'étudiants
$stmt = $pdo->query("SELECT COUNT(*) as total FROM etudiant");
$total_etudiants = $stmt->fetch()['total'];

include '../includes/header.php';
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr valign="top">
        <!-- Sidebar -->
        <td width="250" bgcolor="#f0f0f0" style="padding: 20px;">
            <h3>📚 Menu Enseignant</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 10px 0;"><a href="dashboard_enseignant.php" style="text-decoration: none; color: #333;">📊 Tableau de bord</a></li>
                <li style="margin: 10px 0;"><a href="notes.php" style="text-decoration: none; color: #333;">📝 Gérer les notes</a></li>
                <li style="margin: 10px 0;"><a href="releve.php" style="text-decoration: none; color: #333;">📄 Relevés de notes</a></li>
            </ul>
            <hr>
            <p><a href="logout.php" style="color: #f44336; text-decoration: none;">🔓 Déconnexion</a></p>
        </td>
        
        <!-- Main Content -->
        <td style="padding: 20px;">
            <h2>Tableau de bord Enseignant</h2>
            <p>Bienvenue, <b><?php echo htmlspecialchars($enseignant['prenom'] . ' ' . $enseignant['nom']); ?></b></p>
            
            <?php displayMessage(); ?>
            
            <!-- Statistiques -->
            <table width="100%" border="0" cellpadding="10" cellspacing="10">
                <tr align="center">
                    <td width="33%" bgcolor="#e3f2fd" style="border-radius: 5px;">
                        <font size="5"><b><?php echo count($modules); ?></b></font><br>
                        <font size="2">Mes modules</font>
                    </td>
                    <td width="33%" bgcolor="#e8f5e9" style="border-radius: 5px;">
                        <font size="5"><b><?php echo $total_etudiants; ?></b></font><br>
                        <font size="2">Total étudiants</font>
                    </td>
                </tr>
            </table>
            
            <!-- Mes modules -->
            <h3>📚 Mes modules</h3>
            <table width="100%" border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;">
                <tr bgcolor="#f0f0f0">
                    <th>Module</th>
                    <th>Coefficient</th>
                    <th>Action</th>
                </tr>
                <?php if(count($modules) > 0): ?>
                    <?php foreach($modules as $module): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($module['nom_module']); ?></td>
                        <td align="center"><?php echo $module['coefficient']; ?></td>
                        <td align="center">
                            <a href="notes.php?module=<?php echo $module['id_module']; ?>" style="background-color: #4caf50; color: white; padding: 5px 10px; text-decoration: none;">Gérer notes</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" align="center">Aucun module assigné</td>
                    </tr>
                <?php endif; ?>
            </table>
        </td>
    </tr>
</table>

<?php include '../includes/footer.php'; ?>
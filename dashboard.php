<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Dashboard</title></head>
<body>

<h2>Bienvenue Admin : <?php echo $_SESSION['admin']; ?></h2>

<a href="add_student.php">Ajouter Étudiant</a><br><br>
<a href="list_students.php">Liste des Étudiants</a><br><br>
<a href="logout.php">Déconnexion</a>

</body>
</html>
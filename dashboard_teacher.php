<?php
include("config.php");
session_start();

if(!isset($_SESSION['id'])){ header("Location: index.php"); exit(); }

$teacher_id = $_SESSION['id'];

// Récupérer les élèves de sa classe
$teacher = $conn->query("SELECT * FROM teachers WHERE id='$teacher_id'")->fetch_assoc();
$classe = $teacher['classe'];

$students = $conn->query("SELECT * FROM students WHERE niveau='$classe'");
$modules = $conn->query("SELECT * FROM modules WHERE enseignant_id='$teacher_id'");
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard Enseignant</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
<h2>Dashboard Enseignant</h2>
<a href="logout.php" class="btn btn-danger mb-3">Se déconnecter</a>

<h4>Ajouter/Modifier Note</h4>
<form method="POST" action="add_note.php">
<select name="student_id" class="form-control mb-2">
<?php while($s=$students->fetch_assoc()) { echo "<option value='".$s['id']."'>".$s['nom']." ".$s['prenom']."</option>"; } ?>
</select>

<select name="module_id" class="form-control mb-2">
<?php while($m=$modules->fetch_assoc()) { echo "<option value='".$m['id']."'>".$m['intitule']."</option>"; } ?>
</select>

<input type="number" step="0.01" name="note" class="form-control mb-2" placeholder="Note">
<button type="submit" class="btn btn-primary w-100">Enregistrer Note</button>
</form>

</div>
</body>
</html>
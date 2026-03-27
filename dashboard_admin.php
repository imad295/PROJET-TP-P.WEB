<?php
include("config.php");
session_start();
if(!isset($_SESSION['id'])){ header("Location: index.php"); exit(); }

$students = $conn->query("SELECT * FROM students");
$teachers = $conn->query("SELECT * FROM teachers");
$modules = $conn->query("SELECT * FROM modules");
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
<h2>Dashboard Admin</h2>
<a href="logout.php" class="btn btn-danger mb-3">Se déconnecter</a>

<h4>Ajouter Module</h4>
<form method="POST" action="add_module.php">
<input type="text" name="code_module" class="form-control mb-2" placeholder="Code Module" required>
<input type="text" name="intitule" class="form-control mb-2" placeholder="Intitulé" required>
<input type="number" step="0.01" name="coefficient" class="form-control mb-2" placeholder="Coefficient" required>
<select name="enseignant_id" class="form-control mb-2">
<?php while($t=$teachers->fetch_assoc()){ echo "<option value='".$t['id']."'>".$t['nom']." ".$t['prenom']."</option>"; } ?>
</select>
<button type="submit" class="btn btn-success w-100">Ajouter Module</button>
</form>

</div>
</body>
</html>
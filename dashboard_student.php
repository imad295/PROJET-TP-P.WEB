<?php
include("config.php");
session_start();

if(!isset($_SESSION['id'])){ header("Location: index.php"); exit(); }

$student_id = $_SESSION['id'];

$sql = "SELECT modules.intitule, notes.note
        FROM notes
        JOIN modules ON notes.matiere = modules.id
        WHERE notes.student_id = '$student_id'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard Étudiant</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
<h2>Mes Notes</h2>
<a href="logout.php" class="btn btn-danger mb-3">Se déconnecter</a>

<table class="table table-bordered">
<tr><th>Module</th><th>Note</th></tr>

<?php
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo "<tr><td>".$row['intitule']."</td><td>".$row['note']."</td></tr>";
    }
} else {
    echo "<tr><td colspan='2'>Aucune note disponible</td></tr>";
}
?>
</table>
</div>
</body>
</html>
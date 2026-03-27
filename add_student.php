<?php
session_start();
if(!isset($_SESSION['admin'])) header("Location: index.php");

include("config.php");

if(isset($_POST['add'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];

    $sql = "INSERT INTO students (nom, prenom, email) VALUES('$nom','$prenom','$email')";
    if($conn->query($sql)){
        $msg = "Étudiant ajouté !";
    } else {
        $msg = "Erreur : ".$conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Ajouter Étudiant</title></head>
<body>

<h2>Ajouter un étudiant</h2>

<form method="POST">
    Nom : <input type="text" name="nom" required><br><br>
    Prénom : <input type="text" name="prenom" required><br><br>
    Email : <input type="email" name="email" required><br><br>
    <button type="submit" name="add">Ajouter</button>
</form>

<?php if(isset($msg)) echo $msg; ?>

</body>
</html>
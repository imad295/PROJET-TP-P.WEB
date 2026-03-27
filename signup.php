<?php
include("config.php");

if(isset($_POST['signup'])){
    $role = $_POST['role'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Vérifier si l'email existe déjà
    $check1 = $conn->query("SELECT id FROM students WHERE email='$email'");
    $check2 = $conn->query("SELECT id FROM teachers WHERE email='$email'");
    $check3 = $conn->query("SELECT id FROM admins WHERE email='$email'");

    if($check1->num_rows > 0 || $check2->num_rows > 0 || $check3->num_rows > 0){
        echo "Email déjà utilisé";
    } else {
        if($role == "student"){
            $matricule = uniqid(); // Identifiant unique automatique
            $date_naissance = $_POST['date_naissance'];
            $niveau = $_POST['niveau'];

            $sql = "INSERT INTO students (nom, prenom, matricule, date_naissance, niveau, email, password)
                    VALUES ('$nom','$prenom','$matricule','$date_naissance','$niveau','$email','$password')";
        }
        elseif($role == "teacher"){
            $classe = $_POST['classe'] ?? ""; // classe attribuée par admin
            $sql = "INSERT INTO teachers (nom, prenom, email, password, classe)
                    VALUES ('$nom','$prenom','$email','$password','$classe')";
        }
        elseif($role == "admin"){
            $sql = "INSERT INTO admins (nom, prenom, email, password)
                    VALUES ('$nom','$prenom','$email','$password')";
        }

        if($conn->query($sql) === TRUE){
            echo "Compte créé avec succès";
        } else {
            echo "Erreur: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Signup</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width:500px">
<h2>Créer un compte</h2>
<form method="POST">
<select name="role" class="form-control mb-3">
<option value="student">Étudiant</option>
<option value="teacher">Enseignant</option>
<option value="admin">Administrateur</option>
</select>

<input type="text" name="nom" placeholder="Nom" class="form-control mb-3" required>
<input type="text" name="prenom" placeholder="Prénom" class="form-control mb-3" required>
<input type="text" name="classe" placeholder="Classe (enseignant)" class="form-control mb-3">
<input type="text" name="niveau" placeholder="Niveau (étudiant)" class="form-control mb-3">
<input type="date" name="date_naissance" class="form-control mb-3">
<input type="email" name="email" placeholder="Email" class="form-control mb-3" required>
<input type="password" name="password" placeholder="Mot de passe" class="form-control mb-3" required>
<button name="signup" class="btn btn-success w-100">Créer compte</button>
</form>
<a href="index.php" class="btn btn-primary mt-3 w-100">Retour Login</a>
</div>
</body>
</html>
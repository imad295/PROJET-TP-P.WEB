<?php
include("config.php");
session_start();

if(isset($_POST['login'])){
    $role = $_POST['role'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if($role == "student"){
        $sql = "SELECT * FROM students WHERE email='$email'";
        $redirect = "dashboard_student.php";
    } elseif($role == "teacher"){
        $sql = "SELECT * FROM teachers WHERE email='$email'";
        $redirect = "dashboard_teacher.php";
    } elseif($role == "admin"){
        $sql = "SELECT * FROM admins WHERE email='$email'";
        $redirect = "dashboard_admin.php";
    }

    $result = $conn->query($sql);

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            $_SESSION['id'] = $user['id'];
            $_SESSION['role'] = $role;
            header("Location: ".$redirect);
            exit();
        } else {
            $error = "Mot de passe incorrect";
        }
    } else {
        $error = "Email non trouvé";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width:400px">
<h2 class="text-center">Connexion</h2>
<form method="POST">
<input type="email" name="email" placeholder="Email" class="form-control mb-3" required>
<input type="password" name="password" placeholder="Mot de passe" class="form-control mb-3" required>
<select name="role" class="form-control mb-3">
<option value="student">Étudiant</option>
<option value="teacher">Enseignant</option>
<option value="admin">Administrateur</option>
</select>
<button name="login" class="btn btn-primary w-100">Se connecter</button>
</form>

<?php if(isset($error)){ echo "<div class='alert alert-danger mt-3'>$error</div>"; } ?>
<a href="signup.php" class="btn btn-success mt-3 w-100">Créer un compte</a>
</div>
</body>
</html>
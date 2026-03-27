<?php
include("config.php");
session_start();

// Vérifie que l'utilisateur est un enseignant
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: index.php");
    exit();
}

if(isset($_POST['student_id'], $_POST['matiere'], $_POST['note'])){
    $student_id = $_POST['student_id'];
    $matiere = $_POST['matiere'];
    $note = $_POST['note'];

    // Vérifie si la matière existe déjà pour cet étudiant
    $check = $conn->query("SELECT * FROM notes WHERE student_id='$student_id' AND matiere='$matiere'");
    
    if($check->num_rows > 0){
        // Mise à jour
        $sql = "UPDATE notes SET note='$note' WHERE student_id='$student_id' AND matiere='$matiere'";
    } else {
        // Nouvelle note
        $sql = "INSERT INTO notes (student_id, matiere, note) VALUES ('$student_id','$matiere','$note')";
    }

    if($conn->query($sql) === TRUE){
        header("Location: dashboard_teacher.php"); // Retour au dashboard
        exit();
    } else {
        echo "Erreur : " . $conn->error;
    }
} else {
    echo "Données manquantes !";
}
?>
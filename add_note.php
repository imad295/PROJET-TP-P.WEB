<?php
include("config.php");
session_start();

if(isset($_POST['student_id']) && isset($_POST['module_id']) && isset($_POST['note'])){
    $student_id = $_POST['student_id'];
    $module_id = $_POST['module_id'];
    $note = $_POST['note'];

    // Vérifier si une note existe déjà
    $check = $conn->query("SELECT id FROM notes WHERE student_id='$student_id' AND matiere='$module_id'");
    if($check->num_rows > 0){
        $conn->query("UPDATE notes SET note='$note' WHERE student_id='$student_id' AND matiere='$module_id'");
    } else {
        $conn->query("INSERT INTO notes (student_id, matiere, note) VALUES ('$student_id','$module_id','$note')");
    }
    header("Location: dashboard_teacher.php");
}
?>
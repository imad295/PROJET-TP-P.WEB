<?php
include("config.php");
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'teacher') header("Location: index.php");

if(isset($_POST['add_note'])){
    $student_id = $_POST['student_id'];
    $subject = $_POST['subject'];
    $note = $_POST['note'];
    $teacher_id = $_SESSION['id'];

    // Vérifier si note existe
    $check = $conn->query("SELECT * FROM notes WHERE student_id=$student_id AND subject='$subject'");
    if($check->num_rows == 1){
        $conn->query("UPDATE notes SET note=$note, teacher_id=$teacher_id WHERE student_id=$student_id AND subject='$subject'");
    } else {
        $conn->query("INSERT INTO notes(student_id, subject, note, teacher_id)
                      VALUES($student_id,'$subject',$note,$teacher_id)");
    }
}

header("Location: dashboard_teacher.php");
exit();
?>
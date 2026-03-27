<?php

include("config.php");
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location:index.php");
    exit();
}

$id = $_GET['id'];

$conn->query("DELETE FROM modules WHERE id='$id'");

header("Location: dashboard_admin.php");

?>
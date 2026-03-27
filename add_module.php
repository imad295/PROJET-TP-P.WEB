<?php
include("config.php");
if(isset($_POST['code_module'])){
    $code = $_POST['code_module'];
    $intitule = $_POST['intitule'];
    $coef = $_POST['coefficient'];
    $ens_id = $_POST['enseignant_id'];

    $conn->query("INSERT INTO modules (code_module,intitule,coefficient,enseignant_id) 
                  VALUES ('$code','$intitule','$coef','$ens_id')");
    header("Location: dashboard_admin.php");
}
?>
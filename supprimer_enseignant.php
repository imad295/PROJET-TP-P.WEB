<?php
include("config.php");

$id = $_GET['id'];

$sql = "DELETE FROM enseignants WHERE id=$id";

if ($conn->query($sql) === TRUE) {
    echo "Enseignant supprimé avec succès";
} else {
    echo "Erreur: " . $conn->error;
}

$conn->close();

header("Location: liste_enseignants.php");
exit();
?>
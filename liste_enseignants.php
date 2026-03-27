<?php
include("config.php");

$sql = "SELECT * FROM enseignants";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Liste des enseignants</title>
</head>
<body>

<h2>Liste des enseignants</h2>

<table border="1">

<tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Prénom</th>
    <th>Email</th>
    <th>Supprimer</th>
</tr>

<?php

while($row = $result->fetch_assoc()){
?>

<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['nom']; ?></td>
    <td><?php echo $row['prenom']; ?></td>
    <td><?php echo $row['email']; ?></td>

    <td>
        <a href="supprimer_enseignant.php?id=<?php echo $row['id']; ?>">
            Supprimer
        </a>
    </td>
</tr>

<?php
}
?>

</table>

</body>
</html>
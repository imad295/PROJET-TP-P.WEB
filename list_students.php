<?php
session_start();
if(!isset($_SESSION['admin'])) header("Location: index.php");

include("config.php");
$sql = "SELECT * FROM students";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head><title>Liste Étudiants</title></head>
<body>

<h2>Liste des étudiants</h2>

<table border="1">
<tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Prénom</th>
    <th>Email</th>
    <th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()){ ?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['nom']; ?></td>
    <td><?php echo $row['prenom']; ?></td>
    <td><?php echo $row['email']; ?></td>
    <td>
        <a href="delete_student.php?id=<?php echo $row['id']; ?>">Supprimer</a>
    </td>
</tr>
<?php } ?>

</table>

</body>
</html>
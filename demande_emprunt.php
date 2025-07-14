<?php
session_start();
include("config.php");

if (!isset($_SESSION["id_membre"])) {
    header("Location: login.php");
    exit();
}

$id_membre = $_SESSION["id_membre"];
$id_objet = (int) $_POST["id_objet"];
$date_emprunt = $_POST["date_emprunt"];
$date_retour = $_POST["date_retour"];

// Vérifie si l’objet est encore dispo à ces dates
$sql_verif = "
    SELECT * FROM emprunt 
    WHERE id_objet = $id_objet AND (
        ('$date_emprunt' BETWEEN date_emprunt AND date_retour)
        OR ('$date_retour' BETWEEN date_emprunt AND date_retour)
        OR ('$date_emprunt' <= date_emprunt AND '$date_retour' >= date_retour)
    )
";
$res_verif = mysqli_query($bdd, $sql_verif);

if (mysqli_num_rows($res_verif) > 0) {
    echo "❌ Cet objet est déjà réservé pour cette période.";
    exit;
}

// Enregistre l’emprunt
$sql_insert = "
    INSERT INTO emprunt (id_objet, id_membre, date_emprunt, date_retour)
    VALUES ($id_objet, $id_membre, '$date_emprunt', '$date_retour')
";
if (mysqli_query($bdd, $sql_insert)) {
    header("Location: fiche_objet.php?id=$id_objet");
} else {
    echo "Erreur lors de l'enregistrement.";
}
?>

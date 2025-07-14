<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

include("config.php");

if (!isset($_SESSION["id_membre"])) {
    header("Location: login.php");
    exit();
}

$id_objet = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_objet <= 0) {
    die("ID d'objet invalide.");
}

$sql_objet = "
    SELECT o.*, c.nom_categorie, m.nom AS proprietaire
    FROM objet o
    LEFT JOIN categorie_objet c ON o.id_categorie = c.id_categorie
    LEFT JOIN membre m ON o.id_membre = m.id_membre
    WHERE o.id_objet = $id_objet
";
$res_objet = mysqli_query($bdd, $sql_objet);
if (mysqli_num_rows($res_objet) == 0) {
    die("Objet non trouvé.");
}
$objet = mysqli_fetch_assoc($res_objet);

$sql_images = "SELECT * FROM images_objet WHERE id_objet = $id_objet ORDER BY est_principale DESC, id_image ASC";
$res_images = mysqli_query($bdd, $sql_images);

$sql_emprunts = "
    SELECT e.*, m.nom AS emprunteur
    FROM emprunt e
    JOIN membre m ON e.id_membre = m.id_membre
    WHERE e.id_objet = $id_objet
    ORDER BY e.date_emprunt DESC
";
$res_emprunts = mysqli_query($bdd, $sql_emprunts);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Fiche de l'objet <?= htmlspecialchars($objet['nom_objet']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />

</head>
<body>
<div class="container py-4">

    <h2>Fiche détaillée : <?= htmlspecialchars($objet['nom_objet']) ?></h2>
    <p><strong>Catégorie :</strong> <?= htmlspecialchars($objet['nom_categorie']) ?></p>
    <p><strong>Propriétaire :</strong> <?= htmlspecialchars($objet['proprietaire']) ?></p>

    <div class="mb-4">
        <?php if (mysqli_num_rows($res_images) > 0): ?>
            <?php while ($img = mysqli_fetch_assoc($res_images)): ?>
                <img src="img/<?= htmlspecialchars($img['nom_image']) ?>" alt="Image de <?= htmlspecialchars($objet['nom_objet']) ?>" style="max-width:200px; margin-right:10px; <?= ($img['est_principale']) ? 'border:3px solid #007bff;' : '' ?>" />
            <?php endwhile; ?>
        <?php else: ?>
            <p>Aucune image disponible pour cet objet.</p>
        <?php endif; ?>
    </div>

    <h2>Historique des emprunts</h2>
    <?php if (mysqli_num_rows($res_emprunts) == 0): ?>
        <p>Aucun emprunt enregistré pour cet objet.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Emprunteur</th>
                    <th>Date emprunt</th>
                    <th>Date retour</th>
                </tr>
                
            </thead>
            <tbody>
                <?php while ($emprunt = mysqli_fetch_assoc($res_emprunts)): ?>
                    <tr>
                        <td><?= htmlspecialchars($emprunt['emprunteur']) ?></td>
                        <td><?= htmlspecialchars($emprunt['date_emprunt']) ?></td>
                        <td><?= htmlspecialchars($emprunt['date_retour']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <?php
// Vérifie s’il vient de la section "disponibles"
$is_disponible = isset($_GET['disponible']) && $_GET['disponible'] == '1';

// Vérifie si l’objet est actuellement libre (aucun emprunt en cours)
$sql_verif = "
    SELECT * FROM emprunt 
    WHERE id_objet = $id_objet AND CURDATE() <= date_retour
";
$res_verif = mysqli_query($bdd, $sql_verif);
$est_disponible = (mysqli_num_rows($res_verif) == 0);
?>

<?php if ($is_disponible && $est_disponible): ?>
    <div class="mt-4">
        <h2>Demander un emprunt</h2>
        <form action="demande_emprunt.php" method="post" class="row g-3">
            <input type="hidden" name="id_objet" value="<?= $id_objet ?>" />

            <div class="col-md-6">
                <label for="date_emprunt" class="form-label">Date de début</label>
                <input type="date" class="form-control" name="date_emprunt" id="date_emprunt" required>
            </div>

            <div class="col-md-6">
                <label for="date_retour" class="form-label">Date de retour</label>
                <input type="date" class="form-control" name="date_retour" id="date_retour" required>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-success">Envoyer la demande</button>
            </div>
        </form>
    </div>
<?php endif; ?>

    <a href="liste_objet.php" class="btn btn-secondary mt-3">Retour à la liste</a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

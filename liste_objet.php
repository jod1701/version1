<?php
session_start();
include("config.php");

if (!isset($_SESSION["id_membre"])) {
    header("Location: login.php");
    exit();
}

$id_membre = $_SESSION["id_membre"];
$categorie_choisie = $_GET['categorie'] ?? '';

$sql_empruntes = "
    SELECT o.nom_objet, o.id_objet, c.nom_categorie, NULL AS date_emprunt, NULL AS date_retour
    FROM objet o
    JOIN categorie_objet c ON o.id_categorie = c.id_categorie
    WHERE o.id_membre = $id_membre
    ORDER BY o.id_objet DESC
    LIMIT 10
";
$res_empruntes = mysqli_query($bdd, $sql_empruntes);

$sql_disponibles = "
    SELECT o.*, c.nom_categorie
    FROM objet o
    JOIN categorie_objet c ON o.id_categorie = c.id_categorie
    WHERE o.id_objet NOT IN (
        SELECT id_objet FROM emprunt
        WHERE CURDATE() <= date_retour
    )
";
if ($categorie_choisie) {
    $categorie_choisie = mysqli_real_escape_string($bdd, $categorie_choisie);
    $sql_disponibles .= " AND c.nom_categorie = '$categorie_choisie'";
}
$res_disponibles = mysqli_query($bdd, $sql_disponibles);

$categories = ["esthétique", "bricolage", "mécanique", "cuisine"];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Objets de <?= htmlspecialchars($_SESSION['nom']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
</head>
<body>

<div class="container py-4">
    <h2 class="text-center text-white mb-4">Bienvenue, <?= htmlspecialchars($_SESSION['nom']) ?></h2>

    <h4 class="text-white">Vos 10 derniers objets empruntés ou que vous avez ajoutés</h4>
    <div class="row mb-5">
        <?php if (mysqli_num_rows($res_empruntes) == 0): ?>
            <p class="text-light">Aucun objet emprunté ou ajouté.</p>
        <?php else: ?>
            <?php while ($obj = mysqli_fetch_assoc($res_empruntes)) : ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($obj['nom_objet']) ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($obj['nom_categorie']) ?></span>
                            </h5>
                            <span class="badge bg-info text-dark">Objet ajouté par vous</span><br>
                            <span class="badge bg-warning text-dark">Statut : Emprunté (personnel)</span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>


    <form method="GET" class="mb-4">
        <label for="categorie" class="form-label text-white">Filtrer les objets disponibles :</label>
        <select name="categorie" id="categorie" class="form-select w-50" onchange="this.form.submit()">
            <option value="">-- Toutes les catégories --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= ($categorie_choisie == $cat) ? 'selected' : '' ?>>
                    <?= ucfirst($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <h4 class="text-white mb-3">Objets disponibles à emprunter</h4>
    <div class="row">
        <?php if (mysqli_num_rows($res_disponibles) == 0): ?>
            <p class="text-light">Aucun objet disponible dans cette catégorie.</p>
        <?php else: ?>
            <?php while ($obj = mysqli_fetch_assoc($res_disponibles)) : ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($obj['nom_objet']) ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($obj['nom_categorie']) ?></span>
                            </h5>
                            <span class="badge bg-success">Disponible</span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

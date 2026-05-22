<?php
$config = require __DIR__ . '/../../config/config.php';
$base = $config['app']['base_url'];
$lvl = (int) $tache['niveauPriorite'];
$cls = ['','secondary','info','warning','danger'][$lvl] ?? 'secondary';
?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <h1 class="h3 mb-1"><?= htmlspecialchars($tache['titreTache']) ?></h1>
    <p class="text-muted mb-0">
      Créée le <?= htmlspecialchars(date('d/m/Y H:i', strtotime($tache['dateCreation']))) ?>
      <?php if ($tache['completedAt']): ?>
        · Terminée le <?= htmlspecialchars(date('d/m/Y H:i', strtotime($tache['completedAt']))) ?>
      <?php endif; ?>
    </p>
  </div>
  <div>
    <a class="btn btn-primary" href="<?= $base ?>/?r=tache/edit&id=<?= (int)$tache['idTache'] ?>">Modifier</a>
    <form method="post" action="<?= $base ?>/?r=tache/delete&id=<?= (int)$tache['idTache'] ?>" class="d-inline"
          onsubmit="return confirm('Supprimer cette tâche ?');">
      <input type="hidden" name="_csrf" value="<?= App\Csrf::token() ?>">
      <button class="btn btn-outline-danger">Supprimer</button>
    </form>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-4"><div class="card"><div class="card-body">
    <div class="text-muted small">Priorité</div>
    <span class="badge text-bg-<?= $cls ?> fs-6"><?= htmlspecialchars($tache['libellePriorite']) ?></span>
  </div></div></div>
  <div class="col-sm-4"><div class="card"><div class="card-body">
    <div class="text-muted small">Statut</div>
    <div class="fs-5"><?= htmlspecialchars($tache['libelleStatut']) ?></div>
  </div></div></div>
  <div class="col-sm-4"><div class="card"><div class="card-body">
    <div class="text-muted small">Échéance</div>
    <div class="fs-5"><?= $tache['dateEcheance'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($tache['dateEcheance']))) : '—' ?></div>
  </div></div></div>
</div>

<?php if (!empty($tache['descriptionTache'])): ?>
<div class="card mb-4"><div class="card-body">
  <h2 class="h6 text-muted">Description</h2>
  <p class="mb-0"><?= nl2br(htmlspecialchars($tache['descriptionTache'])) ?></p>
</div></div>
<?php endif; ?>

<h2 class="h5">Collaborateurs affectés</h2>
<?php if (empty($collaborateurs)): ?>
  <p class="text-muted">Aucun collaborateur affecté.</p>
<?php else: ?>
  <table class="table">
    <thead><tr><th>Nom</th><th class="text-end">% d'investissement</th><th>Affecté le</th></tr></thead>
    <tbody>
    <?php foreach ($collaborateurs as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['nomCollaborateur']) ?></td>
        <td class="text-end"><strong><?= (int) $c['pourcentageInvestissement'] ?> %</strong></td>
        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['dateAffectation']))) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<a class="btn btn-outline-secondary mt-3" href="<?= $base ?>/?r=tache/index">← Retour à la liste</a>

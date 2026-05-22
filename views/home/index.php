<?php
$config = require __DIR__ . '/../../config/config.php';
$base = $config['app']['base_url'];
?>
<h1 class="h3 mb-4">Tableau de bord</h1>

<div class="row g-3 mb-4">
  <?php foreach ($counts as $lib => $n): ?>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="text-muted small"><?= htmlspecialchars($lib) ?></div>
          <div class="display-6 fw-bold"><?= (int) $n ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-4">
  <div class="col-md-7">
    <h2 class="h5">Prochaines tâches</h2>
    <?php if (empty($upcoming)): ?>
      <p class="text-muted">Aucune tâche pour le moment.</p>
    <?php else: ?>
      <ul class="list-group">
        <?php foreach ($upcoming as $t): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span>
              <a href="<?= $base ?>/?r=tache/show&id=<?= (int)$t['idTache'] ?>" class="text-decoration-none">
                <?= htmlspecialchars($t['titreTache']) ?>
              </a>
              <small class="text-muted ms-2">[<?= htmlspecialchars($t['libelleStatut']) ?>]</small>
            </span>
            <span class="badge text-bg-secondary"><?= htmlspecialchars($t['libellePriorite']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="col-md-5">
    <h2 class="h5">Charge collaborateurs</h2>
    <?php if (empty($workload)): ?>
      <p class="text-muted">Pas encore de collaborateurs.</p>
    <?php else: ?>
      <table class="table table-sm">
        <thead><tr><th>Collaborateur</th><th class="text-end">Tâches</th><th class="text-end">% total</th></tr></thead>
        <tbody>
          <?php foreach ($workload as $w): ?>
            <tr>
              <td><?= htmlspecialchars($w['nomCollaborateur']) ?></td>
              <td class="text-end"><?= (int) $w['nbTaches'] ?></td>
              <td class="text-end"><?= (int) $w['investTotal'] ?> %</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

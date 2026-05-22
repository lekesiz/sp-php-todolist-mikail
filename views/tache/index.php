<?php
$config = require __DIR__ . '/../../config/config.php';
$base = $config['app']['base_url'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Tâches <?php if ($search): ?><small class="text-muted">(résultats pour « <?= htmlspecialchars($search) ?> »)</small><?php endif; ?></h1>
  <a class="btn btn-primary" href="<?= $base ?>/?r=tache/create">+ Nouvelle</a>
</div>

<form method="get" class="mb-3">
  <input type="hidden" name="r" value="tache/index">
  <div class="row g-2 align-items-center">
    <div class="col-md-4">
      <input type="search" name="q" class="form-control" placeholder="🔍 Rechercher titre ou description…"
             value="<?= htmlspecialchars($search ?? '') ?>">
    </div>
    <div class="col-md-3">
      <select name="statut" class="form-select" onchange="this.form.submit()">
        <option value="">— Tous statuts —</option>
        <?php foreach ($statuts as $s): ?>
          <option value="<?= (int)$s['idStatut'] ?>" <?= ($filterStatut == $s['idStatut'])?'selected':'' ?>>
            <?= htmlspecialchars($s['libelleStatut']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-outline-primary w-100">Filtrer</button>
    </div>
    <div class="col-md-3 text-end">
      <small class="text-muted"><strong><?= (int) $total ?></strong> tâche(s) total</small>
    </div>
  </div>
</form>

<?php if (empty($taches)): ?>
  <p class="text-muted">
    <?php if ($search): ?>
      Aucune tâche ne correspond à <em>« <?= htmlspecialchars($search) ?> »</em>.
    <?php else: ?>
      Aucune tâche pour le moment.
    <?php endif; ?>
  </p>
<?php else: ?>
  <div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead>
      <tr>
        <th>Titre</th><th>Priorité</th><th>Statut</th><th>Catégorie</th>
        <th>Échéance</th><th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($taches as $t): ?>
      <tr>
        <td>
          <a href="<?= $base ?>/?r=tache/show&id=<?= (int)$t['idTache'] ?>" class="text-decoration-none fw-medium">
            <?= htmlspecialchars($t['titreTache']) ?>
          </a>
        </td>
        <td>
          <?php $lvl = (int) $t['niveauPriorite']; $cls = ['','secondary','info','warning','danger'][$lvl] ?? 'secondary'; ?>
          <span class="badge text-bg-<?= $cls ?>"><?= htmlspecialchars($t['libellePriorite']) ?></span>
        </td>
        <td><?= htmlspecialchars($t['libelleStatut']) ?></td>
        <td>
          <?php if (!empty($t['libelleCategorie'])): ?>
            <span class="badge" style="background-color: <?= htmlspecialchars($t['couleurCategorie']) ?>; color:#fff;">
              <?= htmlspecialchars($t['libelleCategorie']) ?>
            </span>
          <?php endif; ?>
        </td>
        <td><?= $t['dateEcheance'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($t['dateEcheance']))) : '—' ?></td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-secondary" href="<?= $base ?>/?r=tache/show&id=<?= (int)$t['idTache'] ?>">Voir</a>
          <a class="btn btn-sm btn-outline-primary" href="<?= $base ?>/?r=tache/edit&id=<?= (int)$t['idTache'] ?>">Modifier</a>
          <form method="post" action="<?= $base ?>/?r=tache/delete&id=<?= (int)$t['idTache'] ?>" class="d-inline"
                onsubmit="return confirm('Supprimer cette tâche ?');">
            <input type="hidden" name="_csrf" value="<?= App\Csrf::token() ?>">
            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>

  <?php if ($totalPages > 1): ?>
  <nav aria-label="Pagination" class="mt-3">
    <ul class="pagination justify-content-center">
      <?php
        $base = $config['app']['base_url'] . '/?r=tache/index';
        if ($filterStatut !== null && $filterStatut !== '') $base .= '&statut=' . (int) $filterStatut;
        if ($search) $base .= '&q=' . urlencode($search);
      ?>
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= $base ?>&page=<?= max(1, $page - 1) ?>">←</a>
      </li>
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= $base ?>&page=<?= $p ?>"><?= $p ?></a>
        </li>
      <?php endfor; ?>
      <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= $base ?>&page=<?= min($totalPages, $page + 1) ?>">→</a>
      </li>
    </ul>
  </nav>
  <?php endif; ?>
<?php endif; ?>

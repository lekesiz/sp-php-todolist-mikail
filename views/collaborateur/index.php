<?php
$config = require __DIR__ . '/../../config/config.php';
$base = $config['app']['base_url'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Collaborateurs</h1>
  <a class="btn btn-primary" href="<?= $base ?>/?r=collaborateur/create">+ Nouveau</a>
</div>

<?php if (empty($workload)): ?>
  <p class="text-muted">Aucun collaborateur.</p>
<?php else: ?>
  <table class="table table-hover align-middle">
    <thead><tr><th>Nom</th><th class="text-end">Tâches</th><th class="text-end">% total</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($workload as $w): ?>
      <tr>
        <td><?= htmlspecialchars($w['nomCollaborateur']) ?></td>
        <td class="text-end"><?= (int) $w['nbTaches'] ?></td>
        <td class="text-end"><?= (int) $w['investTotal'] ?> %</td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-primary" href="<?= $base ?>/?r=collaborateur/edit&id=<?= (int)$w['idCollaborateur'] ?>">Modifier</a>
          <form method="post" action="<?= $base ?>/?r=collaborateur/delete&id=<?= (int)$w['idCollaborateur'] ?>" class="d-inline"
                onsubmit="return confirm('Supprimer ce collaborateur ? (Toutes ses affectations seront perdues.)');">
            <input type="hidden" name="_csrf" value="<?= App\Csrf::token() ?>">
            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php
$config = require __DIR__ . '/../../config/config.php';
$base = $config['app']['base_url'];
$isEdit = $tache !== null;
$formAction = $isEdit
    ? $base . '/?r=tache/update&id=' . (int) $tache['idTache']
    : $base . '/?r=tache/store';
$echeance = !empty($data['dateEcheance']) ? date('Y-m-d\TH:i', strtotime($data['dateEcheance'])) : '';
?>
<h1 class="h3 mb-3"><?= $isEdit ? 'Modifier la tâche' : 'Nouvelle tâche' ?></h1>

<form method="post" action="<?= htmlspecialchars($formAction) ?>" class="row g-3">
  <input type="hidden" name="_csrf" value="<?= App\Csrf::token() ?>">

  <div class="col-md-8">
    <label class="form-label" for="titreTache">Titre *</label>
    <input type="text" id="titreTache" name="titreTache" maxlength="150" required
           class="form-control" value="<?= htmlspecialchars($data['titreTache'] ?? '') ?>">
  </div>

  <div class="col-md-4">
    <label class="form-label" for="dateEcheance">Échéance</label>
    <input type="datetime-local" id="dateEcheance" name="dateEcheance"
           class="form-control" value="<?= htmlspecialchars($echeance) ?>">
  </div>

  <div class="col-md-4">
    <label class="form-label" for="idPriorite">Priorité *</label>
    <select id="idPriorite" name="idPriorite" class="form-select" required>
      <?php foreach ($priorites as $p): ?>
        <option value="<?= (int) $p['idPriorite'] ?>" <?= ($data['idPriorite'] == $p['idPriorite']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($p['libellePriorite']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label" for="idStatut">Statut *</label>
    <select id="idStatut" name="idStatut" class="form-select" required>
      <?php foreach ($statuts as $s): ?>
        <option value="<?= (int) $s['idStatut'] ?>" <?= ($data['idStatut'] == $s['idStatut']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($s['libelleStatut']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label" for="idCategorie">Catégorie</label>
    <select id="idCategorie" name="idCategorie" class="form-select">
      <option value="">—</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int) $c['idCategorie'] ?>" <?= ($data['idCategorie'] == $c['idCategorie']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($c['libelleCategorie']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-12">
    <label class="form-label" for="descriptionTache">Description</label>
    <textarea id="descriptionTache" name="descriptionTache" rows="3"
              class="form-control"><?= htmlspecialchars($data['descriptionTache'] ?? '') ?></textarea>
  </div>

  <div class="col-12">
    <h2 class="h6 mt-3 mb-2">Affectation aux collaborateurs (% d'investissement)</h2>
    <?php if (empty($collaborateurs)): ?>
      <p class="text-muted small">
        Aucun collaborateur. <a href="<?= $base ?>/?r=collaborateur/create">Ajoutez-en un</a> avant d'affecter.
      </p>
    <?php else: ?>
      <div class="row g-2">
        <?php foreach ($collaborateurs as $c):
          $idC = (int) $c['idCollaborateur'];
          $val = $assignedMap[$idC] ?? 0;
        ?>
          <div class="col-md-6 col-lg-4">
            <div class="input-group">
              <span class="input-group-text" style="min-width: 60%; justify-content:flex-start;">
                <?= htmlspecialchars($c['nomCollaborateur']) ?>
              </span>
              <input type="number" name="collaborateur[<?= $idC ?>]" min="0" max="100"
                     class="form-control text-end" placeholder="0"
                     value="<?= $val > 0 ? $val : '' ?>">
              <span class="input-group-text">%</span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <small class="text-muted">Laisser vide ou 0 pour ne pas affecter ce collaborateur.</small>
    <?php endif; ?>
  </div>

  <div class="col-12 mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">
      <?= $isEdit ? 'Enregistrer' : 'Créer la tâche' ?>
    </button>
    <a class="btn btn-outline-secondary" href="<?= $base ?>/?r=tache/index">Annuler</a>
  </div>
</form>

<?php
$config = require __DIR__ . '/../../config/config.php';
$base = $config['app']['base_url'];
$isEdit = $collaborateur !== null;
$formAction = $isEdit
    ? $base . '/?r=collaborateur/update&id=' . (int) $collaborateur['idCollaborateur']
    : $base . '/?r=collaborateur/store';
?>
<h1 class="h3 mb-3"><?= $isEdit ? 'Modifier le collaborateur' : 'Nouveau collaborateur' ?></h1>

<form method="post" action="<?= htmlspecialchars($formAction) ?>" class="row g-3" style="max-width: 480px;">
  <input type="hidden" name="_csrf" value="<?= App\Csrf::token() ?>">

  <div class="col-12">
    <label class="form-label" for="nomCollaborateur">Nom *</label>
    <input type="text" id="nomCollaborateur" name="nomCollaborateur" maxlength="80" required
           class="form-control" value="<?= htmlspecialchars($data['nomCollaborateur'] ?? '') ?>">
  </div>

  <div class="col-12 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
    <a class="btn btn-outline-secondary" href="<?= $base ?>/?r=collaborateur/index">Annuler</a>
  </div>
</form>

<!DOCTYPE html>
<html lang="fr"><head>
<meta charset="utf-8"><title>500 — Erreur serveur</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head><body class="bg-light">
<div class="container py-5">
  <h1 class="display-5">500 — Erreur serveur</h1>
  <p>Une erreur inattendue est survenue.</p>
  <?php if (!empty($debug) && isset($e)): ?>
    <div class="card mt-3"><div class="card-body">
      <strong><?= htmlspecialchars(get_class($e)) ?></strong><br>
      <?= htmlspecialchars($e->getMessage()) ?>
      <pre class="small mt-2 mb-0"><?= htmlspecialchars($e->getTraceAsString()) ?></pre>
    </div></div>
  <?php endif; ?>
</div>
</body></html>

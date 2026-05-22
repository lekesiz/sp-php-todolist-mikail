<?php
use App\Csrf;
$config = require __DIR__ . '/../../config/config.php';
$base = $config['app']['base_url'];
$pageTitle = $title ?? 'To Do List';
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> · To Do List</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= $base ?>/?r=home/index">📋 To Do List</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= $base ?>/?r=tache/index">Tâches</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $base ?>/?r=collaborateur/index">Collaborateurs</a></li>
      </ul>
      <a class="btn btn-primary btn-sm" href="<?= $base ?>/?r=tache/create">+ Nouvelle tâche</a>
    </div>
  </div>
</nav>

<main class="container pb-5">

<?php if (!empty($flashes)): ?>
  <?php foreach ($flashes as $f): ?>
    <div class="alert alert-<?= $f['type']==='error'?'danger':htmlspecialchars($f['type']) ?> alert-dismissible fade show">
      <?= htmlspecialchars($f['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

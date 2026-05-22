<?php
namespace App\Controllers;

use App\Controller;
use App\Csrf;
use App\Flash;
use App\Models\Collaborateur;

final class CollaborateurController extends Controller
{
    public function index(): void
    {
        $this->render('collaborateur/index', [
            'workload' => Collaborateur::workload(),
        ], 'Collaborateurs');
    }

    public function create(): void
    {
        $old = $_SESSION['old'] ?? ['nomCollaborateur' => ''];
        unset($_SESSION['old']);
        $this->render('collaborateur/form', [
            'data' => $old, 'collaborateur' => null,
        ], 'Nouveau collaborateur');
    }

    public function store(): void
    {
        $this->requirePost();
        $errors = $this->validate($_POST);
        if ($errors) {
            $_SESSION['old'] = $_POST;
            foreach ($errors as $e) Flash::set('error', $e);
            $this->redirect('/?r=collaborateur/create');
            return;
        }
        try {
            Collaborateur::create($_POST['nomCollaborateur']);
            Csrf::rotate();
            Flash::set('success', 'Collaborateur ajouté.');
        } catch (\PDOException $e) {
            Flash::set('error', 'Nom déjà utilisé.');
        }
        $this->redirect('/?r=collaborateur/index');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $c = Collaborateur::find($id);
        if (!$c) { $this->notFound(); return; }
        $old = $_SESSION['old'] ?? $c;
        unset($_SESSION['old']);
        $this->render('collaborateur/form', [
            'data' => $old, 'collaborateur' => $c,
        ], 'Modifier le collaborateur');
    }

    public function update(): void
    {
        $this->requirePost();
        $id = (int) ($_GET['id'] ?? 0);
        if (!Collaborateur::find($id)) { $this->notFound(); return; }
        $errors = $this->validate($_POST);
        if ($errors) {
            $_SESSION['old'] = $_POST;
            foreach ($errors as $e) Flash::set('error', $e);
            $this->redirect('/?r=collaborateur/edit&id=' . $id);
            return;
        }
        try {
            Collaborateur::update($id, $_POST['nomCollaborateur']);
            Csrf::rotate();
            Flash::set('success', 'Collaborateur mis à jour.');
        } catch (\PDOException $e) {
            Flash::set('error', 'Nom déjà utilisé.');
        }
        $this->redirect('/?r=collaborateur/index');
    }

    public function delete(): void
    {
        $this->requirePost();
        $id = (int) ($_GET['id'] ?? 0);
        Collaborateur::delete($id);
        Csrf::rotate();
        Flash::set('success', 'Collaborateur supprimé (et toutes ses affectations).');
        $this->redirect('/?r=collaborateur/index');
    }

    // ---- helpers ----
    /** @return array<int,string> */
    private function validate(array $data): array
    {
        $errs = [];
        if (!Csrf::check($data['_csrf'] ?? null))                  $errs[] = 'Jeton CSRF invalide.';
        $nom = trim($data['nomCollaborateur'] ?? '');
        if ($nom === '')                                           $errs[] = 'Le nom est obligatoire.';
        if (mb_strlen($nom) > 80)                                  $errs[] = 'Le nom dépasse 80 caractères.';
        return $errs;
    }

    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Méthode non autorisée.');
        }
    }

    private function notFound(): void
    {
        http_response_code(404);
        Flash::set('error', 'Collaborateur introuvable.');
        $this->redirect('/?r=collaborateur/index');
    }
}

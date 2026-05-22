<?php
namespace App\Controllers;

use App\Controller;
use App\Csrf;
use App\Flash;
use App\Models\Tache;
use App\Models\Collaborateur;
use App\Models\Reference;

final class TacheController extends Controller
{
    // GET /index.php?r=tache/index[&statut=2&q=foo&page=1]
    public function index(): void
    {
        $statut  = $_GET['statut'] ?? null;
        $search  = trim((string) ($_GET['q'] ?? '')) ?: null;
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        $taches    = Tache::all($statut, $search, $page, $perPage);
        $total     = Tache::count($statut, $search);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $statuts   = Reference::statuts();

        $this->render('tache/index', [
            'taches'        => $taches,
            'statuts'       => $statuts,
            'filterStatut'  => $statut,
            'search'        => $search,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'total'         => $total,
        ], 'Liste des tâches');
    }

    // GET /index.php?r=tache/create
    public function create(): void
    {
        $this->renderForm(null);
    }

    // POST /index.php?r=tache/store
    public function store(): void
    {
        $this->requirePost();
        $errors = $this->validate($_POST);
        if ($errors) {
            $_SESSION['old'] = $_POST;
            foreach ($errors as $e) Flash::set('error', $e);
            $this->redirect('/?r=tache/create');
            return;
        }

        $id = Tache::create($_POST);
        $this->syncAssignments($id, $_POST);

        Csrf::rotate();
        Flash::set('success', 'Tâche créée.');
        $this->redirect('/?r=tache/show&id=' . $id);
    }

    // GET /index.php?r=tache/show&id=X
    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $tache = Tache::find($id);
        if (!$tache) { $this->notFound(); return; }
        $collaborateurs = Tache::collaborateurs($id);
        $this->render('tache/show', [
            'tache'          => $tache,
            'collaborateurs' => $collaborateurs,
        ], 'Tâche : ' . $tache['titreTache']);
    }

    // GET /index.php?r=tache/edit&id=X
    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $tache = Tache::find($id);
        if (!$tache) { $this->notFound(); return; }
        $this->renderForm($tache);
    }

    // POST /index.php?r=tache/update&id=X
    public function update(): void
    {
        $this->requirePost();
        $id = (int) ($_GET['id'] ?? 0);
        if (!Tache::find($id)) { $this->notFound(); return; }

        $errors = $this->validate($_POST);
        if ($errors) {
            $_SESSION['old'] = $_POST;
            foreach ($errors as $e) Flash::set('error', $e);
            $this->redirect('/?r=tache/edit&id=' . $id);
            return;
        }

        Tache::update($id, $_POST);
        $this->syncAssignments($id, $_POST);

        Csrf::rotate();
        Flash::set('success', 'Tâche mise à jour.');
        $this->redirect('/?r=tache/show&id=' . $id);
    }

    // POST /index.php?r=tache/delete&id=X
    public function delete(): void
    {
        $this->requirePost();
        $id = (int) ($_GET['id'] ?? 0);
        Tache::delete($id);
        Csrf::rotate();
        Flash::set('success', 'Tâche supprimée.');
        $this->redirect('/?r=tache/index');
    }

    // ---------------- Helpers internes ----------------

    private function renderForm(?array $tache): void
    {
        $old = $_SESSION['old'] ?? null;
        unset($_SESSION['old']);

        $data = $old ?? $tache ?? [
            'idPriorite' => 2, 'idStatut' => 1, 'idCategorie' => '',
            'titreTache' => '', 'descriptionTache' => '', 'dateEcheance' => null,
        ];
        $assigned = $tache ? Tache::collaborateurs((int) $tache['idTache']) : [];
        $assignedMap = [];
        foreach ($assigned as $a) $assignedMap[$a['idCollaborateur']] = (int) $a['pourcentageInvestissement'];

        $this->render('tache/form', [
            'data'           => $data,
            'tache'          => $tache,
            'priorites'      => Reference::priorites(),
            'statuts'        => Reference::statuts(),
            'categories'     => Reference::categories(),
            'collaborateurs' => Collaborateur::all(),
            'assignedMap'    => $assignedMap,
        ], $tache ? 'Modifier la tâche' : 'Nouvelle tâche');
    }

    /** @return array<int,string> */
    private function validate(array $data): array
    {
        $errors = [];
        if (!Csrf::check($data['_csrf'] ?? null))                  $errors[] = 'Jeton CSRF invalide.';
        if (empty(trim($data['titreTache'] ?? '')))                $errors[] = 'Le titre est obligatoire.';
        if (mb_strlen($data['titreTache'] ?? '') > 150)            $errors[] = 'Le titre dépasse 150 caractères.';
        if (empty($data['idPriorite']) || !is_numeric($data['idPriorite'])) $errors[] = 'Priorité invalide.';
        if (empty($data['idStatut'])   || !is_numeric($data['idStatut']))   $errors[] = 'Statut invalide.';
        if (!empty($data['dateEcheance']) && !strtotime($data['dateEcheance'])) $errors[] = 'Date d\'échéance invalide.';

        // Validation des % d'investissement
        $collabs = $data['collaborateur'] ?? [];
        if (is_array($collabs)) {
            foreach ($collabs as $idC => $p) {
                if (!is_numeric($p) || (int)$p < 0 || (int)$p > 100) {
                    $errors[] = "Pourcentage invalide pour le collaborateur #$idC (doit être entre 0 et 100).";
                }
            }
        }
        return $errors;
    }

    private function syncAssignments(int $idTache, array $data): void
    {
        $assignments = [];
        $collabs = $data['collaborateur'] ?? [];
        if (is_array($collabs)) {
            foreach ($collabs as $idCollab => $pourcent) {
                $pourcent = (int) $pourcent;
                if ($pourcent > 0) {
                    $assignments[(int) $idCollab] = $pourcent;
                }
            }
        }
        Tache::syncCollaborateurs($idTache, $assignments);
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
        Flash::set('error', 'Tâche introuvable.');
        $this->redirect('/?r=tache/index');
    }
}

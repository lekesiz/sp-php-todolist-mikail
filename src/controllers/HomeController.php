<?php
namespace App\Controllers;

use App\Controller;
use App\Models\Tache;
use App\Models\Collaborateur;

final class HomeController extends Controller
{
    public function index(): void
    {
        $counts    = Tache::countsByStatut();
        $workload  = Collaborateur::workload();
        $upcoming  = array_slice(Tache::all(), 0, 5);

        $this->render('home/index', [
            'counts'   => $counts,
            'workload' => $workload,
            'upcoming' => $upcoming,
        ], 'Tableau de bord');
    }
}

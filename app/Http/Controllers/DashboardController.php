<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Aiguille l'utilisateur vers le bon tableau de bord selon son rôle. */
    public function __invoke(Request $request)
    {
        return $request->user()->isOrganizer()
            ? redirect()->route('organizer.events.index')
            : redirect()->route('events.index');
    }
}

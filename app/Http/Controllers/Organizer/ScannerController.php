<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Scanner web de démonstration pour l'organisateur.
 *
 * La page génère un token Sanctum à usage du navigateur puis appelle
 * la VRAIE API protégée (POST /api/v1/tickets/validate), exactement comme
 * le ferait l'application mobile de scan.
 */
class ScannerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Un seul token "web-scanner" actif à la fois
        $user->tokens()->where('name', 'web-scanner')->delete();
        $token = $user->createToken('web-scanner', ['ticket:validate'])->plainTextToken;

        $events = $user->events()->orderByDesc('starts_at')->get();

        return view('organizer.scanner', [
            'apiToken' => $token,
            'events' => $events,
        ]);
    }
}

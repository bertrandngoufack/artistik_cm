<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Vérifier si l'utilisateur est connecté
        $session = session();
        
        // Si pas de session ou utilisateur non connecté
        if (!$session->has('user_id') || !$session->has('user_role')) {
            // Rediriger vers la page de connexion
            return redirect()->to('/auth/login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }
        
        // Vérifier si l'utilisateur a le rôle requis
        $userRole = $session->get('user_role');
        $allowedRoles = ['admin', 'directeur', 'secretaire', 'enseignant'];
        
        if (!in_array($userRole, $allowedRoles)) {
            // Rediriger vers la page d'accueil avec erreur
            return redirect()->to('/')->with('error', 'Accès non autorisé. Rôle insuffisant.');
        }
        
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}





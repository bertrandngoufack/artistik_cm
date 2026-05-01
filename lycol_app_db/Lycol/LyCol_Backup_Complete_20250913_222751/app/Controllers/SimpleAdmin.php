<?php

namespace App\Controllers;

/**
 * Contrôleur admin simplifié pour diagnostic
 */
class SimpleAdmin extends BaseController
{
    /**
     * Dashboard simplifié
     */
    public function index()
    {
        return "SimpleAdmin::index() - Fonctionne !";
    }
    
    /**
     * Test de base
     */
    public function test()
    {
        return "SimpleAdmin::test() - Fonctionne !";
    }
}

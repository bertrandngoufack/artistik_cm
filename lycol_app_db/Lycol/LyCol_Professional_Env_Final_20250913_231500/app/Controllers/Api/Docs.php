<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class Docs extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Documentation API - LyCol',
            'swagger_url' => base_url('api/docs/swagger.json')
        ];

        return view('api/docs/index', $data);
    }

    public function show($endpoint = null)
    {
        $data = [
            'title' => 'Documentation API - ' . ucfirst($endpoint),
            'endpoint' => $endpoint
        ];

        return view('api/docs/show', $data);
    }
}





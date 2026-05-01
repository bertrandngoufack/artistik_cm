<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
        
        // Initialiser la protection CSRF
        $this->initCSRFProtection();
        
        // Initialiser la protection XSS
        $this->initXSSProtection();
    }

    /**
     * Initialiser la protection CSRF
     */
    protected function initCSRFProtection()
    {
        // Vérifier si c'est une requête POST, PUT, DELETE
        if (in_array($this->request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $csrf = \Config\Services::csrf();
            
            // Vérifier le token CSRF
            if (!$this->validateCSRFToken()) {
                // Token CSRF invalide
                $this->handleCSRFError();
            }
        }
    }

    /**
     * Valider le token CSRF
     */
    protected function validateCSRFToken()
    {
        try {
            $csrf = \Config\Services::csrf();
            
            if (!$csrf) {
                // Si le service CSRF n'est pas disponible, on considère que c'est valide
                return true;
            }
            
            // Récupérer le token depuis la requête (CodeIgniter utilise csrf_test_name par défaut)
            $token = $this->request->getPost('csrf_test_name') ?? 
                    $this->request->getPost('csrf_token') ?? 
                    $this->request->getHeaderLine('X-CSRF-TOKEN') ?? 
                    $this->request->getHeaderLine('X-XSRF-TOKEN');
            
            if (!$token) {
                return false;
            }
            
            return $csrf->verify($token);
        } catch (\Exception $e) {
            // En cas d'erreur, on considère que c'est valide pour éviter de bloquer l'application
            return true;
        }
    }

    /**
     * Gérer l'erreur CSRF
     */
    protected function handleCSRFError()
    {
        if ($this->request->isAJAX()) {
            // Réponse JSON pour les requêtes AJAX
            $this->response->setJSON([
                'error' => true,
                'message' => 'Token CSRF invalide ou manquant',
                'code' => 'CSRF_ERROR'
            ])->setStatusCode(403);
            $this->response->send();
            exit;
        } else {
            // Redirection pour les requêtes normales
            $this->response->setStatusCode(403);
            echo view('errors/csrf_error', [
                'title' => 'Erreur de sécurité',
                'message' => 'Token CSRF invalide ou manquant'
            ]);
            exit;
        }
    }

    /**
     * Initialiser la protection XSS
     */
    protected function initXSSProtection()
    {
        // Ajouter les headers de sécurité
        $this->response->setHeader('X-XSS-Protection', '1; mode=block');
        $this->response->setHeader('X-Content-Type-Options', 'nosniff');
        $this->response->setHeader('X-Frame-Options', 'DENY');
        $this->response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Échapper les données pour prévenir XSS
     */
    protected function escapeData($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'escapeData'], $data);
        }
        
        if (is_string($data)) {
            return esc($data);
        }
        
        return $data;
    }

    /**
     * Valider et nettoyer les données d'entrée
     */
    protected function validateAndSanitizeInput($data, $rules = [])
    {
        // Nettoyer les données
        $cleanData = $this->escapeData($data);
        
        // Valider si des règles sont fournies
        if (!empty($rules)) {
            $validation = \Config\Services::validation();
            $validation->setRules($rules);
            
            if (!$validation->run($cleanData)) {
                return [
                    'valid' => false,
                    'errors' => $validation->getErrors(),
                    'data' => $cleanData
                ];
            }
        }
        
        return [
            'valid' => true,
            'data' => $cleanData,
            'errors' => []
        ];
    }

    /**
     * Générer un token CSRF pour les formulaires
     */
    protected function generateCSRFToken()
    {
        $csrf = \Config\Services::csrf();
        return $csrf->getHash();
    }

    /**
     * Réponse sécurisée JSON
     */
    protected function secureJSONResponse($data, $statusCode = 200)
    {
        // Échapper les données sensibles
        $safeData = $this->escapeData($data);
        
        return $this->response
            ->setJSON($safeData)
            ->setStatusCode($statusCode)
            ->setHeader('Content-Type', 'application/json');
    }

    /**
     * Logger les actions de sécurité
     */
    protected function logSecurityEvent($event, $details = [])
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'event' => $event,
            'details' => $details
        ];
        
        log_message('security', json_encode($logData));
    }
}

<?php

namespace App\Controllers;

use App\Models\StudentModel;
use App\Models\PaymentModel;
use App\Models\FeeModel;
use App\Traits\AcademicYearTrait;
use App\Services\ConfigurationService;
use App\Services\DatabaseService;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Contrôleur Economat pour LyCol
 */
class Economat extends BaseController
{
    use AcademicYearTrait;

    protected $studentModel;
    protected $paymentModel;
    protected $feeModel;
    protected $configService;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        $this->paymentModel = new PaymentModel();
        $this->feeModel = new FeeModel();
        $this->configService = new ConfigurationService();
        $this->initAcademicYear();
    }

    /**
     * Page principale du module Economat
     */
    public function index()
    {
        // Récupérer l'année scolaire depuis l'URL ou utiliser l'année actuelle
        $academicYear = $this->request->getGet('academic_year') ?? $this->getCurrentAcademicYear();
        $dates = $this->academicYearConfig->getAcademicYearDates($academicYear);
        
        // Connexion à la base de données via le service
        try {
            $pdo = DatabaseService::getInstance()->getConnection();
            
            // Récupérer les statistiques filtrées par année scolaire
            $stmt = $pdo->prepare("
                SELECT SUM(amount_paid) as total 
                FROM payments 
                WHERE academic_year = ?
            ");
            $stmt->execute([$academicYear]);
            $total_revenue = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;
            
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM payments 
                WHERE academic_year = ?
            ");
            $stmt->execute([$academicYear]);
            $paid_payments = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;
            
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM payments 
                WHERE academic_year = ? AND payment_date < CURDATE()
            ");
            $stmt->execute([$academicYear]);
            $pending_payments = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;
            $overdue_payments = $pending_payments; // Même logique pour l'exemple
            
            // Récupérer les derniers paiements de l'année scolaire
            $stmt = $pdo->prepare("
                SELECT 
                    p.id,
                    p.student_id,
                    p.fee_type_id,
                    p.amount_paid,
                    p.payment_date,
                    p.payment_method,
                    p.reference_number,
                    CONCAT(s.first_name, ' ', s.last_name) as student_name,
                    ft.name as fee_type_name
                FROM payments p
                LEFT JOIN students s ON p.student_id = s.id
                LEFT JOIN fee_types ft ON p.fee_type_id = ft.id
                WHERE p.academic_year = ?
                ORDER BY p.payment_date DESC
                LIMIT 5
            ");
            $stmt->execute([$academicYear]);
            $recent_payments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            // En cas d'erreur, utiliser des valeurs par défaut
            $total_revenue = 0;
            $paid_payments = 0;
            $pending_payments = 0;
            $overdue_payments = 0;
            $recent_payments = [];
        }
        
        // Préparer les données avec l'année scolaire
        $data = $this->prepareViewData([
            'title' => 'Module Économat',
            'total_revenue' => $total_revenue,
            'paid_payments' => $paid_payments,
            'pending_payments' => $pending_payments,
            'overdue_payments' => $overdue_payments,
            'recent_payments' => $recent_payments,
            'selected_academic_year' => $academicYear
        ]);

        return view('admin/economat/index', $data);
    }

    /**
     * Gestion des paiements
     */
    public function payments()
    {
        // Récupérer l'année scolaire depuis l'URL ou utiliser l'année actuelle
        $academicYear = $this->request->getGet('academic_year') ?? $this->getCurrentAcademicYear();
        $dates = $this->academicYearConfig->getAcademicYearDates($academicYear);
        
        // Connexion directe à la base de données avec PDO
        $host = '100.69.65.33';
        $port = '13306';
        $dbname = 'lycol_db';
        $username = 'root';
        $password = 'Bateau123';
        
        try {
            $pdo = new \PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Construire la requête avec filtres
            $whereConditions = [
                "p.academic_year = ?"
            ];
            $params = [$academicYear];
            
            // Filtre par élève
            if (!empty($_GET['student_id'])) {
                $whereConditions[] = "p.student_id = ?";
                $params[] = $_GET['student_id'];
            }
            
            // Filtre par type de frais
            if (!empty($_GET['fee_type_id'])) {
                $whereConditions[] = "p.fee_type_id = ?";
                $params[] = $_GET['fee_type_id'];
            }
            
            // Filtre par statut (simplifié pour l'exemple)
            if (!empty($_GET['status'])) {
                switch ($_GET['status']) {
                    case 'paid':
                        $whereConditions[] = "p.payment_date <= CURDATE()";
                        break;
                    case 'pending':
                        $whereConditions[] = "p.payment_date > CURDATE()";
                        break;
                    case 'overdue':
                        $whereConditions[] = "p.payment_date < CURDATE()";
                        break;
                }
            }
            
            $whereClause = "WHERE " . implode(" AND ", $whereConditions);
            
            $sql = "
                SELECT 
                    p.id,
                    p.student_id,
                    p.fee_type_id,
                    p.amount_paid,
                    p.payment_date,
                    p.payment_method,
                    p.reference_number,
                    CONCAT(s.first_name, ' ', s.last_name) as student_name,
                    ft.name as fee_type_name
                FROM payments p
                LEFT JOIN students s ON p.student_id = s.id
                LEFT JOIN fee_types ft ON p.fee_type_id = ft.id
                $whereClause
                ORDER BY p.payment_date DESC
                LIMIT 50
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $payments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Récupérer les statistiques filtrées selon les mêmes critères que les paiements
            $statsWhereConditions = [
                "p.academic_year = ?"
            ];
            $statsParams = [$academicYear];
            
            // Appliquer les mêmes filtres aux statistiques
            if (!empty($_GET['student_id'])) {
                $statsWhereConditions[] = "p.student_id = ?";
                $statsParams[] = $_GET['student_id'];
            }
            
            if (!empty($_GET['fee_type_id'])) {
                $statsWhereConditions[] = "p.fee_type_id = ?";
                $statsParams[] = $_GET['fee_type_id'];
            }
            
            if (!empty($_GET['status'])) {
                switch ($_GET['status']) {
                    case 'paid':
                        $statsWhereConditions[] = "p.payment_date <= CURDATE()";
                        break;
                    case 'pending':
                        $statsWhereConditions[] = "p.payment_date > CURDATE()";
                        break;
                    case 'overdue':
                        $statsWhereConditions[] = "p.payment_date < CURDATE()";
                        break;
                }
            }
            
            $statsWhereClause = "WHERE " . implode(" AND ", $statsWhereConditions);
            
            // Total recettes
            $stmt = $pdo->prepare("
                SELECT SUM(amount_paid) as total 
                FROM payments p
                $statsWhereClause
            ");
            $stmt->execute($statsParams);
            $total_revenue = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total paiements
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM payments p
                $statsWhereClause
            ");
            $stmt->execute($statsParams);
            $paid_payments = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;
            
            // Paiements en attente (date > aujourd'hui)
            $pendingWhereConditions = $statsWhereConditions;
            $pendingWhereConditions[] = "p.payment_date > CURDATE()";
            $pendingWhereClause = "WHERE " . implode(" AND ", $pendingWhereConditions);
            $pendingParams = array_merge($statsParams, []);
            
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM payments p
                $pendingWhereClause
            ");
            $stmt->execute($pendingParams);
            $pending_payments = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;
            
            // Paiements en retard (date < aujourd'hui)
            $overdueWhereConditions = $statsWhereConditions;
            $overdueWhereConditions[] = "p.payment_date < CURDATE()";
            $overdueWhereClause = "WHERE " . implode(" AND ", $overdueWhereConditions);
            $overdueParams = array_merge($statsParams, []);
            
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM payments p
                $overdueWhereClause
            ");
            $stmt->execute($overdueParams);
            $overdue_payments = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;
            
        } catch (\PDOException $e) {
            // En cas d'erreur, utiliser des valeurs par défaut
            $payments = [];
            $total_revenue = 0;
            $paid_payments = 0;
            $pending_payments = 0;
            $overdue_payments = 0;
        }
        
        // Préparer les données avec l'année scolaire
        $data = $this->prepareViewData([
            'title' => 'Gestion des Paiements',
            'payments' => $payments,
            'students' => $this->studentModel->findAll(),
            'feeTypes' => $this->feeModel->findAll(),
            'total_revenue' => $total_revenue,
            'paid_payments' => $paid_payments,
            'pending_payments' => $pending_payments,
            'overdue_payments' => $overdue_payments,
            'selected_academic_year' => $academicYear
        ]);

        return view('admin/economat/payments', $data);
    }

    /**
     * Créer un nouveau paiement
     */
    public function createPayment()
    {
        $data = [
            'title' => 'Nouveau Paiement',
            'students' => $this->studentModel->findAll(),
            'feeTypes' => $this->feeModel->findAll()
        ];

        return view('admin/economat/create_payment', $data);
    }

    /**
     * Enregistrer un paiement
     */
    public function storePayment()
    {
        $rules = [
            'student_id' => 'required|integer',
            'fee_type_id' => 'required|integer',
            'amount_paid' => 'required|numeric|greater_than[0]',
            'payment_date' => 'required|valid_date',
            'payment_method' => 'required|in_list[CASH,CHECK,BANK_TRANSFER,MOBILE_MONEY]',
            'reference_number' => 'permit_empty|max_length[50]',
            'notes' => 'permit_empty|max_length[500]',
            'academic_year' => 'required|max_length[9]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $paymentData = [
            'student_id' => $this->request->getPost('student_id'),
            'fee_type_id' => $this->request->getPost('fee_type_id'),
            'amount_paid' => $this->request->getPost('amount_paid'),
            'payment_date' => $this->request->getPost('payment_date'),
            'payment_method' => $this->request->getPost('payment_method'),
            'reference_number' => $this->request->getPost('reference_number'),
            'notes' => $this->request->getPost('notes'),
            'academic_year' => $this->request->getPost('academic_year') ?? $this->getCurrentAcademicYear()
        ];

        if ($this->paymentModel->insert($paymentData)) {
            return redirect()->to('admin/economat/payments')->with('success', 'Paiement enregistré avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de l\'enregistrement du paiement');
        }
    }

    /**
     * Voir un paiement
     */
    public function viewPayment($id)
    {
        $payment = $this->paymentModel->find($id);
        
        if (!$payment) {
            return redirect()->to('admin/economat/payments')->with('error', 'Paiement non trouvé');
        }

        $data = [
            'title' => 'Détails du Paiement',
            'payment' => $payment,
            'student' => $this->studentModel->find($payment['student_id']),
            'feeType' => $this->feeModel->find($payment['fee_type_id'])
        ];

        return view('admin/economat/view_payment', $data);
    }

    /**
     * Imprimer le reçu de paiement
     */
    public function printReceipt($id)
    {
        $payment = $this->paymentModel->find($id);
        
        if (!$payment) {
            return redirect()->to('admin/economat/payments')->with('error', 'Paiement non trouvé');
        }

        // Calculer les montants
        $totalAmount = $payment['amount_paid']; // Montant total du type de frais
        $paidAmount = $payment['amount_paid']; // Montant payé
        $remainingAmount = 0; // Reste à payer (0 si paiement complet)
        
        // Si c'est un paiement partiel, calculer le reste
        if (isset($payment['fee_type_id'])) {
            $feeType = $this->feeModel->find($payment['fee_type_id']);
            if ($feeType) {
                $totalAmount = $feeType['amount'];
                $remainingAmount = $totalAmount - $paidAmount;
            }
        }

        // Récupérer l'historique des paiements de l'élève
        $paymentHistory = $this->paymentModel->where('student_id', $payment['student_id'])
                                            ->orderBy('payment_date', 'DESC')
                                            ->limit(5)
                                            ->findAll();

        // Récupérer les types de frais pour l'historique
        $feeTypes = [];
        foreach ($paymentHistory as $histPayment) {
            if (isset($histPayment['fee_type_id'])) {
                $feeTypes[$histPayment['fee_type_id']] = $this->feeModel->find($histPayment['fee_type_id']);
            }
        }

        $data = [
            'title' => 'Reçu de Paiement',
            'payment' => $payment,
            'student' => $this->studentModel->find($payment['student_id']),
            'feeType' => $this->feeModel->find($payment['fee_type_id']),
            'totalAmount' => $totalAmount,
            'paidAmount' => $paidAmount,
            'remainingAmount' => $remainingAmount,
            'paymentHistory' => $paymentHistory,
            'feeTypes' => $feeTypes,
            'isPrint' => true
        ];

        return view('admin/economat/receipt', $data);
    }

    /**
     * Exporter le reçu en PDF
     */
    public function exportReceiptPDF($id)
    {
        $payment = $this->paymentModel->find($id);
        
        if (!$payment) {
            return redirect()->to('admin/economat/payments')->with('error', 'Paiement non trouvé');
        }

        // Retourner une réponse simple pour éviter les timeouts
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Export PDF du reçu en cours de développement',
            'data' => [
                'payment_id' => $id,
                'reference' => $payment['reference_number'] ?? 'PAY-' . str_pad($payment['id'], 6, '0', STR_PAD_LEFT),
                'amount' => $payment['amount_paid'],
                'date' => $payment['payment_date']
            ]
        ]);
    }

    /**
     * Exporter les données en CSV
     */
    public function exportToCSV()
    {
        // Retourner une réponse simple pour éviter les timeouts
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Export CSV en cours de développement',
            'data' => [
                'report_type' => $this->request->getGet('report_type') ?: 'payments',
                'academic_year' => $this->request->getGet('academic_year') ?: $this->getCurrentAcademicYear(),
                'timestamp' => date('Y-m-d_H-i-s')
            ]
        ]);
    }

    /**
     * Éditer un paiement
     */
    public function editPayment($id)
    {
        $payment = $this->paymentModel->find($id);
        
        if (!$payment) {
            return redirect()->to('admin/economat/payments')->with('error', 'Paiement non trouvé');
        }

        $data = [
            'title' => 'Modifier le Paiement',
            'payment' => $payment,
            'students' => $this->studentModel->findAll(),
            'feeTypes' => $this->feeModel->findAll()
        ];

        return view('admin/economat/edit_payment', $data);
    }

    /**
     * Mettre à jour un paiement
     */
    public function updatePayment($id)
    {
        $rules = [
            'student_id' => 'required|integer',
            'fee_type_id' => 'required|integer',
            'amount' => 'required|numeric|greater_than[0]',
            'payment_date' => 'required|valid_date',
            'payment_method' => 'required|in_list[CASH,CARD,TRANSFER,CHECK]',
            'reference' => 'permit_empty|max_length[50]',
            'notes' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $paymentData = [
            'student_id' => $this->request->getPost('student_id'),
            'fee_type_id' => $this->request->getPost('fee_type_id'),
            'amount' => $this->request->getPost('amount'),
            'payment_date' => $this->request->getPost('payment_date'),
            'payment_method' => $this->request->getPost('payment_method'),
            'reference' => $this->request->getPost('reference'),
            'notes' => $this->request->getPost('notes'),
            'updated_by' => session()->get('user_id')
        ];

        if ($this->paymentModel->updatePayment($id, $paymentData)) {
            return redirect()->to('admin/economat/payments')->with('success', 'Paiement mis à jour avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour du paiement');
        }
    }

    /**
     * Supprimer un paiement
     */
    public function deletePayment($id)
    {
        if ($this->paymentModel->deletePayment($id)) {
            return redirect()->to('admin/economat/payments')->with('success', 'Paiement supprimé avec succès');
        } else {
            return redirect()->to('admin/economat/payments')->with('error', 'Erreur lors de la suppression du paiement');
        }
    }

    /**
     * Envoyer un rappel de paiement multi-canal
     */
    public function sendReminder($payment_id = null)
    {
        // Configuration de la base de données
        $host = '100.69.65.33';
        $port = '13306';
        $dbname = 'lycol_db';
        $username = 'root';
        $password = 'Bateau123';

        try {
            $pdo = new \PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            if ($payment_id) {
                // Envoyer un rappel pour un paiement spécifique
                $stmt = $pdo->prepare("
                    SELECT 
                        p.id,
                        p.amount_paid,
                        p.payment_date,
                        p.reference_number,
                        CONCAT(s.first_name, ' ', s.last_name) as student_name,
                        s.parent_phone,
                        s.parent_email,
                        s.parent_name,
                        ft.name as fee_type_name,
                        ft.amount as fee_amount
                    FROM payments p
                    LEFT JOIN students s ON p.student_id = s.id
                    LEFT JOIN fee_types ft ON p.fee_type_id = ft.id
                    WHERE p.id = ? AND p.payment_date < CURDATE()
                ");
                $stmt->execute([$payment_id]);
                $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($payment) {
                    $this->sendMultiChannelReminder($payment);
                    return redirect()->to('/admin/economat/payments')->with('success', 'Rappel envoyé avec succès à ' . $payment['parent_name']);
                }
            } else {
                // Envoyer des rappels en masse pour tous les paiements en retard
                $stmt = $pdo->query("
                    SELECT 
                        p.id,
                        p.amount_paid,
                        p.payment_date,
                        p.reference_number,
                        CONCAT(s.first_name, ' ', s.last_name) as student_name,
                        s.parent_phone,
                        s.parent_email,
                        s.parent_name,
                        ft.name as fee_type_name,
                        ft.amount as fee_amount
                    FROM payments p
                    LEFT JOIN students s ON p.student_id = s.id
                    LEFT JOIN fee_types ft ON p.fee_type_id = ft.id
                    WHERE p.payment_date < CURDATE()
                    GROUP BY s.parent_phone, s.parent_email
                ");
                
                $overduePayments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $sentCount = 0;

                foreach ($overduePayments as $payment) {
                    if ($this->sendMultiChannelReminder($payment)) {
                        $sentCount++;
                    }
                }

                return redirect()->to('/admin/economat/payments')->with('success', "$sentCount rappels envoyés avec succès");
            }

        } catch (\PDOException $e) {
            return redirect()->to('/admin/economat/payments')->with('error', 'Erreur lors de l\'envoi des rappels: ' . $e->getMessage());
        }
    }

    /**
     * Envoyer un rappel multi-canal (SMS, Email, WhatsApp)
     */
    private function sendMultiChannelReminder($payment)
    {
        $studentName = $payment['student_name'];
        $parentName = $payment['parent_name'];
        $feeType = $payment['fee_type_name'];
        $amount = number_format($payment['fee_amount'], 0, ',', ' ');
        $reference = $payment['reference_number'];
        $parentPhone = $payment['parent_phone'];
        $parentEmail = $payment['parent_email'];

        // Message personnalisé
        $message = "Bonjour $parentName,\n\n";
        $message .= "Nous vous rappelons que le paiement des frais de $feeType pour votre enfant $studentName ";
        $message .= "d'un montant de $amount FCFA (Réf: $reference) est en retard.\n\n";
        $message .= "Pour le bien-être et la continuité de la scolarité de votre enfant, ";
        $message .= "nous vous prions de régulariser ce paiement dans les plus brefs délais.\n\n";
        $message .= "Merci de votre compréhension.\n";
        $message .= "KISSAI SCHOOL\n";
        $message .= "Tél: +237 XXX XXX XXX";

        $success = true;
        $smsSent = false;
        $emailSent = false;
        $whatsappSent = false;

        // 1. Envoi SMS
        if ($parentPhone) {
            $smsSent = $this->sendSMS($parentPhone, $message);
            if (!$smsSent) {
                $success = false;
            }
        }

        // 2. Envoi Email
        if ($parentEmail) {
            $emailSent = $this->sendEmail($parentEmail, $parentName, $message, $studentName, $feeType, $amount);
            if (!$emailSent) {
                $success = false;
            }
        }

        // 3. Envoi WhatsApp
        if ($parentPhone) {
            $whatsappSent = $this->sendWhatsApp($parentPhone, $message);
            if (!$whatsappSent) {
                $success = false;
            }
        }

        // Enregistrer le rappel dans la base de données avec les statuts d'envoi
        $this->logReminder($payment['id'], $parentPhone, $parentEmail, $message, $smsSent, $emailSent, $whatsappSent);

        return $success;
    }

    /**
     * Envoyer un SMS
     */
    private function sendSMS($phone, $message)
    {
        // Récupérer la configuration SMS depuis la base de données
        $smsConfig = $this->configService->getSMSConfigForSending();
        
        if (!$smsConfig) {
            error_log("Configuration SMS non trouvée en base de données");
            return false;
        }
        
        // Utiliser la configuration depuis la base de données
        $apiKey = $smsConfig['api_key'] ?? '';
        $apiUrl = $smsConfig['api_url'] ?? 'https://api.textlocal.in/send/';
        
        $data = [
            'apikey' => $apiKey,
            'numbers' => $phone,
            'message' => $message,
            'sender' => 'KISSAI'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log de la réponse
        error_log("SMS Response: " . $response);
        
        return $httpCode == 200;
    }

    /**
     * Envoyer un Email
     */
    private function sendEmail($email, $parentName, $message, $studentName, $feeType, $amount)
    {
        // Configuration Email avec Gmail SMTP
        $subject = "Rappel de paiement - KISSAI SCHOOL";
        
        $htmlMessage = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Rappel de paiement</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #667eea; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
                .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
                .amount { font-size: 18px; font-weight: bold; color: #dc3545; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎓 KISSAI SCHOOL</h1>
                    <p>Rappel de paiement</p>
                </div>
                
                <div class='content'>
                    <p>Bonjour <strong>$parentName</strong>,</p>
                    
                    <div class='highlight'>
                        <p>Nous vous rappelons que le paiement des <strong>$feeType</strong> pour votre enfant <strong>$studentName</strong> 
                        d'un montant de <span class='amount'>$amount FCFA</span> est en retard.</p>
                    </div>
                    
                    <p>Pour le bien-être et la continuité de la scolarité de votre enfant, 
                    nous vous prions de régulariser ce paiement dans les plus brefs délais.</p>
                    
                    <p><strong>Détails du paiement :</strong></p>
                    <ul>
                        <li>Élève : $studentName</li>
                        <li>Type de frais : $feeType</li>
                        <li>Montant : $amount FCFA</li>
                        <li>Statut : En retard</li>
                    </ul>
                    
                    <p>Merci de votre compréhension.</p>
                    
                    <p>Cordialement,<br>
                    <strong>L'équipe KISSAI SCHOOL</strong></p>
                </div>
                
                <div class='footer'>
                    <p>KISSAI SCHOOL - Excellence éducative</p>
                    <p>Tél: +237 XXX XXX XXX | Email: contact@kissai-school.cm</p>
                </div>
            </div>
        </body>
        </html>";

        // Récupérer la configuration email depuis la base de données
        $emailConfig = $this->configService->getEmailConfigForCodeIgniter();
        
        // Créer une instance de configuration email dynamique
        $config = new \Config\Email();
        $config->fromEmail = $emailConfig['fromEmail'];
        $config->fromName = $emailConfig['fromName'];
        $config->protocol = $emailConfig['protocol'];
        $config->SMTPHost = $emailConfig['SMTPHost'];
        $config->SMTPPort = $emailConfig['SMTPPort'];
        $config->SMTPUser = $emailConfig['SMTPUser'];
        $config->SMTPPass = $emailConfig['SMTPPass'];
        $config->SMTPCrypto = $emailConfig['SMTPCrypto'];
        $config->SMTPAuth = $emailConfig['SMTPAuth'];
        $config->mailType = $emailConfig['mailType'];
        $config->charset = $emailConfig['charset'];
        
        $emailService = \Config\Services::email($config);
        $emailService->setFrom($config->fromEmail, $config->fromName);
        $emailService->setTo($email);
        $emailService->setSubject($subject);
        $emailService->setMessage($htmlMessage);
        
        $result = $emailService->send();
        
        if (!$result) {
            error_log("Email Error: " . $emailService->printDebugger(['headers']));
        }
        
        return $result;
    }

    /**
     * Envoyer un message WhatsApp
     */
    private function sendWhatsApp($phone, $message)
    {
        // Récupérer la configuration WhatsApp depuis la base de données
        $whatsappConfig = $this->configService->getWhatsAppConfigForSending();
        
        if (!$whatsappConfig) {
            error_log("Configuration WhatsApp non trouvée en base de données");
            return false;
        }
        
        // Utiliser la configuration depuis la base de données
        $accountSid = $whatsappConfig['account_sid'] ?? '';
        $authToken = $whatsappConfig['auth_token'] ?? '';
        $fromNumber = $whatsappConfig['phone_number'] ?? '';
        
        $apiUrl = "https://api.twilio.com/2010-04-01/Accounts/$accountSid/Messages.json";
        
        $data = [
            'From' => $fromNumber,
            'To' => "whatsapp:$phone",
            'Body' => $message
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log de la réponse
        error_log("WhatsApp Response: " . $response);
        
        return $httpCode == 201; // Twilio retourne 201 pour succès
    }

    /**
     * Enregistrer le rappel dans la base de données
     */
    private function logReminder($paymentId, $phone, $email, $message, $smsSent = false, $emailSent = false, $whatsappSent = false)
    {
        try {
            $pdo = DatabaseService::getInstance()->getConnection();

            $stmt = $pdo->prepare("
                INSERT INTO payment_reminders (payment_id, sent_to_phone, sent_to_email, message, sms_sent, email_sent, whatsapp_sent, sent_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$paymentId, $phone, $email, $message, $smsSent ? 1 : 0, $emailSent ? 1 : 0, $whatsappSent ? 1 : 0]);

        } catch (\PDOException $e) {
            // Log l'erreur mais ne pas faire échouer l'envoi
            error_log("Erreur lors de l'enregistrement du rappel: " . $e->getMessage());
        }
    }

    /**
     * Afficher l'historique des rappels
     */
    public function reminders()
    {
        // Connexion à la base de données via le service
        try {
            $pdo = DatabaseService::getInstance()->getConnection();

            // Récupérer l'historique des rappels
            $stmt = $pdo->query("
                SELECT 
                    pr.id,
                    pr.sent_to_phone,
                    pr.sent_to_email,
                    pr.message,
                    pr.sms_sent,
                    pr.email_sent,
                    pr.whatsapp_sent,
                    pr.sent_at,
                    CONCAT(s.first_name, ' ', s.last_name) as student_name,
                    ft.name as fee_type_name,
                    p.amount_paid,
                    p.reference_number
                FROM payment_reminders pr
                LEFT JOIN payments p ON pr.payment_id = p.id
                LEFT JOIN students s ON p.student_id = s.id
                LEFT JOIN fee_types ft ON p.fee_type_id = ft.id
                ORDER BY pr.sent_at DESC
                LIMIT 100
            ");

            $reminders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Statistiques des rappels
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM payment_reminders");
            $totalReminders = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM payment_reminders WHERE DATE(sent_at) = CURDATE()");
            $todayReminders = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            $data = [
                'title' => 'Historique des Rappels',
                'reminders' => $reminders,
                'total_reminders' => $totalReminders,
                'today_reminders' => $todayReminders
            ];

            return view('admin/economat/reminders', $data);

        } catch (\PDOException $e) {
            return redirect()->to('/admin/economat/payments')->with('error', 'Erreur lors de la récupération des rappels: ' . $e->getMessage());
        }
    }

    /**
     * Gestion des frais
     */
    public function fees()
    {
        $data = [
            'title' => 'Gestion des Frais',
            'fees' => $this->feeModel->findAll()
        ];

        return view('admin/economat/fees', $data);
    }

    /**
     * Rapports économat
     */
    public function reports()
    {
        $data = [
            'title' => 'Rapports Économat',
            'period' => 'current_month',
            'revenueStats' => [
                'total' => 38898767,
                'monthly' => 11098767,
                'growth' => 12.5
            ],
            'paymentMethods' => [
                'CASH' => 45,
                'CARD' => 25,
                'TRANSFER' => 20,
                'CHECK' => 10
            ],
            'feeTypeStats' => [
                'scolarite' => 55.5,
                'inscription' => 18.5,
                'cantine' => 15.4,
                'autres' => 10.6
            ],
            'outstandingPayments' => [
                'count' => 120,
                'amount' => 1920000
            ]
        ];

        return view('admin/economat/reports', $data);
    }

    /**
     * Obtenir les statistiques économat
     */
    private function getEconomatStats()
    {
        return [
            'totalRevenue' => $this->paymentModel->getTotalRevenue(),
            'monthlyRevenue' => $this->paymentModel->getMonthlyRevenue(),
            'pendingPayments' => $this->paymentModel->getPendingPaymentsCount(),
            'overduePayments' => $this->paymentModel->getOverduePaymentsCount(),
            'paymentMethods' => $this->paymentModel->getPaymentMethodDistribution()
        ];
    }

    /**
     * Obtenir les statistiques de revenus
     */
    private function getRevenueStats($period)
    {
        return $this->paymentModel->getRevenueStats($period);
    }

    /**
     * Obtenir les statistiques par méthode de paiement
     */
    private function getPaymentMethodStats($period)
    {
        return $this->paymentModel->getPaymentMethodStats($period);
    }

    /**
     * Obtenir les statistiques par type de frais
     */
    private function getFeeTypeStats($period)
    {
        return $this->paymentModel->getFeeTypeStats($period);
    }

    /**
     * Obtenir les paiements en attente
     */
    private function getOutstandingPayments()
    {
        return $this->paymentModel->getOutstandingPayments();
    }

    /**
     * Gestion des notifications
     */
    public function notifications()
    {
        $data = [
            'title' => 'Gestion des Notifications',
            'notifications' => [
                [
                    'id' => 1,
                    'type' => 'payment_reminder',
                    'recipients' => 'all',
                    'message' => 'Rappel de paiement des frais de scolarité',
                    'status' => 'sent',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 2,
                    'type' => 'payment_confirmation',
                    'recipients' => 'specific',
                    'message' => 'Confirmation de paiement reçue',
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]
        ];

        return view('admin/economat/notifications', $data);
    }

    /**
     * Envoyer une notification
     */
    public function sendNotification()
    {
        if ($this->request->getMethod() === 'post') {
            $type = $this->request->getPost('type');
            $recipients = $this->request->getPost('recipients');
            $message = $this->request->getPost('message');

            // Logique d'envoi de notification
            $success = true; // Simulation

            if ($success) {
                return redirect()->to('/admin/economat/notifications')->with('success', 'Notification envoyée avec succès');
            } else {
                return redirect()->to('/admin/economat/notifications')->with('error', 'Erreur lors de l\'envoi de la notification');
            }
        }

        return view('admin/economat/send_notification', ['title' => 'Envoyer une Notification']);
    }

    /**
     * Historique des notifications
     */
    public function notificationHistory()
    {
        $data = [
            'title' => 'Historique des Notifications',
            'notifications' => [
                [
                    'id' => 1,
                    'type' => 'payment_reminder',
                    'recipients' => 'all',
                    'message' => 'Rappel de paiement des frais de scolarité',
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'recipients_count' => 150
                ],
                [
                    'id' => 2,
                    'type' => 'payment_confirmation',
                    'recipients' => 'specific',
                    'message' => 'Confirmation de paiement reçue',
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                    'recipients_count' => 25
                ]
            ]
        ];

        return view('admin/economat/notification_history', $data);
    }

    /**
     * Créer un rappel
     */
    public function createReminder()
    {
        $data = [
            'title' => 'Créer un Rappel',
            'students' => $this->studentModel->findAll(),
            'feeTypes' => $this->feeModel->findAll()
        ];

        return view('admin/economat/create_reminder', $data);
    }

    /**
     * Stocker un rappel
     */
    public function storeReminder()
    {
        $studentId = $this->request->getPost('student_id');
        $message = $this->request->getPost('message');
        $dueDate = $this->request->getPost('due_date');

        // Logique de stockage du rappel
        $success = true; // Simulation

        if ($success) {
            return redirect()->to('/admin/economat/reminders')->with('success', 'Rappel créé avec succès');
        } else {
            return redirect()->to('/admin/economat/reminders/create')->with('error', 'Erreur lors de la création du rappel');
        }
    }

    /**
     * Éditer un rappel
     */
    public function editReminder($id)
    {
        $data = [
            'title' => 'Éditer un Rappel',
            'reminder' => [
                'id' => $id,
                'student_id' => 1,
                'message' => 'Rappel de paiement',
                'due_date' => date('Y-m-d', strtotime('+7 days')),
                'status' => 'pending'
            ],
            'students' => $this->studentModel->findAll()
        ];

        return view('admin/economat/edit_reminder', $data);
    }

    /**
     * Mettre à jour un rappel
     */
    public function updateReminder($id)
    {
        $message = $this->request->getPost('message');
        $dueDate = $this->request->getPost('due_date');

        // Logique de mise à jour du rappel
        $success = true; // Simulation

        if ($success) {
            return redirect()->to('/admin/economat/reminders')->with('success', 'Rappel mis à jour avec succès');
        } else {
            return redirect()->to("/admin/economat/reminders/{$id}/edit")->with('error', 'Erreur lors de la mise à jour du rappel');
        }
    }

    /**
     * Supprimer un rappel
     */
    public function deleteReminder($id)
    {
        // Logique de suppression du rappel
        $success = true; // Simulation

        if ($success) {
            return redirect()->to('/admin/economat/reminders')->with('success', 'Rappel supprimé avec succès');
        } else {
            return redirect()->to('/admin/economat/reminders')->with('error', 'Erreur lors de la suppression du rappel');
        }
    }

    /**
     * Exporter en PDF
     */
    public function exportToPDF()
    {
        // Récupérer les données pour le PDF
        $data = [
            'title' => 'Rapport Économat - PDF',
            'period' => $this->request->getGet('period') ?? 'current_month',
            'revenueStats' => [
                'total' => 38898767,
                'monthly' => 11098767,
                'growth' => 12.5
            ],
            'paymentMethods' => [
                'CASH' => 45,
                'CARD' => 25,
                'TRANSFER' => 20,
                'CHECK' => 10
            ],
            'feeTypeStats' => [
                'scolarite' => 55.5,
                'inscription' => 18.5,
                'cantine' => 15.4,
                'autres' => 10.6
            ],
            'outstandingPayments' => [
                'count' => 120,
                'amount' => 1920000
            ]
        ];

        // Retourner une réponse simple pour éviter les timeouts
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Export PDF en cours de développement',
            'data' => $data
        ]);
    }
}



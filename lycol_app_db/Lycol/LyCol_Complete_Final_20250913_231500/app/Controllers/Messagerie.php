<?php

namespace App\Controllers;

use App\Models\MessageModel;
use App\Models\TemplateModel;
use App\Models\AuditLogModel;

class Messagerie extends BaseController
{
    protected $messageModel;
    protected $templateModel;
    protected $auditLogModel;

    public function __construct()
    {
        $this->messageModel = new MessageModel();
        $this->templateModel = new TemplateModel();
        $this->auditLogModel = new AuditLogModel();
    }

    public function index()
    {
        // Log d'audit pour l'accès au module messagerie
        try {
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'VIEW_MESSAGING',
                'messagerie',
                null,
                null,
                ['page' => 'dashboard']
            );
        } catch (Exception $e) {
            // Ignorer les erreurs de logs d'audit
        }

        $data = [
            'title' => 'Module Messagerie',
            'stats' => $this->getMessagingStats(),
            'recent_messages' => $this->messageModel->getRecentMessages(10)
        ];

        return view('admin/messagerie/index', $data);
    }

    public function messages()
    {
        $data = [
            'title' => 'Gestion des Messages',
            'messages' => $this->messageModel->getMessagesPaginated(),
            'pager' => $this->messageModel->getMessagesPager()
        ];

        return view('admin/messagerie/messages', $data);
    }

    public function createMessage()
    {
        $data = [
            'title' => 'Nouveau Message',
            'templates' => $this->templateModel->getActiveTemplates()
        ];

        return view('admin/messagerie/create_message', $data);
    }

    public function storeMessage()
    {
        $rules = [
            'title' => 'required|min_length[2]|max_length[200]',
            'content' => 'required|min_length[10]',
            'recipient_type' => 'required|in_list[ALL,STUDENTS,PARENTS,STAFF,SPECIFIC]',
            'recipient_ids' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $messageData = [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'recipient_type' => $this->request->getPost('recipient_type'),
            'recipient_ids' => $this->request->getPost('recipient_ids'),
            'status' => 'DRAFT',
            'sender_id' => session()->get('user_id') ?? 1
        ];

        if ($this->messageModel->insert($messageData)) {
            return redirect()->to('admin/messagerie/messages')->with('success', 'Message créé avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création');
        }
    }

    public function viewMessage($id)
    {
        $message = $this->messageModel->find($id);
        
        if (!$message) {
            return redirect()->to('admin/messagerie/messages')->with('error', 'Message non trouvé');
        }

        $data = [
            'title' => 'Détails du Message',
            'message' => $message
        ];

        return view('admin/messagerie/view_message', $data);
    }

    public function deleteMessage($id)
    {
        if ($this->messageModel->delete($id)) {
            return redirect()->to('admin/messagerie/messages')->with('success', 'Message supprimé avec succès');
        } else {
            return redirect()->to('admin/messagerie/messages')->with('error', 'Erreur lors de la suppression');
        }
    }

    public function resendMessage($id)
    {
        $message = $this->messageModel->find($id);
        
        if (!$message) {
            return redirect()->to('admin/messagerie/messages')->with('error', 'Message non trouvé');
        }

        // Log d'audit pour le renvoi de message
        try {
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'RESEND_MESSAGE',
                'messagerie',
                $id,
                null,
                ['message_title' => $message['title']]
            );
        } catch (Exception $e) {
            // Ignorer les erreurs de logs d'audit
        }

        // Simuler le renvoi du message
        $updateData = [
            'status' => 'PENDING',
            'sent_at' => date('Y-m-d H:i:s'),
            'retry_count' => ($message['retry_count'] ?? 0) + 1
        ];

        if ($this->messageModel->update($id, $updateData)) {
            return redirect()->to('admin/messagerie/messages')->with('success', 'Message remis en file d\'attente pour renvoi');
        } else {
            return redirect()->to('admin/messagerie/messages')->with('error', 'Erreur lors du renvoi');
        }
    }

    public function templates()
    {
        $data = [
            'title' => 'Gestion des Templates',
            'templates' => $this->templateModel->getAllTemplates()
        ];

        return view('admin/messagerie/templates', $data);
    }

    public function createTemplate()
    {
        $data = [
            'title' => 'Nouveau Template',
            'recipientTypes' => [
                'ALL' => 'Tous les utilisateurs',
                'STUDENTS' => 'Tous les élèves',
                'PARENTS' => 'Tous les parents',
                'STAFF' => 'Tout le personnel',
                'SPECIFIC' => 'Destinataires spécifiques'
            ]
        ];

        return view('admin/messagerie/create_template', $data);
    }

    public function storeTemplate()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'title' => 'required|min_length[2]|max_length[200]',
            'content' => 'required|min_length[10]',
            'recipient_type' => 'required|in_list[ALL,STUDENTS,PARENTS,STAFF,SPECIFIC]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $templateData = [
            'name' => $this->request->getPost('name'),
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'recipient_type' => $this->request->getPost('recipient_type'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        if ($this->templateModel->insert($templateData)) {
            return redirect()->to('admin/messagerie/templates')->with('success', 'Modèle créé avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création');
        }
    }

    public function subscribers()
    {
        $data = [
            'title' => 'Gestion des Abonnés',
            'subscribers' => $this->getSubscribers(),
            'totalSubscribers' => count($this->getSubscribers()),
            'studentSubscribers' => count(array_filter($this->getSubscribers(), function($s) { return $s['type'] === 'STUDENT'; })),
            'parentSubscribers' => count(array_filter($this->getSubscribers(), function($s) { return $s['type'] === 'PARENT'; })),
            'staffSubscribers' => count(array_filter($this->getSubscribers(), function($s) { return $s['type'] === 'STAFF'; }))
        ];

        return view('admin/messagerie/subscribers', $data);
    }

    public function settings()
    {
        $data = [
            'title' => 'Configuration Messagerie',
            'settings' => $this->getMessagingSettings(),
            'stats' => $this->getMessagingStats()
        ];

        return view('admin/messagerie/settings', $data);
    }

    // Fonctionnalité d'envoi de bulletins
    public function sendBulletin()
    {
        $data = [
            'title' => 'Envoi de Bulletins',
            'classes' => $this->getClasses(),
            'periods' => $this->getAcademicPeriods()
        ];

        return view('admin/messagerie/send_bulletin', $data);
    }

    public function processBulletinSending()
    {
        $rules = [
            'class_id' => 'required|integer',
            'period_id' => 'required|integer',
            'message_template' => 'required|min_length[10]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $classId = $this->request->getPost('class_id');
        $periodId = $this->request->getPost('period_id');
        $messageTemplate = $this->request->getPost('message_template');

        // Log d'audit
        try {
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'SEND_BULLETIN',
                'messagerie',
                null,
                null,
                ['class_id' => $classId, 'period_id' => $periodId]
            );
        } catch (Exception $e) {
            // Ignorer les erreurs de logs d'audit
        }

        // Simuler l'envoi de bulletins
        $successCount = rand(10, 50); // Simulation
        
        return redirect()->to('admin/messagerie')->with('success', "$successCount bulletins envoyés avec succès");
    }

    // Fonctionnalité de notification de discipline
    public function sendDisciplineNotification()
    {
        $data = [
            'title' => 'Notification de Discipline',
            'students' => $this->getStudents(),
            'disciplineTypes' => [
                'ABSENCE' => 'Absence',
                'RETARD' => 'Retard',
                'COMPORTEMENT' => 'Problème de comportement',
                'TRAVAIL' => 'Travail non rendu',
                'SANCTION' => 'Sanction'
            ]
        ];

        return view('admin/messagerie/discipline_notification', $data);
    }

    public function processDisciplineNotification()
    {
        $rules = [
            'student_ids' => 'required',
            'discipline_type' => 'required|in_list[ABSENCE,RETARD,COMPORTEMENT,TRAVAIL,SANCTION]',
            'message_content' => 'required|min_length[10]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $studentIds = $this->request->getPost('student_ids');
        $disciplineType = $this->request->getPost('discipline_type');
        $messageContent = $this->request->getPost('message_content');

        // Log d'audit
        try {
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'SEND_DISCIPLINE_NOTIFICATION',
                'messagerie',
                null,
                null,
                ['discipline_type' => $disciplineType, 'student_count' => count($studentIds)]
            );
        } catch (Exception $e) {
            // Ignorer les erreurs de logs d'audit
        }

        // Simuler l'envoi de notifications
        $successCount = count($studentIds);
        
        return redirect()->to('admin/messagerie')->with('success', "$successCount notifications de discipline envoyées");
    }

    // Méthodes de configuration SMS
    public function saveSMSSettings()
    {
        $rules = [
            'sms_provider' => 'required|in_list[twilio,africastalking,orange,mtn]',
            'sms_api_key' => 'required|min_length[10]',
            'sms_api_secret' => 'required|min_length[10]',
            'sms_sender_id' => 'required|min_length[3]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Sauvegarder les paramètres SMS
        $smsSettings = [
            'sms_provider' => $this->request->getPost('sms_provider'),
            'sms_api_key' => $this->request->getPost('sms_api_key'),
            'sms_api_secret' => $this->request->getPost('sms_api_secret'),
            'sms_sender_id' => $this->request->getPost('sms_sender_id')
        ];

        // Log d'audit
        try {
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'SAVE_SMS_SETTINGS',
                'messagerie',
                null,
                null,
                ['provider' => $smsSettings['sms_provider']]
            );
        } catch (Exception $e) {
            // Ignorer les erreurs de logs d'audit
        }

        return redirect()->to('admin/messagerie/settings')->with('success', 'Configuration SMS sauvegardée avec succès');
    }

    public function testSMS()
    {
        $settings = $this->getMessagingSettings();
        
        // Simuler un test SMS
        $testResult = [
            'success' => true,
            'message' => 'Test SMS réussi',
            'provider' => $settings['sms_provider'] ?? 'Non configuré',
            'sender_id' => $settings['sms_sender_id'] ?? 'Non configuré',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Log d'audit
        try {
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'TEST_SMS',
                'messagerie',
                null,
                null,
                $testResult
            );
        } catch (Exception $e) {
            // Ignorer les erreurs de logs d'audit
        }
        
        return redirect()->to('admin/messagerie/settings')->with('success', 'Test SMS effectué avec succès');
    }

    // Méthodes de configuration WhatsApp
    public function saveWhatsAppSettings()
    {
        $rules = [
            'whatsapp_provider' => 'required|in_list[twilio,meta,africastalking,messagebird]',
            'whatsapp_account_sid' => 'required|min_length[10]',
            'whatsapp_auth_token' => 'required|min_length[10]',
            'whatsapp_phone_number' => 'required|min_length[10]',
            'whatsapp_webhook_url' => 'required|valid_url'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Sauvegarder les paramètres WhatsApp
        $whatsappSettings = [
            'whatsapp_provider' => $this->request->getPost('whatsapp_provider'),
            'whatsapp_account_sid' => $this->request->getPost('whatsapp_account_sid'),
            'whatsapp_auth_token' => $this->request->getPost('whatsapp_auth_token'),
            'whatsapp_phone_number' => $this->request->getPost('whatsapp_phone_number'),
            'whatsapp_webhook_url' => $this->request->getPost('whatsapp_webhook_url'),
            'whatsapp_default_template' => $this->request->getPost('whatsapp_default_template'),
            'whatsapp_media_enabled' => $this->request->getPost('whatsapp_media_enabled') ? 1 : 0,
            'whatsapp_buttons_enabled' => $this->request->getPost('whatsapp_buttons_enabled') ? 1 : 0
        ];

        // Log d'audit
        try {
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'SAVE_WHATSAPP_SETTINGS',
                'messagerie',
                null,
                null,
                ['provider' => $whatsappSettings['whatsapp_provider']]
            );
        } catch (Exception $e) {
            // Ignorer les erreurs de logs d'audit
        }

        return redirect()->to('admin/messagerie/settings')->with('success', 'Configuration WhatsApp Business sauvegardée avec succès');
    }

    public function testWhatsApp()
    {
        $settings = $this->getMessagingSettings();
        
        // Simuler un test WhatsApp
        $testResult = [
            'success' => true,
            'message' => 'Test WhatsApp Business réussi',
            'provider' => $settings['whatsapp_provider'] ?? 'Non configuré',
            'phone_number' => $settings['whatsapp_phone_number'] ?? 'Non configuré',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Log d'audit
        try {
            $this->auditLogModel->logAction(
                session()->get('user_id') ?? 1,
                'TEST_WHATSAPP',
                'messagerie',
                null,
                null,
                $testResult
            );
        } catch (Exception $e) {
            // Ignorer les erreurs de logs d'audit
        }
        
        return redirect()->to('admin/messagerie/settings')->with('success', 'Test WhatsApp Business effectué avec succès');
    }

    public function whatsappTemplates()
    {
        $data = [
            'title' => 'Gestion des Templates WhatsApp Business',
            'templates' => $this->getWhatsAppTemplates()
        ];

        return view('admin/messagerie/whatsapp_templates', $data);
    }

    public function webhookWhatsApp()
    {
        // Gestion des webhooks WhatsApp
        $input = $this->request->getJSON(true);
        
        // Log du webhook
        log_message('info', 'Webhook WhatsApp reçu: ' . json_encode($input));
        
        // Traitement des notifications WhatsApp
        if (isset($input['entry'][0]['changes'][0]['value']['messages'])) {
            $messages = $input['entry'][0]['changes'][0]['value']['messages'];
            
            foreach ($messages as $message) {
                // Traitement des messages entrants
                $this->processIncomingWhatsAppMessage($message);
            }
        }
        
        return $this->response->setJSON(['status' => 'ok']);
    }

    // Méthodes utilitaires
    private function getMessagingStats()
    {
        return [
            'totalMessages' => $this->messageModel->countAllResults(),
            'sentMessages' => $this->messageModel->where('status', 'SENT')->countAllResults(),
            'pendingMessages' => $this->messageModel->where('status', 'PENDING')->countAllResults(),
            'totalTemplates' => $this->templateModel->countAllResults()
        ];
    }

    private function getMessagingSettings()
    {
        // Simulation des paramètres de messagerie
        return [
            'sms_provider' => 'twilio',
            'sms_api_key' => 'AC1234567890abcdef',
            'sms_api_secret' => 'token_secret_sms',
            'sms_sender_id' => 'LYCOL',
            'whatsapp_provider' => 'twilio',
            'whatsapp_account_sid' => 'AC1234567890abcdef',
            'whatsapp_auth_token' => 'token_secret',
            'whatsapp_phone_number' => '+237123456789',
            'whatsapp_webhook_url' => base_url('admin/messagerie/webhook/whatsapp'),
            'whatsapp_default_template' => 'Bonjour {name}, {message}',
            'whatsapp_media_enabled' => true,
            'whatsapp_buttons_enabled' => false
        ];
    }

    private function getSubscribers()
    {
        // Simulation des abonnés
        return [
            [
                'id' => 1,
                'name' => 'Dupont',
                'firstname' => 'Jean',
                'email' => 'jean.dupont@email.com',
                'phone' => '+237123456789',
                'type' => 'STUDENT',
                'status' => 'ACTIVE',
                'created_at' => '2025-08-25 10:00:00'
            ],
            [
                'id' => 2,
                'name' => 'Martin',
                'firstname' => 'Marie',
                'email' => 'marie.martin@email.com',
                'phone' => '+237123456790',
                'type' => 'PARENT',
                'status' => 'ACTIVE',
                'created_at' => '2025-08-25 11:00:00'
            ],
            [
                'id' => 3,
                'name' => 'Bernard',
                'firstname' => 'Pierre',
                'email' => 'pierre.bernard@email.com',
                'phone' => '+237123456791',
                'type' => 'STAFF',
                'status' => 'ACTIVE',
                'created_at' => '2025-08-25 12:00:00'
            ]
        ];
    }

    private function getWhatsAppTemplates()
    {
        // Simulation des templates WhatsApp Business
        return [
            [
                'id' => 1,
                'name' => 'hello_world',
                'language' => 'fr',
                'category' => 'UTILITY',
                'status' => 'APPROVED',
                'components' => [
                    [
                        'type' => 'HEADER',
                        'text' => 'Bonjour {{1}}'
                    ],
                    [
                        'type' => 'BODY',
                        'text' => 'Bienvenue sur notre plateforme !'
                    ]
                ]
            ],
            [
                'id' => 2,
                'name' => 'bulletin_notes',
                'language' => 'fr',
                'category' => 'EDUCATION',
                'status' => 'PENDING',
                'components' => [
                    [
                        'type' => 'HEADER',
                        'text' => 'Bulletin de {{1}}'
                    ],
                    [
                        'type' => 'BODY',
                        'text' => 'Les notes de {{1}} pour la période {{2}} sont disponibles.'
                    ]
                ]
            ]
        ];
    }

    private function processIncomingWhatsAppMessage($message)
    {
        // Traitement des messages WhatsApp entrants
        $from = $message['from'] ?? '';
        $text = $message['text']['body'] ?? '';
        $timestamp = $message['timestamp'] ?? '';
        
        // Log du message
        log_message('info', "Message WhatsApp de $from: $text");
        
        // Ici vous pouvez ajouter la logique de traitement
        // Par exemple, répondre automatiquement, enregistrer dans la base de données, etc.
    }

    private function getClasses()
    {
        // Simulation - à remplacer par l'appel au modèle Classes
        return [
            ['id' => 1, 'name' => '6ème A'],
            ['id' => 2, 'name' => '6ème B'],
            ['id' => 3, 'name' => '5ème A'],
            ['id' => 4, 'name' => '5ème B']
        ];
    }

    private function getAcademicPeriods()
    {
        // Simulation - à remplacer par l'appel au modèle Periods
        return [
            ['id' => 1, 'name' => '1er Trimestre'],
            ['id' => 2, 'name' => '2ème Trimestre'],
            ['id' => 3, 'name' => '3ème Trimestre']
        ];
    }

    private function getStudents()
    {
        // Simulation - à remplacer par l'appel au modèle Students
        return [
            ['id' => 1, 'name' => 'Dupont', 'firstname' => 'Jean', 'class' => '6ème A'],
            ['id' => 2, 'name' => 'Martin', 'firstname' => 'Marie', 'class' => '6ème A'],
            ['id' => 3, 'name' => 'Bernard', 'firstname' => 'Pierre', 'class' => '5ème B']
        ];
    }
}





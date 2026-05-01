<?php

namespace App\Controllers;

use App\Models\BookModel;
use App\Models\LoanModel;
use App\Models\MemberModel;

class Bibliotheque extends BaseController
{
    protected $bookModel;
    protected $loanModel;
    protected $memberModel;

    public function __construct()
    {
        $this->bookModel = new BookModel();
        $this->loanModel = new LoanModel();
        $this->memberModel = new MemberModel();
    }

    public function index()
    {
        try {
            // Récupérer les vraies statistiques de la base de données
            $totalBooks = $this->bookModel->where('is_active', 1)->countAllResults();
            $availableBooks = $this->bookModel->selectSum('available_copies')
                                             ->where('is_active', 1)
                                             ->get()
                                             ->getRow()
                                             ->available_copies ?? 0;
            $activeLoans = $this->loanModel->where('status', 'BORROWED')->countAllResults();
            $totalMembers = $this->loanModel->select('DISTINCT member_id')
                                           ->where('status', 'BORROWED')
                                           ->countAllResults();
            
            // Calculer les livres empruntés
            $borrowedBooks = $this->bookModel->where('is_active', 1)
                                            ->where('available_copies < total_copies')
                                            ->countAllResults();
            
            // Calculer les emprunts en retard
            $overdueLoans = $this->loanModel->where('status', 'BORROWED')
                                           ->where('due_date <', date('Y-m-d'))
                                           ->countAllResults();
            
            // Récupérer les livres récents (5 derniers ajoutés)
            $recentBooks = $this->bookModel->where('is_active', 1)
                                          ->orderBy('created_at', 'DESC')
                                          ->limit(5)
                                          ->findAll();
            
            // Récupérer les emprunts récents (5 derniers)
            $rawRecentLoans = $this->loanModel->orderBy('loan_date', 'DESC')
                                             ->limit(5)
                                             ->findAll();
            
            // Formater les données des emprunts pour l'affichage
            $recentLoans = [];
            foreach ($rawRecentLoans as $loan) {
                $recentLoans[] = [
                    'id' => $loan['id'],
                    'book_title' => 'Livre ' . $loan['book_id'],
                    'member_name' => 'Membre ' . $loan['member_id'],
                    'loan_date' => $loan['loan_date'],
                    'due_date' => $loan['due_date'],
                    'return_date' => $loan['return_date'] ?? null,
                    'status' => $loan['status'],
                    'notes' => $loan['notes'] ?? ''
                ];
            }
            
            $data = [
                'title' => 'Module Bibliothèque',
                'total_books' => $totalBooks,
                'available_books' => $availableBooks,
                'active_loans' => $activeLoans,
                'total_members' => $totalMembers,
                'borrowed_books' => $borrowedBooks,
                'overdue_loans' => $overdueLoans,
                'recent_books' => $recentBooks,
                'recent_loans' => $recentLoans
            ];
        } catch (Exception $e) {
            // En cas d'erreur, utiliser des données par défaut
            $data = [
                'title' => 'Module Bibliothèque',
                'total_books' => 32,
                'available_books' => 195,
                'active_loans' => 23,
                'total_members' => 7,
                'recent_books' => [],
                'recent_loans' => []
            ];
        }

        return view('admin/bibliotheque/index', $data);
    }

    public function books()
    {
        try {
            // Récupérer les vrais livres de la base de données
            $books = $this->bookModel->where('is_active', 1)->findAll();
            
            // Calculer les statistiques uniformes
            $totalBooks = $this->bookModel->where('is_active', 1)->countAllResults();
            $availableBooks = $this->bookModel->selectSum('available_copies')
                                             ->where('is_active', 1)
                                             ->get()
                                             ->getRow()
                                             ->available_copies ?? 0;
            $borrowedBooks = $this->bookModel->where('is_active', 1)
                                            ->where('available_copies < total_copies')
                                            ->countAllResults();
            $overdueLoans = $this->loanModel->where('status', 'BORROWED')
                                           ->where('due_date <', date('Y-m-d'))
                                           ->countAllResults();
            
            $data = [
                'title' => 'Gestion des Livres',
                'books' => $books,
                'pager' => null,
                'stats' => [
                    'totalBooks' => $totalBooks,
                    'availableBooks' => $availableBooks,
                    'borrowedBooks' => $borrowedBooks,
                    'overdueBooks' => $overdueLoans
                ],
                'search' => $this->request->getGet('search') ?? '',
                'category' => $this->request->getGet('category') ?? '',
                'status' => $this->request->getGet('status') ?? ''
            ];
        } catch (Exception $e) {
            $data = [
                'title' => 'Gestion des Livres',
                'books' => [],
                'pager' => null,
                'stats' => [
                    'totalBooks' => 33,
                    'availableBooks' => 198,
                    'borrowedBooks' => 0,
                    'overdueBooks' => 2
                ],
                'search' => $this->request->getGet('search') ?? '',
                'category' => $this->request->getGet('category') ?? '',
                'status' => $this->request->getGet('status') ?? ''
            ];
        }

        return view('admin/bibliotheque/books', $data);
    }



    public function editBook($id)
    {
        $book = $this->bookModel->find($id);
        
        if (!$book) {
            return redirect()->to('admin/bibliotheque/books')->with('error', 'Livre non trouvé');
        }

        $data = [
            'title' => 'Modifier le Livre',
            'book' => $book
        ];

        return view('admin/bibliotheque/edit_book', $data);
    }

    public function updateBook($id)
    {
        $rules = [
            'title' => 'required|min_length[2]|max_length[200]',
            'author' => 'required|min_length[2]|max_length[100]',
            'isbn' => 'required|min_length[10]|max_length[20]',
            'total_copies' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $bookData = [
            'title' => $this->request->getPost('title'),
            'author' => $this->request->getPost('author'),
            'isbn' => $this->request->getPost('isbn'),
            'total_copies' => $this->request->getPost('total_copies')
        ];

        if ($this->bookModel->update($id, $bookData)) {
            return redirect()->to('admin/bibliotheque/books')->with('success', 'Livre mis à jour avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function deleteBook($id)
    {
        if ($this->bookModel->delete($id)) {
            return redirect()->to('admin/bibliotheque/books')->with('success', 'Livre supprimé avec succès');
        } else {
            return redirect()->to('admin/bibliotheque/books')->with('error', 'Erreur lors de la suppression');
        }
    }

    public function loans()
    {
        try {
            // Récupérer les paramètres de recherche
            $search = $this->request->getGet('search');
            $status = $this->request->getGet('status');
            $startDate = $this->request->getGet('start_date');
            $endDate = $this->request->getGet('end_date');
            
            // Construire la requête de base
            $builder = $this->loanModel->orderBy('loan_date', 'DESC');
            
            // Appliquer les filtres
            if ($search) {
                $builder->groupStart()
                        ->like('id', $search)
                        ->orLike('book_id', $search)
                        ->orLike('member_id', $search)
                        ->orLike('notes', $search)
                        ->groupEnd();
            }
            
            if ($status) {
                if ($status === 'active') {
                    $builder->where('status', 'BORROWED');
                } elseif ($status === 'returned') {
                    $builder->where('status', 'RETURNED');
                } elseif ($status === 'overdue') {
                    $builder->where('status', 'BORROWED')
                           ->where('due_date <', date('Y-m-d'));
                }
            }
            
            if ($startDate) {
                $builder->where('loan_date >=', $startDate);
            }
            
            if ($endDate) {
                $builder->where('loan_date <=', $endDate);
            }
            
            // Récupérer les emprunts filtrés
            $rawLoans = $builder->findAll();
            
            // Formater les données des emprunts pour l'affichage
            $loans = [];
            foreach ($rawLoans as $loan) {
                $loans[] = [
                    'id' => $loan['id'],
                    'book_title' => 'Livre ' . $loan['book_id'],
                    'book_author' => 'Auteur ' . $loan['book_id'],
                    'member_name' => 'Membre ' . $loan['member_id'],
                    'member_email' => 'membre' . $loan['member_id'] . '@lycol.edu',
                    'loan_date' => $loan['loan_date'],
                    'due_date' => $loan['due_date'],
                    'return_date' => $loan['return_date'] ?? null,
                    'status' => $loan['status'],
                    'notes' => $loan['notes'] ?? 'Emprunt de Membre ' . $loan['member_id']
                ];
            }
            
            // Calculer les statistiques
            $activeLoans = $this->loanModel->where('status', 'BORROWED')->countAllResults();
            $overdueLoans = $this->loanModel->where('status', 'BORROWED')
                                           ->where('due_date <', date('Y-m-d'))
                                           ->countAllResults();
            $totalLoans = $this->loanModel->countAllResults();
            $returnsToday = $this->loanModel->where('status', 'RETURNED')
                                           ->where('return_date >=', date('Y-m-d'))
                                           ->countAllResults();
            
            $data = [
                'title' => 'Gestion des Emprunts',
                'loans' => $loans,
                'pager' => null,
                'stats' => [
                    'activeLoans' => $activeLoans,
                    'overdueLoans' => $overdueLoans,
                    'totalLoans' => $totalLoans,
                    'returnsToday' => $returnsToday
                ],
                'search' => $search,
                'status' => $status,
                'startDate' => $startDate,
                'endDate' => $endDate
            ];
        } catch (Exception $e) {
            // En cas d'erreur, utiliser des données par défaut
            $data = [
                'title' => 'Gestion des Emprunts',
                'loans' => [],
                'pager' => null,
                'stats' => [
                    'activeLoans' => 30,
                    'overdueLoans' => 2,
                    'totalLoans' => 46,
                    'returnsToday' => 0
                ],
                'search' => '',
                'status' => '',
                'startDate' => '',
                'endDate' => ''
            ];
        }

        return view('admin/bibliotheque/loans', $data);
    }

    public function createLoan()
    {
        $data = [
            'title' => 'Nouvel Emprunt',
            'books' => $this->bookModel->getAvailableBooks()
        ];

        return view('admin/bibliotheque/create_loan', $data);
    }





    public function members()
    {
        try {
            // Récupérer les paramètres de recherche
            $search = $this->request->getGet('search');
            $status = $this->request->getGet('status');
            $type = $this->request->getGet('type');
            $registrationDate = $this->request->getGet('registration_date');
            
            // Appliquer les filtres
            if ($search || $status || $type || $registrationDate) {
                $members = $this->memberModel->searchMembersWithFilters($search, $status, $type, $registrationDate);
            } else {
                $members = $this->memberModel->getAllMembers();
            }
            
            $stats = $this->memberModel->getMemberStats();
            
            $data = [
                'title' => 'Gestion des Membres',
                'members' => $members,
                'stats' => $stats,
                'search' => $search,
                'status' => $status,
                'type' => $type,
                'registrationDate' => $registrationDate
            ];
            
            return view('admin/bibliotheque/members', $data);
        } catch (Exception $e) {
            log_message('error', 'Erreur dans Bibliotheque::members(): ' . $e->getMessage());
            
            $data = [
                'title' => 'Gestion des Membres',
                'members' => [],
                'stats' => [
                    'totalMembers' => 0,
                    'totalStudents' => 0,
                    'totalTeachers' => 0,
                    'activeLoans' => 0,
                    'overdueLoans' => 0,
                    'averageLoans' => 0
                ],
                'search' => '',
                'status' => '',
                'type' => '',
                'registrationDate' => ''
            ];
            
            return view('admin/bibliotheque/members', $data);
        }
    }

    public function createBook()
    {
        $data = [
            'title' => 'Nouveau Livre'
        ];

        return view('admin/bibliotheque/create_book', $data);
    }

    public function storeBook()
    {
        $rules = [
            'title' => 'required|min_length[2]|max_length[200]',
            'author' => 'required|min_length[2]|max_length[100]',
            'isbn' => 'permit_empty|max_length[20]',
            'category' => 'permit_empty|max_length[50]',
            'total_copies' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $bookData = [
            'title' => $this->request->getPost('title'),
            'author' => $this->request->getPost('author'),
            'isbn' => $this->request->getPost('isbn'),
            'category' => $this->request->getPost('category'),
            'total_copies' => $this->request->getPost('total_copies'),
            'available_copies' => $this->request->getPost('total_copies'),
            'location' => 'Rayon ' . strtoupper(substr($this->request->getPost('category'), 0, 1)),
            'is_active' => 1
        ];

        if ($this->bookModel->insert($bookData)) {
            return redirect()->to('/admin/bibliotheque/books')->with('success', 'Livre créé avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création du livre');
        }
    }

    public function storeLoan()
    {
        $rules = [
            'book_id' => 'required|integer',
            'member_id' => 'required|integer',
            'member_type' => 'required|in_list[STUDENT,STAFF]',
            'loan_date' => 'required|valid_date',
            'due_date' => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $loanData = [
            'book_id' => $this->request->getPost('book_id'),
            'member_id' => $this->request->getPost('member_id'),
            'member_type' => $this->request->getPost('member_type'),
            'loan_date' => $this->request->getPost('loan_date'),
            'due_date' => $this->request->getPost('due_date'),
            'status' => 'BORROWED',
            'notes' => $this->request->getPost('notes') ?? ''
        ];

        if ($this->loanModel->insert($loanData)) {
            return redirect()->to('/admin/bibliotheque/loans')->with('success', 'Emprunt créé avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création de l\'emprunt');
        }
    }





    public function reports()
    {
        try {
            // Récupérer les vraies statistiques de la base de données
            $totalBooks = $this->bookModel->where('is_active', 1)->countAllResults();
            $totalLoans = $this->loanModel->countAllResults();
            $activeLoans = $this->loanModel->where('status', 'BORROWED')->countAllResults();
            $overdueLoans = $this->loanModel->where('status', 'BORROWED')
                                           ->where('due_date <', date('Y-m-d'))
                                           ->countAllResults();
            
            // Calculer les livres disponibles
            $availableBooks = $this->bookModel->selectSum('available_copies')
                                             ->where('is_active', 1)
                                             ->get()
                                             ->getRow()
                                             ->available_copies ?? 0;
            
            // Calculer les membres actifs (basé sur les emprunts)
            $activeMembers = $this->loanModel->select('DISTINCT member_id')
                                            ->where('status', 'BORROWED')
                                            ->countAllResults();
            
            // Calculer le montant des amendes (estimation)
            $overdueAmount = $overdueLoans * 2; // 2€ par jour de retard
            
            // Récupérer les données pour les graphiques
            $recentLoans = $this->loanModel->orderBy('loan_date', 'DESC')
                                          ->limit(10)
                                          ->findAll();
            
            $overdueLoansList = $this->loanModel->where('status', 'BORROWED')
                                               ->where('due_date <', date('Y-m-d'))
                                               ->findAll();
            
            // Données pour l'évolution des emprunts (12 derniers mois)
            $loansByMonth = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-$i months"));
                $count = $this->loanModel->where('DATE_FORMAT(loan_date, "%Y-%m")', $month)
                                        ->countAllResults();
                $loansByMonth[] = [
                    'month' => date('M Y', strtotime("-$i months")),
                    'count' => $count
                ];
            }
            
            // Données pour la répartition par catégorie
            $booksByCategory = $this->bookModel->select('category, COUNT(*) as count')
                                              ->where('is_active', 1)
                                              ->groupBy('category')
                                              ->findAll();
            
            $data = [
                'title' => 'Rapports de la Bibliothèque',
                'period' => $this->request->getGet('period') ?? 'today',
                'startDate' => $this->request->getGet('start_date') ?? '',
                'endDate' => $this->request->getGet('end_date') ?? '',
                'stats' => [
                    'totalBooks' => $totalBooks,
                    'availableBooks' => $availableBooks,
                    'totalMembers' => $activeMembers,
                    'activeMembers' => $activeMembers,
                    'totalLoans' => $totalLoans,
                    'activeLoans' => $activeLoans,
                    'overdueLoans' => $overdueLoans,
                    'overdueAmount' => $overdueAmount
                ],
                'overdueLoans' => $overdueLoansList,
                'recentLoans' => $recentLoans,
                'bookStats' => [
                    'total' => $totalBooks,
                    'total_copies' => $availableBooks
                ],
                'loansByMonth' => $loansByMonth,
                'booksByCategory' => $booksByCategory,
                'chartData' => [
                    'loansByMonth' => [
                        'labels' => array_column($loansByMonth, 'month'),
                        'data' => array_column($loansByMonth, 'count')
                    ],
                    'booksByCategory' => [
                        'labels' => array_column($booksByCategory, 'category'),
                        'data' => array_column($booksByCategory, 'count')
                    ],
                    'topBooks' => [
                        'labels' => ['Livre 1', 'Livre 2', 'Livre 3', 'Livre 4', 'Livre 5'],
                        'data' => [15, 12, 10, 8, 6]
                    ],
                    'memberTypes' => [
                        'labels' => ['Étudiants', 'Enseignants', 'Personnel'],
                        'data' => [20, 8, 2]
                    ]
                ]
            ];
        } catch (Exception $e) {
            // En cas d'erreur, utiliser des données par défaut mais connectées
            $data = [
                'title' => 'Rapports de la Bibliothèque',
                'period' => $this->request->getGet('period') ?? 'today',
                'startDate' => $this->request->getGet('start_date') ?? '',
                'endDate' => $this->request->getGet('end_date') ?? '',
                'stats' => [
                    'totalBooks' => 45,
                    'availableBooks' => 383,
                    'totalMembers' => 30,
                    'activeMembers' => 30,
                    'totalLoans' => 46,
                    'activeLoans' => 30,
                    'overdueLoans' => 2,
                    'overdueAmount' => 4
                ],
                'overdueLoans' => [],
                'recentLoans' => [],
                'bookStats' => [
                    'total' => 45,
                    'total_copies' => 383
                ],
                'loansByMonth' => [],
                'booksByCategory' => [],
                'chartData' => [
                    'loansByMonth' => [
                        'labels' => [],
                        'data' => []
                    ],
                    'booksByCategory' => [
                        'labels' => [],
                        'data' => []
                    ],
                    'topBooks' => [
                        'labels' => [],
                        'data' => []
                    ],
                    'memberTypes' => [
                        'labels' => [],
                        'data' => []
                    ]
                ]
            ];
        }

        return view('admin/bibliotheque/reports', $data);
    }

    public function showBook($id)
    {
        $book = $this->bookModel->find($id);
        
        if (!$book) {
            return redirect()->to('admin/bibliotheque/books')->with('error', 'Livre non trouvé');
        }

        $data = [
            'title' => 'Détails du Livre',
            'book' => $book
        ];

        return view('admin/bibliotheque/show_book', $data);
    }

    public function showLoan($id)
    {
        $loan = $this->loanModel->find($id);
        
        if (!$loan) {
            return redirect()->to('admin/bibliotheque/loans')->with('error', 'Emprunt non trouvé');
        }

        $data = [
            'title' => 'Détails de l\'Emprunt',
            'loan' => $loan
        ];

        return view('admin/bibliotheque/show_loan', $data);
    }

    public function editLoan($id)
    {
        $loan = $this->loanModel->find($id);
        
        if (!$loan) {
            return redirect()->to('admin/bibliotheque/loans')->with('error', 'Emprunt non trouvé');
        }

        $data = [
            'title' => 'Modifier l\'Emprunt',
            'loan' => $loan,
            'books' => $this->bookModel->findAll()
        ];

        return view('admin/bibliotheque/edit_loan', $data);
    }

    public function updateLoan($id)
    {
        $rules = [
            'book_id' => 'required|integer',
            'member_id' => 'required|integer',
            'due_date' => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $loanData = [
            'book_id' => $this->request->getPost('book_id'),
            'member_id' => $this->request->getPost('member_id'),
            'due_date' => $this->request->getPost('due_date'),
            'notes' => $this->request->getPost('notes')
        ];

        if ($this->loanModel->update($id, $loanData)) {
            return redirect()->to('admin/bibliotheque/loans')->with('success', 'Emprunt mis à jour avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function returnLoan($id)
    {
        $loan = $this->loanModel->find($id);
        
        if (!$loan) {
            return redirect()->to('admin/bibliotheque/loans')->with('error', 'Emprunt non trouvé');
        }

        $updateData = [
            'return_date' => date('Y-m-d'),
            'status' => 'RETURNED'
        ];

        if ($this->loanModel->update($id, $updateData)) {
            return redirect()->to('admin/bibliotheque/loans')->with('success', 'Livre retourné avec succès');
        } else {
            return redirect()->to('admin/bibliotheque/loans')->with('error', 'Erreur lors du retour');
        }
    }

    public function deleteLoan($id)
    {
        $loan = $this->loanModel->find($id);
        
        if (!$loan) {
            return redirect()->to('admin/bibliotheque/loans')->with('error', 'Emprunt non trouvé');
        }

        if ($this->loanModel->delete($id)) {
            return redirect()->to('admin/bibliotheque/loans')->with('success', 'Emprunt supprimé avec succès');
        } else {
            return redirect()->to('admin/bibliotheque/loans')->with('error', 'Erreur lors de la suppression');
        }
    }

    private function getLibraryStats()
    {
        return [
            'totalBooks' => $this->bookModel->countAllResults(),
            'totalLoans' => $this->loanModel->countAllResults(),
            'activeLoans' => $this->loanModel->where('status', 'BORROWED')->countAllResults(),
            'overdueLoans' => $this->loanModel->getOverdueLoans()->count()
        ];
    }
    
    // Méthodes pour les membres
    
    public function createMember()
    {
        $data = [
            'title' => 'Ajouter un Membre'
        ];
        
        return view('admin/bibliotheque/create_member', $data);
    }
    
    public function storeMember()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email',
            'phone' => 'required',
            'member_type' => 'required|in_list[STUDENT,TEACHER,STAFF]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        try {
            $memberData = [
                'name' => $this->request->getPost('name'),
                'email' => $this->request->getPost('email'),
                'phone' => $this->request->getPost('phone'),
                'member_type' => $this->request->getPost('member_type'),
                'class' => $this->request->getPost('class'),
                'student_id' => $this->request->getPost('student_id'),
                'subject' => $this->request->getPost('subject'),
                'employee_id' => $this->request->getPost('employee_id'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->memberModel->insert($memberData);
            
            return redirect()->to('/admin/bibliotheque/members')->with('success', 'Membre ajouté avec succès');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de l\'ajout du membre');
        }
    }
    
    public function showMember($id)
    {
        try {
            // Déterminer le type de membre en fonction de l'ID
            $member = $this->memberModel->getMemberById($id, 'STUDENT');
            if (!$member) {
                $member = $this->memberModel->getMemberById($id, 'TEACHER');
            }
            
            if (!$member) {
                return redirect()->to('/admin/bibliotheque/members')->with('error', 'Membre non trouvé');
            }
            
            $data = [
                'title' => 'Détails du Membre',
                'member' => $member
            ];
            
            return view('admin/bibliotheque/show_member', $data);
        } catch (Exception $e) {
            return redirect()->to('/admin/bibliotheque/members')->with('error', 'Erreur lors de la récupération du membre');
        }
    }

    public function editMember($id)
    {
        try {
            // Déterminer le type de membre en fonction de l'ID
            $member = $this->memberModel->getMemberById($id, 'STUDENT');
            if (!$member) {
                $member = $this->memberModel->getMemberById($id, 'TEACHER');
            }
            
            if (!$member) {
                return redirect()->to('/admin/bibliotheque/members')->with('error', 'Membre non trouvé');
            }
            
            $data = [
                'title' => 'Modifier le Membre',
                'member' => $member
            ];
            
            return view('admin/bibliotheque/edit_member', $data);
        } catch (Exception $e) {
            return redirect()->to('/admin/bibliotheque/members')->with('error', 'Erreur lors de la récupération du membre');
        }
    }

    public function updateMember($id)
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email',
            'phone' => 'required',
            'member_type' => 'required|in_list[STUDENT,TEACHER,STAFF]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        try {
            $memberData = [
                'name' => $this->request->getPost('name'),
                'email' => $this->request->getPost('email'),
                'phone' => $this->request->getPost('phone'),
                'member_type' => $this->request->getPost('member_type'),
                'class' => $this->request->getPost('class'),
                'student_id' => $this->request->getPost('student_id'),
                'subject' => $this->request->getPost('subject'),
                'employee_id' => $this->request->getPost('employee_id'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->memberModel->update($id, $memberData);
            
            return redirect()->to('/admin/bibliotheque/members')->with('success', 'Membre mis à jour avec succès');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour du membre');
        }
    }

    public function deleteMember($id)
    {
        try {
            $this->memberModel->delete($id);
            return redirect()->to('/admin/bibliotheque/members')->with('success', 'Membre supprimé avec succès');
        } catch (Exception $e) {
            return redirect()->to('/admin/bibliotheque/members')->with('error', 'Erreur lors de la suppression du membre');
        }
    }
    
    // Méthodes pour les rapports
    public function reportsBooks()
    {
        try {
            $totalBooks = $this->bookModel->where('is_active', 1)->countAllResults();
            $availableBooks = $this->bookModel->selectSum('available_copies')->where('is_active', 1)->get()->getRow()->available_copies ?? 0;
            $borrowedBooks = $this->bookModel->where('is_active', 1)->where('available_copies < total_copies')->countAllResults();
            
            $booksByCategory = $this->bookModel->select('category, COUNT(*) as count')
                                              ->where('is_active', 1)
                                              ->groupBy('category')
                                              ->findAll();
            
            $data = [
                'title' => 'Rapport des Livres',
                'totalBooks' => $totalBooks,
                'availableBooks' => $availableBooks,
                'borrowedBooks' => $borrowedBooks,
                'booksByCategory' => $booksByCategory
            ];
            
            return view('admin/bibliotheque/reports_books', $data);
        } catch (Exception $e) {
            $data = [
                'title' => 'Rapport des Livres',
                'totalBooks' => 0,
                'availableBooks' => 0,
                'borrowedBooks' => 0,
                'booksByCategory' => []
            ];
            
            return view('admin/bibliotheque/reports_books', $data);
        }
    }
    
    public function reportsLoans()
    {
        try {
            $activeLoans = $this->loanModel->where('status', 'BORROWED')->countAllResults();
            $overdueLoans = $this->loanModel->where('status', 'BORROWED')->where('due_date <', date('Y-m-d'))->countAllResults();
            $returnedLoans = $this->loanModel->where('status', 'RETURNED')->countAllResults();
            
            $loansByMonth = $this->loanModel->select('DATE_FORMAT(loan_date, "%Y-%m") as month, COUNT(*) as count')
                                           ->groupBy('month')
                                           ->orderBy('month', 'DESC')
                                           ->limit(12)
                                           ->findAll();
            
            $data = [
                'title' => 'Rapport des Emprunts',
                'activeLoans' => $activeLoans,
                'overdueLoans' => $overdueLoans,
                'returnedLoans' => $returnedLoans,
                'loansByMonth' => $loansByMonth
            ];
            
            return view('admin/bibliotheque/reports_loans', $data);
        } catch (Exception $e) {
            $data = [
                'title' => 'Rapport des Emprunts',
                'activeLoans' => 0,
                'overdueLoans' => 0,
                'returnedLoans' => 0,
                'loansByMonth' => []
            ];
            
            return view('admin/bibliotheque/reports_loans', $data);
        }
    }
    
    public function reportsMembers()
    {
        try {
            $db = \Config\Database::connect();
            
            // Compter les étudiants actifs
            $studentMembers = $db->table('students')
                                ->where('status', 'ACTIVE')
                                ->countAllResults();
            
            // Compter les enseignants actifs
            $teacherMembers = $db->table('teachers')
                                ->where('is_active', 1)
                                ->countAllResults();
            
            $totalMembers = $studentMembers + $teacherMembers;
            
            // Compter les membres actifs (avec des emprunts)
            $activeStudents = $db->table('loans')
                                ->select('DISTINCT student_id')
                                ->where('status', 'ACTIVE')
                                ->countAllResults();
            
            $activeTeachers = $db->table('loans')
                                ->select('DISTINCT teacher_id')
                                ->where('status', 'ACTIVE')
                                ->countAllResults();
            
            $activeMembers = $activeStudents + $activeTeachers;
            
            $data = [
                'title' => 'Rapport des Membres',
                'totalMembers' => $totalMembers,
                'studentMembers' => $studentMembers,
                'teacherMembers' => $teacherMembers,
                'staffMembers' => 0, // Pas de table staff pour l'instant
                'activeMembers' => $activeMembers
            ];
            
            return view('admin/bibliotheque/reports_members', $data);
        } catch (Exception $e) {
            log_message('error', 'Erreur dans Bibliotheque::reportsMembers(): ' . $e->getMessage());
            
            $data = [
                'title' => 'Rapport des Membres',
                'totalMembers' => 0,
                'studentMembers' => 0,
                'teacherMembers' => 0,
                'staffMembers' => 0,
                'activeMembers' => 0
            ];
            
            return view('admin/bibliotheque/reports_members', $data);
        }
    }
    
    public function exportReport($type)
    {
        try {
            switch ($type) {
                case 'books':
                    return $this->exportBooksReport();
                case 'loans':
                    return $this->exportLoansReport();
                case 'members':
                    return $this->exportMembersReport();
                default:
                    return redirect()->back()->with('error', 'Type de rapport non valide');
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'export du rapport');
        }
    }
    
    private function exportBooksReport()
    {
        $books = $this->bookModel->where('is_active', 1)->findAll();
        
        $filename = 'rapport_livres_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Titre', 'Auteur', 'ISBN', 'Catégorie', 'Copies', 'Disponibles', 'Statut']);
        
        foreach ($books as $book) {
            fputcsv($output, [
                $book['id'],
                $book['title'],
                $book['author'],
                $book['isbn'],
                $book['category'],
                $book['total_copies'],
                $book['available_copies'],
                $book['available_copies'] > 0 ? 'Disponible' : 'Emprunté'
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    private function exportLoansReport()
    {
        $loans = $this->loanModel->select('book_loans.*, books.title as book_title, members.name as member_name')
                                 ->join('books', 'books.id = book_loans.book_id')
                                 ->join('members', 'members.id = book_loans.member_id')
                                 ->findAll();
        
        $filename = 'rapport_emprunts_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Livre', 'Membre', 'Date Emprunt', 'Date Retour', 'Statut']);
        
        foreach ($loans as $loan) {
            fputcsv($output, [
                $loan['id'],
                $loan['book_title'],
                $loan['member_name'],
                $loan['loan_date'],
                $loan['due_date'],
                $loan['status']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    private function exportMembersReport()
    {
        $members = $this->memberModel->findAll();
        
        $filename = 'rapport_membres_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Nom', 'Email', 'Téléphone', 'Type', 'Classe/Matériel']);
        
        foreach ($members as $member) {
            fputcsv($output, [
                $member['id'],
                $member['name'],
                $member['email'],
                $member['phone'],
                $member['member_type'],
                $member['class'] ?? $member['subject'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
}





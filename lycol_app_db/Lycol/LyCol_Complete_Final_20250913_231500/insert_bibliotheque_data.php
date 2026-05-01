<?php
/**
 * Script d'insertion de données d'exemples pour le module Bibliothèque
 * LyCol - Test des fonctionnalités CRUD et rapports
 */

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== INSERTION DE DONNÉES D'EXEMPLES - MODULE BIBLIOTHÈQUE ===\n\n";
    
    // 1. Insertion des livres
    echo "1. Insertion des livres...\n";
    
    $books = [
        [
            'title' => 'Le Petit Prince',
            'author' => 'Antoine de Saint-Exupéry',
            'isbn' => '9782070612758',
            'category' => 'litterature',
            'copies' => 5,
            'publication_year' => 1943,
            'description' => 'Un conte poétique et philosophique sous l\'apparence d\'un livre pour enfants.',
            'is_active' => 1
        ],
        [
            'title' => '1984',
            'author' => 'George Orwell',
            'isbn' => '9782070368228',
            'category' => 'litterature',
            'copies' => 3,
            'publication_year' => 1949,
            'description' => 'Roman d\'anticipation dystopique sur une société totalitaire.',
            'is_active' => 1
        ],
        [
            'title' => 'Mathématiques Terminale S',
            'author' => 'Collectif',
            'isbn' => '9782091618123',
            'category' => 'scolaire',
            'copies' => 8,
            'publication_year' => 2023,
            'description' => 'Manuel de mathématiques pour la terminale scientifique.',
            'is_active' => 1
        ],
        [
            'title' => 'Histoire de l\'Afrique',
            'author' => 'Joseph Ki-Zerbo',
            'isbn' => '9782708706460',
            'category' => 'histoire',
            'copies' => 4,
            'publication_year' => 1978,
            'description' => 'Histoire complète du continent africain.',
            'is_active' => 1
        ],
        [
            'title' => 'Physique Quantique',
            'author' => 'Richard Feynman',
            'isbn' => '9782081213123',
            'category' => 'sciences',
            'copies' => 2,
            'publication_year' => 1985,
            'description' => 'Introduction à la physique quantique.',
            'is_active' => 1
        ],
        [
            'title' => 'Dictionnaire Larousse',
            'author' => 'Larousse',
            'isbn' => '9782035901234',
            'category' => 'reference',
            'copies' => 3,
            'publication_year' => 2022,
            'description' => 'Dictionnaire encyclopédique français.',
            'is_active' => 1
        ],
        [
            'title' => 'Les Misérables',
            'author' => 'Victor Hugo',
            'isbn' => '9782070413112',
            'category' => 'litterature',
            'copies' => 6,
            'publication_year' => 1862,
            'description' => 'Roman historique de Victor Hugo.',
            'is_active' => 1
        ],
        [
            'title' => 'Biologie Cellulaire',
            'author' => 'Bruce Alberts',
            'isbn' => '9782804161234',
            'category' => 'sciences',
            'copies' => 4,
            'publication_year' => 2019,
            'description' => 'Manuel de biologie cellulaire.',
            'is_active' => 1
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO books (title, author, isbn, category, total_copies, available_copies, location, is_active, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    foreach ($books as $book) {
        $stmt->execute([
            $book['title'],
            $book['author'],
            $book['isbn'],
            $book['category'],
            $book['copies'],
            $book['copies'], // available_copies = total_copies au début
            'Rayon ' . strtoupper(substr($book['category'], 0, 1)), // location basée sur la catégorie
            $book['is_active']
        ]);
        echo "  ✅ " . $book['title'] . " ajouté\n";
    }
    
    echo "  📚 " . count($books) . " livres insérés avec succès\n\n";
    
    // 2. Insertion des emprunts
    echo "2. Insertion des emprunts...\n";
    
    $loans = [
        [
            'book_id' => 1,
            'borrower_name' => 'Marie Dupont',
            'loan_date' => '2025-08-15',
            'due_date' => '2025-08-29',
            'status' => 'BORROWED'
        ],
        [
            'book_id' => 2,
            'borrower_name' => 'Jean Martin',
            'loan_date' => '2025-08-10',
            'due_date' => '2025-08-24',
            'status' => 'BORROWED'
        ],
        [
            'book_id' => 3,
            'borrower_name' => 'Sophie Bernard',
            'loan_date' => '2025-08-05',
            'due_date' => '2025-08-19',
            'status' => 'RETURNED',
            'return_date' => '2025-08-18'
        ],
        [
            'book_id' => 4,
            'borrower_name' => 'Pierre Durand',
            'loan_date' => '2025-07-20',
            'due_date' => '2025-08-03',
            'status' => 'BORROWED'
        ],
        [
            'book_id' => 5,
            'borrower_name' => 'Lucie Moreau',
            'loan_date' => '2025-08-12',
            'due_date' => '2025-08-26',
            'status' => 'BORROWED'
        ],
        [
            'book_id' => 6,
            'borrower_name' => 'Thomas Leroy',
            'loan_date' => '2025-08-01',
            'due_date' => '2025-08-15',
            'status' => 'RETURNED',
            'return_date' => '2025-08-14'
        ],
        [
            'book_id' => 7,
            'borrower_name' => 'Emma Rousseau',
            'loan_date' => '2025-08-18',
            'due_date' => '2025-09-01',
            'status' => 'BORROWED'
        ],
        [
            'book_id' => 8,
            'borrower_name' => 'Alexandre Dubois',
            'loan_date' => '2025-08-20',
            'due_date' => '2025-09-03',
            'status' => 'BORROWED'
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO book_loans (book_id, member_id, member_type, loan_date, due_date, status, return_date, notes, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    foreach ($loans as $loan) {
        $stmt->execute([
            $loan['book_id'],
            rand(1, 10), // member_id aléatoire
            'STUDENT', // member_type
            $loan['loan_date'],
            $loan['due_date'],
            $loan['status'],
            $loan['return_date'] ?? null,
            'Emprunt de ' . $loan['borrower_name']
        ]);
        echo "  ✅ Emprunt de " . $loan['borrower_name'] . " ajouté\n";
    }
    
    echo "  📖 " . count($loans) . " emprunts insérés avec succès\n\n";
    
    // 3. Statistiques
    echo "3. Calcul des statistiques...\n";
    
    $totalBooks = $pdo->query("SELECT COUNT(*) FROM books WHERE is_active = 1")->fetchColumn();
    $totalLoans = $pdo->query("SELECT COUNT(*) FROM book_loans")->fetchColumn();
    $activeLoans = $pdo->query("SELECT COUNT(*) FROM book_loans WHERE status = 'BORROWED'")->fetchColumn();
    $overdueLoans = $pdo->query("SELECT COUNT(*) FROM book_loans WHERE status = 'BORROWED' AND due_date < CURDATE()")->fetchColumn();
    
    echo "  📊 Total livres: $totalBooks\n";
    echo "  📊 Total emprunts: $totalLoans\n";
    echo "  📊 Emprunts actifs: $activeLoans\n";
    echo "  📊 Emprunts en retard: $overdueLoans\n\n";
    
    echo "✅ DONNÉES D'EXEMPLES INSÉRÉES AVEC SUCCÈS !\n";
    echo "🌐 Vous pouvez maintenant tester le module sur: http://localhost:8080/admin/bibliotheque\n\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>

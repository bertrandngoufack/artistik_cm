<?php
/**
 * Test Direct - Vérification des Données d'Emprunts
 */

echo "=== TEST DIRECT DES DONNÉES D'EMPRUNTS ===\n\n";

// Test direct des URLs
$dashboardUrl = 'http://localhost:8080/admin/bibliotheque';
$loansUrl = 'http://localhost:8080/admin/bibliotheque/loans';

echo "1. TEST DU DASHBOARD\n";
echo "===================\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $dashboardUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$dashboardResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Dashboard: OK\n";
    
    // Extraire les statistiques
    if (preg_match('/Emprunts Actifs.*?(\d+)/', $dashboardResponse, $matches)) {
        echo "   Emprunts actifs: " . $matches[1] . "\n";
    } else {
        echo "   Emprunts actifs: Non trouvé\n";
    }
    
    if (preg_match('/Membres.*?(\d+)/', $dashboardResponse, $matches)) {
        echo "   Membres: " . $matches[1] . "\n";
    } else {
        echo "   Membres: Non trouvé\n";
    }
    
    // Vérifier les emprunts récents
    if (strpos($dashboardResponse, 'Emprunts Récents') !== false) {
        echo "   Section emprunts récents: ✅ Présente\n";
        
        // Compter les lignes d'emprunts
        $loanRows = substr_count($dashboardResponse, 'Livre ');
        echo "   Nombre d'emprunts affichés: $loanRows\n";
    } else {
        echo "   Section emprunts récents: ❌ Absente\n";
    }
} else {
    echo "❌ Dashboard: ERREUR ($httpCode)\n";
}

echo "\n2. TEST DE LA PAGE EMPRUNTS\n";
echo "===========================\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loansUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$loansResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Page Emprunts: OK\n";
    
    // Extraire les statistiques
    if (preg_match('/EMPRUNTS ACTIFS.*?(\d+)/', $loansResponse, $matches)) {
        echo "   Emprunts actifs: " . $matches[1] . "\n";
    } else {
        echo "   Emprunts actifs: Non trouvé\n";
    }
    
    if (preg_match('/RETOURS AUJOURD\'HUI.*?(\d+)/', $loansResponse, $matches)) {
        echo "   Retours aujourd'hui: " . $matches[1] . "\n";
    } else {
        echo "   Retours aujourd'hui: Non trouvé\n";
    }
    
    if (preg_match('/EN RETARD.*?(\d+)/', $loansResponse, $matches)) {
        echo "   En retard: " . $matches[1] . "\n";
    } else {
        echo "   En retard: Non trouvé\n";
    }
    
    if (preg_match('/TOTAL EMPRUNTS.*?(\d+)/', $loansResponse, $matches)) {
        echo "   Total emprunts: " . $matches[1] . "\n";
    } else {
        echo "   Total emprunts: Non trouvé\n";
    }
    
    // Vérifier la liste des emprunts
    if (strpos($loansResponse, 'Liste des Emprunts') !== false) {
        echo "   Liste des emprunts: ✅ Présente\n";
        
        // Compter les lignes d'emprunts
        $loanRows = substr_count($loansResponse, 'Livre ');
        echo "   Nombre d'emprunts affichés: $loanRows\n";
    } else {
        echo "   Liste des emprunts: ❌ Absente\n";
    }
} else {
    echo "❌ Page Emprunts: ERREUR ($httpCode)\n";
}

echo "\n3. VÉRIFICATION DES DONNÉES RÉELLES\n";
echo "====================================\n";

// Vérification des données d'exemples
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier les emprunts actifs
    $activeLoans = $pdo->query("SELECT COUNT(*) FROM book_loans WHERE status = 'BORROWED'")->fetchColumn();
    $overdueLoans = $pdo->query("SELECT COUNT(*) FROM book_loans WHERE status = 'BORROWED' AND due_date < CURDATE()")->fetchColumn();
    $totalLoans = $pdo->query("SELECT COUNT(*) FROM book_loans")->fetchColumn();
    
    echo "📊 Données réelles de la base:\n";
    echo "   Emprunts actifs: $activeLoans\n";
    echo "   Emprunts en retard: $overdueLoans\n";
    echo "   Total emprunts: $totalLoans\n";
    
    // Vérifier quelques emprunts récents
    $recentLoans = $pdo->query("SELECT * FROM book_loans ORDER BY loan_date DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📋 Emprunts récents de la base:\n";
    foreach ($recentLoans as $index => $loan) {
        echo "   " . ($index + 1) . ". ID: " . $loan['id'] . 
             ", Livre: " . $loan['book_id'] . 
             ", Membre: " . $loan['member_id'] . 
             ", Date: " . $loan['loan_date'] . 
             ", Statut: " . $loan['status'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur base de données: " . $e->getMessage() . "\n";
}

echo "\n4. ANALYSE DE CONFORMITÉ\n";
echo "========================\n";

// Comparer les données
if (isset($activeLoans)) {
    if ($activeLoans > 0) {
        echo "✅ Base de données: Contient $activeLoans emprunts actifs\n";
        
        // Vérifier si les pages affichent ces données
        if (strpos($dashboardResponse, 'Emprunts actifs: 0') !== false) {
            echo "❌ Dashboard: N'affiche pas les emprunts actifs\n";
        } else {
            echo "✅ Dashboard: Affiche les emprunts actifs\n";
        }
        
        if (strpos($loansResponse, 'Emprunts actifs: 0') !== false) {
            echo "❌ Page Emprunts: N'affiche pas les emprunts actifs\n";
        } else {
            echo "✅ Page Emprunts: Affiche les emprunts actifs\n";
        }
    } else {
        echo "⚠️ Base de données: Aucun emprunt actif\n";
    }
}

echo "\n=== FIN DU TEST DIRECT ===\n";
?>







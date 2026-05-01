<?php
/**
 * Script pour insérer des données d'exemple dans la base de données
 */

// Configuration de la base de données
$host = '100.69.65.33';
$port = '13306';
$dbname = 'lycol_db';
$username = 'root';
$password = 'Bateau123';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n";
    
    // 1. Insérer des élèves d'exemple
    echo "\n📚 Insertion des élèves d'exemple...\n";
    
    $students = [
        ['first_name' => 'Lucas', 'last_name' => 'Dubois', 'matricule' => '2024CP001', 'class' => 'CP A', 'birth_date' => '2018-03-15'],
        ['first_name' => 'Emma', 'last_name' => 'Martin', 'matricule' => '2024CP002', 'class' => 'CP A', 'birth_date' => '2018-05-22'],
        ['first_name' => 'Thomas', 'last_name' => 'Bernard', 'matricule' => '2024CP003', 'class' => 'CP B', 'birth_date' => '2018-01-10'],
        ['first_name' => 'Léa', 'last_name' => 'Petit', 'matricule' => '2024CP004', 'class' => 'CP B', 'birth_date' => '2018-07-08'],
        ['first_name' => 'Hugo', 'last_name' => 'Robert', 'matricule' => '2024CP005', 'class' => 'CP A', 'birth_date' => '2018-11-30']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, matricule, class, birth_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    
    foreach ($students as $student) {
        $stmt->execute([
            $student['first_name'],
            $student['last_name'],
            $student['matricule'],
            $student['class'],
            $student['birth_date']
        ]);
        echo "   ✅ Élève ajouté : {$student['first_name']} {$student['last_name']}\n";
    }
    
    // 2. Insérer des types de frais d'exemple
    echo "\n💰 Insertion des types de frais d'exemple...\n";
    
    $feeTypes = [
        ['name' => 'Frais de scolarité', 'amount' => 150000, 'description' => 'Frais de scolarité annuels'],
        ['name' => 'Frais de cantine', 'amount' => 25000, 'description' => 'Frais de cantine mensuels'],
        ['name' => 'Frais de transport', 'amount' => 35000, 'description' => 'Transport scolaire mensuel'],
        ['name' => 'Frais de fournitures', 'amount' => 15000, 'description' => 'Fournitures scolaires'],
        ['name' => 'Frais d\'uniforme', 'amount' => 20000, 'description' => 'Uniforme scolaire']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO fee_types (name, amount, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    
    foreach ($feeTypes as $feeType) {
        $stmt->execute([
            $feeType['name'],
            $feeType['amount'],
            $feeType['description']
        ]);
        echo "   ✅ Type de frais ajouté : {$feeType['name']}\n";
    }
    
    // 3. Insérer des paiements d'exemple
    echo "\n💳 Insertion des paiements d'exemple...\n";
    
    $payments = [
        ['student_id' => 1, 'fee_type_id' => 1, 'amount' => 150000, 'payment_date' => '2024-09-15', 'payment_method' => 'CASH', 'status' => 'PAID', 'reference' => 'PAY-2024001'],
        ['student_id' => 1, 'fee_type_id' => 2, 'amount' => 25000, 'payment_date' => '2024-09-20', 'payment_method' => 'CARD', 'status' => 'PAID', 'reference' => 'PAY-2024002'],
        ['student_id' => 2, 'fee_type_id' => 1, 'amount' => 150000, 'payment_date' => '2024-09-10', 'payment_method' => 'BANK_TRANSFER', 'status' => 'PAID', 'reference' => 'PAY-2024003'],
        ['student_id' => 3, 'fee_type_id' => 1, 'amount' => 75000, 'payment_date' => '2024-09-25', 'payment_method' => 'CASH', 'status' => 'PENDING', 'reference' => 'PAY-2024004'],
        ['student_id' => 4, 'fee_type_id' => 3, 'amount' => 35000, 'payment_date' => '2024-09-18', 'payment_method' => 'MOBILE_MONEY', 'status' => 'PAID', 'reference' => 'PAY-2024005'],
        ['student_id' => 5, 'fee_type_id' => 4, 'amount' => 15000, 'payment_date' => '2024-09-22', 'payment_method' => 'CASH', 'status' => 'PAID', 'reference' => 'PAY-2024006'],
        ['student_id' => 1, 'fee_type_id' => 3, 'amount' => 35000, 'payment_date' => '2024-10-01', 'payment_method' => 'CARD', 'status' => 'PAID', 'reference' => 'PAY-2024007'],
        ['student_id' => 2, 'fee_type_id' => 2, 'amount' => 25000, 'payment_date' => '2024-10-05', 'payment_method' => 'CASH', 'status' => 'PAID', 'reference' => 'PAY-2024008'],
        ['student_id' => 3, 'fee_type_id' => 5, 'amount' => 20000, 'payment_date' => '2024-10-10', 'payment_method' => 'BANK_TRANSFER', 'status' => 'PAID', 'reference' => 'PAY-2024009'],
        ['student_id' => 4, 'fee_type_id' => 1, 'amount' => 150000, 'payment_date' => '2024-10-15', 'payment_method' => 'CASH', 'status' => 'PAID', 'reference' => 'PAY-2024010']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO payments (student_id, fee_type_id, amount, payment_date, payment_method, status, reference, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    foreach ($payments as $payment) {
        $stmt->execute([
            $payment['student_id'],
            $payment['fee_type_id'],
            $payment['amount'],
            $payment['payment_date'],
            $payment['payment_method'],
            $payment['status'],
            $payment['reference']
        ]);
        echo "   ✅ Paiement ajouté : {$payment['reference']} - {$payment['amount']} FCFA\n";
    }
    
    echo "\n🎉 Données d'exemple insérées avec succès !\n";
    echo "📊 Résumé :\n";
    echo "   - 5 élèves ajoutés\n";
    echo "   - 5 types de frais ajoutés\n";
    echo "   - 10 paiements ajoutés\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de base de données : " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
?>



<?php
/**
 * Script de correction des références au port 8080
 * Remplace toutes les références aux autres ports par 8080
 */

class PortCorrector {
    private $projectRoot;
    private $filesProcessed = 0;
    private $replacements = 0;
    
    public function __construct() {
        $this->projectRoot = __DIR__;
    }
    
    public function run() {
        echo "🔧 CORRECTION DES RÉFÉRENCES AU PORT 8080\n";
        echo "==========================================\n\n";
        
        $this->correctConfigFiles();
        $this->correctViewFiles();
        $this->correctJavaScriptFiles();
        $this->correctCSSFiles();
        $this->correctTestFiles();
        $this->correctShellScripts();
        
        echo "\n✅ CORRECTION TERMINÉE\n";
        echo "Fichiers traités: {$this->filesProcessed}\n";
        echo "Remplacements effectués: {$this->replacements}\n";
    }
    
    private function correctConfigFiles() {
        echo "📁 Correction des fichiers de configuration...\n";
        
        $configFiles = [
            'app/Config/App.php',
            'app/Config/Routes.php',
            'app/Config/Database.php',
            'app/Config/AcademicYear.php'
        ];
        
        foreach ($configFiles as $file) {
            $this->correctFile($file);
        }
    }
    
    private function correctViewFiles() {
        echo "📁 Correction des fichiers de vues...\n";
        
        $viewDirs = [
            'app/Views/admin/configuration',
            'app/Views/admin',
            'app/Views/auth',
            'app/Views/layouts'
        ];
        
        foreach ($viewDirs as $dir) {
            if (is_dir($dir)) {
                $this->correctDirectory($dir, ['php', 'html']);
            }
        }
    }
    
    private function correctJavaScriptFiles() {
        echo "📁 Correction des fichiers JavaScript...\n";
        
        $jsDirs = [
            'public/assets/js',
            'public/assets/bulma/js'
        ];
        
        foreach ($jsDirs as $dir) {
            if (is_dir($dir)) {
                $this->correctDirectory($dir, ['js']);
            }
        }
    }
    
    private function correctCSSFiles() {
        echo "📁 Correction des fichiers CSS...\n";
        
        $cssDirs = [
            'public/assets/css',
            'public/assets/bulma/css'
        ];
        
        foreach ($cssDirs as $dir) {
            if (is_dir($dir)) {
                $this->correctDirectory($dir, ['css']);
            }
        }
    }
    
    private function correctTestFiles() {
        echo "📁 Correction des fichiers de test...\n";
        
        $testFiles = glob('*.php');
        foreach ($testFiles as $file) {
            if (strpos($file, 'test') !== false || strpos($file, 'audit') !== false) {
                $this->correctFile($file);
            }
        }
    }
    
    private function correctShellScripts() {
        echo "📁 Correction des scripts shell...\n";
        
        $shellFiles = glob('*.sh');
        foreach ($shellFiles as $file) {
            $this->correctFile($file);
        }
    }
    
    private function correctDirectory($dir, $extensions) {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (in_array($ext, $extensions)) {
                    $this->correctFile($file);
                }
            } elseif (is_dir($file)) {
                $this->correctDirectory($file, $extensions);
            }
        }
    }
    
    private function correctFile($filePath) {
        if (!file_exists($filePath)) {
            return;
        }
        
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Remplacer les références aux autres ports par 8080
        $patterns = [
            '/localhost:808[12]/' => 'localhost:8080',
            '/127\.0\.0\.1:808[12]/' => '127.0.0.1:8080',
            '/0\.0\.0\.0:808[12]/' => '0.0.0.0:8080',
            '/--port=808[12]/' => '--port=8080',
            '/SPARK_PORT=808[12]/' => 'SPARK_PORT=8080',
            '/APP_PORT=808[12]/' => 'APP_PORT=8080',
            '/"port":\s*808[12]/' => '"port": 8080',
            '/\'port\':\s*808[12]/' => "'port': 8080"
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        // Vérifier s'il y a eu des changements
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $this->filesProcessed++;
            $this->replacements += substr_count($originalContent, '808') - substr_count($content, '808');
            echo "   ✅ {$filePath}\n";
        }
    }
}

// Exécuter la correction
$corrector = new PortCorrector();
$corrector->run();
?>





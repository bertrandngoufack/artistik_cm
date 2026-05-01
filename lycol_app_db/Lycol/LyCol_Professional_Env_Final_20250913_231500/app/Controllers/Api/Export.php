<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class Export extends BaseController
{
    public function exportData($type = 'students')
    {
        switch ($type) {
            case 'students':
                $data = $this->getStudentsData();
                $filename = 'eleves_' . date('Y-m-d') . '.csv';
                break;
            case 'grades':
                $data = $this->getGradesData();
                $filename = 'notes_' . date('Y-m-d') . '.csv';
                break;
            case 'absences':
                $data = $this->getAbsencesData();
                $filename = 'absences_' . date('Y-m-d') . '.csv';
                break;
            default:
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Type d\'export non valide'
                ])->setStatusCode(400);
        }

        return $this->response->download($filename, $this->generateCSV($data));
    }

    private function getStudentsData()
    {
        // Simulation des données d'élèves
        return [
            ['matricule' => '2024001', 'nom' => 'Dupont', 'prenom' => 'Jean', 'classe' => '6ème A'],
            ['matricule' => '2024002', 'nom' => 'Martin', 'prenom' => 'Marie', 'classe' => '6ème A']
        ];
    }

    private function getGradesData()
    {
        // Simulation des données de notes
        return [
            ['matricule' => '2024001', 'matiere' => 'Mathématiques', 'note' => 15, 'coefficient' => 4],
            ['matricule' => '2024001', 'matiere' => 'Français', 'note' => 14, 'coefficient' => 4]
        ];
    }

    private function getAbsencesData()
    {
        // Simulation des données d'absences
        return [
            ['matricule' => '2024001', 'date' => '2024-01-15', 'motif' => 'Maladie'],
            ['matricule' => '2024002', 'date' => '2024-01-16', 'motif' => 'Rendez-vous médical']
        ];
    }

    private function generateCSV($data)
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        
        // Headers
        fputcsv($output, array_keys($data[0]));
        
        // Data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}





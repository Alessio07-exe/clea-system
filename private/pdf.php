<?php
/**
 * CLEA System - PDF Generator
 */

require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../../includes/functions.php';

$machine_id = intval($_GET['id'] ?? 0);

if (!$machine_id) {
    die('Macchinario non trovato');
}

// Get machine
$machine = $db->fetch(
    "SELECT * FROM machines WHERE id = ?",
    [$machine_id]
);

if (!$machine) {
    die('Macchinario non trovato');
}

// Get photos
$photos = $db->fetchAll(
    "SELECT file_path FROM machine_photos WHERE machine_id = ? ORDER BY created_at DESC",
    [$machine_id]
);

// Get maintenance
$maintenance = $db->fetchAll(
    "SELECT * FROM maintenance WHERE machine_id = ? ORDER BY maintenance_date DESC",
    [$machine_id]
);

// Get technician
$technician = $db->fetch(
    "SELECT nome, cognome FROM users WHERE id = ?",
    [$machine['technician_id']]
);

// Generate HTML for PDF
$html = '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Macchinario - ' . esc($machine['serial_number']) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .header { border-bottom: 3px solid #1e3c72; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #1e3c72; }
        .header p { margin: 5px 0; color: #666; }
        .section { margin-bottom: 30px; page-break-inside: avoid; }
        .section h2 { color: #1e3c72; border-bottom: 2px solid #c92a2a; padding-bottom: 10px; margin-top: 0; }
        .grid { display: table; width: 100%; margin-bottom: 15px; }
        .grid-row { display: table-row; }
        .grid-col { display: table-cell; width: 50%; padding: 10px; border: 1px solid #ddd; vertical-align: top; }
        .grid-col-full { display: table-cell; width: 100%; padding: 10px; border: 1px solid #ddd; }
        .label { font-weight: bold; color: #1e3c72; margin-bottom: 5px; }
        .maintenance-item { margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #c92a2a; page-break-inside: avoid; }
        .maintenance-header { font-weight: bold; color: #1e3c72; margin-bottom: 10px; }
        .maintenance-type { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; margin-left: 10px; }
        .maintenance-type.ordinaria { background: #e3f2fd; color: #1976d2; }
        .maintenance-type.straordinaria { background: #ffebee; color: #c92a2a; }
        .photo { margin: 10px 0; page-break-inside: avoid; }
        .photo img { max-width: 100%; height: auto; border: 1px solid #ddd; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #999; font-size: 0.85em; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f9f9f9; font-weight: bold; color: #1e3c72; }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . APP_NAME . '</h1>
        <p>Rapporto Macchinario</p>
        <p>Data: ' . formatDate(date('Y-m-d')) . '</p>
    </div>

    <div class="section">
        <h2>Dati Macchinario</h2>
        <div class="grid">
            <div class="grid-row">
                <div class="grid-col">
                    <div class="label">Numero Seriale</div>
                    ' . esc($machine['serial_number']) . '
                </div>
                <div class="grid-col">
                    <div class="label">Marca</div>
                    ' . esc($machine['brand']) . '
                </div>
            </div>
            <div class="grid-row">
                <div class="grid-col">
                    <div class="label">Modello</div>
                    ' . esc($machine['model']) . '
                </div>
                <div class="grid-col">
                    <div class="label">Gas Refrigerante</div>
                    ' . esc($machine['refrigerant_quantity'] ?? 'N/A') . ' L
                </div>
            </div>
            <div class="grid-row">
                <div class="grid-col">
                    <div class="label">Data Installazione</div>
                    ' . formatDate($machine['installation_date']) . '
                </div>
                <div class="grid-col">
                    <div class="label">Ditta Installazione</div>
                    ' . esc($machine['installation_company']) . '
                </div>
            </div>
            <div class="grid-row">
                <div class="grid-col">
                    <div class="label">Tecnico</div>
                    ' . esc($technician['nome'] . ' ' . $technician['cognome']) . '
                </div>
            </div>
        </div>
    </div>';

    if (!empty($machine['installation_description'])) {
        $html .= '<div class="section">
            <h2>Descrizione Impianto</h2>
            ' . nl2br(esc($machine['installation_description'])) . '
        </div>';
    }

    if (!empty($machine['general_description'])) {
        $html .= '<div class="section">
            <h2>Descrizione Generale</h2>
            ' . nl2br(esc($machine['general_description'])) . '
        </div>';
    }

    if (!empty($photos)) {
        $html .= '<div class="section">
            <h2>Fotografie</h2>';
        foreach ($photos as $photo) {
            $html .= '<div class="photo">
                <img src="' . $_SERVER['DOCUMENT_ROOT'] . esc($photo['file_path']) . '" alt="Foto macchinario">
            </div>';
        }
        $html .= '</div>';
    }

    if (!empty($maintenance)) {
        $html .= '<div class="section">
            <h2>Storico Manutenzioni</h2>';
        foreach ($maintenance as $item) {
            $html .= '<div class="maintenance-item">
                <div class="maintenance-header">
                    ' . formatDate($item['maintenance_date']) . '
                    <span class="maintenance-type ' . $item['maintenance_type'] . '">
                        ' . formatMaintenanceType($item['maintenance_type']) . '
                    </span>
                </div>
                <p><strong>Tecnico:</strong> ' . esc($item['technician_name']) . '</p>';
            if (!empty($item['maintenance_company'])) {
                $html .= '<p><strong>Ditta:</strong> ' . esc($item['maintenance_company']) . '</p>';
            }
            if (!empty($item['problem_description'])) {
                $html .= '<p><strong>Problema:</strong> ' . nl2br(esc($item['problem_description'])) . '</p>';
            }
            if (!empty($item['intervention_description'])) {
                $html .= '<p><strong>Intervento:</strong> ' . nl2br(esc($item['intervention_description'])) . '</p>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
    }

    $html .= '<div class="footer">
        <p>Generato da ' . APP_NAME . ' - ' . formatDate(date('Y-m-d H:i')) . '</p>
    </div>
</body>
</html>';

// Output as HTML for now (PDF generation requires external library)
// In production, use mPDF or TCPDF
header('Content-Type: text/html; charset=utf-8');
echo $html;
?>

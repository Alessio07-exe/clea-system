<?php
/**
 * CLEA System - Public Machinery Page (Accessible via QR Code)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/functions.php';

$token = sanitize($_GET['token'] ?? '');
$machine_id = intval($_GET['id'] ?? 0);

if (!$token && !$machine_id) {
    die('Accesso non valido');
}

// Get machine by token or ID (token is more secure)
if ($token) {
    $machine = $db->fetch(
        "SELECT * FROM machines WHERE public_token = ?",
        [$token]
    );
} else {
    $machine = $db->fetch(
        "SELECT * FROM machines WHERE id = ?",
        [$machine_id]
    );
}

if (!$machine) {
    die('Macchinario non trovato');
}

// Get photos
$photos = $db->fetchAll(
    "SELECT file_path FROM machine_photos WHERE machine_id = ? ORDER BY created_at DESC",
    [$machine['id']]
);

// Get maintenance history
$maintenance = $db->fetchAll(
    "SELECT maintenance_date, technician_name, maintenance_company, maintenance_type,
            problem_description, intervention_description
     FROM maintenance
     WHERE machine_id = ?
     ORDER BY maintenance_date DESC",
    [$machine['id']]
);

// Get technician info
$technician = $db->fetch(
    "SELECT nome, cognome FROM users WHERE id = ?",
    [$machine['technician_id']]
);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - <?php echo esc($machine['brand'] . ' ' . $machine['model']); ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/responsive.css">
    <style>
        body { background: #f5f5f5; }
        .public-container { max-width: 100%; padding: 20px; }
        .public-header { background: linear-gradient(135deg, #1e3c72 0%, #c92a2a 100%); color: white; padding: 30px 20px; border-radius: 10px; margin-bottom: 30px; text-align: center; }
        .public-header h1 { margin: 0; font-size: 1.8em; }
        .public-header p { margin: 5px 0; opacity: 0.9; }
        .info-section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .info-item { border-left: 4px solid #1e3c72; padding-left: 12px; }
        .info-label { font-weight: bold; color: #333; font-size: 0.9em; }
        .info-value { color: #666; margin-top: 5px; }
        @media (max-width: 600px) { .info-grid { grid-template-columns: 1fr; } }
        .gallery { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
        .gallery-item { border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .gallery-item img { width: 100%; height: 250px; object-fit: cover; }
        .maintenance-item { border-left: 4px solid #c92a2a; padding: 15px; margin-bottom: 15px; background: #fafafa; border-radius: 4px; }
        .maintenance-date { font-weight: bold; color: #1e3c72; }
        .maintenance-type { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; margin-left: 10px; }
        .maintenance-type.ordinaria { background: #e3f2fd; color: #1976d2; }
        .maintenance-type.straordinaria { background: #ffebee; color: #c92a2a; }
        .footer-info { text-align: center; padding: 20px; color: #999; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="public-container">
        <div class="public-header">
            <h1><?php echo APP_NAME; ?></h1>
            <p>Dati Macchinario</p>
        </div>

        <div class="info-section">
            <h2><?php echo esc($machine['brand']); ?> - <?php echo esc($machine['model']); ?></h2>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Numero Seriale</div>
                    <div class="info-value"><?php echo esc($machine['serial_number']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Data Installazione</div>
                    <div class="info-value"><?php echo formatDate($machine['installation_date']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Gas Refrigerante</div>
                    <div class="info-value"><?php echo esc($machine['refrigerant_quantity'] ?? 'N/A'); ?> L</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Ditta Installazione</div>
                    <div class="info-value"><?php echo esc($machine['installation_company']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tecnico Installazione</div>
                    <div class="info-value"><?php echo esc($technician['nome'] . ' ' . $technician['cognome']); ?></div>
                </div>
            </div>

            <?php if (!empty($machine['installation_description'])): ?>
                <div style="margin-top: 15px;">
                    <strong style="color: #1e3c72;">Descrizione Impianto:</strong>
                    <p style="margin: 8px 0; color: #666;"><?php echo nl2br(esc($machine['installation_description'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($machine['general_description'])): ?>
                <div style="margin-top: 15px;">
                    <strong style="color: #1e3c72;">Descrizione Generale:</strong>
                    <p style="margin: 8px 0; color: #666;"><?php echo nl2br(esc($machine['general_description'])); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($photos)): ?>
            <div class="info-section">
                <h3 style="color: #1e3c72; margin-top: 0;">Fotografie</h3>
                <div class="gallery">
                    <?php foreach ($photos as $photo): ?>
                        <div class="gallery-item">
                            <img src="<?php echo esc($photo['file_path']); ?>" alt="Foto macchinario">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="info-section">
            <h3 style="color: #1e3c72; margin-top: 0;">Storico Manutenzioni</h3>
            <?php if (!empty($maintenance)): ?>
                <?php foreach ($maintenance as $item): ?>
                    <div class="maintenance-item">
                        <div class="maintenance-date">
                            <?php echo formatDate($item['maintenance_date']); ?>
                            <span class="maintenance-type <?php echo $item['maintenance_type']; ?>">
                                <?php echo formatMaintenanceType($item['maintenance_type']); ?>
                            </span>
                        </div>
                        <p style="margin: 8px 0; color: #666;">
                            <strong>Tecnico:</strong> <?php echo esc($item['technician_name']); ?>
                        </p>
                        <?php if (!empty($item['maintenance_company'])): ?>
                            <p style="margin: 8px 0; color: #666;">
                                <strong>Ditta:</strong> <?php echo esc($item['maintenance_company']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($item['problem_description'])): ?>
                            <p style="margin: 8px 0; color: #666;">
                                <strong>Problema:</strong> <?php echo nl2br(esc($item['problem_description'])); ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($item['intervention_description'])): ?>
                            <p style="margin: 8px 0; color: #666;">
                                <strong>Intervento:</strong> <?php echo nl2br(esc($item['intervention_description'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #999; text-align: center;">Nessuna manutenzione registrata</p>
            <?php endif; ?>
        </div>

        <div class="footer-info">
            <p><?php echo APP_NAME; ?> - Sistema di Gestione Manutenzione Macchinari</p>
        </div>
    </div>
</body>
</html>

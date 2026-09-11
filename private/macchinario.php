<?php
/**
 * CLEA System - Machinery Detail Page (Authenticated)
 */

require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/header.php';

$user = getCurrentUser($db);
$machine_id = intval($_GET['id'] ?? 0);
$new = isset($_GET['new']) ? 1 : 0;

if (!$machine_id) {
    redirect('/private/macchinari.php');
}

// Get machine details
$machine = $db->fetch(
    "SELECT * FROM machines WHERE id = ?",
    [$machine_id]
);

if (!$machine) {
    setFlash('error', 'Macchinario non trovato');
    redirect('/private/macchinari.php');
}

// Get photos
$photos = $db->fetchAll(
    "SELECT id, file_path FROM machine_photos WHERE machine_id = ? ORDER BY created_at DESC",
    [$machine_id]
);

// Get maintenance history
$maintenance = $db->fetchAll(
    "SELECT id, maintenance_date, technician_name, maintenance_company, maintenance_type, 
            problem_description, intervention_description
     FROM maintenance
     WHERE machine_id = ?
     ORDER BY maintenance_date DESC",
    [$machine_id]
);

// Get technician info
$technician = $db->fetch(
    "SELECT nome, cognome FROM users WHERE id = ?",
    [$machine['technician_id']]
);

$page_title = $machine['brand'] . ' ' . $machine['model'];
?>

<div class="machinery-detail">
    <?php if ($new): ?>
        <div class="alert alert-success">
            <p>✅ Macchinario registrato con successo!</p>
        </div>
    <?php endif; ?>

    <div class="detail-header">
        <h2><?php echo esc($machine['brand']); ?> - <?php echo esc($machine['model']); ?></h2>
        <div class="detail-actions">
            <a href="<?php echo APP_URL; ?>/private/modifica_macchinario.php?id=<?php echo $machine['id']; ?>" class="btn btn-secondary">
                ✏️ Modifica
            </a>
            <a href="<?php echo APP_URL; ?>/private/pdf.php?id=<?php echo $machine['id']; ?>" class="btn btn-secondary" target="_blank">
                📄 Scarica PDF
            </a>
            <a href="<?php echo APP_URL; ?>/private/nuova_manutenzione.php?machine_id=<?php echo $machine['id']; ?>" class="btn btn-primary">
                + Aggiungi Manutenzione
            </a>
        </div>
    </div>

    <div class="detail-content">
        <div class="detail-section">
            <h3>Dati Installazione</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="label">Numero Seriale:</span>
                    <span class="value"><?php echo esc($machine['serial_number']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Marca:</span>
                    <span class="value"><?php echo esc($machine['brand']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Modello:</span>
                    <span class="value"><?php echo esc($machine['model']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Gas Refrigerante:</span>
                    <span class="value"><?php echo esc($machine['refrigerant_quantity'] ?? 'N/A'); ?> L</span>
                </div>
                <div class="detail-item">
                    <span class="label">Data Installazione:</span>
                    <span class="value"><?php echo formatDate($machine['installation_date']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Ditta:</span>
                    <span class="value"><?php echo esc($machine['installation_company']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Tecnico:</span>
                    <span class="value"><?php echo esc($technician['nome'] . ' ' . $technician['cognome']); ?></span>
                </div>
            </div>

            <?php if (!empty($machine['installation_description'])): ?>
                <div class="description-box">
                    <h4>Descrizione Impianto:</h4>
                    <p><?php echo esc($machine['installation_description']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($machine['general_description'])): ?>
                <div class="description-box">
                    <h4>Descrizione Generale:</h4>
                    <p><?php echo esc($machine['general_description']); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($photos)): ?>
            <div class="detail-section">
                <h3>Fotografie</h3>
                <div class="photos-gallery">
                    <?php foreach ($photos as $photo): ?>
                        <div class="photo-item">
                            <img src="<?php echo esc($photo['file_path']); ?>" alt="Foto macchinario">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="detail-section">
            <h3>Storico Manutenzioni</h3>
            <?php if (!empty($maintenance)): ?>
                <div class="maintenance-history">
                    <?php foreach ($maintenance as $item): ?>
                        <div class="maintenance-record">
                            <div class="record-header">
                                <span class="date"><?php echo formatDate($item['maintenance_date']); ?></span>
                                <span class="type <?php echo $item['maintenance_type']; ?>">
                                    <?php echo formatMaintenanceType($item['maintenance_type']); ?>
                                </span>
                            </div>
                            <div class="record-body">
                                <p><strong>Tecnico:</strong> <?php echo esc($item['technician_name']); ?></p>
                                <p><strong>Ditta:</strong> <?php echo esc($item['maintenance_company']); ?></p>
                                <div class="description-box">
                                    <p><strong>Problema:</strong> <?php echo esc($item['problem_description']); ?></p>
                                </div>
                                <div class="description-box">
                                    <p><strong>Intervento:</strong> <?php echo esc($item['intervention_description']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-state">Nessuna manutenzione registrata</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

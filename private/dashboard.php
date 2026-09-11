<?php
/**
 * CLEA System - Dashboard
 */

require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/header.php';

$user = getCurrentUser($db);

// Get statistics
$machine_count = $db->count('machines', 'technician_id = ?', [$_SESSION['user_id']]);
$maintenance_count = $db->count('maintenance', 'technician_id = ?', [$_SESSION['user_id']]);

// Get recent maintenance
$recent_maintenance = $db->fetchAll(
    "SELECT m.id, m.maintenance_date, m.maintenance_type, m.problem_description,
            mc.serial_number, mc.brand, mc.model
     FROM maintenance m
     JOIN machines mc ON m.machine_id = mc.id
     WHERE m.technician_id = ?
     ORDER BY m.maintenance_date DESC
     LIMIT 10",
    [$_SESSION['user_id']]
);

$page_title = 'Dashboard';
?>

<div class="dashboard">
    <h2>Benvenuto, <?php echo esc($user['nome']); ?>!</h2>
    <p class="subtitle">Azienda: <?php echo esc($user['azienda']); ?></p>

    <div class="dashboard-stats">
        <div class="stat-card">
            <h3><?php echo $machine_count; ?></h3>
            <p>Macchinari Registrati</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $maintenance_count; ?></h3>
            <p>Manutenzioni Effettuate</p>
        </div>
    </div>

    <div class="dashboard-actions">
        <a href="<?php echo APP_URL; ?>/private/nuova_installazione.php" class="btn btn-primary">
            ➕ Nuova Installazione
        </a>
        <a href="<?php echo APP_URL; ?>/private/macchinari.php" class="btn btn-secondary">
            🔍 Cerca Macchinario
        </a>
    </div>

    <div class="recent-section">
        <h3>Ultimi Interventi</h3>
        <?php if (!empty($recent_maintenance)): ?>
            <div class="maintenance-list">
                <?php foreach ($recent_maintenance as $maintenance): ?>
                    <div class="maintenance-item">
                        <div class="maintenance-header">
                            <strong><?php echo esc($maintenance['brand']); ?> - <?php echo esc($maintenance['model']); ?></strong>
                            <span class="date"><?php echo formatDate($maintenance['maintenance_date']); ?></span>
                        </div>
                        <p class="serial">SN: <?php echo esc($maintenance['serial_number']); ?></p>
                        <p class="type"><?php echo formatMaintenanceType($maintenance['maintenance_type']); ?></p>
                        <p class="problem"><?php echo esc($maintenance['problem_description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty-state">Nessun intervento registrato</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

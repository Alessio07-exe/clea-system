<?php
/**
 * CLEA System - Machinery List and Search
 */

require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/header.php';

$user = getCurrentUser($db);
$search_query = sanitize($_GET['q'] ?? '');
$machines = [];

if (!empty($search_query)) {
    // Search by serial number or brand/model
    $machines = $db->fetchAll(
        "SELECT id, serial_number, brand, model, installation_date, installation_company
         FROM machines
         WHERE (serial_number LIKE ? OR brand LIKE ? OR model LIKE ?)
         AND technician_id = ?
         ORDER BY installation_date DESC",
        ["%$search_query%", "%$search_query%", "%$search_query%", $_SESSION['user_id']]
    );
} else {
    // Get all machines for this technician
    $machines = $db->fetchAll(
        "SELECT id, serial_number, brand, model, installation_date, installation_company
         FROM machines
         WHERE technician_id = ?
         ORDER BY installation_date DESC",
        [$_SESSION['user_id']]
    );
}

$page_title = 'Macchinari';
?>

<div class="machinery-page">
    <h2>Macchinari</h2>
    
    <div class="search-box">
        <form method="GET" class="search-form">
            <input 
                type="text" 
                name="q" 
                placeholder="Cerca per seriale, marca o modello..." 
                value="<?php echo esc($search_query); ?>"
                class="search-input"
            >
            <button type="submit" class="btn btn-primary">Cerca</button>
            <?php if (!empty($search_query)): ?>
                <a href="<?php echo APP_URL; ?>/private/macchinari.php" class="btn btn-secondary">Cancella</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!empty($machines)): ?>
        <div class="machinery-list">
            <?php foreach ($machines as $machine): ?>
                <div class="machinery-card">
                    <div class="card-header">
                        <h3><?php echo esc($machine['brand']); ?> - <?php echo esc($machine['model']); ?></h3>
                        <span class="serial">SN: <?php echo esc($machine['serial_number']); ?></span>
                    </div>
                    <div class="card-body">
                        <p><strong>Azienda:</strong> <?php echo esc($machine['installation_company']); ?></p>
                        <p><strong>Data Installazione:</strong> <?php echo formatDate($machine['installation_date']); ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo APP_URL; ?>/private/macchinario.php?id=<?php echo $machine['id']; ?>" class="btn btn-primary">
                            Visualizza
                        </a>
                        <a href="<?php echo APP_URL; ?>/private/modifica_macchinario.php?id=<?php echo $machine['id']; ?>" class="btn btn-secondary">
                            Modifica
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>Nessun macchinario trovato</p>
            <a href="<?php echo APP_URL; ?>/private/nuova_installazione.php" class="btn btn-primary">
                Registra il primo macchinario
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

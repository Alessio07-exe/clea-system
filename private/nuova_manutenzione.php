<?php
/**
 * CLEA System - Add Maintenance
 */

require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/header.php';

$user = getCurrentUser($db);
$machine_id = intval($_GET['machine_id'] ?? 0);
$error = '';

if (!$machine_id) {
    redirect('/private/macchinari.php');
}

// Get machine
$machine = $db->fetch(
    "SELECT id, serial_number, brand, model FROM machines WHERE id = ?",
    [$machine_id]
);

if (!$machine) {
    setFlash('error', 'Macchinario non trovato');
    redirect('/private/macchinari.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token di sicurezza non valido';
    } else {
        $maintenance_date = $_POST['maintenance_date'] ?? '';
        $technician_name = sanitize($_POST['technician_name'] ?? '');
        $maintenance_company = sanitize($_POST['maintenance_company'] ?? '');
        $maintenance_type = $_POST['maintenance_type'] ?? '';
        $problem_description = sanitize($_POST['problem_description'] ?? '');
        $intervention_description = sanitize($_POST['intervention_description'] ?? '');

        if (empty($maintenance_date) || empty($technician_name) || empty($maintenance_type)) {
            $error = 'Compila tutti i campi obbligatori';
        } elseif (!in_array($maintenance_type, ['ordinaria', 'straordinaria'])) {
            $error = 'Tipo di manutenzione non valido';
        } else {
            try {
                $db->insert('maintenance', [
                    'machine_id' => $machine_id,
                    'technician_id' => $_SESSION['user_id'],
                    'maintenance_date' => $maintenance_date,
                    'technician_name' => $technician_name,
                    'maintenance_company' => $maintenance_company,
                    'maintenance_type' => $maintenance_type,
                    'problem_description' => $problem_description,
                    'intervention_description' => $intervention_description
                ]);

                setFlash('success', 'Manutenzione registrata con successo');
                redirect('/private/macchinario.php?id=' . $machine_id);
            } catch (Exception $e) {
                $error = 'Errore durante la registrazione: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Nuova Manutenzione';
?>

<div class="new-maintenance-page">
    <h2>Aggiungi Manutenzione</h2>
    
    <div class="machine-info">
        <p><strong><?php echo esc($machine['brand']); ?> - <?php echo esc($machine['model']); ?></strong></p>
        <p>SN: <?php echo esc($machine['serial_number']); ?></p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <p><?php echo esc($error); ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" class="maintenance-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="machine_id" value="<?php echo $machine_id; ?>">

        <div class="form-section">
            <h3>Dati Manutenzione</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="maintenance_date">Data Manutenzione *</label>
                    <input type="date" id="maintenance_date" name="maintenance_date" required>
                </div>
                <div class="form-group">
                    <label for="maintenance_type">Tipo *</label>
                    <select id="maintenance_type" name="maintenance_type" required>
                        <option value="">Seleziona...</option>
                        <option value="ordinaria">Manutenzione Ordinaria</option>
                        <option value="straordinaria">Manutenzione Straordinaria</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="technician_name">Nome Tecnico *</label>
                    <input type="text" id="technician_name" name="technician_name" value="<?php echo esc($user['nome'] . ' ' . $user['cognome']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="maintenance_company">Ditta</label>
                    <input type="text" id="maintenance_company" name="maintenance_company" value="<?php echo esc($user['azienda']); ?>">
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-group">
                <label for="problem_description">Descrizione Problema</label>
                <textarea id="problem_description" name="problem_description" rows="4" placeholder="Descrivi il problema riscontrato..."></textarea>
            </div>

            <div class="form-group">
                <label for="intervention_description">Descrizione Intervento *</label>
                <textarea id="intervention_description" name="intervention_description" rows="4" placeholder="Descrivi l'intervento effettuato..." required></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">Registra Manutenzione</button>
            <a href="<?php echo APP_URL; ?>/private/macchinario.php?id=<?php echo $machine_id; ?>" class="btn btn-secondary btn-lg">Annulla</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

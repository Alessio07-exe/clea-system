<?php
/**
 * CLEA System - New Machinery Installation
 */

require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/header.php';

$user = getCurrentUser($db);
$error = '';
$success = false;
$uploaded_photos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token di sicurezza non valido';
    } else {
        $serial_number = sanitize($_POST['serial_number'] ?? '');
        $brand = sanitize($_POST['brand'] ?? '');
        $model = sanitize($_POST['model'] ?? '');
        $refrigerant = floatval($_POST['refrigerant'] ?? 0);
        $description = sanitize($_POST['description'] ?? '');
        $installation_date = $_POST['installation_date'] ?? '';
        $installation_company = sanitize($_POST['installation_company'] ?? '');
        $general_description = sanitize($_POST['general_description'] ?? '');

        // Validate required fields
        if (empty($serial_number) || empty($brand) || empty($model) || empty($installation_date)) {
            $error = 'Compila tutti i campi obbligatori';
        } else {
            try {
                // Check if serial number already exists
                $existing = $db->fetch(
                    "SELECT id FROM machines WHERE serial_number = ?",
                    [$serial_number]
                );

                if ($existing) {
                    $error = 'Questo numero seriale è già registrato nel sistema';
                } else {
                    // Generate unique public token
                    $public_token = generatePublicToken();

                    // Insert machine
                    $machine_id = $db->insert('machines', [
                        'serial_number' => $serial_number,
                        'brand' => $brand,
                        'model' => $model,
                        'refrigerant_quantity' => $refrigerant,
                        'installation_description' => $description,
                        'installation_date' => $installation_date,
                        'technician_id' => $_SESSION['user_id'],
                        'installation_company' => $installation_company,
                        'general_description' => $general_description,
                        'public_token' => $public_token
                    ]);

                    // Handle photo uploads
                    if (!empty($_FILES['photos']['name'][0])) {
                        for ($i = 0; $i < count($_FILES['photos']['name']); $i++) {
                            $file = [
                                'name' => $_FILES['photos']['name'][$i],
                                'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                                'error' => $_FILES['photos']['error'][$i],
                                'size' => $_FILES['photos']['size'][$i]
                            ];

                            $upload_result = uploadMachinePhoto($file);
                            if ($upload_result['success']) {
                                $db->insert('machine_photos', [
                                    'machine_id' => $machine_id,
                                    'file_path' => $upload_result['path'],
                                    'original_filename' => $upload_result['original_name']
                                ]);
                                $uploaded_photos[] = $upload_result['path'];
                            }
                        }
                    }

                    // Success
                    $_SESSION['new_machine_id'] = $machine_id;
                    $_SESSION['new_machine_token'] = $public_token;
                    redirect('/private/macchinario.php?id=' . $machine_id . '&new=1');
                }
            } catch (Exception $e) {
                $error = 'Errore durante la registrazione: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Nuova Installazione';
?>

<div class="new-installation-page">
    <h2>Registra Nuova Installazione</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <p><?php echo esc($error); ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="installation-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <div class="form-section">
            <h3>Dati Macchinario</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="serial_number">Numero Seriale *</label>
                    <input type="text" id="serial_number" name="serial_number" required>
                </div>
                <div class="form-group">
                    <label for="brand">Marca *</label>
                    <input type="text" id="brand" name="brand" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="model">Modello *</label>
                    <input type="text" id="model" name="model" required>
                </div>
                <div class="form-group">
                    <label for="refrigerant">Gas Refrigerante (litri)</label>
                    <input type="number" id="refrigerant" name="refrigerant" step="0.1" min="0">
                </div>
            </div>

            <div class="form-group">
                <label for="description">Descrizione dell'Impianto</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>Dati Installazione</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="installation_date">Data Installazione *</label>
                    <input type="date" id="installation_date" name="installation_date" required>
                </div>
                <div class="form-group">
                    <label for="installation_company">Ditta Installazione</label>
                    <input type="text" id="installation_company" name="installation_company" value="<?php echo esc($user['azienda']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="general_description">Descrizione Generale</label>
                <textarea id="general_description" name="general_description" rows="3"></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>Fotografie</h3>
            <p class="form-help">Carica una o più fotografie dell'impianto (JPG, PNG, WebP - Max 5MB)</p>
            
            <div class="form-group">
                <label for="photos">Seleziona Foto</label>
                <input 
                    type="file" 
                    id="photos" 
                    name="photos[]" 
                    multiple 
                    accept="image/jpeg,image/png,image/webp"
                >
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">Registra Macchinario</button>
            <a href="<?php echo APP_URL; ?>/private/macchinari.php" class="btn btn-secondary btn-lg">Annulla</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

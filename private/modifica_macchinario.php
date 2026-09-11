<?php
/**
 * CLEA System - Edit Machinery
 */

require_once __DIR__ . '/../auth/check_auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/header.php';

$user = getCurrentUser($db);
$machine_id = intval($_GET['id'] ?? 0);
$error = '';

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

// Check permissions
if ($machine['technician_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] !== 'admin') {
    setFlash('error', 'Non hai i permessi per modificare questo macchinario');
    redirect('/private/macchinari.php');
}

// Get photos
$photos = $db->fetchAll(
    "SELECT id, file_path FROM machine_photos WHERE machine_id = ?",
    [$machine_id]
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token di sicurezza non valido';
    } else {
        $brand = sanitize($_POST['brand'] ?? '');
        $model = sanitize($_POST['model'] ?? '');
        $refrigerant = floatval($_POST['refrigerant'] ?? 0);
        $description = sanitize($_POST['description'] ?? '');
        $installation_company = sanitize($_POST['installation_company'] ?? '');
        $general_description = sanitize($_POST['general_description'] ?? '');

        if (empty($brand) || empty($model)) {
            $error = 'Compila tutti i campi obbligatori';
        } else {
            try {
                // Update machine
                $db->update('machines', [
                    'brand' => $brand,
                    'model' => $model,
                    'refrigerant_quantity' => $refrigerant,
                    'installation_description' => $description,
                    'installation_company' => $installation_company,
                    'general_description' => $general_description
                ], 'id = ?', [$machine_id]);

                // Handle new photo uploads
                if (!empty($_FILES['photos']['name'][0])) {
                    for ($i = 0; $i < count($_FILES['photos']['name']); $i++) {
                        if (!empty($_FILES['photos']['name'][$i])) {
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
                            }
                        }
                    }
                }

                setFlash('success', 'Macchinario modificato con successo');
                redirect('/private/macchinario.php?id=' . $machine_id);
            } catch (Exception $e) {
                $error = 'Errore durante la modifica: ' . $e->getMessage();
            }
        }
    }
}

// Handle photo deletion
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_photo'])) {
    $photo_id = intval($_GET['delete_photo']);
    if (verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        deletePhoto($photo_id, $db);
        redirect('/private/modifica_macchinario.php?id=' . $machine_id);
    }
}

$page_title = 'Modifica ' . $machine['brand'];
?>

<div class="edit-machinery-page">
    <h2>Modifica Macchinario</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <p><?php echo esc($error); ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="edit-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <div class="form-section">
            <h3>Dati Macchinario</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="serial_number">Numero Seriale</label>
                    <input type="text" id="serial_number" name="serial_number" value="<?php echo esc($machine['serial_number']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="brand">Marca *</label>
                    <input type="text" id="brand" name="brand" value="<?php echo esc($machine['brand']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="model">Modello *</label>
                    <input type="text" id="model" name="model" value="<?php echo esc($machine['model']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="refrigerant">Gas Refrigerante (litri)</label>
                    <input type="number" id="refrigerant" name="refrigerant" step="0.1" value="<?php echo esc($machine['refrigerant_quantity']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="description">Descrizione Impianto</label>
                <textarea id="description" name="description" rows="3"><?php echo esc($machine['installation_description']); ?></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>Dati Installazione</h3>
            
            <div class="form-group">
                <label for="installation_company">Ditta Installazione</label>
                <input type="text" id="installation_company" name="installation_company" value="<?php echo esc($machine['installation_company']); ?>">
            </div>

            <div class="form-group">
                <label for="general_description">Descrizione Generale</label>
                <textarea id="general_description" name="general_description" rows="3"><?php echo esc($machine['general_description']); ?></textarea>
            </div>
        </div>

        <?php if (!empty($photos)): ?>
            <div class="form-section">
                <h3>Fotografie Attuali</h3>
                <div class="photos-list">
                    <?php foreach ($photos as $photo): ?>
                        <div class="photo-item">
                            <img src="<?php echo esc($photo['file_path']); ?>" alt="Foto macchinario" style="max-width: 200px;">
                            <a href="?id=<?php echo $machine_id; ?>&delete_photo=<?php echo $photo['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Eliminare questa foto?')">
                                Elimina
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h3>Aggiungi Fotografie</h3>
            <p class="form-help">Carica una o più fotografie (JPG, PNG, WebP - Max 5MB)</p>
            
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
            <button type="submit" class="btn btn-primary btn-lg">Salva Modifiche</button>
            <a href="<?php echo APP_URL; ?>/private/macchinario.php?id=<?php echo $machine_id; ?>" class="btn btn-secondary btn-lg">Annulla</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<?php
require_once '../database/db.php';
require_once 'common.php';

// Get database instance
$db = ResumeDB::getInstance();

// Get resume ID and name
$resumeId = getResumeId($db);
$resumeName = getResumeName($db, $resumeId);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $educationEntries = $_POST['education'] ?? [];
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Delete existing education entries for this resume
        $db->execute("DELETE FROM education WHERE resume_id = ?", [$resumeId]);
        
        // Insert new entries
        $displayOrder = 1;
        foreach ($educationEntries as $entry) {
            if (!empty($entry['degree']) && !empty($entry['institution'])) {
                $db->execute(
                    "INSERT INTO education (resume_id, degree, institution, location, date_range, description, display_order) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $resumeId,
                        $entry['degree'],
                        $entry['institution'],
                        $entry['location'] ?? '',
                        $entry['date_range'] ?? '',
                        $entry['description'] ?? '',
                        $displayOrder++
                    ]
                );
            }
        }
        
        $db->commit();
        $_SESSION['message'] = 'Education updated successfully!';
        header("Location: education.php?resume_id=" . $resumeId);
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = "Error updating education: " . $e->getMessage();
    }
}

// Get existing education entries for this resume
$education = $db->query(
    "SELECT * FROM education WHERE resume_id = ? AND is_visible = 1 ORDER BY display_order, date_range DESC",
    [$resumeId]
);

// Set page title and description
$pageTitle = 'Edit Education - ' . $resumeName;
$pageDescription = 'Manage your educational background';

require_once '../includes/header.php';
?>

<div class="container py-4">
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php displayBreadcrumbs($resumeName, 'Education'); ?>

    <div class="row">
        <div class="col-md-3">
            <?php displaySectionNav($resumeId, 'education'); ?>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Education
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" id="educationForm" class="needs-validation" novalidate>
                        <div id="education-fields">
                            <?php foreach ($education as $index => $entry): ?>
                            <div class="dynamic-field card mb-3" data-index="<?php echo $index; ?>">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Degree</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="education[<?php echo $index; ?>][degree]" 
                                                   value="<?php echo htmlspecialchars($entry['degree']); ?>" 
                                                   required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Institution</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="education[<?php echo $index; ?>][institution]" 
                                                   value="<?php echo htmlspecialchars($entry['institution']); ?>" 
                                                   required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Location</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="education[<?php echo $index; ?>][location]" 
                                                   value="<?php echo htmlspecialchars($entry['location']); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Date Range</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="education[<?php echo $index; ?>][date_range]" 
                                                   value="<?php echo htmlspecialchars($entry['date_range']); ?>"
                                                   placeholder="e.g., 2018 - 2022">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" 
                                                      name="education[<?php echo $index; ?>][description]" 
                                                      rows="3"><?php echo htmlspecialchars($entry['description']); ?></textarea>
                                        </div>
                                    </div>
                                    <?php if ($index > 0): ?>
                                    <button type="button" class="btn btn-outline-danger mt-3 remove-field">
                                        <i class="fas fa-trash me-1"></i>Remove Entry
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <?php if (empty($education)): ?>
                            <div class="dynamic-field card mb-3" data-index="0">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Degree</label>
                                            <input type="text" class="form-control" name="education[0][degree]" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Institution</label>
                                            <input type="text" class="form-control" name="education[0][institution]" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Location</label>
                                            <input type="text" class="form-control" name="education[0][location]">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Date Range</label>
                                            <input type="text" class="form-control" name="education[0][date_range]" placeholder="e.g., 2018 - 2022">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="education[0][description]" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-primary" onclick="addEducationField()">
                                <i class="fas fa-plus me-1"></i>Add Education
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
// Initialize sortable
const container = document.getElementById('education-fields');
new Sortable(container, {
    animation: 150,
    handle: '.card-body',
    ghostClass: 'sortable-ghost'
});

function addEducationField() {
    const container = document.getElementById('education-fields');
    const fieldCount = container.getElementsByClassName('dynamic-field').length;
    const template = document.querySelector('.dynamic-field').cloneNode(true);
    
    // Update all IDs and names
    template.setAttribute('data-index', fieldCount);
    template.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${fieldCount}]`);
        el.value = '';
    });
    
    // Add remove button
    if (fieldCount > 0) {
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-outline-danger mt-3 remove-field';
        removeBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Remove Entry';
        template.querySelector('.card-body').appendChild(removeBtn);
    }
    
    container.appendChild(template);
    template.querySelector('input').focus();
}

// Handle remove buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-field') || e.target.closest('.remove-field')) {
        const field = e.target.closest('.dynamic-field');
        field.remove();
        
        // Reindex remaining fields
        document.querySelectorAll('.dynamic-field').forEach((field, index) => {
            field.setAttribute('data-index', index);
            field.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/\[\d+\]/, `[${index}]`);
            });
        });
    }
});

// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php require_once '../includes/footer.php'; ?>
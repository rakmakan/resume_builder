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
    $projectEntries = $_POST['project'] ?? [];
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Delete existing projects for this resume
        $db->execute("DELETE FROM projects WHERE resume_id = ?", [$resumeId]);
        
        // Insert new projects
        $displayOrder = 1;
        foreach ($projectEntries as $entry) {
            if (!empty($entry['title'])) {
                $db->execute(
                    "INSERT INTO projects (resume_id, title, technologies, link, description, display_order) 
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $resumeId,
                        $entry['title'],
                        $entry['technologies'] ?? '',
                        $entry['link'] ?? '',
                        $entry['description'] ?? '',
                        $displayOrder++
                    ]
                );
            }
        }
        
        // Commit transaction
        $db->commit();
        $_SESSION['message'] = 'Projects updated successfully!';
    } catch (Exception $e) {
        // Rollback on error
        $db->rollBack();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    header("Location: projects.php?resume_id=" . $resumeId);
    exit;
}

// Load projects from database
$projects = $db->query(
    "SELECT * FROM projects WHERE resume_id = ? AND is_visible = 1 ORDER BY display_order",
    [$resumeId]
);

// Set page title and description
$pageTitle = 'Edit Projects - ' . $resumeName;
$pageDescription = 'Manage your project portfolio';

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

    <?php displayBreadcrumbs($resumeName, 'Projects'); ?>

    <div class="row">
        <div class="col-md-3">
            <?php displaySectionNav($resumeId, 'projects'); ?>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-project-diagram me-2"></i>Projects
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" class="needs-validation" novalidate>
                        <div id="project-fields">
                            <?php foreach ($projects as $index => $project): ?>
                            <div class="dynamic-field card mb-3" data-index="<?php echo $index; ?>">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Project Title</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="project[<?php echo $index; ?>][title]" 
                                                   value="<?php echo htmlspecialchars($project['title']); ?>" 
                                                   required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Technologies</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="project[<?php echo $index; ?>][technologies]" 
                                                   value="<?php echo htmlspecialchars($project['technologies']); ?>"
                                                   placeholder="e.g., React, Node.js, MongoDB">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Project Link</label>
                                            <input type="url" 
                                                   class="form-control" 
                                                   name="project[<?php echo $index; ?>][link]" 
                                                   value="<?php echo htmlspecialchars($project['link']); ?>"
                                                   placeholder="https://github.com/username/project">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Project Description</label>
                                            <textarea class="form-control" 
                                                      name="project[<?php echo $index; ?>][description]" 
                                                      rows="3" 
                                                      required><?php echo htmlspecialchars($project['description']); ?></textarea>
                                        </div>
                                    </div>
                                    <?php if ($index > 0): ?>
                                    <button type="button" class="btn btn-outline-danger mt-3 remove-field">
                                        <i class="fas fa-trash me-1"></i>Remove Project
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <?php if (empty($projects)): ?>
                            <div class="dynamic-field card mb-3" data-index="0">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Project Title</label>
                                            <input type="text" class="form-control" name="project[0][title]" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Technologies</label>
                                            <input type="text" class="form-control" name="project[0][technologies]" placeholder="e.g., React, Node.js, MongoDB">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Project Link</label>
                                            <input type="url" class="form-control" name="project[0][link]" placeholder="https://github.com/username/project">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Project Description</label>
                                            <textarea class="form-control" name="project[0][description]" rows="3" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-primary" onclick="addProjectField()">
                                <i class="fas fa-plus me-1"></i>Add Project
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
const container = document.getElementById('project-fields');
new Sortable(container, {
    animation: 150,
    handle: '.card-body',
    ghostClass: 'sortable-ghost'
});

function addProjectField() {
    const container = document.getElementById('project-fields');
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
        removeBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Remove Project';
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
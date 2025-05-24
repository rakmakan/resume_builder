<?php
require_once '../database/db.php';
require_once 'common.php';

// Get database instance
$db = ResumeDB::getInstance();

// Get resume ID and name
$resumeId = getResumeId($db);
$resumeName = getResumeName($db, $resumeId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    
    if (!empty($content)) {
        // Check if summary exists for this resume
        $existing = $db->querySingle("SELECT id FROM summary WHERE resume_id = ?", [$resumeId]);
        
        if ($existing) {
            $db->execute(
                "UPDATE summary SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE resume_id = ?",
                [$content, $resumeId]
            );
        } else {
            $db->execute(
                "INSERT INTO summary (resume_id, content) VALUES (?, ?)",
                [$resumeId, $content]
            );
        }
        
        $_SESSION['message'] = 'Professional summary updated successfully!';
        header("Location: summary.php?resume_id=" . $resumeId);
        exit;
    }
}

// Get current summary
$summary = $db->querySingle("SELECT * FROM summary WHERE resume_id = ?", [$resumeId]);
$currentContent = $summary['content'] ?? '';

// Set page title and description
$pageTitle = 'Edit Professional Summary - ' . $resumeName;
$pageDescription = 'Update your professional summary';

require_once '../includes/header.php';
?>

<div class="container py-4">
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php displayBreadcrumbs($resumeName, 'Professional Summary'); ?>

    <div class="row">
        <div class="col-md-3">
            <?php displaySectionNav($resumeId, 'summary'); ?>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>Professional Summary
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="content" class="form-label">Professional Summary</label>
                            <textarea class="form-control" 
                                    id="content" 
                                    name="content" 
                                    rows="6" 
                                    required><?php echo htmlspecialchars($currentContent); ?></textarea>
                            <div class="form-text">
                                Write a brief overview of your professional background, skills, and career objectives.
                            </div>
                            <div class="invalid-feedback">
                                Please enter your professional summary
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="resumes.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Resumes
                            </a>
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

<script>
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
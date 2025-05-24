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
    $name = $_POST['name'] ?? '';
    $contactInfo = $_POST['contact_info'] ?? '';
    
    if (!empty($name) && !empty($contactInfo)) {
        // Check if personal info exists for this resume
        $existing = $db->querySingle("SELECT id FROM personal_info WHERE resume_id = ?", [$resumeId]);
        
        if ($existing) {
            $db->execute(
                "UPDATE personal_info SET name = ?, contact_info = ?, updated_at = CURRENT_TIMESTAMP WHERE resume_id = ?",
                [$name, $contactInfo, $resumeId]
            );
        } else {
            $db->execute(
                "INSERT INTO personal_info (resume_id, name, contact_info) VALUES (?, ?, ?)",
                [$resumeId, $name, $contactInfo]
            );
        }
        
        $_SESSION['message'] = 'Personal information updated successfully!';
        header("Location: personal_info.php?resume_id=" . $resumeId);
        exit;
    }
}

// Get current personal info
$personal = $db->querySingle("SELECT * FROM personal_info WHERE resume_id = ?", [$resumeId]);
$currentName = $personal['name'] ?? '';
$currentContactInfo = $personal['contact_info'] ?? '';

// Set page title and description
$pageTitle = 'Edit Personal Information - ' . $resumeName;
$pageDescription = 'Update your personal and contact information';

require_once '../includes/header.php';
?>

<div class="container py-4">
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php displayBreadcrumbs($resumeName, 'Personal Information'); ?>

    <div class="row">
        <div class="col-md-3">
            <?php displaySectionNav($resumeId, 'personal_info'); ?>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="<?php echo htmlspecialchars($currentName); ?>" 
                                   required>
                            <div class="invalid-feedback">
                                Please enter your name
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact_info" class="form-label">Contact Information</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="contact_info" 
                                   name="contact_info" 
                                   value="<?php echo htmlspecialchars($currentContactInfo); ?>" 
                                   required>
                            <div class="form-text">
                                Separate multiple items with | (e.g., "email@example.com | (123) 456-7890 | github.com/username")
                            </div>
                            <div class="invalid-feedback">
                                Please enter your contact information
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
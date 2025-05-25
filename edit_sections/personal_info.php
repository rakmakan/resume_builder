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
    
    // First, update the main personal info
    $existing = $db->querySingle("SELECT id FROM personal_info WHERE resume_id = ?", [$resumeId]);
    
    if ($existing) {
        $db->execute(
            "UPDATE personal_info SET name = ?, updated_at = CURRENT_TIMESTAMP WHERE resume_id = ?",
            [$name, $resumeId]
        );
    } else {
        $db->execute(
            "INSERT INTO personal_info (resume_id, name) VALUES (?, ?)",
            [$resumeId, $name]
        );
    }
    
    // Handle personal info details
    // First, remove all existing details for this resume
    $db->execute("DELETE FROM personal_info_details WHERE resume_id = ?", [$resumeId]);
    
    // Add new details
    $detailTypes = ['email', 'phone', 'github', 'linkedin'];
    foreach ($detailTypes as $type) {
        if (!empty($_POST[$type])) {
            $icon = match($type) {
                'email' => 'fas fa-envelope',
                'phone' => 'fas fa-phone',
                'github' => 'fab fa-github',
                'linkedin' => 'fab fa-linkedin',
                default => ''
            };
            
            $detailName = ucfirst($type);
            $detailInfo = $_POST[$type];
            
            $db->execute(
                "INSERT INTO personal_info_details (resume_id, detail_name, detail_icon, detail_info) 
                 VALUES (?, ?, ?, ?)",
                [$resumeId, $detailName, $icon, $detailInfo]
            );
        }
    }
    
    $_SESSION['message'] = 'Personal information updated successfully!';
    header("Location: personal_info.php?resume_id=" . $resumeId);
    exit;
}

// Get current personal info
$personal = $db->querySingle("SELECT * FROM personal_info WHERE resume_id = ?", [$resumeId]);
$currentName = $personal['name'] ?? '';

// Get current details
$details = $db->query("SELECT * FROM personal_info_details WHERE resume_id = ?", [$resumeId]);
$currentDetails = [];
foreach ($details as $detail) {
    $key = strtolower($detail['detail_name']);
    $currentDetails[$key] = $detail['detail_info'];
}

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
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($currentDetails['email'] ?? ''); ?>" 
                                   required>
                            <div class="invalid-feedback">
                                Please enter a valid email address
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?php echo htmlspecialchars($currentDetails['phone'] ?? ''); ?>"
                                   pattern="^\+?[\d\s()\-]+$"
                                   required>
                            <div class="invalid-feedback">
                                Please enter a valid phone number
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="linkedin" class="form-label">LinkedIn Profile URL</label>
                            <input type="url" 
                                   class="form-control" 
                                   id="linkedin" 
                                   name="linkedin" 
                                   value="<?php echo htmlspecialchars($currentDetails['linkedin'] ?? ''); ?>"
                                   placeholder="https://linkedin.com/in/yourusername">
                            <div class="form-text">Optional: Add your LinkedIn profile URL</div>
                        </div>

                        <div class="mb-3">
                            <label for="github" class="form-label">GitHub Profile URL</label>
                            <input type="url" 
                                   class="form-control" 
                                   id="github" 
                                   name="github" 
                                   value="<?php echo htmlspecialchars($currentDetails['github'] ?? ''); ?>"
                                   placeholder="https://github.com/yourusername">
                            <div class="form-text">Optional: Add your GitHub profile URL</div>
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
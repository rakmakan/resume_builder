<?php
require_once '../database/db.php';

// Get database instance
$db = ResumeDB::getInstance();

// Get experience ID and resume ID from URL
$expId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$resumeId = isset($_GET['resume_id']) ? (int)$_GET['resume_id'] : null;

if (!$expId || !$resumeId) {
    die("Missing required parameters");
}

// Get resume details
$resume = $db->querySingle("SELECT name FROM resumes WHERE id = ?", [$resumeId]);
$resumeName = $resume ? $resume['name'] : 'Unknown Resume';

// Get experience details
$experience = $db->querySingle(
    "SELECT * FROM experience WHERE id = ? AND resume_id = ?", 
    [$expId, $resumeId]
);

if (!$experience) {
    die("Experience not found");
}

// Get accomplishments
$accomplishments = $db->query(
    "SELECT * FROM job_accomplishments 
     WHERE experience_id = ? AND resume_id = ? 
     ORDER BY display_order", 
    [$expId, $resumeId]
);

// Set page title and description
$pageTitle = 'Edit Work Experience - ' . $resumeName;
$pageDescription = 'Edit job position details';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = $_POST['company'];
    $title = $_POST['title'];
    $dateRange = $_POST['date_range'];
    $location = $_POST['location'];
    $accomplishments = $_POST['accomplishments'];

    // Update experience
    $db->execute(
        "UPDATE experience 
         SET company = ?, title = ?, date_range = ?, location = ? 
         WHERE id = ? AND resume_id = ?",
        [$company, $title, $dateRange, $location, $expId, $resumeId]
    );

    // Delete existing accomplishments
    $db->execute(
        "DELETE FROM job_accomplishments 
         WHERE experience_id = ? AND resume_id = ?", 
        [$expId, $resumeId]
    );

    // Insert new accomplishments
    if (!empty($accomplishments)) {
        $order = 1;
        foreach ($accomplishments as $acc) {
            if (!empty($acc)) {
                $db->insert(
                    "INSERT INTO job_accomplishments 
                     (experience_id, description, display_order, resume_id) 
                     VALUES (?, ?, ?, ?)",
                    [$expId, $acc, $order++, $resumeId]
                );
            }
        }
    }

    // Redirect back to experience page
    header("Location: experience.php?resume_id=" . $resumeId);
    exit;
}

// Include header
require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-briefcase me-2"></i>Edit Job Details
                </h5>
            </div>
            <div class="card-body">
                <form method="post" action="experience_edit.php?id=<?php echo $expId; ?>&resume_id=<?php echo $resumeId; ?>" id="experienceForm">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="title" class="form-label fw-bold">Job Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($experience['title']); ?>" required>
                            <div class="form-text">Your official position or role</div>
                        </div>
                        <div class="col-md-6">
                            <label for="company" class="form-label fw-bold">Company/Organization <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="company" name="company" 
                                   value="<?php echo htmlspecialchars($experience['company']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="location" class="form-label fw-bold">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?php echo htmlspecialchars($experience['location']); ?>">
                            <div class="form-text">City, State, Country (e.g., San Francisco, CA)</div>
                        </div>
                        <div class="col-md-6">
                            <label for="date_range" class="form-label fw-bold">Date Range</label>
                            <input type="text" class="form-control" id="date_range" name="date_range" 
                                   value="<?php echo htmlspecialchars($experience['date_range']); ?>">
                            <div class="form-text">Format: Jan 2020 - Present, or Jan 2019 - Dec 2020</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_visible" name="is_visible" 
                                   <?php echo $experience['is_visible'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_visible">Show this experience on your resume</label>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5 class="card-title text-primary mb-3">
                        <i class="fas fa-list-check me-2"></i>Job Accomplishments
                    </h5>
                    <p class="text-muted mb-4">
                        Add key responsibilities, achievements, and contributions from this position.
                        <br>
                        <small>For maximum impact, start each with a strong action verb (e.g., "Developed", "Led", "Implemented").</small>
                    </p>
                    
                    <div id="accomplishments-container">
                        <?php if (!empty($accomplishments)): ?>
                            <?php foreach ($accomplishments as $accomplishment): ?>
                                <div class="accomplishment-item mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-check-circle text-success"></i>
                                        </span>
                                        <input type="text" class="form-control" name="accomplishments[]" 
                                               value="<?php echo htmlspecialchars($accomplishment['description']); ?>"
                                               placeholder="Describe a key achievement or responsibility">
                                        <button type="button" class="btn btn-outline-secondary" onclick="removeAccomplishment(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="accomplishment-item mb-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-check-circle text-success"></i>
                                    </span>
                                    <input type="text" class="form-control" name="accomplishments[]" 
                                           placeholder="Describe a key achievement or responsibility">
                                    <button type="button" class="btn btn-outline-secondary" onclick="removeAccomplishment(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <button type="button" class="btn btn-success btn-sm" onclick="addAccomplishment()">
                            <i class="fas fa-plus me-1"></i>Add Another Accomplishment
                        </button>
                    </div>
                    
                    <div class="d-flex mt-4 pt-3 border-top">
                        <a href="experience.php?resume_id=<?php echo $resumeId; ?>" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary ms-auto">
                            <i class="fas fa-save me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function addAccomplishment() {
        const container = document.getElementById('accomplishments-container');
        const newItem = document.createElement('div');
        newItem.className = 'accomplishment-item mb-3';
        newItem.innerHTML = `
            <div class="input-group">
                <span class="input-group-text bg-light">
                    <i class="fas fa-check-circle text-success"></i>
                </span>
                <input type="text" class="form-control" name="accomplishments[]" 
                       placeholder="Describe a key achievement or responsibility">
                <button type="button" class="btn btn-outline-secondary" onclick="removeAccomplishment(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(newItem);
    }
    
    function removeAccomplishment(button) {
        const container = document.getElementById('accomplishments-container');
        const item = button.closest('.accomplishment-item');
        
        // Don't remove if it's the last item
        if (container.children.length > 1) {
            container.removeChild(item);
        } else {
            // Clear the value instead
            item.querySelector('input').value = '';
        }
    }
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>

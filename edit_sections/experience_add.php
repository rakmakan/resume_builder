<?php
require_once '../database/db.php';

// Get database instance
$db = ResumeDB::getInstance();

// Get resume ID from URL
$resumeId = isset($_GET['resume_id']) ? (int)$_GET['resume_id'] : null;
if (!$resumeId) {
    $defaultResume = $db->querySingle("SELECT id FROM resumes WHERE is_default = 1");
    $resumeId = $defaultResume ? $defaultResume['id'] : null;
    if (!$resumeId) {
        die("No resume found. Please create a resume first.");
    }
}

// Get resume details
$resume = $db->querySingle("SELECT name FROM resumes WHERE id = ?", [$resumeId]);
$resumeName = $resume ? $resume['name'] : 'Unknown Resume';

// Set page title and description
$pageTitle = 'Add Work Experience - ' . $resumeName;
$pageDescription = 'Add a new job position to your resume';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobTitle = trim($_POST['job_title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $dateRange = trim($_POST['date_range'] ?? '');
    $accomplishments = $_POST['accomplishments'] ?? [];
    $isVisible = isset($_POST['is_visible']) ? 1 : 0;
    
    // Validate input
    if (empty($jobTitle) || empty($company)) {
        $_SESSION['message'] = 'Error: Job title and company are required.';
        header('Location: experience_add.php');
        exit;
    }
    
    // Start transaction to add experience and accomplishments
    $db->beginTransaction();
    
    try {
        // Get highest display order
        $maxOrder = $db->querySingle("SELECT MAX(display_order) as max_order FROM experience WHERE resume_id = ?", [$resumeId]);
        $displayOrder = ($maxOrder && isset($maxOrder['max_order'])) ? $maxOrder['max_order'] + 1 : 1;
        
        // Insert the experience
        $db->execute(
            "INSERT INTO experience (job_title, company, location, date_range, is_visible, display_order, resume_id) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$jobTitle, $company, $location, $dateRange, $isVisible, $displayOrder, $resumeId]
        );
        
        // Get the newly inserted experience ID
        $experienceId = $db->lastInsertId();
        
        // Insert accomplishments
        $order = 1;
        foreach ($accomplishments as $accomplishment) {
            $description = trim($accomplishment);
            if (!empty($description)) {
                $db->execute(
                    "INSERT INTO job_accomplishments (experience_id, description, display_order, resume_id) VALUES (?, ?, ?, ?)",
                    [$experienceId, $description, $order++, $resumeId]
                );
            }
        }
        
        $db->commit();
        $_SESSION['message'] = 'Work experience added successfully!';
        header('Location: experience.php?resume_id=' . $resumeId);
        exit;
    } catch (Exception $e) {
        $db->rollback();
        $_SESSION['message'] = 'Error: Failed to add work experience. ' . $e->getMessage();
    }
}

// Include header
require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-briefcase me-2"></i>Job Details
                </h5>
            </div>
            <div class="card-body">
                <form method="post" action="experience_add.php?resume_id=<?php echo $resumeId; ?>" id="experienceForm">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="job_title" class="form-label fw-bold">Job Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="job_title" name="job_title" required>
                            <div class="form-text">Your official position or role</div>
                        </div>
                        <div class="col-md-6">
                            <label for="company" class="form-label fw-bold">Company/Organization <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="company" name="company" required>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="location" class="form-label fw-bold">Location</label>
                            <input type="text" class="form-control" id="location" name="location">
                            <div class="form-text">City, State, Country (e.g., San Francisco, CA)</div>
                        </div>
                        <div class="col-md-6">
                            <label for="date_range" class="form-label fw-bold">Date Range</label>
                            <input type="text" class="form-control" id="date_range" name="date_range">
                            <div class="form-text">Format: Jan 2020 - Present, or Jan 2019 - Dec 2020</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_visible" name="is_visible" checked>
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
                            <i class="fas fa-save me-1"></i>Save Experience
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-lightbulb me-2 text-warning"></i>Tips for Writing Effective Accomplishments
                </h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li><strong>Use action verbs</strong> - Start each bullet with a strong action verb like "Managed," "Created," or "Implemented."</li>
                    <li><strong>Quantify results</strong> - Include numbers, percentages, or metrics when possible (e.g., "Increased sales by 25%").</li>
                    <li><strong>Show impact</strong> - Focus on achievements and results rather than just listing responsibilities.</li>
                    <li><strong>Be concise</strong> - Keep each accomplishment to one sentence if possible, and avoid technical jargon.</li>
                </ul>
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
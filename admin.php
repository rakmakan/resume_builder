<?php
require_once 'database/db.php';
require_once 'includes/utils.php';
require_once 'edit_sections/common.php';

// Start session (only if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get database instance
$db = ResumeDB::getInstance();

// Get resume ID from URL parameter
$resumeId = getResumeId($db);
$resumeName = getResumeName($db, $resumeId);

// Verify resume exists
$resume = $db->querySingle("SELECT * FROM resumes WHERE id = ?", [$resumeId]);
if (!$resume) {
    header('Location: edit_sections/resumes.php');
    exit;
}

// Set page title and description
$pageTitle = 'Manage Resume: ' . $resumeName;
$pageDescription = 'Manage sections and content for ' . $resumeName;

// Get resume-specific statistics
$resumeStats = $db->getResumeStats($resumeId);

// Count entries in each section for this specific resume
$personalCount = count($db->query("SELECT * FROM personal_info WHERE resume_id = ?", [$resumeId]));
$educationCount = count($db->query("SELECT * FROM education WHERE resume_id = ? AND is_visible = 1", [$resumeId]));
$skillCategoriesCount = count($db->query("SELECT * FROM skill_categories WHERE resume_id = ? AND is_visible = 1", [$resumeId]));
$skillsCount = count($db->query("SELECT * FROM skills WHERE resume_id = ? AND is_visible = 1", [$resumeId]));
$experienceCount = count($db->query("SELECT * FROM experience WHERE resume_id = ? AND is_visible = 1", [$resumeId]));
$projectsCount = count($db->query("SELECT * FROM projects WHERE resume_id = ? AND is_visible = 1", [$resumeId]));

// Get summary for this resume
$summaryExists = $db->querySingle("SELECT id FROM summary WHERE resume_id = ?", [$resumeId]) ? 1 : 0;

// Include header
require_once 'includes/header.php';
?>

<!-- Breadcrumb Navigation -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="edit_sections/resumes.php">All Resumes</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($resumeName); ?></li>
    </ol>
</nav>

<!-- Resume Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-1"><?php echo htmlspecialchars($resumeName); ?></h1>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($resume['description'] ?: 'No description'); ?></p>
            </div>
            <div class="btn-group">
                <a href="index.php?resume_id=<?php echo $resumeId; ?>" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-eye me-1"></i>Preview
                </a>
                <a href="export.php?id=<?php echo $resumeId; ?>" class="btn btn-outline-success">
                    <i class="fas fa-download me-1"></i>Download PDF
                </a>
                <a href="edit_sections/resumes.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to All Resumes
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Overview -->
<?php if ($resumeStats && ($resumeStats['view_count'] > 0 || $resumeStats['download_count'] > 0)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Analytics Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="border-end">
                            <h3 class="text-success"><?php echo $resumeStats['view_count'] ?? 0; ?></h3>
                            <small class="text-muted">Views</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border-end">
                            <h3 class="text-info"><?php echo $resumeStats['download_count'] ?? 0; ?></h3>
                            <small class="text-muted">Downloads</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3 class="text-warning"><?php echo $resumeStats['last_viewed_at'] ? formatDate($resumeStats['last_viewed_at'], 'M j') : 'Never'; ?></h3>
                        <small class="text-muted">Last Viewed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Dashboard Cards -->
<div class="row g-4">

    <!-- Personal Information Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>Personal Information
                </h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="text-center mb-3">
                    <span class="display-3 text-primary"><?php echo $personalCount; ?></span>
                    <p class="lead"><?php echo $personalCount === 1 ? 'Profile' : 'Profiles'; ?></p>
                </div>
                <p class="card-text">Your name, contact information, and other personal details.</p>
                <div class="mt-auto text-center">
                    <a href="edit_sections/personal_info.php?resume_id=<?php echo $resumeId; ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Edit Information
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Professional Summary Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-alt me-2"></i>Professional Summary
                </h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="text-center mb-3">
                    <span class="display-3 text-primary"><?php echo $summaryExists; ?></span>
                    <p class="lead"><?php echo $summaryExists ? 'Complete' : 'Missing'; ?></p>
                </div>
                <p class="card-text">A concise overview of your professional background and key strengths.</p>
                <div class="mt-auto text-center">
                    <a href="edit_sections/summary.php?resume_id=<?php echo $resumeId; ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Edit Summary
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Education Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-graduation-cap me-2"></i>Education
                </h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="text-center mb-3">
                    <span class="display-3 text-primary"><?php echo $educationCount; ?></span>
                    <p class="lead"><?php echo $educationCount === 1 ? 'Entry' : 'Entries'; ?></p>
                </div>
                <p class="card-text">Academic qualifications, degrees, certifications, and training.</p>
                <div class="mt-auto">
                    <div class="d-grid gap-2">
                        <a href="edit_sections/education.php?resume_id=<?php echo $resumeId; ?>" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>Manage Education
                        </a>
                        <a href="edit_sections/education.php?resume_id=<?php echo $resumeId; ?>&action=add" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Add New Qualification
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Skills Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tools me-2"></i>Skills
                </h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="text-center mb-3">
                    <div class="d-flex justify-content-center gap-3">
                        <div class="text-center">
                            <span class="display-4 text-primary"><?php echo $skillCategoriesCount; ?></span>
                            <p>Categories</p>
                        </div>
                        <div class="text-center">
                            <span class="display-4 text-primary"><?php echo $skillsCount; ?></span>
                            <p>Skills</p>
                        </div>
                    </div>
                </div>
                <p class="card-text">Technical, professional, and soft skills organized by category.</p>
                <div class="mt-auto">
                    <div class="d-grid gap-2">
                        <a href="edit_sections/skills.php?resume_id=<?php echo $resumeId; ?>" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>Manage Skills
                        </a>
                        <a href="edit_sections/skills.php?resume_id=<?php echo $resumeId; ?>&action=add" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Add New Skill
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Experience Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-briefcase me-2"></i>Work Experience
                </h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="text-center mb-3">
                    <span class="display-3 text-primary"><?php echo $experienceCount; ?></span>
                    <p class="lead"><?php echo $experienceCount === 1 ? 'Position' : 'Positions'; ?></p>
                </div>
                <p class="card-text">Current and previous job roles, responsibilities, and accomplishments.</p>
                <div class="mt-auto">
                    <div class="d-grid gap-2">
                        <a href="edit_sections/experience.php?resume_id=<?php echo $resumeId; ?>" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>Manage Experience
                        </a>
                        <a href="edit_sections/experience_add.php?resume_id=<?php echo $resumeId; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Add New Position
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-project-diagram me-2"></i>Projects
                </h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="text-center mb-3">
                    <span class="display-3 text-primary"><?php echo $projectsCount; ?></span>
                    <p class="lead"><?php echo $projectsCount === 1 ? 'Project' : 'Projects'; ?></p>
                </div>
                <p class="card-text">Significant projects, portfolio items, and notable achievements.</p>
                <div class="mt-auto">
                    <div class="d-grid gap-2">
                        <a href="edit_sections/projects.php?resume_id=<?php echo $resumeId; ?>" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>Manage Projects
                        </a>
                        <a href="edit_sections/projects.php?resume_id=<?php echo $resumeId; ?>&action=add" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Add New Project
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="d-grid">
                            <a href="index.php?resume_id=<?php echo $resumeId; ?>" target="_blank" class="btn btn-outline-info btn-lg">
                                <i class="fas fa-eye me-2"></i>Preview Resume
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid">
                            <a href="export.php?id=<?php echo $resumeId; ?>" class="btn btn-outline-success btn-lg">
                                <i class="fas fa-download me-2"></i>Download PDF
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid">
                            <a href="edit_sections/resumes.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-list me-2"></i>All Resumes
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid">
                            <a href="edit_sections/resumes.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-plus me-2"></i>New Resume
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>
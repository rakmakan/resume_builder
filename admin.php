<?php
require_once 'database/db.php';

// Set page title and description
$pageTitle = 'Resume Dashboard';
$pageDescription = 'Manage your resume sections and content in one place';

// Get database instance
$db = ResumeDB::getInstance();

// Count entries in each section
$personalCount = count($db->query("SELECT * FROM personal_info"));
$educationCount = count($db->query("SELECT * FROM education WHERE is_visible = 1"));
$skillCategoriesCount = count($db->query("SELECT * FROM skill_categories WHERE is_visible = 1"));
$skillsCount = count($db->query("SELECT * FROM skills WHERE is_visible = 1"));
$experienceCount = count($db->query("SELECT * FROM experience WHERE is_visible = 1"));
$projectsCount = count($db->query("SELECT * FROM projects WHERE is_visible = 1"));

// Include header
require_once 'includes/header.php';
?>

<!-- Dashboard Cards -->
<div class="row g-4">
    <!-- Resume Versions Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-alt me-2"></i>Resume Versions
                </h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="text-center mb-3">
                    <span class="display-3 text-primary"><?php echo count($db->query("SELECT * FROM resumes")); ?></span>
                    <p class="lead">Versions</p>
                </div>
                <p class="card-text">Manage multiple versions of your resume for different job applications.</p>
                <div class="mt-auto text-center">
                    <a href="edit_sections/resumes.php" class="btn btn-primary">
                        <i class="fas fa-tasks me-1"></i>Manage Resumes
                    </a>
                </div>
            </div>
        </div>
    </div>

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
                    <a href="edit_sections/personal_info.php" class="btn btn-primary">
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
                    <span class="display-3 text-primary">1</span>
                    <p class="lead">Section</p>
                </div>
                <p class="card-text">A concise overview of your professional background and key strengths.</p>
                <div class="mt-auto text-center">
                    <a href="edit_sections/summary.php" class="btn btn-primary">
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
                        <a href="edit_sections/education.php" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>Manage Education
                        </a>
                        <a href="edit_sections/education_add.php" class="btn btn-outline-primary">
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
                        <a href="edit_sections/skills.php" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>Manage Skills
                        </a>
                        <a href="edit_sections/skills_add.php" class="btn btn-outline-primary">
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
                        <a href="edit_sections/experience.php" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>Manage Experience
                        </a>
                        <a href="edit_sections/experience_add.php" class="btn btn-outline-primary">
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
                        <a href="edit_sections/projects.php" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>Manage Projects
                        </a>
                        <a href="edit_sections/projects_add.php" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Add New Project
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-grid">
                            <a href="index.php" target="_blank" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-eye me-2"></i>Preview Resume
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-grid">
                            <a href="index.php?download=true" class="btn btn-outline-success btn-lg">
                                <i class="fas fa-download me-2"></i>Download PDF
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-grid">
                            <a href="#" class="btn btn-outline-dark btn-lg">
                                <i class="fas fa-cog me-2"></i>Settings
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
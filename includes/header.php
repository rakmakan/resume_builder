<?php
// Include utilities
require_once dirname(__FILE__) . '/utils.php';

// Session handling (only start if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for any flash messages
$flashMessage = getFlashMessage();

// Get current resume name if editing a specific resume
$currentResumeName = '';
if (isset($_GET['resume_id']) && str_contains($_SERVER['PHP_SELF'], 'edit_sections')) {
    require_once dirname(__FILE__) . '/../database/db.php';
    $db = ResumeDB::getInstance();
    $resume = $db->querySingle("SELECT name FROM resumes WHERE id = ?", [$_GET['resume_id']]);
    if ($resume) {
        $currentResumeName = $resume['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Resume Builder'; ?></title>
    <meta name="description" content="<?php echo $pageDescription ?? 'Build and manage your professional resume'; ?>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? '../style.css' : 'style.css'; ?>">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? '../admin.php' : 'admin.php'; ?>">
                <i class="fas fa-file-alt me-2"></i>Resume Builder
            </a>
            <?php if ($currentResumeName): ?>
            <span class="navbar-text text-light ms-3">
                <i class="fas fa-edit me-1"></i>Editing: <?php echo htmlspecialchars($currentResumeName); ?>
            </span>
            <?php endif; ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? '../admin.php' : 'admin.php'; ?>">
                            <i class="fas fa-columns me-1"></i>Dashboard
                        </a>
                    </li>
                    <?php if (isset($_GET['resume_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-edit me-1"></i>Edit Sections
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? 'personal_info.php?resume_id=' . $_GET['resume_id'] : 'edit_sections/personal_info.php?resume_id=' . $_GET['resume_id']; ?>">
                                    <i class="fas fa-user me-2"></i>Personal Info
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? 'summary.php?resume_id=' . $_GET['resume_id'] : 'edit_sections/summary.php?resume_id=' . $_GET['resume_id']; ?>">
                                    <i class="fas fa-file-alt me-2"></i>Summary
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? 'education.php?resume_id=' . $_GET['resume_id'] : 'edit_sections/education.php?resume_id=' . $_GET['resume_id']; ?>">
                                    <i class="fas fa-graduation-cap me-2"></i>Education
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? 'experience.php?resume_id=' . $_GET['resume_id'] : 'edit_sections/experience.php?resume_id=' . $_GET['resume_id']; ?>">
                                    <i class="fas fa-briefcase me-2"></i>Experience
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? 'skills.php?resume_id=' . $_GET['resume_id'] : 'edit_sections/skills.php?resume_id=' . $_GET['resume_id']; ?>">
                                    <i class="fas fa-tools me-2"></i>Skills
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? 'projects.php?resume_id=' . $_GET['resume_id'] : 'edit_sections/projects.php?resume_id=' . $_GET['resume_id']; ?>">
                                    <i class="fas fa-project-diagram me-2"></i>Projects
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_GET['resume_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? '../index.php?resume_id=' . $_GET['resume_id'] : 'index.php?resume_id=' . $_GET['resume_id']; ?>">
                            <i class="fas fa-eye me-1"></i>Preview Resume
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-sm btn-outline-light ms-2" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? '../index.php?resume_id=' . $_GET['resume_id'] . '&download=true' : 'index.php?resume_id=' . $_GET['resume_id'] . '&download=true'; ?>">
                            <i class="fas fa-download me-1"></i>Download PDF
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? '../index.php' : 'index.php'; ?>">
                            <i class="fas fa-eye me-1"></i>Preview Resume
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-sm btn-outline-light ms-2" href="<?php echo str_contains($_SERVER['PHP_SELF'], 'edit_sections') ? '../index.php?download=true' : 'index.php?download=true'; ?>">
                            <i class="fas fa-download me-1"></i>Download PDF
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Container -->
    <div class="container">
        <!-- Flash Message -->
        <?php if ($flashMessage): ?>
        <div class="alert alert-<?php echo $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type']; ?> alert-dismissible fade show mb-4" role="alert">
            <?php echo htmlspecialchars($flashMessage['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <!-- Page Header -->
        <?php if (isset($pageTitle)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-2"><?php echo $pageTitle; ?></h1>
                <?php if (isset($pageDescription)): ?>
                <p class="text-muted"><?php echo $pageDescription; ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
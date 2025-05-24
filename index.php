<?php
require_once 'database/db.php';

// Only redirect to resumes.php if no resume_id is provided
if (!isset($_GET['resume_id'])) {
    header('Location: edit_sections/resumes.php');
    exit;
}

// Get database instance
$db = ResumeDB::getInstance();

// Handle download request
if (isset($_GET['download']) && $_GET['download'] === 'true') {
    require_once 'export.php';
    exit;
}

// Set page title and description 
$pageTitle = 'Resume Preview';
$pageDescription = 'View your professional resume';

// Get resume ID from URL, fallback to default resume
$resumeId = isset($_GET['resume_id']) ? (int)$_GET['resume_id'] : null;
if (!$resumeId) {
    $defaultResume = $db->querySingle("SELECT id FROM resumes WHERE is_default = 1");
    $resumeId = $defaultResume['id'] ?? null;
    if (!$resumeId) {
        die("No resume found. Please create a resume first.");
    }
}

// Debug output
error_log("Resume ID: " . $resumeId);

// Get resume data
$personal = $db->querySingle(
    "SELECT * FROM personal_info WHERE resume_id = ? ORDER BY updated_at DESC LIMIT 1", 
    [$resumeId]
);
$summary = $db->querySingle("SELECT * FROM summary WHERE resume_id = ?", [$resumeId]);
$experiences = $db->query(
    "SELECT * FROM experience WHERE resume_id = ? AND is_visible = 1 ORDER BY display_order, date_range DESC",
    [$resumeId]
);
$education = $db->query(
    "SELECT * FROM education WHERE resume_id = ? AND is_visible = 1 ORDER BY display_order, date_range DESC",
    [$resumeId]
);
$skillCategories = $db->query(
    "SELECT * FROM skill_categories WHERE resume_id = ? AND is_visible = 1 ORDER BY display_order",
    [$resumeId]
);
$projects = $db->query(
    "SELECT * FROM projects WHERE resume_id = ? AND is_visible = 1 ORDER BY display_order",
    [$resumeId]
);

// Debug output
error_log("Personal Info: " . print_r($personal, true));
error_log("Experiences Count: " . count($experiences));

// Get accomplishments for each experience
foreach ($experiences as &$exp) {
    $exp['accomplishments'] = $db->query(
        "SELECT * FROM job_accomplishments WHERE experience_id = ? AND resume_id = ? ORDER BY display_order", 
        [$exp['id'], $resumeId]
    );
}

// Get skills for each category
foreach ($skillCategories as &$category) {
    $category['skills'] = $db->query(
        "SELECT * FROM skills WHERE category_id = ? AND resume_id = ? AND is_visible = 1 ORDER BY display_order", 
        [$category['id'], $resumeId]
    );
}

// Parse contact info (stored as pipe-separated values)
$contactInfo = [];
if ($personal && !empty($personal['contact_info'])) {
    $contacts = explode('|', $personal['contact_info']);
    foreach ($contacts as $contact) {
        $contact = trim($contact);
        if (strpos($contact, '@') !== false) {
            $contactInfo['email'] = $contact;
        } else if (preg_match('/^\+?[\d\s()\-]+$/', $contact)) { // Fixed regex pattern
            $contactInfo['phone'] = $contact;
        } else if (strpos($contact, 'github.com') !== false) {
            $contactInfo['github'] = $contact;
        } else if (strpos($contact, 'linkedin.com') !== false) {
            $contactInfo['linkedin'] = $contact;
        }
    }
}

// Set resume name
$resume = $db->querySingle("SELECT name FROM resumes WHERE id = ?", [$resumeId]);
$resumeName = $resume ? $resume['name'] : 'Unknown Resume';

// Debug output
error_log("Resume Name: " . $resumeName);
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Minimal Navigation for Preview Page -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-0">
        <div class="container">
            <a class="navbar-brand" href="admin.php">
                <i class="fas fa-file-alt me-2"></i>Resume Builder
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin.php">
                        <i class="fas fa-columns me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-sm btn-outline-light ms-2" href="index.php?download=true&resume_id=<?php echo $resumeId; ?>">
                        <i class="fas fa-download me-1"></i>Download PDF
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Preview Banner -->
    <div class="bg-light py-2 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-primary me-2">Preview Mode</span>
                <small class="text-muted">Viewing: <?php echo htmlspecialchars($resumeName); ?></small>
            </div>
            <div>
                <a href="admin.php" class="btn btn-sm btn-primary me-2">
                    <i class="fas fa-edit me-1"></i>Edit Resume
                </a>
                <a href="index.php?download=true&resume_id=<?php echo $resumeId; ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-download me-1"></i>Download PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Resume Content -->
    <div class="container-fluid mt-3" style="max-width: 1000px;">
        <?php if (empty($personal)): // Only check for personal info ?>
            <div class="alert alert-info text-center" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?php if ($resumeId): ?>
                    Your resume needs personal information. <a href="edit_sections/personal_info.php?resume_id=<?php echo $resumeId; ?>" class="alert-link">Add your personal info</a> to get started.
                <?php else: ?>
                    Your resume is empty. <a href="admin.php" class="alert-link">Go to the dashboard</a> to add your information.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Resume content starts here -->
            <div class="resume-container">
                <!-- Header Section -->
                <div class="header-section resume-section">
                    <div class="header-main">
                        <div class="header-left">
                            <h1><?php echo htmlspecialchars($personal['name'] ?? ''); ?></h1>
                            <?php if (!empty($personal['headline'])): ?>
                                <div class="professional-headline">
                                    <?php echo htmlspecialchars($personal['headline']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="header-right">
                            <div class="contact-info">
                                <?php if (!empty($contactInfo['phone'])): ?>
                                    <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contactInfo['phone']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($contactInfo['email'])): ?>
                                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contactInfo['email']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($portfolioLinks)): ?>
                        <div class="profile-links">
                            <?php foreach ($portfolioLinks as $link): 
                                $icon = match($link['platform']) {
                                    'LinkedIn' => 'linkedin',
                                    'GitHub' => 'github',
                                    'Portfolio' => 'globe',
                                    default => 'link'
                                };
                            ?>
                                <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="fab fa-<?php echo $icon; ?>"></i>
                                    <?php echo htmlspecialchars($link['platform']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Summary Section -->
                <?php if (!empty($summary['content'])): ?>
                <div class="summary-section resume-section">
                    <h2>Professional Summary</h2>
                    <div class="section-content">
                        <p><?php echo nl2br(htmlspecialchars($summary['content'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Experience Section -->
                <?php if (count($experiences) > 0): ?>
                <div class="experience-section resume-section">
                    <h2>Professional Experience</h2>
                    <div class="section-content">
                        <?php foreach ($experiences as $exp): ?>
                        <div class="experience-entry mb-4">
                            <div class="experience-row-1">
                                <div class="job-company">
                                    <span class="job-title"><?php echo htmlspecialchars($exp['job_title']); ?></span>
                                    <span class="company-name"><?php echo htmlspecialchars($exp['company']); ?></span>
                                </div>
                                <div class="experience-date">
                                    <?php if (!empty($exp['date_range'])): ?>
                                        <?php echo htmlspecialchars($exp['date_range']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($exp['location'])): ?>
                            <div class="experience-row-2">
                                <div>
                                    <span class="company-location"><?php echo htmlspecialchars($exp['location']); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($exp['accomplishments'])): ?>
                                <ul class="job-accomplishments">
                                    <?php foreach ($exp['accomplishments'] as $accomplishment): ?>
                                        <li><?php echo htmlspecialchars($accomplishment['description']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Education Section -->
                <?php if (count($education) > 0): ?>
                <div class="education-section resume-section">
                    <h2>Education</h2>
                    <div class="section-content">
                        <?php foreach ($education as $edu): ?>
                            <div class="education-entry">
                                <div class="education-row-1">
                                    <div class="degree-institution">
                                        <span class="degree"><?php echo htmlspecialchars($edu['degree']); ?></span>
                                        <span class="institution"><?php echo htmlspecialchars($edu['institution']); ?></span>
                                    </div>
                                    <div class="education-date">
                                        <?php if (!empty($edu['date_range'])): ?>
                                            <?php echo htmlspecialchars($edu['date_range']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="education-row-2">
                                    <div>
                                        <?php if (!empty($edu['location'])): ?>
                                            <span class="location"><?php echo htmlspecialchars($edu['location']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($edu['description'])): ?>
                                        <div class="education-description">
                                            <?php echo htmlspecialchars($edu['description']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($edu['achievements'])): ?>
                                    <div class="education-achievements">
                                        <?php echo htmlspecialchars($edu['achievements']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Skills Section -->
                <?php if (count($skillCategories) > 0): ?>
                <div class="skills-section resume-section">
                    <h2>Skills</h2>
                    <div class="section-content skills-container">
                        <?php foreach ($skillCategories as $category): ?>
                            <?php if (!empty($category['skills'])): ?>
                                <p>
                                    <span class="skill-category"><?php echo htmlspecialchars($category['name']); ?>:</span>
                                    <span class="skill-list">
                                        <?php 
                                        $skillNames = array_map(function($skill) {
                                            return htmlspecialchars($skill['name']);
                                        }, $category['skills']);
                                        echo implode(', ', $skillNames);
                                        ?>
                                    </span>
                                </p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Projects Section -->
                <?php if (count($projects) > 0): ?>
                <div class="resume-section">
                    <h2 class="resume-section-title">Projects</h2>
                    <div class="resume-section-content">
                        <?php foreach ($projects as $project): ?>
                            <div class="resume-item">
                                <div class="resume-item-header">
                                    <h3 class="resume-item-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                                    <?php if (!empty($project['details'])): ?>
                                        <p class="resume-item-subtitle"><?php echo htmlspecialchars($project['details']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($project['description'])): ?>
                                    <p class="resume-item-description"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

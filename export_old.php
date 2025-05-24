<?php
require_once('vendor/autoload.php');
require_once 'database/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Get database instance
$db = ResumeDB::getInstance();

// Get resume data
$personal = $db->querySingle("SELECT * FROM personal_info LIMIT 1");

// Get contact info
$contactInfo = [];
if ($personal) {
    $contacts = $db->query("SELECT * FROM contact_info WHERE personal_info_id = ? ORDER BY type", [$personal['id']]);
    foreach ($contacts as $contact) {
        $contactInfo[$contact['type']] = $contact['value'];
    }
}

// Get portfolio links
$portfolioLinks = [];
if ($personal) {
    $portfolioLinks = $db->query(
        "SELECT * FROM portfolio_links WHERE personal_info_id = ? ORDER BY display_order",
        [$personal['id']]
    );
}

$summary = $db->querySingle("SELECT * FROM summary LIMIT 1");
$experiences = $db->query("SELECT * FROM experience WHERE is_visible = 1 ORDER BY display_order, date_range DESC");
$education = $db->query("SELECT * FROM education WHERE is_visible = 1 ORDER BY display_order, date_range DESC");
$skillCategories = $db->query("SELECT * FROM skill_categories WHERE is_visible = 1 ORDER BY display_order");
$projects = $db->query("SELECT * FROM projects WHERE is_visible = 1 ORDER BY display_order");

// Get accomplishments for each experience
foreach ($experiences as &$exp) {
    $exp['accomplishments'] = $db->query(
        "SELECT * FROM job_accomplishments WHERE experience_id = ? ORDER BY display_order", 
        [$exp['id']]
    );
}

// Get skills for each category
foreach ($skillCategories as &$category) {
    $category['skills'] = $db->query(
        "SELECT * FROM skills WHERE category_id = ? AND is_visible = 1 ORDER BY display_order", 
        [$category['id']]
    );
}

// CSS for PDF export
$css = '
    /* Base styles */
    * {
        font-family: "Roboto", "DejaVu Sans", "Helvetica", Arial, sans-serif;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        line-height: 1.5;
        color: #333;
        margin: 0;
        padding: 20px;
        font-size: 14px;
    }

    p, span, li {
        font-size: 14px;
    }

    .resume-section {
        margin-bottom: 25px;
    }

    .section-content {
        margin-left: 15px;
        font-size: 14px;
    }

    h2 {
        font-size: 16px;
        color: #2c3e50;
        margin-bottom: 15px;
        border-bottom: 1px solid #333;
        padding-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: bold;
    }

    h1 {
        font-size: 24px;
        margin-bottom: 10px;
    }

    .professional-headline {
        font-size: 16px;
        color: #666;
        margin-bottom: 5px;
        font-style: italic;
    }

    /* Common text elements */
    .summary-section p,
    .experience-description,
    .education-description,
    .skill-list,
    .project-description {
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 5px;
    }

    /* Header Section */
    .header-section {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #ddd;
    }

    .header-main {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .header-left {
        text-align: left;
    }

    .header-right {
        text-align: right;
    }

    .header-section h1 {
        font-size: 18px;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .professional-headline {
        color: #666;
        font-size: 14px;
        margin-bottom: 0.5rem;
    }

    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 0;
        text-align: right;
    }

    .contact-info span {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        color: #555;
        font-size: 14px;
    }

    .contact-info i {
        margin-right: 0.5rem;
        color: #666;
        width: 14px;
        text-align: center;
    }

    .contact-info a {
        color: #0066cc;
        text-decoration: none;
    }

    .profile-links {
        text-align: center;
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px solid #eee;
    }

    .profile-links a {
        color: #2c3e50;
        text-decoration: none;
        margin: 0 12px;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
    }

    .profile-links i {
        margin-right: 4px;
        font-size: 14px;
    }

    .profile-links a:hover {
        color: #0066cc;
    }

    /* Education Section */
    .education-entry {
        margin-bottom: 18px;
        page-break-inside: avoid;
    }

    .education-row-1, .education-row-2 {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 5px;
        line-height: 1.5;
    }

    .degree-institution {
        display: flex;
        gap: 12px;
        align-items: baseline;
        flex: 1;
    }

    .degree {
        font-weight: bold;
        color: #000;
        font-size: 14px;
    }

    .institution {
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .education-date {
        color: #666;
        font-size: 14px;
        white-space: nowrap;
    }

    .location, .minor {
        color: #666;
        font-size: 14px;
        margin-right: 16px;
    }

    .education-gpa {
        color: #666;
        font-size: 14px;
    }

    .education-achievements {
        margin-top: 5px;
        padding-left: 4px;
        color: #444;
        font-size: 14px;
        line-height: 1.5;
    }

    /* Experience Section */
    .experience-entry {
        margin-bottom: 18px;
        page-break-inside: avoid;
    }

    .job-company {
        display: flex;
        gap: 12px;
        align-items: baseline;
        flex: 1;
    }

    .job-title {
        font-weight: bold;
        color: #000;
        font-size: 14px;
    }

    .company-name {
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .company-location {
        color: #666;
        font-size: 14px;
    }

    .experience-date {
        color: #666;
        font-size: 14px;
        white-space: nowrap;
    }

    .job-accomplishments {
        margin-top: 5px;
        padding-left: 20px;
        list-style-type: disc;
        color: #444;
        font-size: 14px;
        line-height: 1.5;
    }

    .job-accomplishments li {
        margin-bottom: 3px;
    }

    /* Skills & Projects */
    .skill-category {
        font-weight: bold;
        font-size: 14px;
    }

    .skill-list {
        font-size: 14px;
    }

    .project-title {
        font-weight: bold;
        font-size: 14px;
    }

    .project-description {
        font-size: 14px;
        line-height: 1.5;
    }';

// Generate resume HTML
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style><?php echo $css; ?></style>
</head>
<body>
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
                    <a href="<?php echo htmlspecialchars($link['url']); ?>">
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
            <div class="experience-entry">
                <div class="job-header">
                    <p class="job-title"><?php echo htmlspecialchars($exp['job_title']); ?></p>
                    <p class="company-info">
                        <?php echo htmlspecialchars($exp['company']); ?>
                        <?php if (!empty($exp['location'])): ?>
                            • <?php echo htmlspecialchars($exp['location']); ?>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($exp['date_range'])): ?>
                        <p class="date-range"><?php echo htmlspecialchars($exp['date_range']); ?></p>
                    <?php endif; ?>
                </div>
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
                            <span class="degree"><?php echo htmlspecialchars($edu['degree_type']); ?> in <?php echo htmlspecialchars($edu['major']); ?></span>
                            <span class="institution"><?php echo htmlspecialchars($edu['institution']); ?></span>
                        </div>
                        <div class="education-date">
                            <?php if (!empty($edu['start_date'])): ?>
                                <?php echo htmlspecialchars($edu['start_date']); ?> - 
                                <?php echo $edu['currently_enrolled'] ? 'Present' : htmlspecialchars($edu['end_date']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="education-row-2">
                        <div>
                            <?php if (!empty($edu['location'])): ?>
                                <span class="location"><?php echo htmlspecialchars($edu['location']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($edu['minor'])): ?>
                                <span class="minor">Minor: <?php echo htmlspecialchars($edu['minor']); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($edu['gpa'])): ?>
                            <span class="education-gpa">GPA: <?php echo htmlspecialchars($edu['gpa']); ?></span>
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
    <div class="projects-section resume-section">
        <h2>Projects</h2>
        <div class="section-content">
            <?php foreach ($projects as $project): ?>
                <div class="project-entry">
                    <p><span class="project-title"><?php echo htmlspecialchars($project['title']); ?></span></p>
                    <?php if (!empty($project['description'])): ?>
                        <div class="project-description">
                            <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<!-- Resume content ends here -->

<?php
$html = ob_get_clean();

// Configure Dompdf options
$options = new Options();
$options->setIsRemoteEnabled(true);
$options->setIsHtml5ParserEnabled(true);
$options->setIsFontSubsettingEnabled(true);

// Create new Dompdf instance
$dompdf = new Dompdf($options);

// Add custom styles specific for PDF generation - using the older, preferred design
$styles = '
<style>
    /* Base styles */
    * {
        font-family: "Roboto", "DejaVu Sans", "Helvetica", Arial, sans-serif;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        color: #333;
        line-height: 1.3;
    }
    
    /* Resume Structure */
    .resume-section {
        margin-bottom: 15px;
        clear: both;
    }
    
    .section-content {
        padding-left: 4px;
    }
    
    /* Section headers */
    .resume-section h2 {
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #333;
        padding-bottom: 4px;
        margin-bottom: 8px;
    }
    
    /* Header Section - Use default styles from above */
    
    /* Summary Section */
    .summary-section p {
        font-size: 12px;
        text-align: justify;
        margin-bottom: 4px;
        line-height: 1.4;
    }
    
    /* Education Entries */
    .education-entry {
        margin-bottom: 18px;
        page-break-inside: avoid;
    }
    
    .education-row-1, .education-row-2 {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 5px;
        line-height: 1.5;
    }
    
    .degree-institution {
        display: flex;
        gap: 12px;
        align-items: baseline;
        flex: 1;
    }
    
    .degree {
        font-weight: bold;
        color: #000;
        font-size: 13px;
    }
    
    .institution {
        font-weight: 600;
        color: #333;
        font-size: 13px;
    }
    
    .education-date {
        color: #666;
        font-size: 12px;
        white-space: nowrap;
        margin-left: 16px;
    }
    
    .location, .minor {
        color: #666;
        margin-right: 16px;
        font-size: 12px;
    }
    
    .education-gpa {
        color: #666;
        font-size: 12px;
        white-space: nowrap;
    }
    
    .education-achievements {
        margin-top: 5px;
        padding-left: 4px;
        color: #444;
        font-size: 12px;
        line-height: 1.5;
    }
    
    /* Skills List */
    .skills-container p {
        font-size: 12px;
        margin-bottom: 4px;
        line-height: 1.4;
    }
    
    .skill-category {
        font-weight: bold;
        display: inline-block;
        min-width: 100px;
        vertical-align: top;
    }
    
    .skill-list {
        display: inline-block;
        vertical-align: top;
    }
    
    /* Experience Section */
    .experience-entry {
        margin-bottom: 18px;
        page-break-inside: avoid;
    }
    
    .experience-row-1, .experience-row-2 {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 5px;
        line-height: 1.5;
    }
    
    .job-company {
        display: flex;
        gap: 12px;
        align-items: baseline;
        flex: 1;
    }
    
    .job-title {
        font-weight: bold;
        color: #000;
        font-size: 13px;
    }
    
    .company-name {
        font-weight: 600;
        color: #333;
        font-size: 13px;
    }
    
    .experience-date {
        color: #666;
        font-size: 12px;
        white-space: nowrap;
        margin-left: 16px;
    }
    
    .company-location {
        color: #666;
        font-size: 12px;
    }
    
    .job-accomplishments {
        margin-top: 5px;
        padding-left: 20px;
        list-style-type: disc;
        color: #444;
        font-size: 12px;
        line-height: 1.5;
    }
    
    .job-accomplishments li {
        margin-bottom: 3px;
    }
    
    /* Projects Section */
    .project-entry {
        margin-bottom: 10px;
    }
    
    .project-entry p {
        font-size: 12px;
        margin-bottom: 4px;
        line-height: 1.4;
    }
    
    .project-title {
        font-weight: bold;
    }
    
    .project-description {
        padding-left: 4px;
    }
</style>
';

// Create HTML content for PDF
$finalHtml = '<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    ' . $styles . '
</head>
<body style="margin: 30px 40px;">
    ' . $html . '
</body>
</html>';

// Load HTML content
$dompdf->loadHtml($finalHtml);

// Set paper size to A4 and orientation to portrait
$dompdf->setPaper('A4', 'portrait');

// Render the PDF
$dompdf->render();

// Output the PDF for download
$dompdf->stream('Rakshit_Resume.pdf', [
    'Attachment' => true // Set to true to download, false to display in browser
]);

exit;
?>
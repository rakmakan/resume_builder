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

// Generate resume HTML
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        /* Base styles */
        * {
            font-family: "DejaVu Sans", "Helvetica", Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }

        /* Resume Container */
        .resume-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0;
            background: white;
        }

        /* Resume Structure */
        .resume-section {
            margin-bottom: 12px;
            clear: both;
        }

        .section-content {
            padding-left: 2px;
        }

        .resume-section h2 {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #333;
            padding-bottom: 2px;
            margin-bottom: 6px;
        }

        /* Header Section */
        .header-section {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #ddd;
        }

        .header-main {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
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

        /* Summary Section */
        .summary-section p {
            font-size: 12px;
            text-align: justify;
            margin-bottom: 4px;
            line-height: 1.4;
        }

        /* Education Entries */
        .education-entry {
            margin-bottom: 8px;
        }

        .education-row-1, .education-row-2 {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 3px;
            line-height: 1.4;
        }

        .degree-institution {
            display: flex;
            gap: 12px;
            align-items: baseline;
        }

        .degree {
            font-weight: bold;
            color: #000;
            font-size: 12px;
        }

        .institution {
            font-weight: 600;
            color: #333;
            font-size: 12px;
        }

        .education-date {
            color: #666;
            font-size: 12px;
            white-space: nowrap;
        }

        .location, .minor {
            color: #666;
            margin-right: 16px;
            font-size: 12px;
        }

        .education-gpa {
            color: #666;
            font-size: 12px;
        }

        .education-achievements {
            margin-top: 3px;
            padding-left: 4px;
            color: #444;
            font-size: 12px;
            line-height: 1.4;
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
            margin-bottom: 8px;
        }

        .experience-row-1, .experience-row-2 {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 3px;
            line-height: 1.4;
        }

        .job-company {
            display: flex;
            gap: 12px;
            align-items: baseline;
        }

        .job-title {
            font-weight: bold;
            color: #000;
            font-size: 12px;
        }

        .company-name {
            font-weight: 600;
            color: #333;
            font-size: 12px;
        }

        .experience-date {
            color: #666;
            font-size: 12px;
            white-space: nowrap;
        }

        .company-location {
            color: #666;
            font-size: 12px;
        }

        .job-accomplishments {
            list-style-type: disc;
            margin-left: 16px;
            margin-top: 2px;
            margin-bottom: 4px;
        }

        .job-accomplishments li {
            font-size: 12px;
            margin-bottom: 2px;
            line-height: 1.3;
            padding-left: 2px;
        }

        /* Projects Section */
        .project-entry {
            margin-bottom: 8px;
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
                            <span>☎ <?php echo htmlspecialchars($contactInfo['phone']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($contactInfo['email'])): ?>
                            <span>✉ <?php echo htmlspecialchars($contactInfo['email']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if (!empty($portfolioLinks)): ?>
                <div class="profile-links">
                    <?php foreach ($portfolioLinks as $link): ?>
                        <a href="<?php echo htmlspecialchars($link['url']); ?>">
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
</body>
</html>

<?php
$html = ob_get_clean();

// Configure Dompdf options
$options = new Options();
$options->setIsRemoteEnabled(true);
$options->setIsHtml5ParserEnabled(true);
$options->setIsFontSubsettingEnabled(true);

// Create new Dompdf instance
$dompdf = new Dompdf($options);

// Load HTML content
$dompdf->loadHtml($html);

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

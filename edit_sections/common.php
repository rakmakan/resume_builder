<?php
// Common functions for edit sections
session_start();

// Function to load HTML content from index.php
function loadIndexHtml() {
    $indexContent = file_get_contents('../index.php');
    return $indexContent;
}

// Function to extract a specific section from the HTML content
function extractSection($html, $sectionClass) {
    $dom = new DOMDocument();
    // Load HTML and suppress warnings about HTML5 tags
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    $section = $xpath->query("//section[contains(@class, '$sectionClass')]");
    
    if ($section->length > 0) {
        // Save the content of the section as HTML
        $sectionNode = $section->item(0);
        $content = $dom->saveHTML($sectionNode);
        return $content;
    }
    
    return null;
}

// Function to update a section in the HTML content
function updateSection($newSectionHtml, $sectionClass, $sectionTitle = null) {
    $indexPath = '../index.php';
    $indexContent = file_get_contents($indexPath);
    
    // Create DOMDocument for the original index page
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($indexContent);
    libxml_clear_errors();
    
    // Create DOMDocument for the new section
    $newDom = new DOMDocument();
    libxml_use_internal_errors(true);
    $newDom->loadHTML($newSectionHtml);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    
    // If we have a section title, use it to find the specific section
    if ($sectionTitle) {
        $section = $xpath->query("//div[contains(@class, '$sectionClass')][.//h2[contains(text(), '$sectionTitle')]]")->item(0);
    } else {
        // Fall back to the old behavior
        $section = $xpath->query("//div[contains(@class, '$sectionClass')]")->item(0);
        
        // If that doesn't work, try the section tag
        if (!$section) {
            $section = $xpath->query("//section[contains(@class, '$sectionClass')]")->item(0);
        }
    }
    
    if ($section) {
        // Get the new section node - could be either a div or section tag
        $newSection = $newDom->getElementsByTagName('div')->item(0);
        if (!$newSection) {
            $newSection = $newDom->getElementsByTagName('section')->item(0);
        }
        
        if ($newSection) {
            // Import the new section into the original document
            $newSectionNode = $dom->importNode($newSection, true);
            // Replace the old section with the new one
            $section->parentNode->replaceChild($newSectionNode, $section);
            
            // Save the updated HTML back to the file
            $updatedHtml = $dom->saveHTML();
            file_put_contents($indexPath, $updatedHtml);
            
            // Set success message
            $_SESSION['success_message'] = "Section updated successfully!";
            return true;
        }
    }
    
    return false;
}

// Get resume ID from URL, fallback to default resume
function getResumeId($db) {
    $resumeId = isset($_GET['resume_id']) ? (int)$_GET['resume_id'] : null;
    if (!$resumeId) {
        $defaultResume = $db->querySingle("SELECT id FROM resumes WHERE is_default = 1");
        $resumeId = $defaultResume ? $defaultResume['id'] : null;
        if (!$resumeId) {
            die("No resume found. Please create a resume first.");
        }
    }
    return $resumeId;
}

// Get resume name
function getResumeName($db, $resumeId) {
    $resume = $db->querySingle("SELECT name FROM resumes WHERE id = ?", [$resumeId]);
    return $resume ? $resume['name'] : 'Unknown Resume';
}

// Display section navigation
function displaySectionNav($resumeId, $currentSection) {
    $sections = [
        'personal_info' => ['name' => 'Personal Info', 'icon' => 'user'],
        'summary' => ['name' => 'Summary', 'icon' => 'file-alt'],
        'education' => ['name' => 'Education', 'icon' => 'graduation-cap'],
        'experience' => ['name' => 'Experience', 'icon' => 'briefcase'],
        'skills' => ['name' => 'Skills', 'icon' => 'tools'],
        'projects' => ['name' => 'Projects', 'icon' => 'project-diagram']
    ];
    
    echo '<div class="list-group mb-4">';
    foreach ($sections as $section => $info) {
        $activeClass = ($section === $currentSection) ? 'active' : '';
        echo sprintf(
            '<a href="%s.php?resume_id=%d" class="list-group-item list-group-item-action %s">
                <i class="fas fa-%s me-2"></i>%s
            </a>',
            $section,
            $resumeId,
            $activeClass,
            $info['icon'],
            $info['name']
        );
    }
    echo '</div>';
}

// Display breadcrumb navigation
function displayBreadcrumbs($resumeName, $sectionName, $resumeId = null) {
    echo '<nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="resumes.php">All Resumes</a></li>';
    if ($resumeId) {
        echo '<li class="breadcrumb-item"><a href="../admin.php?resume_id=' . $resumeId . '">' . htmlspecialchars($resumeName) . '</a></li>';
    } else {
        echo '<li class="breadcrumb-item">' . htmlspecialchars($resumeName) . '</li>';
    }
    echo '<li class="breadcrumb-item active">' . htmlspecialchars($sectionName) . '</li>
        </ol>
    </nav>';
}

// Function to display common page header
function displayHeader($title) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title; ?> | Resume Builder</title>
        <link rel="stylesheet" href="../style.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
        <style>
            .edit-container {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }
            
            .edit-header {
                text-align: center;
                margin-bottom: 30px;
            }
            
            .edit-header h1 {
                font-size: 28px;
                margin-bottom: 10px;
            }
            
            .edit-form {
                background: white;
                padding: 30px;
                border-radius: 4px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
            }
            
            .form-control {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-family: 'Roboto', sans-serif;
                font-size: 14px;
            }
            
            textarea.form-control {
                min-height: 100px;
                resize: vertical;
            }
            
            .btn {
                display: inline-block;
                background-color: #2d7ff9;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                font-weight: 500;
            }
            
            .btn:hover {
                background-color: #1a6fe0;
            }
            
            .footer-links {
                text-align: center;
                margin-top: 30px;
            }
            
            .footer-links a {
                margin: 0 15px;
                color: #2d7ff9;
                text-decoration: none;
            }
            
            .footer-links a:hover {
                text-decoration: underline;
            }
            
            .dynamic-fields {
                margin-bottom: 20px;
            }
            
            .dynamic-field {
                margin-bottom: 15px;
                padding: 15px;
                border: 1px solid #eee;
                border-radius: 4px;
                background-color: #f9f9f9;
            }
            
            .field-actions {
                text-align: right;
                margin-bottom: 20px;
            }
            
            .add-field {
                background-color: #28a745;
            }
            
            .remove-field {
                background-color: #dc3545;
                padding: 5px 10px;
                font-size: 14px;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="edit-container">
            <div class="edit-header">
                <h1><?php echo $title; ?></h1>
                <p>Edit your resume section and save changes</p>
            </div>
    <?php
}

// Function to display common page footer
function displayFooter($resumeId = null) {
    ?>
            <div class="footer-links">
                <?php if ($resumeId): ?>
                    <a href="../admin.php?resume_id=<?php echo $resumeId; ?>">Back to Resume Dashboard</a>
                    <a href="../index.php?resume_id=<?php echo $resumeId; ?>">View Resume</a>
                <?php else: ?>
                    <a href="resumes.php">Back to Resumes</a>
                    <a href="../index.php">View Resume</a>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
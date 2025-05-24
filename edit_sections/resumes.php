<?php
require_once '../database/db.php';

// Set page title and description
$pageTitle = 'Manage Resumes';
$pageDescription = 'Create and manage multiple versions of your resume';

// Get database instance
$db = ResumeDB::getInstance();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        // Create new resume
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (!empty($name)) {
            $db->execute(
                "INSERT INTO resumes (name, description) VALUES (?, ?)",
                [$name, $description]
            );
            $_SESSION['message'] = 'New resume version created successfully!';
        }
    } 
    elseif ($action === 'update') {
        // Update existing resume
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if ($id > 0 && !empty($name)) {
            $db->execute(
                "UPDATE resumes SET name = ?, description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$name, $description, $id]
            );
            $_SESSION['message'] = 'Resume updated successfully!';
        }
    }
    elseif ($action === 'delete') {
        // Delete resume
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id > 0) {
            $db->execute("DELETE FROM resumes WHERE id = ?", [$id]);
            $_SESSION['message'] = 'Resume deleted successfully!';
        }
    }
    elseif ($action === 'duplicate') {
        // Duplicate resume
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id > 0) {
            $db->beginTransaction();
            try {
                // Get source resume
                $sourceResume = $db->querySingle("SELECT * FROM resumes WHERE id = ?", [$id]);
                
                if ($sourceResume) {
                    // Create new resume
                    $db->execute(
                        "INSERT INTO resumes (name, description) VALUES (?, ?)",
                        [$sourceResume['name'] . ' (Copy)', $sourceResume['description']]
                    );
                    $newResumeId = $db->lastInsertId();
                    
                    // Copy personal info
                    $personalInfo = $db->querySingle("SELECT * FROM personal_info WHERE resume_id = ?", [$id]);
                    if ($personalInfo) {
                        $db->execute(
                            "INSERT INTO personal_info (resume_id, name, contact_info) VALUES (?, ?, ?)",
                            [$newResumeId, $personalInfo['name'], $personalInfo['contact_info']]
                        );
                    }
                    
                    // Copy summary
                    $summary = $db->querySingle("SELECT * FROM summary WHERE resume_id = ?", [$id]);
                    if ($summary) {
                        $db->execute(
                            "INSERT INTO summary (resume_id, content) VALUES (?, ?)",
                            [$newResumeId, $summary['content']]
                        );
                    }
                    
                    // Copy education
                    $education = $db->query("SELECT * FROM education WHERE resume_id = ?", [$id]);
                    foreach ($education as $edu) {
                        $db->execute(
                            "INSERT INTO education (resume_id, degree, institution, location, date_range, description, is_visible, display_order) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                            [$newResumeId, $edu['degree'], $edu['institution'], $edu['location'], $edu['date_range'], 
                             $edu['description'], $edu['is_visible'], $edu['display_order']]
                        );
                    }
                    
                    // Copy skill categories and skills
                    $categories = $db->query("SELECT * FROM skill_categories WHERE resume_id = ?", [$id]);
                    foreach ($categories as $cat) {
                        $db->execute(
                            "INSERT INTO skill_categories (resume_id, name, display_order, is_visible) 
                             VALUES (?, ?, ?, ?)",
                            [$newResumeId, $cat['name'], $cat['display_order'], $cat['is_visible']]
                        );
                        $newCatId = $db->lastInsertId();
                        
                        // Copy skills for this category
                        $skills = $db->query("SELECT * FROM skills WHERE resume_id = ? AND category_id = ?", [$id, $cat['id']]);
                        foreach ($skills as $skill) {
                            $db->execute(
                                "INSERT INTO skills (resume_id, category_id, name, proficiency, is_visible, display_order) 
                                 VALUES (?, ?, ?, ?, ?, ?)",
                                [$newResumeId, $newCatId, $skill['name'], $skill['proficiency'], 
                                 $skill['is_visible'], $skill['display_order']]
                            );
                        }
                    }
                    
                    // Copy experience and accomplishments
                    $experiences = $db->query("SELECT * FROM experience WHERE resume_id = ?", [$id]);
                    foreach ($experiences as $exp) {
                        $db->execute(
                            "INSERT INTO experience (resume_id, job_title, company, location, date_range, is_visible, display_order) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)",
                            [$newResumeId, $exp['job_title'], $exp['company'], $exp['location'], 
                             $exp['date_range'], $exp['is_visible'], $exp['display_order']]
                        );
                        $newExpId = $db->lastInsertId();
                        
                        // Copy accomplishments for this experience
                        $accomplishments = $db->query(
                            "SELECT * FROM job_accomplishments WHERE resume_id = ? AND experience_id = ?", 
                            [$id, $exp['id']]
                        );
                        foreach ($accomplishments as $acc) {
                            $db->execute(
                                "INSERT INTO job_accomplishments (resume_id, experience_id, description, display_order, is_visible) 
                                 VALUES (?, ?, ?, ?, ?)",
                                [$newResumeId, $newExpId, $acc['description'], $acc['display_order'], $acc['is_visible']]
                            );
                        }
                    }
                    
                    // Copy projects
                    $projects = $db->query("SELECT * FROM projects WHERE resume_id = ?", [$id]);
                    foreach ($projects as $proj) {
                        $db->execute(
                            "INSERT INTO projects (resume_id, title, technologies, link, description, is_visible, display_order) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)",
                            [$newResumeId, $proj['title'], $proj['technologies'], $proj['link'], 
                             $proj['description'], $proj['is_visible'], $proj['display_order']]
                        );
                    }
                    
                    $db->commit();
                    $_SESSION['message'] = 'Resume duplicated successfully!';
                }
            } catch (Exception $e) {
                $db->rollback();
                $_SESSION['error'] = 'Error duplicating resume: ' . $e->getMessage();
            }
        }
    }
    elseif ($action === 'set_default') {
        // Set default resume
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id > 0) {
            $db->beginTransaction();
            try {
                $db->execute("UPDATE resumes SET is_default = 0");
                $db->execute("UPDATE resumes SET is_default = 1 WHERE id = ?", [$id]);
                $db->commit();
                $_SESSION['message'] = 'Default resume set successfully!';
            } catch (Exception $e) {
                $db->rollBack();
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
            }
        }
    }
    
    header('Location: resumes.php');
    exit;
}

// Get all resumes
$resumes = $db->query("SELECT * FROM resumes ORDER BY created_at DESC");

// Include header
require_once '../includes/header.php';
?>

<div class="container py-4">
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Resumes</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newResumeModal">
            <i class="fas fa-plus"></i> New Resume
        </button>
    </div>

    <!-- Resumes Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Last Updated</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumes as $resume): ?>
                        <tr>
                            <td>
                                <span class="resume-name" data-id="<?php echo $resume['id']; ?>">
                                    <?php echo htmlspecialchars($resume['name']); ?>
                            </span>
                            </td>
                            <td>
                                <span class="resume-description" data-id="<?php echo $resume['id']; ?>">
                                    <?php echo htmlspecialchars($resume['description']); ?>
                            </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($resume['updated_at'])); ?></td>
                            <td>
                                <?php if ($resume['is_default']): ?>
                                    <span class="badge bg-success">Default</span>
                                <?php endif; ?>
                            </td>
                            <td>                            <div class="btn-group" role="group">
                                <a href="../index.php?resume_id=<?php echo $resume['id']; ?>" target="_blank" 
                                   class="btn btn-sm btn-outline-info" title="Preview Resume">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="../edit_sections/personal_info.php?resume_id=<?php echo $resume['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Edit Resume">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../export.php?id=<?php echo $resume['id']; ?>" 
                                       class="btn btn-sm btn-outline-success" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary edit-resume" 
                                            data-id="<?php echo $resume['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($resume['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($resume['description']); ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editResumeModal">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to duplicate this resume?');">
                                        <input type="hidden" name="action" value="duplicate">
                                        <input type="hidden" name="id" value="<?php echo $resume['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-info" title="Duplicate">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </form>
                                    <?php if (!$resume['is_default']): ?>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to make this the default resume?');">
                                        <input type="hidden" name="action" value="set_default">
                                        <input type="hidden" name="id" value="<?php echo $resume['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Set as Default">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this resume?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $resume['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- New Resume Modal -->
<div class="modal fade" id="newResumeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Resume</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label for="name" class="form-label">Resume Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Resume Modal -->
<div class="modal fade" id="editResumeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Resume</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Resume Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit modal data
    document.querySelectorAll('.edit-resume').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const description = this.getAttribute('data-description');
            
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-description').value = description;
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

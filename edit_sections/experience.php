<?php
require_once '../database/db.php';
require_once 'common.php';

// Get database instance
$db = ResumeDB::getInstance();

// Get resume ID and name
$resumeId = getResumeId($db);
$resumeName = getResumeName($db, $resumeId);

// Set page title and description
$pageTitle = 'Manage Work Experience - ' . $resumeName;
$pageDescription = 'Add, edit, or hide job positions on your resume';

// Handle visibility toggle
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $currentStatus = $db->querySingle("SELECT is_visible FROM experience WHERE id = ?", [$id]);
    
    if ($currentStatus) {
        $newStatus = $currentStatus['is_visible'] ? 0 : 1;
        $db->execute("UPDATE experience SET is_visible = ? WHERE id = ?", [$newStatus, $id]);
        $_SESSION['message'] = 'Experience visibility updated successfully!';
    }
    
    header('Location: experience.php');
    exit;
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Start transaction to delete experience and related accomplishments
    $db->beginTransaction();
    
    try {
        // Delete accomplishments first (foreign key constraint)
        $db->execute("DELETE FROM job_accomplishments WHERE experience_id = ?", [$id]);
        
        // Then delete the experience
        $db->execute("DELETE FROM experience WHERE id = ?", [$id]);
        
        $db->commit();
        $_SESSION['message'] = 'Experience deleted successfully!';
    } catch (Exception $e) {
        $db->rollback();
        $_SESSION['message'] = 'Error: Failed to delete experience.';
    }
    
    header('Location: experience.php');
    exit;
}

// Get all experiences for this resume version
$experiences = $db->query(
    "SELECT * FROM experience WHERE resume_id = ? ORDER BY display_order, date_range DESC", 
    [$resumeId]
);

// Get accomplishments for each experience
foreach ($experiences as &$exp) {
    $exp['accomplishments'] = $db->query(
        "SELECT * FROM job_accomplishments WHERE experience_id = ? AND resume_id = ? ORDER BY display_order", 
        [$exp['id'], $resumeId]
    );
}

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

    <?php displayBreadcrumbs($resumeName, 'Work Experience'); ?>

    <div class="row">
        <div class="col-md-3">
            <?php displaySectionNav($resumeId, 'experience'); ?>
        </div>
        <div class="col-md-9">
            <div class="d-flex justify-content-end mb-4">
                <a href="experience_add.php?resume_id=<?php echo $resumeId; ?>" class="btn btn-success">
                    <i class="fas fa-plus-circle me-2"></i>Add New Position
                </a>
            </div>

            <?php if (empty($experiences)): ?>
                <div class="card shadow-sm">
                    <div class="card-body p-5 text-center">
                        <div class="py-4">
                            <i class="fas fa-briefcase fa-4x text-muted mb-4"></i>
                            <h3>No Work Experience Added</h3>
                            <p class="text-muted mb-4">You haven't added any work experience to your resume yet.</p>
                            <a href="experience_add.php" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-plus-circle me-2"></i>Add Your First Job
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($experiences as $exp): ?>
                        <div class="col-12">
                            <div class="card shadow-sm <?php echo $exp['is_visible'] ? '' : 'border-warning bg-light'; ?>">
                                <?php if (!$exp['is_visible']): ?>
                                    <div class="position-absolute top-0 end-0 mt-2 me-2">
                                        <span class="badge bg-warning">Hidden</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-header d-flex justify-content-between align-items-center <?php echo $exp['is_visible'] ? 'bg-primary text-white' : 'bg-warning-subtle'; ?>">
                                    <h5 class="card-title m-0"><?php echo htmlspecialchars($exp['job_title']); ?></h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm <?php echo $exp['is_visible'] ? 'btn-light' : 'btn-dark'; ?> dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="experience_edit.php?id=<?php echo $exp['id']; ?>">
                                                    <i class="fas fa-edit text-primary me-2"></i>Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="experience.php?toggle=1&id=<?php echo $exp['id']; ?>">
                                                    <i class="fas fa-<?php echo $exp['is_visible'] ? 'eye-slash text-warning' : 'eye text-success'; ?> me-2"></i>
                                                    <?php echo $exp['is_visible'] ? 'Hide' : 'Show'; ?>
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                                   data-id="<?php echo $exp['id']; ?>" 
                                                   data-title="<?php echo htmlspecialchars($exp['job_title']); ?>">
                                                    <i class="fas fa-trash-alt me-2"></i>Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <i class="fas fa-building me-2 text-muted"></i>
                                                <strong><?php echo htmlspecialchars($exp['company']); ?></strong>
                                            </p>
                                            <?php if (!empty($exp['location'])): ?>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-map-marker-alt me-2"></i>
                                                <?php echo htmlspecialchars($exp['location']); ?>
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <p class="mb-0">
                                                <i class="fas fa-calendar-alt me-2 text-muted"></i>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($exp['date_range']); ?></span>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($exp['accomplishments'])): ?>
                                        <h6 class="fw-bold text-primary mt-4 mb-3">Key Accomplishments</h6>
                                        <ul class="list-group list-group-flush">
                                            <?php foreach ($exp['accomplishments'] as $accomplishment): ?>
                                                <li class="list-group-item bg-transparent px-0">
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    <?php echo htmlspecialchars($accomplishment['description']); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-end mt-3">
                                        <a href="experience_edit.php?id=<?php echo $exp['id']; ?>" class="btn btn-sm btn-outline-primary me-2">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <a href="experience.php?toggle=1&id=<?php echo $exp['id']; ?>" class="btn btn-sm btn-outline-<?php echo $exp['is_visible'] ? 'warning' : 'success'; ?>">
                                            <i class="fas fa-<?php echo $exp['is_visible'] ? 'eye-slash' : 'eye'; ?> me-1"></i>
                                            <?php echo $exp['is_visible'] ? 'Hide' : 'Show'; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete <strong id="deleteItemTitle"></strong>?</p>
                            <p class="mb-0 text-danger"><small>This action cannot be undone. All accomplishments associated with this position will also be deleted.</small></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-1"></i>Yes, Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const title = button.getAttribute('data-title');
                
                document.getElementById('deleteItemTitle').textContent = title;
                document.getElementById('confirmDeleteBtn').href = 'experience.php?delete=1&id=' + id;
            });
        }
    });
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
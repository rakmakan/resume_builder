<?php
require_once '../database/db.php';
require_once 'common.php';

// Get database instance
$db = ResumeDB::getInstance();

// Get resume ID and name
$resumeId = getResumeId($db);
$resumeName = getResumeName($db, $resumeId);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // Add new category
        if ($action === 'add_category') {
            if (!empty($_POST['category_name'])) {
                $maxOrder = $db->querySingle("SELECT MAX(display_order) as max_order FROM skill_categories WHERE resume_id = ?", [$resumeId]);
                $newOrder = ($maxOrder['max_order'] ?? 0) + 1;
                
                $db->execute(
                    "INSERT INTO skill_categories (resume_id, name, display_order) VALUES (?, ?, ?)",
                    [$resumeId, $_POST['category_name'], $newOrder]
                );
            }
        }
        // Delete category
        elseif ($action === 'delete_category' && !empty($_POST['category_id'])) {
            // Delete skills in this category first
            $db->execute("DELETE FROM skills WHERE category_id = ? AND resume_id = ?", [$_POST['category_id'], $resumeId]);
            // Then delete the category
            $db->execute("DELETE FROM skill_categories WHERE id = ? AND resume_id = ?", [$_POST['category_id'], $resumeId]);
        }
        // Update category
        elseif ($action === 'update_category') {
            if (!empty($_POST['category_id']) && isset($_POST['category_name'])) {
                $db->execute(
                    "UPDATE skill_categories SET name = ? WHERE id = ? AND resume_id = ?",
                    [$_POST['category_name'], $_POST['category_id'], $resumeId]
                );
            }
        }
        // Add skill
        elseif ($action === 'add_skill') {
            if (!empty($_POST['category_id']) && !empty($_POST['skill_name'])) {
                $maxOrder = $db->querySingle(
                    "SELECT MAX(display_order) as max_order FROM skills WHERE category_id = ? AND resume_id = ?",
                    [$_POST['category_id'], $resumeId]
                );
                $newOrder = ($maxOrder['max_order'] ?? 0) + 1;
                
                $db->execute(
                    "INSERT INTO skills (resume_id, category_id, name, display_order) VALUES (?, ?, ?, ?)",
                    [$resumeId, $_POST['category_id'], $_POST['skill_name'], $newOrder]
                );
            }
        }
        // Delete skill
        elseif ($action === 'delete_skill' && !empty($_POST['skill_id'])) {
            $db->execute("DELETE FROM skills WHERE id = ? AND resume_id = ?", [$_POST['skill_id'], $resumeId]);
        }
        // Update skill
        elseif ($action === 'update_skill') {
            if (!empty($_POST['skill_id']) && isset($_POST['skill_name'])) {
                $db->execute(
                    "UPDATE skills SET name = ? WHERE id = ? AND resume_id = ?",
                    [$_POST['skill_name'], $_POST['skill_id'], $resumeId]
                );
            }
        }
    }
    
    $_SESSION['message'] = 'Skills updated successfully!';
    header("Location: skills.php?resume_id=" . $resumeId);
    exit;
}

// Get all skill categories with their skills for this resume
$categories = $db->query(
    "SELECT * FROM skill_categories WHERE resume_id = ? AND is_visible = 1 ORDER BY display_order",
    [$resumeId]
);

// Get skills for each category
foreach ($categories as &$category) {
    $category['skills'] = $db->query(
        "SELECT * FROM skills WHERE resume_id = ? AND category_id = ? AND is_visible = 1 ORDER BY display_order",
        [$resumeId, $category['id']]
    );
}

// Set page title and description
$pageTitle = 'Edit Skills - ' . $resumeName;
$pageDescription = 'Manage your skills and categories';

require_once '../includes/header.php';
?>

<div class="container py-4">
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php displayBreadcrumbs($resumeName, 'Skills'); ?>

    <div class="row">
        <div class="col-md-3">
            <?php displaySectionNav($resumeId, 'skills'); ?>
        </div>
        <div class="col-md-9">
            <!-- Header Section -->
            <div class="container-fluid py-4 bg-light border-bottom mb-4">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col">
                            <h1 class="h3 mb-1">Edit Skills</h1>
                            <p class="text-muted mb-0">Manage your resume skills section and organize by categories</p>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="fas fa-plus-circle me-2"></i>Add Category
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="container mb-5">
                <!-- Save Changes Bar -->
                <div class="save-bar" id="saveBar" style="display: none;">
                    <div class="container">
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted">
                                <i class="fas fa-info-circle me-2"></i>You have unsaved changes
                            </span>
                            <button type="button" class="btn btn-success shadow-sm" id="saveAllChanges">
                                <i class="fas fa-save me-2"></i>Save All Changes
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Categories Grid -->
                <div class="row g-4" id="categoriesGrid">
                    <?php if (empty($categories)): ?>
                        <div class="col-12">
                            <div class="text-center py-5 bg-light rounded">
                                <i class="fas fa-layer-group fa-3x mb-3 text-muted"></i>
                                <h3 class="h4 mb-3">No Skill Categories Yet</h3>
                                <p class="text-muted mb-4">Start by adding categories like "Programming Languages" or "Soft Skills"</p>
                                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                    <i class="fas fa-plus-circle me-2"></i>Add Your First Category
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                        <div class="col-md-6 col-lg-4 category-card" data-category-id="<?php echo $category['id']; ?>">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-white">
                                    <div class="d-flex align-items-center">
                                        <div class="category-name-container flex-grow-1">
                                            <h3 class="category-name" data-category-id="<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </h3>
                                        </div>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-link edit-category-btn p-1" title="Edit Category">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-link text-danger delete-category-btn p-1" title="Delete Category">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="skills-container mb-3">
                                        <?php foreach ($category['skills'] as $skill): ?>
                                        <span class="skill-tag" data-skill-id="<?php echo $skill['id']; ?>">
                                            <?php echo htmlspecialchars($skill['name']); ?>
                                            <button type="button" class="btn-close btn-close-white btn-close-sm delete-skill-btn" 
                                                    aria-label="Delete"></button>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                    <form class="add-skill-form" method="post">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control skill-input" 
                                                   placeholder="Type a skill and press Enter"
                                                   data-category-id="<?php echo $category['id']; ?>">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCategoryForm" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" name="category_name" 
                               required placeholder="e.g., Programming Languages">
                        <div class="invalid-feedback">
                            Please enter a unique category name
                        </div>
                    </div>
                    <div class="popular-categories">
                        <label class="form-label text-muted">Popular Categories:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary category-suggestion">
                                Programming Languages
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary category-suggestion">
                                Frameworks
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary category-suggestion">
                                Development Tools
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary category-suggestion">
                                Soft Skills
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i>Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Skills Manager JavaScript -->
<script src="../assets/js/skills-manager.js"></script>

<style>
/* Custom Styles */
.skills-manager {
    font-family: 'Inter', sans-serif;
}

.card {
    transition: all 0.2s ease;
    border-radius: 0.5rem;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important;
}

.category-name {
    font-weight: 600;
    color: #2d3748;
}

.skills-container {
    min-height: 50px;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.skill-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background-color: #4a5568;
    color: white;
    border-radius: 1rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.skill-tag:hover {
    background-color: #2d3748;
}

.skill-tag .btn-close {
    width: 0.5em;
    height: 0.5em;
    margin-left: 0.5rem;
    opacity: 0.7;
}

.skill-tag .btn-close:hover {
    opacity: 1;
}

.save-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
}

.category-suggestion {
    font-size: 0.875rem;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
}

.btn-link {
    color: #4a5568;
    text-decoration: none;
}

.btn-link:hover {
    color: #2d3748;
}

@media (max-width: 768px) {
    .card {
        margin-bottom: 1rem;
    }
    
    .skills-container {
        gap: 0.25rem;
    }
    
    .skill-tag {
        font-size: 0.8125rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let hasUnsavedChanges = false;
    const saveBar = document.getElementById('saveBar');
    
    // Function to show/hide save bar
    function toggleSaveBar(show) {
        hasUnsavedChanges = show;
        saveBar.style.display = show ? 'block' : 'none';
    }
    
    // Handle skill input
    document.querySelectorAll('.skill-input').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addSkill(this.value, this.dataset.categoryId);
                this.value = '';
            }
        });
    });
    
    // Handle category suggestions
    document.querySelectorAll('.category-suggestion').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('categoryName').value = this.textContent.trim();
        });
    });
    
    // Add new skill
    async function addSkill(skillName, categoryId) {
        if (!skillName.trim()) return;
        
        const formData = new FormData();
        formData.append('action', 'add_skill');
        formData.append('category_id', categoryId);
        formData.append('skill_name', skillName);
        
        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                // Add skill tag to UI
                const skillsContainer = document.querySelector(
                    `.category-card[data-category-id="${categoryId}"] .skills-container`
                );
                
                const skillTag = document.createElement('span');
                skillTag.className = 'skill-tag';
                skillTag.innerHTML = `
                    ${skillName}
                    <button type="button" class="btn-close btn-close-white btn-close-sm delete-skill-btn" 
                            aria-label="Delete"></button>
                `;
                
                skillsContainer.appendChild(skillTag);
                toggleSaveBar(true);
            }
        } catch (error) {
            console.error('Error adding skill:', error);
        }
    }
    
    // Save all changes
    document.getElementById('saveAllChanges').addEventListener('click', async function() {
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        
        try {
            // Collect all data
            const categories = Array.from(document.querySelectorAll('.category-card')).map(card => ({
                id: card.dataset.categoryId,
                name: card.querySelector('.category-name').textContent.trim(),
                skills: Array.from(card.querySelectorAll('.skill-tag')).map(tag => ({
                    id: tag.dataset.skillId,
                    name: tag.childNodes[0].textContent.trim()
                }))
            }));
            
            const response = await fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'save_all',
                    data: categories
                })
            });
            
            if (response.ok) {
                toggleSaveBar(false);
                // Show success message
                const toast = new bootstrap.Toast(document.createElement('div'));
                toast.show();
            }
        } catch (error) {
            console.error('Error saving changes:', error);
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-save me-2"></i>Save All Changes';
        }
    });
    
    // Delete skill
    document.addEventListener('click', function(e) {
        if (e.target.matches('.delete-skill-btn')) {
            const skillTag = e.target.closest('.skill-tag');
            if (confirm('Delete this skill?')) {
                const formData = new FormData();
                formData.append('action', 'delete_skill');
                formData.append('skill_id', skillTag.dataset.skillId);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    skillTag.remove();
                    toggleSaveBar(true);
                });
            }
        }
    });
    
    // Edit category name
    document.querySelectorAll('.edit-category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const container = this.closest('.card-header').querySelector('.category-name-container');
            const nameElement = container.querySelector('.category-name');
            const currentName = nameElement.textContent.trim();
            
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control form-control-sm';
            input.value = currentName;
            
            container.replaceChild(input, nameElement);
            input.focus();
            
            input.addEventListener('blur', function() {
                if (this.value.trim() && this.value !== currentName) {
                    updateCategoryName(nameElement.dataset.categoryId, this.value);
                }
                container.replaceChild(nameElement, this);
            });
            
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.blur();
                }
            });
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

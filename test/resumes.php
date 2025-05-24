<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resumes</title>
    <meta name="description" content="Create and manage multiple versions of your resume">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="../admin.php">
                <i class="fas fa-file-alt me-2"></i>Resume Builder
            </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../admin.php">
                            <i class="fas fa-columns me-1"></i>Dashboard
                        </a>
                    </li>
                                    </ul>
                <ul class="navbar-nav">
                                        <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-eye me-1"></i>Preview Resume
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-sm btn-outline-light ms-2" href="../index.php?download=true">
                            <i class="fas fa-download me-1"></i>Download PDF
                        </a>
                    </li>
                                    </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Container -->
    <div class="container">
        <!-- Flash Message -->
                
        <!-- Page Header -->
                <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-2">Manage Resumes</h1>
                                <p class="text-muted">Create and manage multiple versions of your resume</p>
                            </div>
        </div>
        
<div class="container py-4">
        
    
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
                                                <tr>
                            <td>
                                <span class="resume-name" data-id="3">
                                    ddvd                            </span>
                            </td>
                            <td>
                                <span class="resume-description" data-id="3">
                                    vdvd                            </span>
                            </td>
                            <td>May 24, 2025</td>
                            <td>
                                                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="../index.php?resume_id=3" 
                                       class="btn btn-sm btn-outline-secondary" title="Preview Resume">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="../edit_sections/personal_info.php?resume_id=3" 
                                       class="btn btn-sm btn-outline-primary" title="Edit Resume">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../export.php?id=3" 
                                       class="btn btn-sm btn-outline-success" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary edit-resume" 
                                            data-id="3"
                                            data-name="ddvd"
                                            data-description="vdvd"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editResumeModal">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to duplicate this resume?');">
                                        <input type="hidden" name="action" value="duplicate">
                                        <input type="hidden" name="id" value="3">
                                        <button type="submit" class="btn btn-sm btn-outline-info" title="Duplicate">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </form>
                                                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to make this the default resume?');">
                                        <input type="hidden" name="action" value="set_default">
                                        <input type="hidden" name="id" value="3">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Set as Default">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this resume?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="3">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                                                    </div>
                            </td>
                        </tr>
                                                <tr>
                            <td>
                                <span class="resume-name" data-id="2">
                                    Default Resume (Copy)                            </span>
                            </td>
                            <td>
                                <span class="resume-description" data-id="2">
                                    Main resume version                            </span>
                            </td>
                            <td>May 24, 2025</td>
                            <td>
                                                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="../index.php?resume_id=2" 
                                       class="btn btn-sm btn-outline-secondary" title="Preview Resume">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="../edit_sections/personal_info.php?resume_id=2" 
                                       class="btn btn-sm btn-outline-primary" title="Edit Resume">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../export.php?id=2" 
                                       class="btn btn-sm btn-outline-success" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary edit-resume" 
                                            data-id="2"
                                            data-name="Default Resume (Copy)"
                                            data-description="Main resume version"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editResumeModal">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to duplicate this resume?');">
                                        <input type="hidden" name="action" value="duplicate">
                                        <input type="hidden" name="id" value="2">
                                        <button type="submit" class="btn btn-sm btn-outline-info" title="Duplicate">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </form>
                                                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to make this the default resume?');">
                                        <input type="hidden" name="action" value="set_default">
                                        <input type="hidden" name="id" value="2">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Set as Default">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this resume?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="2">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                                                    </div>
                            </td>
                        </tr>
                                                <tr>
                            <td>
                                <span class="resume-name" data-id="1">
                                    Default Resume                            </span>
                            </td>
                            <td>
                                <span class="resume-description" data-id="1">
                                    Main resume version                            </span>
                            </td>
                            <td>May 24, 2025</td>
                            <td>
                                                                    <span class="badge bg-success">Default</span>
                                                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="../index.php?resume_id=1" 
                                       class="btn btn-sm btn-outline-secondary" title="Preview Resume">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="../edit_sections/personal_info.php?resume_id=1" 
                                       class="btn btn-sm btn-outline-primary" title="Edit Resume">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../export.php?id=1" 
                                       class="btn btn-sm btn-outline-success" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary edit-resume" 
                                            data-id="1"
                                            data-name="Default Resume"
                                            data-description="Main resume version"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editResumeModal">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to duplicate this resume?');">
                                        <input type="hidden" name="action" value="duplicate">
                                        <input type="hidden" name="id" value="1">
                                        <button type="submit" class="btn btn-sm btn-outline-info" title="Duplicate">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </form>
                                                                    </div>
                            </td>
                        </tr>
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

    </div><!-- End of main content container -->
    
    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <div class="small text-muted">Resume Builder &copy; 2025</div>
                </div>
                <div class="col-auto">
                    <a href="https://getbootstrap.com/" class="text-decoration-none text-muted" target="_blank">Built with Bootstrap</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom scripts -->
    <script>
        // Automatically fade out alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>
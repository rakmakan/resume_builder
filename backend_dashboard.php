<?php
require_once 'includes/header.php';
require_once 'database/db.php';

$page_title = "AI Backend Dashboard";
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .status-indicator {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-healthy {
            background: #d4edda;
            color: #155724;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-loading {
            background: #fff3cd;
            color: #856404;
        }
        
        .action-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .action-button:hover {
            background: #0056b3;
        }
        
        .action-button.danger {
            background: #dc3545;
        }
        
        .action-button.danger:hover {
            background: #c82333;
        }
        
        .action-button.success {
            background: #28a745;
        }
        
        .action-button.success:hover {
            background: #1e7e34;
        }
        
        .job-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .job-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .job-item:last-child {
            border-bottom: none;
        }
        
        .job-info h4 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 14px;
        }
        
        .job-info p {
            margin: 0;
            color: #666;
            font-size: 12px;
        }
        
        .task-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .task-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }
        
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .task-type {
            font-weight: 600;
            color: #333;
        }
        
        .task-progress {
            width: 100%;
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            margin: 8px 0;
            overflow: hidden;
        }
        
        .task-progress-bar {
            height: 100%;
            background: #007bff;
            transition: width 0.3s ease;
        }
        
        .task-progress-bar.completed {
            background: #28a745;
        }
        
        .task-progress-bar.failed {
            background: #dc3545;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background: #cce7ff;
            color: #004085;
            border: 1px solid #b3d7ff;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1><?= $page_title ?></h1>
        
        <!-- Status Overview -->
        <div class="dashboard-grid">
            <!-- Backend Health Status -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title">Backend Status</h3>
                    <span class="status-indicator status-loading" id="backend-status">Checking...</span>
                </div>
                <div>
                    <p><strong>API Endpoint:</strong> http://localhost:8001</p>
                    <p><strong>Documentation:</strong> <a href="http://localhost:8001/docs" target="_blank">Swagger UI</a></p>
                    <p><strong>Health Check:</strong> <span id="health-status">Loading...</span></p>
                    <button class="action-button" onclick="checkBackendHealth()">Refresh Status</button>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div>
                    <button class="action-button success" onclick="openJobSearchModal()">Search Jobs</button>
                    <button class="action-button" onclick="openResumeModal()">Generate Resume</button>
                    <button class="action-button" onclick="openBackgroundModal()">Update Background</button>
                    <button class="action-button" onclick="refreshData()">Refresh Data</button>
                </div>
            </div>
        </div>
        
        <!-- Main Content Grid -->
        <div class="dashboard-grid">
            <!-- Job Management -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title">Recent Jobs</h3>
                    <button class="action-button" onclick="loadJobs()">Refresh</button>
                </div>
                <div class="job-list" id="job-list">
                    <p>Loading jobs...</p>
                </div>
            </div>
            
            <!-- Task Tracking -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title">Background Tasks</h3>
                    <button class="action-button" onclick="loadTasks()">Refresh</button>
                </div>
                <div class="task-list" id="task-list">
                    <p>Loading tasks...</p>
                </div>
            </div>
        </div>
        
        <!-- Background Information -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Background Information</h3>
                <button class="action-button" onclick="loadBackground()">Refresh</button>
            </div>
            <div id="background-info">
                <p>Loading background information...</p>
            </div>
        </div>
    </div>
    
    <!-- Modals -->
    
    <!-- Job Search Modal -->
    <div id="jobSearchModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Search for Jobs</h3>
            <div class="form-group">
                <label>Search Terms (comma-separated):</label>
                <input type="text" id="searchTerms" class="form-control" placeholder="AI Engineer, Machine Learning, Python Developer">
            </div>
            <div class="form-group">
                <label>Location:</label>
                <input type="text" id="location" class="form-control" value="United States">
            </div>
            <div class="form-group">
                <label>Experience Level:</label>
                <select id="experienceLevel" class="form-control">
                    <option value="entry_level">Entry Level</option>
                    <option value="mid_level">Mid Level</option>
                    <option value="senior_level">Senior Level</option>
                    <option value="executive">Executive</option>
                </select>
            </div>
            <div class="form-group">
                <label>Max Results:</label>
                <input type="number" id="maxResults" class="form-control" value="25" min="1" max="100">
            </div>
            <button class="action-button success" onclick="startJobSearch()">Start Search</button>
            <button class="action-button" onclick="closeModal('jobSearchModal')">Cancel</button>
        </div>
    </div>
    
    <!-- Resume Generation Modal -->
    <div id="resumeModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Generate Resume</h3>
            <div class="form-group">
                <label>Select Job:</label>
                <select id="resumeJobId" class="form-control">
                    <option value="">Loading jobs...</option>
                </select>
            </div>
            <div class="form-group">
                <label>Custom Background (optional):</label>
                <textarea id="customBackground" class="form-control" placeholder="Leave empty to use saved background"></textarea>
            </div>
            <button class="action-button success" onclick="startResumeGeneration()">Generate Resume</button>
            <button class="action-button" onclick="closeModal('resumeModal')">Cancel</button>
        </div>
    </div>
    
    <!-- Background Update Modal -->
    <div id="backgroundModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Update Background Information</h3>
            <div class="form-group">
                <label>Background Content:</label>
                <textarea id="backgroundContent" class="form-control" rows="10" placeholder="Enter your professional background, experience, skills, education, etc."></textarea>
            </div>
            <button class="action-button success" onclick="updateBackground()">Update Background</button>
            <button class="action-button" onclick="closeModal('backgroundModal')">Cancel</button>
        </div>
    </div>

    <script>
        // Global variables
        const API_BASE = 'http://localhost:8001/api';
        let pollInterval = null;
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            checkBackendHealth();
            loadJobs();
            loadTasks();
            loadBackground();
            
            // Start polling for task updates
            startTaskPolling();
        });
        
        // Backend health check
        async function checkBackendHealth() {
            try {
                const response = await fetch(`${API_BASE}/health`);
                const data = await response.json();
                
                const statusElement = document.getElementById('backend-status');
                const healthElement = document.getElementById('health-status');
                
                if (response.ok && data.status === 'healthy') {
                    statusElement.textContent = 'Healthy';
                    statusElement.className = 'status-indicator status-healthy';
                    healthElement.textContent = 'All services operational';
                    healthElement.style.color = '#28a745';
                } else {
                    throw new Error('Backend unhealthy');
                }
            } catch (error) {
                const statusElement = document.getElementById('backend-status');
                const healthElement = document.getElementById('health-status');
                
                statusElement.textContent = 'Error';
                statusElement.className = 'status-indicator status-error';
                healthElement.textContent = 'Backend unavailable';
                healthElement.style.color = '#dc3545';
            }
        }
        
        // Load jobs
        async function loadJobs() {
            try {
                const response = await fetch(`${API_BASE}/jobs?limit=10`);
                const data = await response.json();
                
                const jobList = document.getElementById('job-list');
                
                if (data.success && data.data.jobs.length > 0) {
                    jobList.innerHTML = data.data.jobs.map(job => `
                        <div class="job-item">
                            <div class="job-info">
                                <h4>${job.title}</h4>
                                <p>${job.company} - ${job.location || 'Remote'}</p>
                            </div>
                            <div>
                                <button class="action-button" onclick="generateResumeForJob(${job.id})">Generate Resume</button>
                            </div>
                        </div>
                    `).join('');
                    
                    // Update resume modal job selector
                    const jobSelect = document.getElementById('resumeJobId');
                    jobSelect.innerHTML = '<option value="">Select a job...</option>' + 
                        data.data.jobs.map(job => `<option value="${job.id}">${job.title} at ${job.company}</option>`).join('');
                } else {
                    jobList.innerHTML = '<p>No jobs found. <button class="action-button" onclick="openJobSearchModal()">Search for jobs</button></p>';
                }
            } catch (error) {
                document.getElementById('job-list').innerHTML = '<p>Error loading jobs. Check backend connection.</p>';
            }
        }
        
        // Load tasks
        async function loadTasks() {
            try {
                const response = await fetch(`${API_BASE}/tasks`);
                const data = await response.json();
                
                const taskList = document.getElementById('task-list');
                
                if (data.length > 0) {
                    taskList.innerHTML = data.map(task => `
                        <div class="task-item">
                            <div class="task-header">
                                <span class="task-type">${task.type}</span>
                                <span class="status-indicator ${getTaskStatusClass(task.status)}">${task.status}</span>
                            </div>
                            <div>${task.message}</div>
                            <div class="task-progress">
                                <div class="task-progress-bar ${task.status}" style="width: ${task.progress}%"></div>
                            </div>
                            ${task.error ? `<div style="color: #dc3545; font-size: 12px; margin-top: 5px;">Error: ${task.error}</div>` : ''}
                        </div>
                    `).join('');
                } else {
                    taskList.innerHTML = '<p>No active tasks</p>';
                }
            } catch (error) {
                document.getElementById('task-list').innerHTML = '<p>Error loading tasks</p>';
            }
        }
        
        // Load background information
        async function loadBackground() {
            try {
                const response = await fetch(`${API_BASE}/background`);
                const data = await response.json();
                
                const backgroundInfo = document.getElementById('background-info');
                
                if (data.success && data.data.has_background) {
                    backgroundInfo.innerHTML = `
                        <p><strong>Status:</strong> Background configured (${data.data.content_length} characters)</p>
                        <button class="action-button" onclick="openBackgroundModal()">Update Background</button>
                        <button class="action-button" onclick="viewBackground()">View Content</button>
                    `;
                } else {
                    backgroundInfo.innerHTML = `
                        <p><strong>Status:</strong> No background information</p>
                        <button class="action-button success" onclick="openBackgroundModal()">Add Background</button>
                    `;
                }
            } catch (error) {
                document.getElementById('background-info').innerHTML = '<p>Error loading background information</p>';
            }
        }
        
        // Helper functions
        function getTaskStatusClass(status) {
            switch(status) {
                case 'completed': return 'status-healthy';
                case 'failed': return 'status-error';
                default: return 'status-loading';
            }
        }
        
        function startTaskPolling() {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(loadTasks, 3000); // Poll every 3 seconds
        }
        
        // Modal functions
        function openJobSearchModal() {
            document.getElementById('jobSearchModal').style.display = 'block';
        }
        
        function openResumeModal() {
            document.getElementById('resumeModal').style.display = 'block';
        }
        
        function openBackgroundModal() {
            document.getElementById('backgroundModal').style.display = 'block';
            loadBackgroundContent();
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Action functions
        async function startJobSearch() {
            const searchTerms = document.getElementById('searchTerms').value.split(',').map(s => s.trim());
            const location = document.getElementById('location').value;
            const experienceLevel = document.getElementById('experienceLevel').value;
            const maxResults = parseInt(document.getElementById('maxResults').value);
            
            if (searchTerms.length === 0 || searchTerms[0] === '') {
                alert('Please enter search terms');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/jobs/search`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        search_terms: searchTerms,
                        location: location,
                        experience_level: experienceLevel,
                        max_results: maxResults
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(`Job search started! Task ID: ${data.task_id}`);
                    closeModal('jobSearchModal');
                    loadTasks();
                } else {
                    alert('Job search failed: ' + data.message);
                }
            } catch (error) {
                alert('Error starting job search: ' + error.message);
            }
        }
        
        async function startResumeGeneration() {
            const jobId = document.getElementById('resumeJobId').value;
            const customBackground = document.getElementById('customBackground').value;
            
            if (!jobId) {
                alert('Please select a job');
                return;
            }
            
            try {
                const requestData = { job_id: parseInt(jobId) };
                if (customBackground.trim()) {
                    requestData.background_content = customBackground;
                }
                
                const response = await fetch(`${API_BASE}/resumes`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(requestData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(`Resume generation started! Task ID: ${data.task_id}`);
                    closeModal('resumeModal');
                    loadTasks();
                } else {
                    alert('Resume generation failed: ' + data.message);
                }
            } catch (error) {
                alert('Error starting resume generation: ' + error.message);
            }
        }
        
        async function updateBackground() {
            const content = document.getElementById('backgroundContent').value;
            
            if (!content.trim()) {
                alert('Please enter background content');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/background`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ content: content })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Background updated successfully!');
                    closeModal('backgroundModal');
                    loadBackground();
                } else {
                    alert('Background update failed: ' + data.message);
                }
            } catch (error) {
                alert('Error updating background: ' + error.message);
            }
        }
        
        async function loadBackgroundContent() {
            try {
                const response = await fetch(`${API_BASE}/background`);
                const data = await response.json();
                
                if (data.success && data.data.has_background) {
                    document.getElementById('backgroundContent').value = data.data.content;
                }
            } catch (error) {
                console.error('Error loading background content:', error);
            }
        }
        
        function generateResumeForJob(jobId) {
            document.getElementById('resumeJobId').value = jobId;
            openResumeModal();
        }
        
        function refreshData() {
            checkBackendHealth();
            loadJobs();
            loadTasks();
            loadBackground();
        }
        
        async function viewBackground() {
            try {
                const response = await fetch(`${API_BASE}/background`);
                const data = await response.json();
                
                if (data.success && data.data.has_background) {
                    alert(data.data.content);
                } else {
                    alert('No background content available');
                }
            } catch (error) {
                alert('Error loading background content');
            }
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
    
    <style>
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-content h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
    </style>
</body>
</html>
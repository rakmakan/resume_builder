<?php
require_once 'includes/header.php';
require_once 'database/db.php';

$page_title = "AI Resume Builder - Workflow Dashboard";
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .workflow-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .workflow-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
        }
        
        .workflow-step {
            background: white;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .workflow-step.active {
            border-left: 5px solid #28a745;
            box-shadow: 0 6px 12px rgba(40,167,69,0.2);
        }
        
        .workflow-step.completed {
            border-left: 5px solid #17a2b8;
            background: #f8f9fa;
        }
        
        .workflow-step.disabled {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .step-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }
        
        .step-header.active {
            background: #e8f5e8;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #6c757d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .step-number.active {
            background: #28a745;
        }
        
        .step-number.completed {
            background: #17a2b8;
        }
        
        .step-info {
            flex: 1;
        }
        
        .step-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .step-description {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .step-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-ready {
            background: #d4edda;
            color: #155724;
        }
        
        .status-running {
            background: #cce7ff;
            color: #004085;
        }
        
        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .step-content {
            padding: 20px;
            display: none;
        }
        
        .step-content.active {
            display: block;
        }
        
        .step-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .action-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .action-button:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }
        
        .action-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .action-button.success {
            background: #28a745;
        }
        
        .action-button.success:hover {
            background: #1e7e34;
        }
        
        .action-button.danger {
            background: #dc3545;
        }
        
        .action-button.danger:hover {
            background: #c82333;
        }
        
        .step-output {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
            min-height: 100px;
        }
        
        .output-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #495057;
        }
        
        .output-content {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.4;
            color: #6c757d;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 4px;
            transition: width 0.3s ease;
            width: 0%;
        }
        
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .data-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
        }
        
        .data-card-header {
            font-weight: 600;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        
        .data-item {
            padding: 8px 0;
            border-bottom: 1px solid #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .data-item:last-child {
            border-bottom: none;
        }
        
        .data-label {
            font-weight: 500;
            color: #495057;
        }
        
        .data-value {
            color: #6c757d;
            text-align: right;
            max-width: 60%;
            word-break: break-word;
        }
        
        .next-step-hint {
            background: #e7f3ff;
            border: 1px solid #b3d7ff;
            border-radius: 6px;
            padding: 12px 16px;
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .next-step-hint i {
            color: #004085;
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
            min-height: 120px;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        
        .alert-info {
            background: #cce7ff;
            color: #004085;
            border: 1px solid #b3d7ff;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .workflow-progress {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .progress-step {
            text-align: center;
            flex: 1;
            position: relative;
        }
        
        .progress-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
            z-index: -1;
        }
        
        .progress-step.completed:not(:last-child)::after {
            background: #28a745;
        }
        
        .progress-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: bold;
            color: #6c757d;
        }
        
        .progress-circle.completed {
            background: #28a745;
            color: white;
        }
        
        .progress-circle.active {
            background: #007bff;
            color: white;
        }
        
        .progress-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="workflow-container">
        <!-- Header -->
        <div class="workflow-header">
            <h1><i class="fas fa-robot me-2"></i>AI Resume Builder Workflow</h1>
            <p>Follow these steps to automatically find jobs and generate targeted resumes</p>
        </div>
        
        <!-- Workflow Progress Overview -->
        <div class="workflow-progress">
            <h3><i class="fas fa-chart-line me-2"></i>Overall Progress</h3>
            <div class="progress-steps">
                <div class="progress-step" id="progress-1">
                    <div class="progress-circle">1</div>
                    <div class="progress-label">Setup Background</div>
                </div>
                <div class="progress-step" id="progress-2">
                    <div class="progress-circle">2</div>
                    <div class="progress-label">Search Jobs</div>
                </div>
                <div class="progress-step" id="progress-3">
                    <div class="progress-circle">3</div>
                    <div class="progress-label">Generate Resumes</div>
                </div>
                <div class="progress-step" id="progress-4">
                    <div class="progress-circle">4</div>
                    <div class="progress-label">Review & Download</div>
                </div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="overall-progress"></div>
            </div>
        </div>
        
        <!-- Step 1: Background Setup -->
        <div class="workflow-step active" id="step-1">
            <div class="step-header" onclick="toggleStep(1)">
                <div style="display: flex; align-items: center;">
                    <div class="step-number active">1</div>
                    <div class="step-info">
                        <h3 class="step-title">Setup Your Professional Background</h3>
                        <p class="step-description">Upload or enter your professional background information for AI analysis</p>
                    </div>
                </div>
                <div class="step-status">
                    <span class="status-badge status-ready" id="step-1-status">Ready</span>
                    <i class="fas fa-chevron-down" id="step-1-chevron"></i>
                </div>
            </div>
            <div class="step-content active" id="step-1-content">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Why this matters:</strong> Your background information helps the AI understand your skills, experience, and qualifications to create targeted resumes for specific job opportunities.
                </div>
                
                <div class="step-actions">
                    <button class="action-button" onclick="loadCurrentBackground()">
                        <i class="fas fa-eye"></i> View Current Background
                    </button>
                    <button class="action-button success" onclick="openBackgroundEditor()">
                        <i class="fas fa-edit"></i> Edit Background
                    </button>
                    <button class="action-button" onclick="uploadBackgroundFile()">
                        <i class="fas fa-upload"></i> Upload File
                    </button>
                </div>
                
                <div class="step-output">
                    <div class="output-title"><i class="fas fa-file-text me-2"></i>Current Background Status</div>
                    <div class="output-content" id="step-1-output">Loading background information...</div>
                </div>
                
                <div class="next-step-hint" id="step-1-hint" style="display: none;">
                    <i class="fas fa-arrow-right"></i>
                    <span><strong>Next:</strong> Your background is ready! Now you can search for jobs that match your profile.</span>
                </div>
            </div>
        </div>
        
        <!-- Step 2: Job Search -->
        <div class="workflow-step disabled" id="step-2">
            <div class="step-header" onclick="toggleStep(2)">
                <div style="display: flex; align-items: center;">
                    <div class="step-number">2</div>
                    <div class="step-info">
                        <h3 class="step-title">Search for Relevant Jobs</h3>
                        <p class="step-description">Let AI find job opportunities that match your background and preferences</p>
                    </div>
                </div>
                <div class="step-status">
                    <span class="status-badge status-pending" id="step-2-status">Waiting for Step 1</span>
                    <i class="fas fa-chevron-down" id="step-2-chevron"></i>
                </div>
            </div>
            <div class="step-content" id="step-2-content">
                <div class="alert alert-info">
                    <i class="fas fa-search me-2"></i>
                    <strong>AI Job Search:</strong> The system will generate intelligent search queries based on your background and scrape LinkedIn for matching opportunities.
                </div>
                
                <div class="step-actions">
                    <button class="action-button success" onclick="startJobSearch()" id="start-job-search" disabled>
                        <i class="fas fa-play"></i> Start Job Search
                    </button>
                    <button class="action-button" onclick="viewFoundJobs()" id="view-jobs" disabled>
                        <i class="fas fa-list"></i> View Found Jobs
                    </button>
                    <button class="action-button danger" onclick="clearJobs()" id="clear-jobs" disabled>
                        <i class="fas fa-trash"></i> Clear All Jobs
                    </button>
                </div>
                
                <div class="data-grid" id="job-search-config" style="display: none;">
                    <div class="data-card">
                        <div class="data-card-header">Search Configuration</div>
                        <div class="form-group">
                            <label>Job Keywords (comma-separated):</label>
                            <input type="text" class="form-control" id="job-keywords" placeholder="AI Engineer, Machine Learning, Python Developer">
                        </div>
                        <div class="form-group">
                            <label>Location:</label>
                            <input type="text" class="form-control" id="job-location" value="United States">
                        </div>
                        <div class="form-group">
                            <label>Experience Level:</label>
                            <select class="form-control" id="job-experience">
                                <option value="entry_level">Entry Level</option>
                                <option value="mid_level">Mid Level</option>
                                <option value="senior_level">Senior Level</option>
                                <option value="executive">Executive</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Maximum Results:</label>
                            <input type="number" class="form-control" id="job-max-results" value="25" min="1" max="100">
                        </div>
                    </div>
                </div>
                
                <div class="step-output">
                    <div class="output-title"><i class="fas fa-tasks me-2"></i>Job Search Progress</div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="job-search-progress"></div>
                    </div>
                    <div class="output-content" id="step-2-output">Ready to search for jobs...</div>
                </div>
                
                <div class="next-step-hint" id="step-2-hint" style="display: none;">
                    <i class="fas fa-arrow-right"></i>
                    <span><strong>Next:</strong> Great! Jobs found. Now you can generate targeted resumes for specific positions.</span>
                </div>
            </div>
        </div>
        
        <!-- Step 3: Resume Generation -->
        <div class="workflow-step disabled" id="step-3">
            <div class="step-header" onclick="toggleStep(3)">
                <div style="display: flex; align-items: center;">
                    <div class="step-number">3</div>
                    <div class="step-info">
                        <h3 class="step-title">Generate Targeted Resumes</h3>
                        <p class="step-description">Create AI-optimized resumes tailored to specific job requirements</p>
                    </div>
                </div>
                <div class="step-status">
                    <span class="status-badge status-pending" id="step-3-status">Waiting for Step 2</span>
                    <i class="fas fa-chevron-down" id="step-3-chevron"></i>
                </div>
            </div>
            <div class="step-content" id="step-3-content">
                <div class="alert alert-info">
                    <i class="fas fa-magic me-2"></i>
                    <strong>AI Resume Generation:</strong> Select jobs to generate custom resumes. The AI will analyze job requirements and tailor your background accordingly.
                </div>
                
                <div class="step-actions">
                    <button class="action-button success" onclick="generateBulkResumes()" id="generate-bulk" disabled>
                        <i class="fas fa-magic"></i> Generate All Resumes
                    </button>
                    <button class="action-button" onclick="generateSelectedResumes()" id="generate-selected" disabled>
                        <i class="fas fa-check-square"></i> Generate Selected
                    </button>
                    <button class="action-button" onclick="viewGeneratedResumes()" id="view-resumes" disabled>
                        <i class="fas fa-file-alt"></i> View Generated Resumes
                    </button>
                </div>
                
                <div class="step-output">
                    <div class="output-title"><i class="fas fa-cogs me-2"></i>Resume Generation Progress</div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="resume-progress"></div>
                    </div>
                    <div class="output-content" id="step-3-output">Select jobs to generate resumes...</div>
                </div>
                
                <div class="data-grid" id="jobs-list">
                    <!-- Jobs will be populated here -->
                </div>
                
                <div class="next-step-hint" id="step-3-hint" style="display: none;">
                    <i class="fas fa-arrow-right"></i>
                    <span><strong>Next:</strong> Resumes generated! Review and download your customized resumes.</span>
                </div>
            </div>
        </div>
        
        <!-- Step 4: Review & Download -->
        <div class="workflow-step disabled" id="step-4">
            <div class="step-header" onclick="toggleStep(4)">
                <div style="display: flex; align-items: center;">
                    <div class="step-number">4</div>
                    <div class="step-info">
                        <h3 class="step-title">Review & Download Resumes</h3>
                        <p class="step-description">Review your generated resumes and download them for job applications</p>
                    </div>
                </div>
                <div class="step-status">
                    <span class="status-badge status-pending" id="step-4-status">Waiting for Step 3</span>
                    <i class="fas fa-chevron-down" id="step-4-chevron"></i>
                </div>
            </div>
            <div class="step-content" id="step-4-content">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Workflow Complete:</strong> Your AI-generated resumes are ready! Review each resume and download the ones you want to use.
                </div>
                
                <div class="step-actions">
                    <button class="action-button" onclick="downloadAllResumes()" id="download-all" disabled>
                        <i class="fas fa-download"></i> Download All PDFs
                    </button>
                    <button class="action-button" onclick="openResumeBuilder()" id="open-builder" disabled>
                        <i class="fas fa-external-link-alt"></i> Open Resume Builder
                    </button>
                    <button class="action-button success" onclick="startNewWorkflow()">
                        <i class="fas fa-redo"></i> Start New Workflow
                    </button>
                </div>
                
                <div class="step-output">
                    <div class="output-title"><i class="fas fa-clipboard-list me-2"></i>Generated Resumes Summary</div>
                    <div class="output-content" id="step-4-output">No resumes generated yet...</div>
                </div>
                
                <div class="data-grid" id="resumes-list">
                    <!-- Generated resumes will be populated here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden file input for background upload -->
    <input type="file" id="background-file-input" accept=".txt,.md,.doc,.docx" style="display: none;" onchange="handleBackgroundUpload(event)">
    
    <!-- Background Editor Modal -->
    <div id="background-editor-modal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <h3><i class="fas fa-edit me-2"></i>Edit Professional Background</h3>
            <div class="form-group">
                <label>Professional Background Content:</label>
                <textarea id="background-editor-content" class="form-control" rows="15" placeholder="Enter your professional background, experience, skills, education, achievements, etc.

Example:
Software Engineer with 5+ years of experience in web development and machine learning.

Technical Skills:
- Programming: Python, JavaScript, Java, SQL
- Frameworks: React, Django, Flask, Node.js
- Tools: Git, Docker, AWS, PostgreSQL

Professional Experience:
- Senior Software Engineer at TechCorp (2021-2024)
  - Led development of AI-powered analytics platform
  - Improved system performance by 40%
  - Mentored junior developers

Education:
- MS Computer Science, Stanford University (2019)
- BS Computer Engineering, UC Berkeley (2017)

Achievements:
- Published 3 papers on machine learning
- Speaker at PyConf 2023
- AWS Certified Solutions Architect"></textarea>
            </div>
            <div class="step-actions">
                <button class="action-button success" onclick="saveBackground()">
                    <i class="fas fa-save"></i> Save Background
                </button>
                <button class="action-button" onclick="closeBackgroundEditor()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        const API_BASE = 'http://localhost:8001/api';
        let currentStep = 1;
        let workflowData = {
            backgroundReady: false,
            jobsFound: false,
            resumesGenerated: false,
            jobs: [],
            resumes: [],
            tasks: {}
        };
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            checkBackendStatus();
            loadWorkflowState();
            startTaskPolling();
        });
        
        // Backend status check
        async function checkBackendStatus() {
            try {
                console.log('Checking backend at:', `${API_BASE}/health`);
                const response = await fetch(`${API_BASE}/health`);
                console.log('Backend response status:', response.status);
                
                if (!response.ok) throw new Error('Backend unavailable');
                
                const healthData = await response.json();
                console.log('Backend health data:', healthData);
                console.log('Backend is healthy');
                loadCurrentBackground();
            } catch (error) {
                console.error('Backend connection error:', error);
                showAlert('Backend is not available. Please ensure the AI backend service is running. Error: ' + error.message, 'error');
            }
        }
        
        // Load current background
        async function loadCurrentBackground() {
            try {
                const response = await fetch(`${API_BASE}/background`);
                const data = await response.json();
                
                if (data.success && data.data.has_background) {
                    updateStepOutput(1, `✅ Background loaded (${data.data.content_length} characters)\n\nPreview:\n${data.data.content.substring(0, 200)}...`);
                    workflowData.backgroundReady = true;
                    updateStepStatus(1, 'completed', 'Background Ready');
                    enableStep(2);
                    showNextStepHint(1);
                } else {
                    updateStepOutput(1, '⚠️ No background information found.\n\nPlease add your professional background to continue.');
                    workflowData.backgroundReady = false;
                    updateStepStatus(1, 'ready', 'Ready to Setup');
                }
                
                updateOverallProgress();
            } catch (error) {
                updateStepOutput(1, `❌ Error loading background: ${error.message}`);
            }
        }
        
        // Step management functions
        function toggleStep(stepNumber) {
            const content = document.getElementById(`step-${stepNumber}-content`);
            const chevron = document.getElementById(`step-${stepNumber}-chevron`);
            
            if (content.classList.contains('active')) {
                content.classList.remove('active');
                chevron.style.transform = 'rotate(0deg)';
            } else {
                // Close all other steps
                for (let i = 1; i <= 4; i++) {
                    if (i !== stepNumber) {
                        document.getElementById(`step-${i}-content`).classList.remove('active');
                        document.getElementById(`step-${i}-chevron`).style.transform = 'rotate(0deg)';
                    }
                }
                
                content.classList.add('active');
                chevron.style.transform = 'rotate(180deg)';
            }
        }
        
        function updateStepStatus(stepNumber, status, text) {
            const statusBadge = document.getElementById(`step-${stepNumber}-status`);
            const stepNumber_element = document.querySelector(`#step-${stepNumber} .step-number`);
            const progressCircle = document.getElementById(`progress-${stepNumber}`).querySelector('.progress-circle');
            
            // Remove old status classes
            statusBadge.className = 'status-badge';
            stepNumber_element.className = 'step-number';
            progressCircle.className = 'progress-circle';
            
            // Add new status
            statusBadge.classList.add(`status-${status}`);
            statusBadge.textContent = text;
            
            if (status === 'completed') {
                stepNumber_element.classList.add('completed');
                progressCircle.classList.add('completed');
                document.getElementById(`progress-${stepNumber}`).classList.add('completed');
            } else if (status === 'running') {
                stepNumber_element.classList.add('active');
                progressCircle.classList.add('active');
            }
        }
        
        function enableStep(stepNumber) {
            const step = document.getElementById(`step-${stepNumber}`);
            step.classList.remove('disabled');
            
            // Enable buttons in this step
            const buttons = step.querySelectorAll('button');
            buttons.forEach(button => {
                if (!button.hasAttribute('data-keep-disabled')) {
                    button.disabled = false;
                }
            });
            
            updateStepStatus(stepNumber, 'ready', 'Ready');
        }
        
        function updateStepOutput(stepNumber, content) {
            const output = document.getElementById(`step-${stepNumber}-output`);
            output.textContent = content;
        }
        
        function showNextStepHint(stepNumber) {
            const hint = document.getElementById(`step-${stepNumber}-hint`);
            hint.style.display = 'flex';
        }
        
        function updateOverallProgress() {
            let progress = 0;
            if (workflowData.backgroundReady) progress += 25;
            if (workflowData.jobsFound) progress += 25;
            if (workflowData.resumesGenerated) progress += 25;
            if (workflowData.resumes.length > 0) progress += 25;
            
            document.getElementById('overall-progress').style.width = progress + '%';
        }
        
        // Background management
        function openBackgroundEditor() {
            loadCurrentBackground().then(() => {
                // Load current content into editor
                fetch(`${API_BASE}/background`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.has_background) {
                            document.getElementById('background-editor-content').value = data.data.content;
                        }
                        document.getElementById('background-editor-modal').style.display = 'block';
                    });
            });
        }
        
        function closeBackgroundEditor() {
            document.getElementById('background-editor-modal').style.display = 'none';
        }
        
        async function saveBackground() {
            const content = document.getElementById('background-editor-content').value;
            
            if (!content.trim()) {
                showAlert('Please enter background content', 'warning');
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
                    showAlert('Background saved successfully!', 'success');
                    closeBackgroundEditor();
                    loadCurrentBackground();
                } else {
                    showAlert('Failed to save background: ' + data.message, 'error');
                }
            } catch (error) {
                showAlert('Error saving background: ' + error.message, 'error');
            }
        }
        
        function uploadBackgroundFile() {
            document.getElementById('background-file-input').click();
        }
        
        async function handleBackgroundUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('file', file);
            
            try {
                const response = await fetch(`${API_BASE}/background/upload`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Background file uploaded successfully!', 'success');
                    loadCurrentBackground();
                } else {
                    showAlert('Failed to upload background: ' + data.message, 'error');
                }
            } catch (error) {
                showAlert('Error uploading background: ' + error.message, 'error');
            }
        }
        
        // Job search functions
        async function startJobSearch() {
            // Show configuration first
            const configSection = document.getElementById('job-search-config');
            if (configSection.style.display === 'none') {
                configSection.style.display = 'block';
                return;
            }
            
            const keywords = document.getElementById('job-keywords').value.split(',').map(s => s.trim()).filter(s => s);
            const location = document.getElementById('job-location').value;
            const experience = document.getElementById('job-experience').value;
            const maxResults = parseInt(document.getElementById('job-max-results').value);
            
            if (keywords.length === 0) {
                showAlert('Please enter job keywords', 'warning');
                return;
            }
            
            try {
                console.log('Starting job search with:', {
                    search_terms: keywords,
                    location: location,
                    experience_level: experience,
                    max_results: maxResults
                });
                
                const response = await fetch(`${API_BASE}/jobs/search`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        search_terms: keywords,
                        location: location,
                        experience_level: experience,
                        max_results: maxResults
                    })
                });
                
                console.log('Job search response status:', response.status);
                const data = await response.json();
                console.log('Job search response data:', data);
                
                if (data.success) {
                    updateStepStatus(2, 'running', 'Searching...');
                    updateStepOutput(2, `🔍 Job search started!\nTask ID: ${data.task_id}\nSearching for: ${keywords.join(', ')}\nLocation: ${location}\nMax results: ${maxResults}`);
                    
                    // Store task for tracking
                    workflowData.tasks.jobSearch = data.task_id;
                    
                    // Hide config section
                    configSection.style.display = 'none';
                } else {
                    showAlert('Failed to start job search: ' + data.message, 'error');
                }
            } catch (error) {
                showAlert('Error starting job search: ' + error.message, 'error');
            }
        }
        
        async function viewFoundJobs() {
            try {
                const response = await fetch(`${API_BASE}/jobs?limit=50`);
                const data = await response.json();
                
                if (data.success && data.data.jobs.length > 0) {
                    workflowData.jobs = data.data.jobs;
                    displayJobs();
                    workflowData.jobsFound = true;
                    updateStepStatus(2, 'completed', `${data.data.jobs.length} Jobs Found`);
                    enableStep(3);
                    showNextStepHint(2);
                    updateOverallProgress();
                } else {
                    updateStepOutput(2, '📭 No jobs found yet. Try running a job search first.');
                }
            } catch (error) {
                showAlert('Error loading jobs: ' + error.message, 'error');
            }
        }
        
        function displayJobs() {
            const jobsList = document.getElementById('jobs-list');
            
            if (workflowData.jobs.length === 0) {
                jobsList.innerHTML = '<div class="alert alert-info">No jobs found yet.</div>';
                return;
            }
            
            jobsList.innerHTML = workflowData.jobs.map(job => `
                <div class="data-card">
                    <div class="data-card-header">
                        <input type="checkbox" id="job-${job.id}" style="margin-right: 10px;">
                        ${job.title} at ${job.company}
                    </div>
                    <div class="data-item">
                        <span class="data-label">Location:</span>
                        <span class="data-value">${job.location || 'Remote'}</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Posted:</span>
                        <span class="data-value">${new Date(job.created_at || Date.now()).toLocaleDateString()}</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Description:</span>
                        <span class="data-value">${(job.description || '').substring(0, 100)}...</span>
                    </div>
                    <div style="margin-top: 10px;">
                        <button class="action-button" onclick="generateResumeForJob(${job.id})" style="font-size: 12px; padding: 6px 12px;">
                            <i class="fas fa-magic"></i> Generate Resume
                        </button>
                    </div>
                </div>
            `).join('');
        }
        
        // Resume generation functions
        async function generateBulkResumes() {
            if (workflowData.jobs.length === 0) {
                showAlert('No jobs available for resume generation', 'warning');
                return;
            }
            
            updateStepStatus(3, 'running', 'Generating...');
            updateStepOutput(3, `🎯 Starting bulk resume generation for ${workflowData.jobs.length} jobs...`);
            
            let completed = 0;
            const total = workflowData.jobs.length;
            
            for (const job of workflowData.jobs) {
                try {
                    const response = await fetch(`${API_BASE}/resumes`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ job_id: job.id })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        workflowData.tasks[`resume_${job.id}`] = data.task_id;
                        completed++;
                        
                        const progress = (completed / total) * 100;
                        document.getElementById('resume-progress').style.width = progress + '%';
                        updateStepOutput(3, `🎯 Generated resume ${completed}/${total}\nLast: ${job.title} at ${job.company}\nTask ID: ${data.task_id}`);
                    }
                } catch (error) {
                    console.error(`Error generating resume for job ${job.id}:`, error);
                }
            }
            
            showAlert(`Started ${completed} resume generation tasks`, 'success');
        }
        
        async function generateResumeForJob(jobId) {
            try {
                console.log('Generating resume for job ID:', jobId);
                const response = await fetch(`${API_BASE}/resumes`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ job_id: jobId })
                });
                
                console.log('Resume generation response status:', response.status);
                const data = await response.json();
                console.log('Resume generation response data:', data);
                
                if (data.success) {
                    const job = workflowData.jobs.find(j => j.id === jobId);
                    workflowData.tasks[`resume_${jobId}`] = data.task_id;
                    showAlert(`Resume generation started for ${job?.title || 'job'}. Task ID: ${data.task_id}`, 'success');
                } else {
                    showAlert('Failed to start resume generation: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Resume generation error:', error);
                showAlert('Error starting resume generation: ' + error.message, 'error');
            }
        }
        
        // Task tracking
        function startTaskPolling() {
            setInterval(async () => {
                await updateTasks();
            }, 3000);
        }
        
        async function updateTasks() {
            try {
                const response = await fetch(`${API_BASE}/tasks`);
                const tasks = await response.json();
                
                // Update job search progress
                if (workflowData.tasks.jobSearch) {
                    const jobSearchTask = tasks.find(t => t.task_id === workflowData.tasks.jobSearch);
                    if (jobSearchTask) {
                        const progress = jobSearchTask.progress;
                        document.getElementById('job-search-progress').style.width = progress + '%';
                        
                        if (jobSearchTask.status === 'completed') {
                            updateStepOutput(2, `✅ Job search completed!\nFound ${jobSearchTask.result?.jobs_found || 0} jobs`);
                            updateStepStatus(2, 'completed', `${jobSearchTask.result?.jobs_found || 0} Jobs Found`);
                            workflowData.jobsFound = true;
                            enableStep(3);
                            showNextStepHint(2);
                            updateOverallProgress();
                            
                            // Auto-load jobs
                            setTimeout(viewFoundJobs, 1000);
                            
                            delete workflowData.tasks.jobSearch;
                        } else if (jobSearchTask.status === 'failed') {
                            updateStepOutput(2, `❌ Job search failed: ${jobSearchTask.error}`);
                            updateStepStatus(2, 'ready', 'Search Failed');
                            delete workflowData.tasks.jobSearch;
                        } else {
                            updateStepOutput(2, `🔍 ${jobSearchTask.message}\nProgress: ${progress}%`);
                        }
                    }
                }
                
                // Update resume generation progress
                const resumeTasks = Object.keys(workflowData.tasks).filter(key => key.startsWith('resume_'));
                let completedResumes = 0;
                
                for (const taskKey of resumeTasks) {
                    const taskId = workflowData.tasks[taskKey];
                    const task = tasks.find(t => t.task_id === taskId);
                    
                    if (task && task.status === 'completed') {
                        completedResumes++;
                        delete workflowData.tasks[taskKey];
                    }
                }
                
                if (completedResumes > 0) {
                    updateStepOutput(3, `✅ ${completedResumes} resumes generated successfully!`);
                    
                    if (resumeTasks.length === completedResumes) {
                        updateStepStatus(3, 'completed', `${completedResumes} Resumes Generated`);
                        workflowData.resumesGenerated = true;
                        enableStep(4);
                        showNextStepHint(3);
                        updateOverallProgress();
                    }
                }
                
            } catch (error) {
                console.error('Error updating tasks:', error);
            }
        }
        
        // Utility functions
        function showAlert(message, type) {
            // Create alert element
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>${message}`;
            
            // Insert at top of page
            document.querySelector('.workflow-container').insertBefore(alert, document.querySelector('.workflow-progress'));
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 5000);
        }
        
        function loadWorkflowState() {
            // This could load from localStorage or API
            // For now, we'll check current state from API
            loadCurrentBackground();
        }
        
        function startNewWorkflow() {
            // Reset workflow state
            workflowData = {
                backgroundReady: false,
                jobsFound: false,
                resumesGenerated: false,
                jobs: [],
                resumes: [],
                tasks: {}
            };
            
            // Reset UI
            for (let i = 1; i <= 4; i++) {
                updateStepStatus(i, 'pending', i === 1 ? 'Ready' : `Waiting for Step ${i-1}`);
                document.getElementById(`step-${i}`).classList.toggle('disabled', i > 1);
                document.getElementById(`step-${i}-hint`).style.display = 'none';
                document.getElementById(`progress-${i}`).classList.remove('completed');
            }
            
            updateOverallProgress();
            loadCurrentBackground();
        }
        
        // Additional placeholder functions
        function clearJobs() {
            if (confirm('Are you sure you want to clear all jobs?')) {
                // Implementation for clearing jobs
                showAlert('Jobs cleared successfully', 'success');
            }
        }
        
        function generateSelectedResumes() {
            const selected = document.querySelectorAll('#jobs-list input[type="checkbox"]:checked');
            if (selected.length === 0) {
                showAlert('Please select jobs to generate resumes for', 'warning');
                return;
            }
            
            // Implementation for selected resume generation
            showAlert(`Starting resume generation for ${selected.length} selected jobs`, 'info');
        }
        
        function viewGeneratedResumes() {
            // Implementation for viewing generated resumes
            showAlert('Opening generated resumes view', 'info');
        }
        
        function downloadAllResumes() {
            // Implementation for downloading all resumes
            showAlert('Starting download of all resumes', 'info');
        }
        
        function openResumeBuilder() {
            window.open('admin.php', '_blank');
        }
        
        // Modal click outside to close
        window.onclick = function(event) {
            const modal = document.getElementById('background-editor-modal');
            if (event.target === modal) {
                closeBackgroundEditor();
            }
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: #fefefe;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 8px;
            width: 90%;
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
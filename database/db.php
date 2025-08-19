<?php
/**
 * Database connection and utilities for Resume Builder
 */

class ResumeDB {
    private $db;
    private static $instance = null;
    
    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {
        // Load centralized config
        require_once __DIR__ . '/../shared/config_loader.php';
        $dbFile = ConfigLoader::getDatabasePath();
        $createTables = !file_exists($dbFile);
        
        try {
            $this->db = new PDO('sqlite:' . $dbFile);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables if database doesn't exist
            if ($createTables) {
                $this->createTables();
            }
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get the database instance (singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new ResumeDB();
        }
        return self::$instance;
    }
    
    /**
     * Create the database tables structure
     */
    private function createTables() {
        // Resume Versions
        $this->db->exec("
            CREATE TABLE resumes (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                description TEXT,
                is_default INTEGER DEFAULT 0,
                template_id INTEGER,
                last_viewed_at TIMESTAMP,
                view_count INTEGER DEFAULT 0,
                download_count INTEGER DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Personal Information
        $this->db->exec("
            CREATE TABLE personal_info (
                id INTEGER PRIMARY KEY,
                resume_id INTEGER,
                name TEXT NOT NULL,
                headline TEXT,
                contact_info TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
            )
        ");

        // Personal Information Details
        $this->db->exec("
            CREATE TABLE personal_info_details (
                id INTEGER PRIMARY KEY,
                resume_id INTEGER NOT NULL,
                detail_name TEXT NOT NULL,
                detail_icon TEXT,
                detail_info TEXT NOT NULL,
                display_order INTEGER DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
            )
        ");
        
        // Professional Summary
        $this->db->exec("
            CREATE TABLE summary (
                id INTEGER PRIMARY KEY,
                resume_id INTEGER,
                content TEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
            )
        ");
        
        // Education
        $this->db->exec("
            CREATE TABLE education (
                id INTEGER PRIMARY KEY,
                resume_id INTEGER,
                degree TEXT NOT NULL,
                institution TEXT NOT NULL,
                location TEXT,
                date_range TEXT,
                description TEXT,
                is_visible INTEGER DEFAULT 1,
                display_order INTEGER,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
            )
        ");
        
        // Skills Categories
        $this->db->exec("
            CREATE TABLE skill_categories (
                id INTEGER PRIMARY KEY,
                resume_id INTEGER,
                name TEXT NOT NULL,
                display_order INTEGER,
                is_visible INTEGER DEFAULT 1,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
            )
        ");
        
        // Skills
        $this->db->exec("
            CREATE TABLE skills (
                id INTEGER PRIMARY KEY,
                resume_id INTEGER,
                category_id INTEGER,
                name TEXT NOT NULL,
                proficiency INTEGER,
                is_visible INTEGER DEFAULT 1,
                display_order INTEGER,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES skill_categories(id) ON DELETE CASCADE
            )
        ");
        
        // Work Experience
        $this->db->exec("
            CREATE TABLE experience (
                id INTEGER PRIMARY KEY,
                resume_id INTEGER,
                job_title TEXT NOT NULL,
                company TEXT NOT NULL,
                location TEXT,
                date_range TEXT,
                is_visible INTEGER DEFAULT 1,
                display_order INTEGER,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
            )
        ");
        
        // Work Accomplishments
        $this->db->exec("
            CREATE TABLE job_accomplishments (
                id INTEGER PRIMARY KEY,
                resume_id INTEGER,
                experience_id INTEGER,
                description TEXT NOT NULL,
                display_order INTEGER,
                is_visible INTEGER DEFAULT 1,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
                FOREIGN KEY (experience_id) REFERENCES experience(id) ON DELETE CASCADE
            )
        ");
        
        // Projects
        $this->db->exec("
            CREATE TABLE projects (
                id INTEGER PRIMARY KEY,
                resume_id INTEGER,
                title TEXT NOT NULL,
                technologies TEXT,
                link TEXT,
                description TEXT,
                is_visible INTEGER DEFAULT 1,
                display_order INTEGER,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
            )
        ");
        
        // Insert default data
        $this->insertDefaultData();
    }
    
    /**
     * Insert default data into the tables
     */
    private function insertDefaultData() {
        // Create default resume
        $this->db->exec("
            INSERT INTO resumes (name, description, is_default) 
            VALUES ('Default Resume', 'Main resume version', 1)
        ");
        
        $resumeId = $this->db->lastInsertId();
        
        // Default personal info
        $this->db->exec("
            INSERT INTO personal_info (resume_id, name, contact_info) 
            VALUES ($resumeId, 'Your Name', 'Phone | Email | GitHub | LinkedIn')
        ");
        
        // Default summary
        $this->db->exec("
            INSERT INTO summary (resume_id, content) 
            VALUES ($resumeId, 'Professional with experience in web development, data analysis, and project management. Strong technical skills combined with excellent communication abilities.')
        ");
        
        // Default education
        $this->db->exec("
            INSERT INTO education (resume_id, degree, institution, location, date_range, display_order) 
            VALUES 
            ($resumeId, 'Bachelor of Science in Computer Science', 'University Name', 'City, Country', 'Month Year - Month Year', 1)
        ");
        
        // Default skill categories
        $this->db->exec("
            INSERT INTO skill_categories (resume_id, name, display_order) 
            VALUES 
            ($resumeId, 'Programming Languages', 1),
            ($resumeId, 'Technologies', 2),
            ($resumeId, 'Soft Skills', 3)
        ");
        
        // Get the IDs of the created categories
        $categories = $this->db->query("SELECT id FROM skill_categories WHERE resume_id = ? ORDER BY display_order", [$resumeId]);
        $catIds = array_column($categories, 'id');
        
        // Default skills
        if (count($catIds) >= 3) {
            $this->db->exec("
                INSERT INTO skills (resume_id, category_id, name, display_order) 
                VALUES 
                ($resumeId, {$catIds[0]}, 'JavaScript', 1),
                ($resumeId, {$catIds[0]}, 'PHP', 2),
                ($resumeId, {$catIds[0]}, 'Python', 3),
                ($resumeId, {$catIds[1]}, 'React', 1),
                ($resumeId, {$catIds[1]}, 'Node.js', 2),
                ($resumeId, {$catIds[2]}, 'Leadership', 1),
                ($resumeId, {$catIds[2]}, 'Communication', 2)
            ");
        }
        
        // Default experience
        $this->db->exec("
            INSERT INTO experience (resume_id, job_title, company, location, date_range, display_order) 
            VALUES 
            ($resumeId, 'Software Developer', 'Company Name', 'City, Country', 'Month Year - Present', 1)
        ");
        
        // Get the ID of the created experience
        $exp = $this->db->querySingle("SELECT id FROM experience WHERE resume_id = ? LIMIT 1", [$resumeId]);
        
        if ($exp) {
            // Default job accomplishments
            $this->db->exec("
                INSERT INTO job_accomplishments (resume_id, experience_id, description, display_order) 
                VALUES 
                ($resumeId, {$exp['id']}, 'Developed responsive web applications using modern JavaScript frameworks', 1),
                ($resumeId, {$exp['id']}, 'Collaborated with cross-functional teams to implement new features', 2)
            ");
        }
        
        // Default projects
        $this->db->exec("
            INSERT INTO projects (resume_id, title, technologies, link, description, display_order) 
            VALUES 
            ($resumeId, 'Project Name', 'React, Node.js, MongoDB', 'https://github.com/username/project', 'A web application that helps users track their daily activities and provides insightful analytics.', 1)
        ");
    }
    
    /**
     * Execute a query and return all results
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute a query and return a single result
     */
    public function querySingle($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute a query that doesn't return results (INSERT, UPDATE, DELETE)
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            die("Query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get the last inserted ID
     */
    public function lastInsertId() {
        return $this->db->lastInsertId();
    }
    
    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit a transaction
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Rollback a transaction
     */
    public function rollback() {
        return $this->db->rollBack();
    }
    
    /**
     * Validate input data
     */
    public function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            if (!empty($value)) {
                if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                    $errors[$field] = ucfirst($field) . ' must be at least ' . $rule['min_length'] . ' characters';
                }
                
                if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                    $errors[$field] = ucfirst($field) . ' must not exceed ' . $rule['max_length'] . ' characters';
                }
                
                if (isset($rule['email']) && $rule['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = ucfirst($field) . ' must be a valid email address';
                }
                
                if (isset($rule['url']) && $rule['url'] && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[$field] = ucfirst($field) . ' must be a valid URL';
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Get default resume ID
     */
    public function getDefaultResumeId() {
        $default = $this->querySingle("SELECT id FROM resumes WHERE is_default = 1 LIMIT 1");
        if ($default) {
            return $default['id'];
        }
        
        // If no default, get the first resume
        $first = $this->querySingle("SELECT id FROM resumes ORDER BY created_at ASC LIMIT 1");
        return $first ? $first['id'] : null;
    }
    
    /**
     * Set default resume
     */
    public function setDefaultResume($resumeId) {
        $this->beginTransaction();
        try {
            $this->execute("UPDATE resumes SET is_default = 0");
            $this->execute("UPDATE resumes SET is_default = 1 WHERE id = ?", [$resumeId]);
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }
    
    /**
     * Track resume analytics
     */
    public function trackAnalytics($resumeId, $actionType, $userAgent = null, $ipAddress = null) {
        try {
            $this->execute(
                "INSERT INTO resume_analytics (resume_id, action_type, user_agent, ip_address) VALUES (?, ?, ?, ?)",
                [$resumeId, $actionType, $userAgent, $ipAddress]
            );
            
            // Update counters
            if ($actionType === 'view') {
                $this->execute(
                    "UPDATE resumes SET view_count = view_count + 1, last_viewed_at = CURRENT_TIMESTAMP WHERE id = ?",
                    [$resumeId]
                );
            } elseif ($actionType === 'download') {
                $this->execute(
                    "UPDATE resumes SET download_count = download_count + 1 WHERE id = ?",
                    [$resumeId]
                );
            }
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log("Analytics tracking failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get resume statistics
     */
    public function getResumeStats($resumeId = null) {
        if ($resumeId) {
            return $this->querySingle(
                "SELECT view_count, download_count, last_viewed_at FROM resumes WHERE id = ?",
                [$resumeId]
            );
        } else {
            return $this->querySingle(
                "SELECT 
                    COUNT(*) as total_resumes,
                    SUM(view_count) as total_views,
                    SUM(download_count) as total_downloads,
                    MAX(last_viewed_at) as last_activity
                FROM resumes"
            );
        }
    }
    
    /**
     * Duplicate resume with all related data
     */
    public function duplicateResume($sourceResumeId, $newName = null) {
        $this->beginTransaction();
        try {
            // Get source resume
            $sourceResume = $this->querySingle("SELECT * FROM resumes WHERE id = ?", [$sourceResumeId]);
            if (!$sourceResume) {
                throw new Exception("Source resume not found");
            }
            
            $newName = $newName ?: $sourceResume['name'] . ' (Copy)';
            
            // Create new resume
            $this->execute(
                "INSERT INTO resumes (name, description, template_id) VALUES (?, ?, ?)",
                [$newName, $sourceResume['description'], $sourceResume['template_id']]
            );
            $newResumeId = $this->lastInsertId();
            
            // Copy all related data
            $this->copyResumeData($sourceResumeId, $newResumeId);
            
            $this->commit();
            return $newResumeId;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Copy all resume data from source to target
     */
    private function copyResumeData($sourceResumeId, $targetResumeId) {
        // Copy personal info
        $personalInfo = $this->querySingle("SELECT * FROM personal_info WHERE resume_id = ?", [$sourceResumeId]);
        if ($personalInfo) {
            $this->execute(
                "INSERT INTO personal_info (resume_id, name, headline, contact_info) VALUES (?, ?, ?, ?)",
                [$targetResumeId, $personalInfo['name'], $personalInfo['headline'], $personalInfo['contact_info']]
            );
        }
        
        // Copy personal info details
        $personalDetails = $this->query("SELECT * FROM personal_info_details WHERE resume_id = ?", [$sourceResumeId]);
        foreach ($personalDetails as $detail) {
            $this->execute(
                "INSERT INTO personal_info_details (resume_id, detail_name, detail_icon, detail_info, display_order) VALUES (?, ?, ?, ?, ?)",
                [$targetResumeId, $detail['detail_name'], $detail['detail_icon'], $detail['detail_info'], $detail['display_order']]
            );
        }
        
        // Copy summary
        $summary = $this->querySingle("SELECT * FROM summary WHERE resume_id = ?", [$sourceResumeId]);
        if ($summary) {
            $this->execute(
                "INSERT INTO summary (resume_id, content) VALUES (?, ?)",
                [$targetResumeId, $summary['content']]
            );
        }
        
        // Copy education
        $education = $this->query("SELECT * FROM education WHERE resume_id = ?", [$sourceResumeId]);
        foreach ($education as $edu) {
            $this->execute(
                "INSERT INTO education (resume_id, degree, institution, location, date_range, description, achievements, is_visible, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$targetResumeId, $edu['degree'], $edu['institution'], $edu['location'], $edu['date_range'], $edu['description'], $edu['achievements'], $edu['is_visible'], $edu['display_order']]
            );
        }
        
        // Copy skill categories and skills
        $categories = $this->query("SELECT * FROM skill_categories WHERE resume_id = ?", [$sourceResumeId]);
        foreach ($categories as $cat) {
            $this->execute(
                "INSERT INTO skill_categories (resume_id, name, display_order, is_visible) VALUES (?, ?, ?, ?)",
                [$targetResumeId, $cat['name'], $cat['display_order'], $cat['is_visible']]
            );
            $newCatId = $this->lastInsertId();
            
            // Copy skills for this category
            $skills = $this->query("SELECT * FROM skills WHERE resume_id = ? AND category_id = ?", [$sourceResumeId, $cat['id']]);
            foreach ($skills as $skill) {
                $this->execute(
                    "INSERT INTO skills (resume_id, category_id, name, proficiency, is_visible, display_order) VALUES (?, ?, ?, ?, ?, ?)",
                    [$targetResumeId, $newCatId, $skill['name'], $skill['proficiency'], $skill['is_visible'], $skill['display_order']]
                );
            }
        }
        
        // Copy experience and accomplishments
        $experiences = $this->query("SELECT * FROM experience WHERE resume_id = ?", [$sourceResumeId]);
        foreach ($experiences as $exp) {
            $this->execute(
                "INSERT INTO experience (resume_id, job_title, company, location, date_range, is_visible, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$targetResumeId, $exp['job_title'], $exp['company'], $exp['location'], $exp['date_range'], $exp['is_visible'], $exp['display_order']]
            );
            $newExpId = $this->lastInsertId();
            
            // Copy accomplishments for this experience
            $accomplishments = $this->query("SELECT * FROM job_accomplishments WHERE resume_id = ? AND experience_id = ?", [$sourceResumeId, $exp['id']]);
            foreach ($accomplishments as $acc) {
                $this->execute(
                    "INSERT INTO job_accomplishments (resume_id, experience_id, description, display_order, is_visible) VALUES (?, ?, ?, ?, ?)",
                    [$targetResumeId, $newExpId, $acc['description'], $acc['display_order'], $acc['is_visible']]
                );
            }
        }
        
        // Copy projects
        $projects = $this->query("SELECT * FROM projects WHERE resume_id = ?", [$sourceResumeId]);
        foreach ($projects as $proj) {
            $this->execute(
                "INSERT INTO projects (resume_id, title, technologies, link, description, details, is_visible, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$targetResumeId, $proj['title'], $proj['technologies'], $proj['link'], $proj['description'], $proj['details'], $proj['is_visible'], $proj['display_order']]
            );
        }
    }
}
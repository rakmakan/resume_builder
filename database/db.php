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
        $dbFile = __DIR__ . '/resume.sqlite';
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
                contact_info TEXT NOT NULL,
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
}
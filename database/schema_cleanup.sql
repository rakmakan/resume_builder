-- Resume Builder Database Schema Cleanup
-- This script cleans up redundant tables and improves the schema

-- Drop redundant tables
DROP TABLE IF EXISTS company;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS resume;

-- Add missing indexes for better performance
CREATE INDEX IF NOT EXISTS idx_personal_info_resume_id ON personal_info(resume_id);
CREATE INDEX IF NOT EXISTS idx_personal_info_details_resume_id ON personal_info_details(resume_id);
CREATE INDEX IF NOT EXISTS idx_summary_resume_id ON summary(resume_id);
CREATE INDEX IF NOT EXISTS idx_education_resume_id ON education(resume_id);
CREATE INDEX IF NOT EXISTS idx_skill_categories_resume_id ON skill_categories(resume_id);
CREATE INDEX IF NOT EXISTS idx_skills_resume_id ON skills(resume_id);
CREATE INDEX IF NOT EXISTS idx_skills_category_id ON skills(category_id);
CREATE INDEX IF NOT EXISTS idx_experience_resume_id ON experience(resume_id);
CREATE INDEX IF NOT EXISTS idx_job_accomplishments_resume_id ON job_accomplishments(resume_id);
CREATE INDEX IF NOT EXISTS idx_job_accomplishments_experience_id ON job_accomplishments(experience_id);
CREATE INDEX IF NOT EXISTS idx_projects_resume_id ON projects(resume_id);

-- Add missing columns to existing tables
ALTER TABLE personal_info ADD COLUMN headline TEXT;
ALTER TABLE education ADD COLUMN achievements TEXT;
ALTER TABLE projects ADD COLUMN details TEXT;

-- Ensure personal_info_details table exists (it might be missing in some setups)
CREATE TABLE IF NOT EXISTS personal_info_details (
    id INTEGER PRIMARY KEY,
    resume_id INTEGER NOT NULL,
    detail_name TEXT NOT NULL,
    detail_icon TEXT,
    detail_info TEXT NOT NULL,
    display_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
);

-- Add resume templates table
CREATE TABLE IF NOT EXISTS resume_templates (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    template_data TEXT, -- JSON data for the template
    is_active INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add resume analytics table
CREATE TABLE IF NOT EXISTS resume_analytics (
    id INTEGER PRIMARY KEY,
    resume_id INTEGER NOT NULL,
    action_type TEXT NOT NULL, -- 'view', 'download', 'edit'
    action_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_agent TEXT,
    ip_address TEXT,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
);

-- Update resumes table to ensure proper structure
ALTER TABLE resumes ADD COLUMN template_id INTEGER;
ALTER TABLE resumes ADD COLUMN last_viewed_at TIMESTAMP;
ALTER TABLE resumes ADD COLUMN view_count INTEGER DEFAULT 0;
ALTER TABLE resumes ADD COLUMN download_count INTEGER DEFAULT 0;

-- Insert default templates
INSERT OR IGNORE INTO resume_templates (id, name, description, template_data) VALUES 
(1, 'Professional', 'Clean and professional template suitable for corporate environments', '{}'),
(2, 'Creative', 'Modern and creative template for design and creative roles', '{}'),
(3, 'Technical', 'Technical-focused template for software engineers and developers', '{}');
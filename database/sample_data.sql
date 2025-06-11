-- Sample data for Resume Builder
-- This script adds sample data to demonstrate the application

-- First, let's add some sample data to resume ID 6 (Resume for Chubb)
-- Add experience
INSERT OR IGNORE INTO experience (resume_id, job_title, company, location, date_range, is_visible, display_order) VALUES 
(6, 'Senior AI Software Engineer', 'TechCorp Inc.', 'San Francisco, CA', 'Jan 2022 - Present', 1, 1),
(6, 'AI Software Engineer', 'DataSoft Solutions', 'New York, NY', 'Jun 2020 - Dec 2021', 1, 2),
(6, 'Software Developer', 'StartupXYZ', 'Austin, TX', 'Aug 2018 - May 2020', 1, 3);

-- Add accomplishments for the experiences
INSERT OR IGNORE INTO job_accomplishments (resume_id, experience_id, description, display_order, is_visible) VALUES 
(6, (SELECT id FROM experience WHERE resume_id = 6 AND job_title = 'Senior AI Software Engineer' LIMIT 1), 'Led development of AI-powered recommendation system that increased user engagement by 40%', 1, 1),
(6, (SELECT id FROM experience WHERE resume_id = 6 AND job_title = 'Senior AI Software Engineer' LIMIT 1), 'Optimized machine learning models resulting in 30% reduction in inference latency', 2, 1),
(6, (SELECT id FROM experience WHERE resume_id = 6 AND job_title = 'Senior AI Software Engineer' LIMIT 1), 'Mentored team of 5 junior developers and established best practices for ML deployment', 3, 1),
(6, (SELECT id FROM experience WHERE resume_id = 6 AND job_title = 'AI Software Engineer' LIMIT 1), 'Built and deployed computer vision models for automated quality control', 1, 1),
(6, (SELECT id FROM experience WHERE resume_id = 6 AND job_title = 'AI Software Engineer' LIMIT 1), 'Collaborated with data science team to implement MLOps pipeline using Docker and Kubernetes', 2, 1),
(6, (SELECT id FROM experience WHERE resume_id = 6 AND job_title = 'Software Developer' LIMIT 1), 'Developed RESTful APIs serving 1M+ requests per day with 99.9% uptime', 1, 1),
(6, (SELECT id FROM experience WHERE resume_id = 6 AND job_title = 'Software Developer' LIMIT 1), 'Implemented automated testing framework reducing bug reports by 60%', 2, 1);

-- Add education
INSERT OR IGNORE INTO education (resume_id, degree, institution, location, date_range, description, achievements, is_visible, display_order) VALUES 
(6, 'Master of Science in Computer Science', 'Stanford University', 'Stanford, CA', '2016 - 2018', 'Specialization in Artificial Intelligence and Machine Learning', 'GPA: 3.8/4.0, Dean''s List', 1, 1),
(6, 'Bachelor of Science in Computer Engineering', 'University of California, Berkeley', 'Berkeley, CA', '2012 - 2016', 'Focus on Software Engineering and Data Structures', 'Magna Cum Laude, Phi Beta Kappa', 1, 2);

-- Add skill categories
INSERT OR IGNORE INTO skill_categories (resume_id, name, display_order, is_visible) VALUES 
(6, 'Programming Languages', 1, 1),
(6, 'AI/ML Frameworks', 2, 1),
(6, 'Cloud & DevOps', 3, 1),
(6, 'Databases', 4, 1);

-- Add skills
INSERT OR IGNORE INTO skills (resume_id, category_id, name, proficiency, is_visible, display_order) VALUES 
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'Programming Languages' LIMIT 1), 'Python', 5, 1, 1),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'Programming Languages' LIMIT 1), 'JavaScript', 4, 1, 2),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'Programming Languages' LIMIT 1), 'Java', 4, 1, 3),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'Programming Languages' LIMIT 1), 'C++', 3, 1, 4),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'AI/ML Frameworks' LIMIT 1), 'PyTorch', 5, 1, 1),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'AI/ML Frameworks' LIMIT 1), 'TensorFlow', 4, 1, 2),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'AI/ML Frameworks' LIMIT 1), 'Hugging Face Transformers', 5, 1, 3),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'AI/ML Frameworks' LIMIT 1), 'Scikit-learn', 4, 1, 4),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'Cloud & DevOps' LIMIT 1), 'AWS', 4, 1, 1),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'Cloud & DevOps' LIMIT 1), 'Docker', 5, 1, 2),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'Cloud & DevOps' LIMIT 1), 'Kubernetes', 3, 1, 3),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'Cloud & DevOps' LIMIT 1), 'Git', 5, 1, 4),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'Databases' LIMIT 1), 'PostgreSQL', 4, 1, 1),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'Databases' LIMIT 1), 'MongoDB', 3, 1, 2),
(6, (SELECT id FROM skill_categories WHERE resume_id = 6 AND name = 'Databases' LIMIT 1), 'Redis', 3, 1, 3);

-- Add projects
INSERT OR IGNORE INTO projects (resume_id, title, technologies, link, description, details, is_visible, display_order) VALUES 
(6, 'AI-Powered Code Review Assistant', 'Python, PyTorch, Transformers, FastAPI', 'https://github.com/rakmakan/ai-code-review', 'An intelligent code review tool that uses large language models to provide automated feedback on code quality, security vulnerabilities, and best practices.', 'Built using GPT-based models fine-tuned on code review data', 1, 1),
(6, 'Real-time Sentiment Analysis Dashboard', 'Python, React, WebSocket, TensorFlow', 'https://github.com/rakmakan/sentiment-dashboard', 'A real-time dashboard that analyzes social media sentiment using streaming data and machine learning models.', 'Processes 10k+ tweets per minute with sub-second latency', 1, 2),
(6, 'Computer Vision Quality Control System', 'Python, OpenCV, PyTorch, Docker', 'https://github.com/rakmakan/cv-quality-control', 'An automated quality control system for manufacturing that uses computer vision to detect defects in products.', 'Achieved 99.2% accuracy in defect detection', 1, 3);

-- Add some analytics data to make the dashboard more interesting
INSERT OR IGNORE INTO resume_analytics (resume_id, action_type, action_timestamp, user_agent, ip_address) VALUES 
(6, 'view', datetime('now', '-1 hour'), 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '192.168.1.100'),
(6, 'view', datetime('now', '-2 hours'), 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', '192.168.1.101'),
(6, 'download', datetime('now', '-3 hours'), 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '192.168.1.100'),
(6, 'view', datetime('now', '-1 day'), 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X)', '192.168.1.102'),
(6, 'download', datetime('now', '-2 days'), 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '192.168.1.103');

-- Update view and download counts
UPDATE resumes SET 
    view_count = (SELECT COUNT(*) FROM resume_analytics WHERE resume_id = 6 AND action_type = 'view'),
    download_count = (SELECT COUNT(*) FROM resume_analytics WHERE resume_id = 6 AND action_type = 'download'),
    last_viewed_at = (SELECT MAX(action_timestamp) FROM resume_analytics WHERE resume_id = 6)
WHERE id = 6;

-- Set resume 6 as default for demo purposes
UPDATE resumes SET is_default = 0;
UPDATE resumes SET is_default = 1 WHERE id = 6;
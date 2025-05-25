<?php
require_once dirname(__FILE__) . '/db.php';

function updateDatabaseSchema() {
    $db = ResumeDB::getInstance();
    
    try {
        echo "Starting database update...\n";
        
        // Drop the table if it exists to ensure a clean state
        $db->execute("DROP TABLE IF EXISTS personal_info_details");
        echo "Dropped existing table if it existed...\n";
        
        // Create personal_info_details table
        $db->execute("
            CREATE TABLE personal_info_details (
                personal_detail_id INTEGER PRIMARY KEY AUTOINCREMENT,
                resume_id INTEGER NOT NULL,
                detail_name TEXT NOT NULL,
                detail_icon TEXT,
                detail_info TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
            )
        ");

        // Check if the table was created successfully
        $tableExists = $db->querySingle("
            SELECT name FROM sqlite_master 
            WHERE type='table' AND name='personal_info_details'
        ");

        if ($tableExists) {
            echo "personal_info_details table created successfully!\n";
            
            // Migrate existing data from personal_info table
            $existingData = $db->query("SELECT resume_id, contact_info FROM personal_info");
            
            foreach ($existingData as $row) {
                $contacts = explode(',', $row['contact_info']);
                foreach ($contacts as $contact) {
                    $contact = trim($contact);
                    if (empty($contact)) continue;

                    $detailName = '';
                    $detailIcon = '';
                    
                    // Remove any "Label:" prefixes
                    $contact = preg_replace('/^(Github|LinkedIn|Phone|Email)\s*:?\s*/i', '', $contact);
                    $contact = trim($contact);
                    
                    if (empty($contact)) continue;

                    if (strpos($contact, '@') !== false) {
                        $detailName = 'Email';
                        $detailIcon = 'fas fa-envelope';
                    } else if (preg_match('/^\+?[\d\s()\-]+$/', $contact)) {
                        $detailName = 'Phone';
                        $detailIcon = 'fas fa-phone';
                    } else if (strpos($contact, 'github.com') !== false) {
                        $detailName = 'GitHub';
                        $detailIcon = 'fab fa-github';
                    } else if (strpos($contact, 'linkedin.com') !== false) {
                        $detailName = 'LinkedIn';
                        $detailIcon = 'fab fa-linkedin';
                    }

                    if ($detailName) {
                        $db->execute(
                            "INSERT INTO personal_info_details (resume_id, detail_name, detail_icon, detail_info) 
                             VALUES (?, ?, ?, ?)",
                            [$row['resume_id'], $detailName, $detailIcon, $contact]
                        );
                    }
                }
            }
            
            echo "Existing data migrated successfully!\n";
        } else {
            echo "Error: Failed to create personal_info_details table!\n";
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// Run the update
updateDatabaseSchema();

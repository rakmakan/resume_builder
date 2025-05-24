// Skills Manager JavaScript Module
class SkillsManager {
    constructor() {
        this.hasUnsavedChanges = false;
        this.saveBar = document.getElementById('saveBar');
        this.init();
    }

    init() {
        this.initSkillInput();
        this.initCategorySuggestions();
        this.initSkillTagListeners();
        this.initCategoryEditListeners();
        this.initSaveHandler();
    }

    toggleSaveBar(show) {
        this.hasUnsavedChanges = show;
        this.saveBar.style.display = show ? 'block' : 'none';
    }

    showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    initSkillInput() {
        document.querySelectorAll('.skill-input').forEach(input => {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.addSkill(input.value, input.dataset.categoryId);
                    input.value = '';
                }
            });
        });
    }

    initCategorySuggestions() {
        document.querySelectorAll('.category-suggestion').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('categoryName').value = btn.textContent.trim();
            });
        });
    }

    initSkillTagListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.delete-skill-btn')) {
                this.handleSkillDelete(e);
            }
        });

        document.addEventListener('dblclick', (e) => {
            if (e.target.matches('.skill-tag')) {
                this.handleSkillEdit(e);
            }
        });
    }

    initCategoryEditListeners() {
        document.querySelectorAll('.edit-category-btn').forEach(btn => {
            btn.addEventListener('click', () => this.handleCategoryEdit(btn));
        });
    }

    initSaveHandler() {
        document.getElementById('saveAllChanges').addEventListener('click', (e) => this.handleSaveAll(e));
    }

    async addSkill(skillName, categoryId) {
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
                this.toggleSaveBar(true);
                this.showToast(`Skill "${skillName}" added successfully`);
            }
        } catch (error) {
            console.error('Error adding skill:', error);
            this.showToast('Error adding skill', 'danger');
        }
    }

    async handleSkillDelete(e) {
        const skillTag = e.target.closest('.skill-tag');
        const skillName = skillTag.childNodes[0].textContent.trim();
        
        if (confirm(`Delete skill "${skillName}"?`)) {
            const formData = new FormData();
            formData.append('action', 'delete_skill');
            formData.append('skill_id', skillTag.dataset.skillId);
            
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    skillTag.remove();
                    this.toggleSaveBar(true);
                    this.showToast(`Skill "${skillName}" removed`);
                }
            } catch (error) {
                console.error('Error deleting skill:', error);
                this.showToast('Error deleting skill', 'danger');
            }
        }
    }

    handleSkillEdit(e) {
        const skillTag = e.target;
        const skillText = skillTag.childNodes[0];
        const currentName = skillText.textContent.trim();
        
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'skill-edit-input';
        input.value = currentName;
        
        skillTag.insertBefore(input, skillText);
        skillText.style.display = 'none';
        input.focus();
        
        const handleBlur = async () => {
            const newName = input.value.trim();
            if (newName && newName !== currentName) {
                try {
                    const formData = new FormData();
                    formData.append('action', 'update_skill');
                    formData.append('skill_id', skillTag.dataset.skillId);
                    formData.append('skill_name', newName);
                    
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (response.ok) {
                        skillText.textContent = newName;
                        this.toggleSaveBar(true);
                        this.showToast(`Skill updated to "${newName}"`);
                    }
                } catch (error) {
                    console.error('Error updating skill:', error);
                    this.showToast('Error updating skill', 'danger');
                }
            }
            
            skillText.style.display = '';
            input.remove();
        };
        
        input.addEventListener('blur', handleBlur);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur();
            }
        });
    }

    handleCategoryEdit(btn) {
        const container = btn.closest('.card-header').querySelector('.category-name-container');
        const nameElement = container.querySelector('.category-name');
        const currentName = nameElement.textContent.trim();
        
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm';
        input.value = currentName;
        
        container.replaceChild(input, nameElement);
        input.focus();
        
        const handleBlur = async () => {
            const newName = input.value.trim();
            if (newName && newName !== currentName) {
                try {
                    const formData = new FormData();
                    formData.append('action', 'update_category');
                    formData.append('category_id', nameElement.dataset.categoryId);
                    formData.append('category_name', newName);
                    
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (response.ok) {
                        nameElement.textContent = newName;
                        this.toggleSaveBar(true);
                        this.showToast(`Category renamed to "${newName}"`);
                    }
                } catch (error) {
                    console.error('Error updating category:', error);
                    this.showToast('Error updating category', 'danger');
                }
            }
            container.replaceChild(nameElement, input);
        };
        
        input.addEventListener('blur', handleBlur);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur();
            }
        });
    }

    async handleSaveAll(e) {
        const saveBtn = e.target;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        
        try {
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
                this.toggleSaveBar(false);
                this.showToast('All changes saved successfully');
            }
        } catch (error) {
            console.error('Error saving changes:', error);
            this.showToast('Error saving changes', 'danger');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save All Changes';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.skillsManager = new SkillsManager();
});

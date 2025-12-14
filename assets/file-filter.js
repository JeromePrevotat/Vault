const filterForm = document.querySelector('[name="file_download"]');
const categoryCheckboxes = filterForm.querySelectorAll('[name="file_download[category][]"]');
const filesListContainer = document.getElementById('file-container');
const filterBtn = document.getElementById('filter-button');
const categorySelect = document.getElementById('file_download_category');
const selectedCategories = Array.from(categorySelect.selectedOptions).map(option => option.value);

filterBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    const selectedIds = Array.from(categorySelect.selectedOptions).map(opt => opt.value);
    
    const response = await fetch('/api/files/filter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ categories: selectedIds })
    });
    
    const json = await response.json();
    if (json.success) {
        updateFilesList(json.files);
    }
});

function updateFilesList(files) {
    if (files.length === 0) {
        filesListContainer.innerHTML = '<p>No files found.</p>';
        return;
    }
    
    filesListContainer.innerHTML = files.map(file => `
        <div class="mb-2">
            <input class="form-check-input" type="radio" name="selected-file" id="${file.id}" value="${file.id}">
            <label class="form-check-label" for="${file.id}">${file.filename}</label>
        </div>
    `).join('');
}
import { encryptFile, arrayBufferToBase64 } from './file-encryption.js';

const uploadButton = document.getElementById('upload-button');
const file_input = document.getElementById('file_upload_file');
const category_input = document.getElementById('file_upload_category');
const secretKey_input = document.getElementById('secretKey');

function uploadFieldValidation(file, category, secretKey){
  let errors = [];
  if(!file) errors.push('File is required');
  if(!category || category.length === 0) errors.push('Category is required');
  if(!secretKey) errors.push('Secret Key is required');
  return errors;
}

async function uploadFile(event){
  event.preventDefault();
  const file = file_input.files[0];
  const category = Array.from(category_input.selectedOptions).map(option => option.value);
  const secretKey = secretKey_input.value.trim();
  const errors = uploadFieldValidation(file, category, secretKey);
  if(errors.length > 0){
    alert(errors.join('\n'));
    return;
  }
  console.log('Encrypting File...');
  try {
    const { encryptedData, iv, salt } = await encryptFile(file, secretKey);

    console.log('Uploading File...');
    const fileBlob = new Blob([encryptedData], { type: 'application/octet-stream' });
    const formData = buildFormData(fileBlob, iv, salt, file, category);
    const response = await fetch('/', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) throw new Error('Upload failed: ' + response.statusText);
    const result = await response.json();
    console.log('File Uploaded:', result);
    alert('File uploaded successfully');
  } catch (error) {
    console.error('Error encrypting file:', error);
    alert('Error encrypting file: ' + error.message);
  }
}

function buildFormData(fileBlob, iv, salt, file, category){
  const formData = new FormData();
  formData.append('file', fileBlob, file.name);
  formData.append('iv', arrayBufferToBase64(iv));
  formData.append('salt', arrayBufferToBase64(salt));
  formData.append('fileName', file.name);
  category.forEach(catId => { formData.append('category[]', catId); });
  return formData;
}

function addEventListeners(){
  uploadButton.addEventListener('click', uploadFile);
}

addEventListeners();
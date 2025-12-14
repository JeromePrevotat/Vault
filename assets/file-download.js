import { decryptFile, base64ToArrayBuffer } from './file-encryption.js';
import { showFlash } from './msg-flash.js';

const downloadBtn = document.getElementById('download-button');
const secretKeyInput = document.getElementById('file_download_secretKey');

async function downloadFile(event) {
  event.preventDefault();
  const selectedFileInput = document.querySelector('input[name="selected-file"]:checked');
  if (!selectedFileInput) {
    showFlash('Please select a file to download.', 'warning');
    return;
  }

  const fileId = selectedFileInput.value;
  const secretKey = secretKeyInput?.value.trim();
  if (!secretKey) {
    showFlash('Please enter the secret key.', 'warning');
    return;
  }

  try {
    const response = await fetch(`/download/${fileId}`,
      { method: 'POST' }
    );
    if (!response.ok) throw new Error('Download failed: ' + response.statusText);
    const data = await response.json();
    if (!data.success) {
      showFlash('Error: ' + data.message, 'error');
      return;
    }
    const encryptedData = base64ToArrayBuffer(data.fileContent);
    const iv = base64ToArrayBuffer(data.iv);
    const salt = base64ToArrayBuffer(data.salt);
    let dataToDownload = encryptedData;

    try {
      const decryptedData = await decryptFile(encryptedData, secretKey, iv, salt);
      dataToDownload = decryptedData;
    } catch (error) {}

    const blob = new Blob([dataToDownload]);

    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = data.fileName;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    showFlash('File downloaded successfully.', 'success');
  } catch (error) {
    console.error('Error downloading file:', error);
    showFlash('Error downloading file: ' + error.message, 'error');
  }
}

downloadBtn?.addEventListener('click', downloadFile);
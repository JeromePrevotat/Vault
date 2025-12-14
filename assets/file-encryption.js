export function generateRandomBytes(length) {
  return crypto.getRandomValues(new Uint8Array(length));
}

export function arrayBufferToBase64(buffer){
  const bytes = new Uint8Array(buffer);
  let binary = '';
  for (let i = 0; i < bytes.length; i++) {
    binary += String.fromCharCode(bytes[i]);
  }
  return btoa(binary);
}

export async function deriveKey(secretKey, salt) {
  const keyMaterial = await crypto.subtle.importKey(
    'raw',
    new TextEncoder().encode(secretKey),
    'PBKDF2',
    false,
    ['deriveKey']
  );
  const cryptoKey = await crypto.subtle.deriveKey(
    {
      name: 'PBKDF2',
      salt: salt,
      iterations: 100000,
      hash: 'SHA-256'
    },
    keyMaterial,
    {
      name: 'AES-GCM',
      length: 256
    },
    false,
    ['encrypt']
  );

  return cryptoKey;
}

export async function encryptFile(file, secretKey) {
  const salt = generateRandomBytes(32);
  const iv = generateRandomBytes(12);
  const fileData = await file.arrayBuffer();
  const cryptoKey = await deriveKey(secretKey, salt);
  const encryptedData = await crypto.subtle.encrypt(
    {
      name: 'AES-GCM',
      iv: iv
    },
    cryptoKey,
    fileData
  );
  return { encryptedData, iv, salt };
}

// DECRYPTION
export function base64ToArrayBuffer(base64) {
  const binary = atob(base64);
  const bytes = new Uint8Array(binary.length);
  for (let i = 0; i < binary.length; i++) {
    bytes[i] = binary.charCodeAt(i);
  }
  return bytes.buffer;
}

export async function decryptFile(encryptedData, secretKey, iv, salt) {
  const keyMaterial = await crypto.subtle.importKey(
    'raw',
    new TextEncoder().encode(secretKey),
    'PBKDF2',
    false,
    ['deriveKey']
  );
  
  const cryptoKey = await crypto.subtle.deriveKey(
    {
      name: 'PBKDF2',
      salt: salt,
      iterations: 100000,
      hash: 'SHA-256'
    },
    keyMaterial,
    {
      name: 'AES-GCM',
      length: 256
    },
    false,
    ['decrypt']
  );
  
  const decryptedData = await crypto.subtle.decrypt(
    {
      name: 'AES-GCM',
      iv: iv
    },
    cryptoKey,
    encryptedData
  );
  
  return decryptedData;
}
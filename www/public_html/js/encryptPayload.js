// helpers
function pemToArrayBuffer(pem) {
    // remove headers/footers e newlines
    const b64 = pem.replace(/-----BEGIN PUBLIC KEY-----/, '')
                   .replace(/-----END PUBLIC KEY-----/, '')
                   .replace(/\s+/g, '');
    const binary = atob(b64);
    const len = binary.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) bytes[i] = binary.charCodeAt(i);
    return bytes.buffer;
  }
  
  function arrayBufferToBase64(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.byteLength; i++) {
      binary += String.fromCharCode(bytes[i]);
    }
    return btoa(binary);
  }
  
  async function encryptPasswordWithPublicKey(publicPem, password) {
    const spki = pemToArrayBuffer(publicPem);
    const key = await window.crypto.subtle.importKey(
      'spki',
      spki,
      {
        name: 'RSA-OAEP',
        hash: { name: 'SHA-1' },
      },
      false,
      ['encrypt']
    );
  
    const enc = new TextEncoder();
    const encoded = enc.encode(password);
    const cipherBuffer = await window.crypto.subtle.encrypt(
      { name: 'RSA-OAEP' },
      key,
      encoded
    );
  
    return arrayBufferToBase64(cipherBuffer); // base64 para enviar via JSON
  }
  
  // uso: função de login
  async function login(username, password) {
    // 1) buscar chave pública (HTTPS)
    const res = await fetch('/public-key'); // deve ser HTTPS
    if (!res.ok) throw new Error('Não foi possível obter chave pública');
    const publicPem = await res.text();
  
    // 2) criptografar senha
    const encryptedPasswordBase64 = await encryptPasswordWithPublicKey(publicPem, password);
  
    // 3) enviar para servidor
    const loginRes = await fetch('/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username,
        encrypted_password: encryptedPasswordBase64
      })
    });
  
    return loginRes;
  }
  

let cssId = 'myCss';
let head = document.getElementsByTagName('head')[0];
let link = document.createElement('link');

link.id = cssId;
link.rel = 'stylesheet';
link.type = 'text/css';
link.href = '/css/modal-error-style.css'; // Corrigido o domínio
link.media = 'all';

head.appendChild(link);

let modal_error = document.querySelector('.modal-error');

let button_close = document.querySelector('.close-modal-error');

button_close.addEventListener('click', (event) => {
    modal_error.style.display = 'none';
});
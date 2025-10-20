document.addEventListener('DOMContentLoaded', function() {
    console.log('Login.js cargado correctamente');
    
    const loginForm = document.getElementById('loginForm');
    
    if (!loginForm) {
        console.error('No se encontró el formulario loginForm');
        return;
    }
    
    console.log('Formulario encontrado, agregando event listener');
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Submit interceptado');
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        // Deshabilitar botón
        submitBtn.disabled = true;
        submitBtn.textContent = 'Iniciando sesión...';
        
        console.log('Enviando datos:', {
            email: formData.get('email'),
            password: '***' // No loguear la contraseña real
        });
        
        const baseUrl = window.location.pathname.includes('WEB_ElBuenGusto') 
            ? '/php/login.php'
            : '../php/login.php';

        fetch(baseUrl, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
        .then(response => {
            console.log('Respuesta recibida, status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Texto recibido:', text);
            
            try {
                const data = JSON.parse(text);
                console.log('JSON parseado:', data);
                
                if (data.success) {
                    showMessage('¡Bienvenido! Redirigiendo...', 'success');
                    setTimeout(() => {
                        console.log('Redirigiendo a:', data.redirect);
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    showMessage(data.message || 'Error en el login', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            } catch (jsonError) {
                console.error('Error parseando JSON:', jsonError);
                console.error('Texto recibido:', text);
                showMessage('Error del servidor', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error en fetch:', error);
            showMessage('Error de conexión: ' + error.message, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
        
        return false;
    });
});

function showMessage(message, type = 'info') {
    const existingMessage = document.querySelector('.message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-family: inherit;
        z-index: 10000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        background-color: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
    `;
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        messageDiv.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 300);
    }, 3000);
}
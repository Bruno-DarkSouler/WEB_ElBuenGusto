function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    
    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = '🙈';
    } else {
        input.type = 'password';
        button.textContent = '👁';
    }
}

document.getElementById('registroForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const password = document.getElementById('registro-password').value;
    const confirmPassword = document.getElementById('registro-confirm-password').value;
    
    if (password !== confirmPassword) {
        alert('❌ Las contraseñas no coinciden');
        return;
    }
    
    if (password.length < 6) {
        alert('❌ La contraseña debe tener al menos 6 caracteres');
        return;
    }
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Registrando...';
    
    try {
        const response = await fetch('/WEB_ElBuenGusto/php/registro.php', {
            method: 'POST',
            body: formData
        });
        
        const text = await response.text();
        
        // Intentar limpiar la respuesta si tiene basura
        let cleanText = text.trim();
        
        // Buscar el primer { y el último }
        const firstBrace = cleanText.indexOf('{');
        const lastBrace = cleanText.lastIndexOf('}');
        
        if (firstBrace !== -1 && lastBrace !== -1) {
            cleanText = cleanText.substring(firstBrace, lastBrace + 1);
        }
        
        const data = JSON.parse(cleanText);
        
        if (data.success) {
            alert('✅ ' + data.message + '\n\nAhora puedes iniciar sesión.');
            this.reset();
            cerrar_modal();
            setTimeout(() => {
                abrir_login();
            }, 300);
        } else {
            alert('❌ ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Hubo un problema. Por favor intenta nuevamente.');
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = 'Crear Cuenta';
    }
});
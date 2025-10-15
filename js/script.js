function abrir_login(){
    var login = document.getElementById("login");
    var registro = document.getElementById("registro");
    login.style.display = "flex";
    registro.style.display = "none";
}

function abrir_registro(){
    var login = document.getElementById("login");
    var registro = document.getElementById("registro");
    login.style.display = "none";
    registro.style.display = "flex";
}

function cerrar_modal(){
    var login = document.getElementById("login");
    var registro = document.getElementById("registro");
    login.style.display = "none";
    registro.style.display = "none";
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    
    if (input.type === "password") {
        input.type = "text";
        button.textContent = "🙈";
    } else {
        input.type = "password";
        button.textContent = "👁";
    }
}

function mostrarMensaje(mensaje, tipo) {
    // Crear elemento de mensaje
    const mensajeDiv = document.createElement('div');
    mensajeDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        max-width: 300px;
        word-wrap: break-word;
    `;
    
    if (tipo === 'success') {
        mensajeDiv.style.backgroundColor = '#4CAF50';
    } else {
        mensajeDiv.style.backgroundColor = '#f44336';
    }
    
    mensajeDiv.textContent = mensaje;
    document.body.appendChild(mensajeDiv);
    
    // Remover mensaje después de 5 segundos
    setTimeout(() => {
        if (mensajeDiv.parentNode) {
            mensajeDiv.parentNode.removeChild(mensajeDiv);
        }
    }, 5000);
}

// Manejar formulario de login
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const registroForm = document.getElementById('registroForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(loginForm);
            
            fetch('./php/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarMensaje(data.message, 'success');
                    cerrar_modal();
                    loginForm.reset();
                    // Aquí puedes redirigir o actualizar la interfaz
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    mostrarMensaje(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensaje('Error de conexión. Intente nuevamente.', 'error');
            });
        });
    }
    
    if (registroForm) {
        registroForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(registroForm);
            
            fetch('./php/registro.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (data.success) {
                    mostrarMensaje(data.message, 'success');
                    registroForm.reset();
                    // Cambiar al modal de login después del registro exitoso
                    setTimeout(() => {
                        abrir_login();
                    }, 2000);
                } else {
                    mostrarMensaje(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensaje('Error de conexión. Intente nuevamente.', 'error');
            });
        });
    }
});
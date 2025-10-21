// Sistema de notificaciones personalizado
const NotificationSystem = {
    container: null,

    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'notification-container';
            document.body.appendChild(this.container);
        }
    },

    show(message, type = 'info', title = '', duration = 3000) {
        this.init();

        const notification = document.createElement('div');
        notification.className = `custom-notification ${type}`;

        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        const titles = {
            success: title || 'Éxito',
            error: title || 'Error',
            warning: title || 'Advertencia',
            info: title || 'Información'
        };

        notification.innerHTML = `
            <div class="notification-icon">${icons[type]}</div>
            <div class="notification-content">
                <div class="notification-title">${titles[type]}</div>
                <div class="notification-message">${message}</div>
            </div>
            <button class="notification-close" onclick="this.closest('.custom-notification').remove()">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
        `;

        this.container.appendChild(notification);

        // Auto eliminar después del tiempo especificado
        setTimeout(() => {
            notification.classList.add('slide-out');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }, duration);

        return notification;
    },

    success(message, title = '', duration = 3000) {
        return this.show(message, 'success', title, duration);
    },

    error(message, title = '', duration = 4000) {
        return this.show(message, 'error', title, duration);
    },

    warning(message, title = '', duration = 3500) {
        return this.show(message, 'warning', title, duration);
    },

    info(message, title = '', duration = 3000) {
        return this.show(message, 'info', title, duration);
    }
};

// Alias para uso más sencillo
window.notify = NotificationSystem;

// Sobrescribir alert nativo (opcional)
window.alertOriginal = window.alert;
window.alert = function(message) {
    NotificationSystem.warning(message, 'Atención');
};

// Sobrescribir confirm nativo con una versión más linda
window.confirmOriginal = window.confirm;
window.customConfirm = function(message, onConfirm, onCancel) {
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999999;
        animation: fadeIn 0.2s ease;
    `;

    const modal = document.createElement('div');
    modal.style.cssText = `
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        max-width: 400px;
        width: 90%;
        animation: scaleIn 0.3s ease;
    `;

    modal.innerHTML = `
        <div style="margin-bottom: 20px;">
            <div style="font-size: 20px; margin-bottom: 8px; color: #f59e0b;">⚠</div>
            <div style="font-weight: 600; font-size: 18px; margin-bottom: 8px; color: #333;">Confirmación</div>
            <div style="color: #666; font-size: 14px; line-height: 1.5;">${message}</div>
        </div>
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button id="cancelBtn" style="
                padding: 10px 20px;
                border: 1px solid #ddd;
                background: white;
                border-radius: 8px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                color: #666;
                transition: all 0.2s;
            ">Cancelar</button>
            <button id="confirmBtn" style="
                padding: 10px 20px;
                border: none;
                background: #C81E2D;
                color: white;
                border-radius: 8px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.2s;
            ">Confirmar</button>
        </div>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    // Estilos de animación
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        #confirmBtn:hover {
            background: #a01623 !important;
            transform: translateY(-1px);
        }
        #cancelBtn:hover {
            background: #f5f5f5 !important;
            border-color: #999 !important;
        }
    `;
    document.head.appendChild(style);

    const closeModal = () => {
        overlay.style.animation = 'fadeOut 0.2s ease';
        setTimeout(() => {
            overlay.remove();
            style.remove();
        }, 200);
    };

    document.getElementById('confirmBtn').onclick = () => {
        closeModal();
        if (onConfirm) onConfirm();
    };

    document.getElementById('cancelBtn').onclick = () => {
        closeModal();
        if (onCancel) onCancel();
    };

    overlay.onclick = (e) => {
        if (e.target === overlay) {
            closeModal();
            if (onCancel) onCancel();
        }
    };
};

tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#f59e0b',
                secondary: '#10b981',
                tertiary: '#6366f1'
            }
        }
    }
}

let currentSection = 'metricas';

// Update current time
function updateTime() {
    const now = new Date();
    document.getElementById('currentTime').textContent = 
        now.toLocaleDateString('es-AR') + ' ' + now.toLocaleTimeString('es-AR', {hour: '2-digit', minute:'2-digit'});
}

// Navigation functions
function showSection(sectionId, event) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(section => {
        section.classList.add('hidden');
    });
    
    // Show selected section
    document.getElementById(sectionId).classList.remove('hidden');
    
    // Update navigation
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active', 'bg-indigo-700', 'text-white');
        link.classList.add('text-indigo-100', 'hover:bg-indigo-700');
    });
    
    if (event && event.target) {
        event.target.classList.add('active', 'bg-indigo-700', 'text-white');
        event.target.classList.remove('text-indigo-100', 'hover:bg-indigo-700');
    }
    
    // Update page title
    const titles = {
        'metricas': 'Métricas & Dashboard',
        'usuarios': 'Gestión de Usuarios',
        'productos': 'Productos & Precios',
        'reportes': 'Reportes Financieros',
        'configuracion': 'Configuración del Local',
        'pagos': 'Métodos de Pago',
        'promociones': 'Promociones'
    };
    
    document.getElementById('pageTitle').textContent = titles[sectionId];
    currentSection = sectionId;
}

// Toast notification system
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');
    
    toastMessage.textContent = message;
    
    // Update icon based on type
    if (type === 'success') {
        toastIcon.innerHTML = '<div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center"><span class="text-white text-sm">✓</span></div>';
    } else if (type === 'error') {
        toastIcon.innerHTML = '<div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center"><span class="text-white text-sm">✕</span></div>';
    }
    
    toast.classList.remove('hidden');
    
    setTimeout(() => {
        hideToast();
    }, 4000);
}

function hideToast() {
    document.getElementById('toast').classList.add('hidden');
}

// Chart initialization
function initializeCharts() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Ventas',
                data: [11400, 13050, 12800, 14200, 15600, 18900, 14550],
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Employee Chart
    const employeeCtx = document.getElementById('employeeChart').getContext('2d');
    new Chart(employeeCtx, {
        type: 'bar',
        data: {
            labels: ['Juan P.', 'María G.', 'Carlos L.', 'Ana R.', 'Luis M.'],
            datasets: [{
                label: 'Pedidos Procesados',
                data: [45, 38, 25, 42, 35],
                backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Monthly Revenue Chart (Financial Reports)
    const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep'],
            datasets: [{
                label: 'Ingresos',
                data: [125000, 135000, 142000, 138000, 155000, 148000, 162000, 158000, 145600],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4
            }, {
                label: 'Costos',
                data: [52000, 56000, 59000, 57000, 64000, 61000, 67000, 65000, 58400],
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Revenue Breakdown Chart
    const breakdownCtx = document.getElementById('revenueBreakdownChart').getContext('2d');
    new Chart(breakdownCtx, {
        type: 'doughnut',
        data: {
            labels: ['Local', 'Delivery', 'Takeaway'],
            datasets: [{
                data: [127100, 18500, 0],
                backgroundColor: ['#6366f1', '#f59e0b', '#10b981']
            }]
        },
        options: {
            responsive: true
        }
    });
}

// Modal functions (placeholder)
function showUserModal() {
    showToast('Función de nuevo usuario en desarrollo');
}

function showProductModal() {
    showToast('Función de nuevo producto en desarrollo');
}

function showPaymentModal() {
    showToast('Función de nuevo método de pago en desarrollo');
}

function showPromotionModal() {
    showToast('Función de nueva promoción en desarrollo');
}

// Action functions
function editUser(id) {
    showToast(`Editando usuario ID: ${id}`);
}

function toggleUser(id) {
    showToast(`Estado de usuario ID: ${id} cambiado`);
}

function editProduct(id) {
    showToast(`Editando producto ID: ${id}`);
}

function editPrice(product, price) {
    const newPrice = prompt(`Nuevo precio para ${product}:`, price);
    if (newPrice) {
        showToast(`Precio de ${product} actualizado a ${newPrice}`);
    }
}

function saveConfiguration() {
    showToast('Configuración guardada exitosamente');
}

function exportReport(format) {
    showToast(`Exportando reporte en formato ${format.toUpperCase()}`);
}

function updateFinancialReport() {
    showToast('Reporte financiero actualizado');
}

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    updateTime();
    setInterval(updateTime, 60000); // Update every minute
    
    // Initialize charts after page load
    setTimeout(initializeCharts, 500);
});
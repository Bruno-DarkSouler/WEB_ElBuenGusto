<?php
session_start();
require_once '../php/conexion.php';

// Verificar sesión
if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol'] !== 'administrador' && $_SESSION['user_email'] !== 'admin@elbuengusto.com')) {
    header('Location: ../html/login.html');
    exit;
}

$nombre_usuario = $_SESSION['user_name'];
$rol_usuario = $_SESSION['user_rol'];

// MÉTRICAS DEL DASHBOARD
$fecha_hoy = date('Y-m-d');

// Ventas de hoy
$stmt = $conexion->prepare("SELECT COALESCE(SUM(total), 0) as ventas_hoy FROM pedidos WHERE DATE(fecha_pedido) = ? AND estado IN ('confirmado', 'en_preparacion', 'listo', 'en_camino', 'entregado') AND activo = 1");
$stmt->bind_param("s", $fecha_hoy);
$stmt->execute();
$ventas_hoy = $stmt->get_result()->fetch_assoc()['ventas_hoy'];
$stmt->close();

// Pedidos procesados hoy
$stmt = $conexion->prepare("SELECT COUNT(*) as pedidos_hoy FROM pedidos WHERE DATE(fecha_pedido) = ? AND estado IN ('confirmado', 'en_preparacion', 'listo', 'en_camino', 'entregado') AND activo = 1");
$stmt->bind_param("s", $fecha_hoy);
$stmt->execute();
$pedidos_hoy = $stmt->get_result()->fetch_assoc()['pedidos_hoy'];
$stmt->close();

// Empleados activos
$stmt = $conexion->prepare("SELECT COUNT(*) as empleados_activos FROM usuarios WHERE rol IN ('cajero', 'cocinero', 'repartidor', 'administrador') AND activo = 1");
$stmt->execute();
$empleados_activos = $stmt->get_result()->fetch_assoc()['empleados_activos'];
$stmt->close();

// Gasto promedio hoy
$stmt = $conexion->prepare("SELECT COALESCE(AVG(total), 0) as gasto_promedio FROM pedidos WHERE DATE(fecha_pedido) = ? AND estado IN ('confirmado', 'en_preparacion', 'listo', 'en_camino', 'entregado') AND activo = 1");
$stmt->bind_param("s", $fecha_hoy);
$stmt->execute();
$gasto_promedio = $stmt->get_result()->fetch_assoc()['gasto_promedio'];
$stmt->close();

// REPORTES FINANCIEROS
$fecha_mes_inicio = date('Y-m-01');
$fecha_mes_fin = date('Y-m-d');

// Ingresos brutos del mes
$stmt = $conexion->prepare("SELECT COALESCE(SUM(total), 0) as ingresos_brutos FROM pedidos WHERE DATE(fecha_pedido) BETWEEN ? AND ? AND estado = 'entregado' AND activo = 1");
$stmt->bind_param("ss", $fecha_mes_inicio, $fecha_mes_fin);
$stmt->execute();
$ingresos_brutos = $stmt->get_result()->fetch_assoc()['ingresos_brutos'];
$stmt->close();

// Total delivery del mes
$stmt = $conexion->prepare("SELECT COALESCE(SUM(precio_delivery), 0) as total_delivery FROM pedidos WHERE DATE(fecha_pedido) BETWEEN ? AND ? AND estado = 'entregado' AND activo = 1");
$stmt->bind_param("ss", $fecha_mes_inicio, $fecha_mes_fin);
$stmt->execute();
$total_delivery = $stmt->get_result()->fetch_assoc()['total_delivery'];
$stmt->close();

$costos_estimados = ($ingresos_brutos - $total_delivery) * 0.4;
$ganancia_neta = $ingresos_brutos - $costos_estimados;
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Rotisería</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="h-full overflow-hidden">
    <div class="h-24 bg-[#C81E2D] flex justify-between items-center md:hidden">
        <div class="flex justify-between items-center ml-3">
            <img src="../img/Logotipo_sin_fondo.png" alt="" class="h-12 w-12">
            <span class="text-white">El Buen Gusto - administración</span>
        </div>
        <svg onclick="abrir_opciones()" class="text-white h-12 w-12 mr-3" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
        </svg>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="hidden fixed top-4 right-4 z-50 max-w-sm bg-white border border-gray-200 rounded-lg shadow-lg">
        <div class="flex items-center p-4">
            <div id="toastIcon" class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm">✓</span>
                </div>
            </div>
            <div class="ml-3">
                <p id="toastMessage" class="text-sm font-medium text-gray-900"></p>
            </div>
            <button onclick="hideToast()" class="ml-auto text-gray-400 hover:text-gray-600">
                <span class="sr-only">Cerrar</span>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="flex h-full">
        <!-- Sidebar -->
        <div id="opciones" class="hidden md:flex md:w-64 md:flex-col">
            <div class="flex flex-col flex-grow pt-5 bg-[#C81E2D] overflow-y-auto">
                <div class="flex items-center flex-shrink-0 px-4">
                    <div class="text-white">
                        <h2 class="text-xl font-bold">🍽️ Rotisería</h2>
                        <p class="text-indigo-200 text-sm">Panel Admin</p>
                    </div>
                </div>
                <div class="mt-8 flex-grow flex flex-col">
                    <nav class="flex-1 px-2 pb-4 space-y-1">
                        <a href="#" onclick="showSection('metricas', event)" class="nav-link active bg-[#AD1926] text-white group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <span class="mr-3">📊</span>
                            Métricas & Dashboard
                        </a>
                        <a href="#" onclick="showSection('usuarios', event)" class="nav-link text-indigo-100 hover:bg-[#AD1926] group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <span class="mr-3">👥</span>
                            Gestión Usuarios
                        </a>
                        <a href="#" onclick="showSection('productos', event)" class="nav-link text-indigo-100 hover:bg-[#AD1926] group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <span class="mr-3">⚙️</span>
                            Productos & Precios
                        </a>
                        <a href="#" onclick="showSection('reportes', event)" class="nav-link text-indigo-100 hover:bg-[#AD1926] group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <span class="mr-3">📈</span>
                            Reportes Financieros
                        </a>
                        <a href="#" onclick="showSection('configuracion', event)" class="nav-link text-indigo-100 hover:bg-[#AD1926] group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <span class="mr-3">🏪</span>
                            Configuración Local
                        </a>
                        <a href="#" onclick="showSection('pagos', event)" class="nav-link text-indigo-100 hover:bg-[#AD1926] group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            <span class="mr-3">💰</span>
                            Métodos de Pago
                        </a>
                    </nav>
                </div>
                <div class="flex-shrink-0 flex border-t border-indigo-700 p-4">
                    <div class="flex items-center">
                        <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center">
                            <span class="text-white text-sm font-medium">AU</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white"><?php echo htmlspecialchars($nombre_usuario); ?></p>
                            <p class="text-xs text-indigo-200"><?php echo ucfirst($rol_usuario); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Header -->
            <div class="relative z-10 flex-shrink-0 flex h-16 bg-white shadow-sm border-b border-gray-200">
                <div class="flex-1 px-4 flex justify-between items-center">
                    <h1 id="pageTitle" class="text-2xl font-semibold text-gray-900">Métricas & Dashboard</h1>
                    <div class="ml-4 flex items-center space-x-4">
                        <div class="text-sm text-gray-500">
                            <span id="currentTime"></span>
                        </div>
                        <button onclick="cerrarSesion()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200">
                            Cerrar Sesión
                        </button>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        
                        <!-- Métricas & Dashboard Section -->
                        <div id="metricas" class="section">
                            <!-- Stats Cards -->
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                                <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-all duration-300">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                                    <span class="text-white text-sm">💰</span>
                                                </div>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">Ventas Hoy</dt>
                                                    <dd class="flex items-baseline">
                                                        <div class="text-2xl font-semibold text-gray-900">$<?php echo number_format($ventas_hoy, 2, ',', '.'); ?></div>
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-all duration-300">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                                    <span class="text-white text-sm">📋</span>
                                                </div>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">Pedidos Procesados</dt>
                                                    <dd class="flex items-baseline">
                                                        <div class="text-2xl font-semibold text-gray-900"><?php echo $pedidos_hoy; ?></div>
                                                        <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                                            <svg class="self-center flex-shrink-0 h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                            15%
                                                        </div>
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-all duration-300">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                                    <span class="text-white text-sm">👥</span>
                                                </div>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">Empleados Activos</dt>
                                                    <dd class="text-2xl font-semibold text-gray-900"><?php echo $empleados_activos; ?></dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-all duration-300">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                                    <span class="text-white text-sm">💵</span>
                                                </div>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">Gasto Promedio</dt>
                                                    <dd class="text-2xl font-semibold text-gray-900">$<?php echo number_format($gasto_promedio, 0, ',', '.'); ?></dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Charts Row -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                                <div class="bg-white p-6 rounded-lg shadow-lg" style="min-height: 300px;">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-medium text-gray-900">Ventas de la Semana</h3>
                                        <button class="text-sm text-indigo-600 hover:text-indigo-800">Ver detalles</button>
                                    </div>
                                    <canvas id="salesChart" width="400" height="200"></canvas>
                                </div>
                                <div class="bg-white p-6 rounded-lg shadow-lg" style="min-height: 300px;">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-medium text-gray-900">Performance por Empleado</h3>
                                        <button class="text-sm text-indigo-600 hover:text-indigo-800">Gestionar</button>
                                    </div>
                                    <canvas id="employeeChart" width="400" height="200"></canvas>
                                </div>
                            </div>

                            <!-- Quick Status -->
                            <div class="bg-white rounded-lg shadow-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Estado General del Local</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="text-center p-4 bg-green-50 rounded-lg">
                                        <div class="text-2xl mb-2">🟢</div>
                                        <div class="text-sm font-medium text-gray-900">Local Abierto</div>
                                        <div class="text-xs text-gray-500">11:00 - 23:00</div>
                                    </div>
                                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                                        <div class="text-2xl mb-2">💳</div>
                                        <div class="text-sm font-medium text-gray-900">3 Métodos Activos</div>
                                        <div class="text-xs text-gray-500">Efectivo, Digital, Tarjeta</div>
                                    </div>
                                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                                        <div class="text-2xl mb-2">🎯</div>
                                        <div class="text-sm font-medium text-gray-900">2 Promociones</div>
                                        <div class="text-xs text-gray-500">Activas hasta el viernes</div>
                                    </div>
                                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                                        <div class="text-2xl mb-2">📊</div>
                                        <div class="text-sm font-medium text-gray-900">Meta: 85%</div>
                                        <div class="text-xs text-gray-500">$12,400 de $14,600</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Usuarios Section -->
                        <div id="usuarios" class="section hidden">
                            <div class="mb-6">
                                <div class="flex justify-between items-center">
                                    <h2 class="text-xl font-semibold text-gray-900">Gestión de Usuarios y Empleados</h2>
                                    <button onclick="showUserModal()" class="bg-primary hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center">
                                        <span>➕</span>
                                        <span class="ml-2">Nuevo Usuario</span>
                                    </button>
                                </div>
                            </div>

                            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permisos</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Acceso</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <!-- Los usuarios se cargarán dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Productos Section -->
                        <div id="productos" class="section hidden">
                            <div class="mb-6">
                                <div class="flex justify-between items-center">
                                    <h2 class="text-xl font-semibold text-gray-900">Gestión de Productos y Precios</h2>
                                    <button onclick="showProductModal()" class="bg-primary hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                                        <span>Nuevo Producto</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Bar -->
                            <div class="bg-white p-4 rounded-lg shadow-lg mb-6">
                                <div class="flex flex-wrap gap-4 items-center">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Categoría:</label>
                                        <select class="ml-2 border-gray-300 rounded-md text-sm">
                                            <option>Todas</option>
                                            <option>Minutas</option>
                                            <option>Empanadas</option>
                                            <option>Pastas</option>
                                            <option>Bebidas</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Estado:</label>
                                        <select class="ml-2 border-gray-300 rounded-md text-sm">
                                            <option>Todos</option>
                                            <option>Activos</option>
                                            <option>Inactivos</option>
                                        </select>
                                    </div>
                                    <button onclick="applyBulkPriceUpdate()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200">
                                        Actualización Masiva
                                    </button>
                                </div>
                            </div>

                            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Actual</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margen</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <!-- Los productos se cargarán dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Reportes Financieros Section -->
                        <div id="reportes" class="section hidden">
                            <div class="mb-6">
                                <div class="flex justify-between items-center">
                                    <h2 class="text-xl font-semibold text-gray-900">Reportes Financieros</h2>
                                    <div class="flex space-x-2">
                                        <button onclick="exportReport('pdf')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200">
                                            📄 Exportar PDF
                                        </button>
                                        <button onclick="exportReport('excel')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200">
                                            📊 Exportar Excel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Date Range Selector -->
                            <div class="bg-white p-4 rounded-lg shadow-lg mb-6">
                                <div class="flex flex-wrap gap-4 items-center">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Desde:</label>
                                        <input type="date" id="dateFrom" class="ml-2 border-gray-300 rounded-md text-sm" value="<?php echo $fecha_mes_inicio; ?>">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Hasta:</label>
                                        <input type="date" id="dateTo" class="ml-2 border-gray-300 rounded-md text-sm" value="<?php echo $fecha_mes_fin; ?>">
                                    </div>
                                    <button onclick="updateFinancialReport()" class="bg-tertiary hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200">
                                        Actualizar Reporte
                                    </button>
                                </div>
                            </div>

                            <!-- Financial Summary Cards -->
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                                    <span class="text-white text-sm">💰</span>
                                                </div>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">Ingresos Brutos</dt>
                                                    <dd class="text-2xl font-semibold text-gray-900">$<?php echo number_format($ingresos_brutos, 2, ',', '.'); ?></dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                    </div>

<div class="bg-white overflow-hidden shadow-lg rounded-lg">
    <div class="p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <span class="text-white text-sm">🚗</span>
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Delivery</dt>
                    <dd class="text-2xl font-semibold text-gray-900">$<?php echo number_format($total_delivery, 2, ',', '.'); ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="bg-white overflow-hidden shadow-lg rounded-lg">
    <div class="p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                    <span class="text-white text-sm">📉</span>
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Costos</dt>
                    <dd class="text-2xl font-semibold text-gray-900">$<?php echo number_format($costos_estimados, 2, ',', '.'); ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="bg-white overflow-hidden shadow-lg rounded-lg">
    <div class="p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                    <span class="text-white text-sm">📈</span>
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Ganancia Neta</dt>
                    <dd class="text-2xl font-semibold text-green-600">$<?php echo number_format($ganancia_neta, 2, ',', '.'); ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Financial Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
<div class="bg-white p-6 rounded-lg shadow-lg" style="min-height: 300px;">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Evolución Mensual</h3>
    <canvas id="monthlyRevenueChart" width="400" height="200"></canvas>
</div>
<div class="bg-white p-6 rounded-lg shadow-lg" style="min-height: 300px;">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Distribución de Ingresos</h3>
    <canvas id="revenueBreakdownChart" width="400" height="200"></canvas>
</div>
</div>

<!-- Detailed Financial Table -->
<div class="bg-white shadow-lg rounded-lg overflow-hidden">
<div class="px-6 py-4 border-b border-gray-200">
    <h3 class="text-lg font-medium text-gray-900">Detalle Diario</h3>
</div>
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedidos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ingresos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ganancia</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margen %</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <!-- Los datos se cargarán dinámicamente -->
        </tbody>
    </table>
</div>
</div>
</div>

<!-- Configuración Local Section -->
<div id="configuracion" class="section hidden">
<div class="mb-6">
<h2 class="text-xl font-semibold text-gray-900">Configuración del Local</h2>
</div>

<div class="space-y-6">
<!-- General Settings -->
<div class="bg-white shadow-lg rounded-lg p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Información General</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Nombre del Local</label>
            <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary" value="Rotisería Del Barrio">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Teléfono Principal</label>
            <input type="tel" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary" value="+54 11 1234-5678">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email Contacto</label>
            <input type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary" value="info@rotiseria.com">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Dirección</label>
            <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary" value="Av. Corrientes 1234, CABA">
        </div>
    </div>
</div>

<div class="flex justify-end">
    <button onclick="saveConfiguration()" class="bg-primary hover:bg-yellow-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
        💾 Guardar Configuración
    </button>
</div>
</div>
</div>

<!-- Métodos de Pago Section -->
<div id="pagos" class="section hidden">
<div class="mb-6">
<div class="flex justify-between items-center">
    <h2 class="text-xl font-semibold text-gray-900">Gestión de Métodos de Pago</h2>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
<!-- Payment Method Cards -->
<div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-3">
            <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">💵</span>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900">Efectivo</h3>
                <p class="text-sm text-gray-500">Pago tradicional</p>
            </div>
        </div>
        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">ACTIVO</span>
    </div>
    <div class="space-y-2 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-600">Comisión:</span>
            <span class="font-medium">0%</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-600">Uso esta semana:</span>
            <span class="font-medium">68%</span>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-3">
            <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">📱</span>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900">Digital</h3>
                <p class="text-sm text-gray-500">MercadoPago, Modo</p>
            </div>
        </div>
        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">ACTIVO</span>
    </div>
    <div class="space-y-2 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-600">Comisión:</span>
            <span class="font-medium">3.2%</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-600">Uso esta semana:</span>
            <span class="font-medium">32%</span>
        </div>
    </div>
</div>
</div>
</div>

</div>
</div>
</main>
</div>
</div>

<!-- MODALES -->
<!-- Modal Usuario -->
<div id="modalUsuario" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
<div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
<div class="mt-3">
<h3 class="text-lg font-medium text-gray-900 mb-4" id="tituloModalUsuario">Nuevo Usuario</h3>
<form id="formUsuario" onsubmit="guardarUsuario(event)">
<input type="hidden" id="usuario_id">

<div class="mb-4">
<label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
<input type="text" id="usuario_nombre" name="nombre" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C81E2D]">
</div>

<div class="mb-4">
<label class="block text-sm font-medium text-gray-700 mb-2">Apellido</label>
<input type="text" id="usuario_apellido" name="apellido" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C81E2D]">
</div>

<div class="mb-4">
<label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
<input type="email" id="usuario_email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C81E2D]">
</div>

<div class="mb-4">
<label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
<input type="tel" id="usuario_telefono" name="telefono" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C81E2D]">
</div>

<div class="mb-4">
<label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
<select id="usuario_rol" name="rol" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C81E2D]">
<option value="cliente">Cliente</option>
<option value="cajero">Cajero</option>
<option value="cocinero">Cocinero</option>
<option value="repartidor">Repartidor</option>
<option value="administrador">Administrador</option>
</select>
</div>

<div class="mb-4">
<label class="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
<input type="password" id="usuario_password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C81E2D]">
<p class="text-xs text-gray-500 mt-1">Dejar en blanco para mantener la actual (solo al editar)</p>
</div>

<div class="flex justify-end space-x-2 mt-6">
<button type="button" onclick="cerrarModalUsuario()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
Cancelar
</button>
<button type="submit" class="px-4 py-2 bg-[#C81E2D] text-white rounded-md hover:bg-[#AD1926]">
Guardar
</button>
</div>
</form>
</div>
</div>
</div>

<!-- Modal Producto -->
<div id="modalProducto" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
<div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
<div class="mt-3">
<h3 class="text-lg font-medium text-gray-900 mb-4" id="tituloModalProducto">Nuevo Producto</h3>
<form id="formProducto" onsubmit="guardarProducto(event)" enctype="multipart/form-data">
<input type="hidden" id="producto_id" name="id">

<div class="mb-4">
<label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
<input type="text" id="producto_nombre" name="nombre" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C81E2D]">
</div>

<div class="mb-4">
<label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
<textarea id="producto_descripcion" name="descripcion" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C81E2D]"></textarea>
</div>

<div class="mb-4">
<label class="block text-sm font-medium text-gray-700 mb-2">Precio</label>
<input type="number" id="producto_precio" name="precio" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C81E2D]">
</div>

<div class="mb-4">
<label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
<select id="producto_categoria" name="categoria_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C81E2D]">
<option value="">Seleccione una categoría</option>
</select>
</div>

<div class="mb-4">
<label class="block text-sm font-medium text-gray-700 mb-2">Ingredientes</label>
<textarea id="producto_ingredientes" name="ingredientes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C81E2D]"></textarea>
</div>

<div class="mb-4">
<label class="block text-sm font-medium text-gray-700 mb-2">Tiempo de Preparación (minutos)</label>
<input type="number" id="producto_tiempo" name="tiempo_preparacion" value="20" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C81E2D]">
</div>

<div class="mb-4">
<label class="flex items-center">
<input type="checkbox" id="producto_disponible" name="disponible" checked class="rounded border-gray-300 text-[#C81E2D] focus:ring-[#C81E2D]">
<span class="ml-2 text-sm text-gray-700">Disponible</span>
</label>
</div>

<div class="flex justify-end space-x-2 mt-6">
<button type="button" onclick="cerrarModalProducto()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
Cancelar
</button>
<button type="submit" class="px-4 py-2 bg-[#C81E2D] text-white rounded-md hover:bg-[#AD1926]">
Guardar
</button>
</div>
</form>
</div>
</div>
</div>

</body>
<script src="../js/admin.js"></script>
</html>
<?php
include '../php/conexion.php';

// Obtener el ID del usuario de la sesión o de algún otro método
session_start();
$user_id = $_SESSION['user_id'];

// Consultar la información del usuario
$sql = "SELECT * FROM usuarios WHERE id = $user_id";
$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Usuario no encontrado.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - El Buen Gusto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/perfil.js"></script>
    <link rel="stylesheet" href="../css/perfil.css">
    <link rel="stylesheet" href="../css/daltonismo.css">
    <link href="https://fonts.googleapis.com/css2?family=Averia+Serif+Libre:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="font-averia bg-primary-cream min-h-screen">
    <header class="bg-primary-red shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="../index.html">
                        <div class="text-3xl"><img class="h-[7vh]" src="../img/isotipo_sm.png" alt="isotipo"></div>
                    </a>
                    <div>
                        <h1 class="text-white text-2xl font-bold">El Buen Gusto</h1>
                        <p class="text-primary-cream text-sm">Mi Perfil</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 text-white">
                        <img id="avatarUsuario" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiNmNWViZDIiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0yMCAyMXYtMmE0IDQgMCAwIDAtNC00SDhhNCA0IDAgMCAwLTQgNHYyIiBzdHJva2U9IiM1MDMyMTQiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CjxjaXJjbGUgY3g9IjEyIiBjeT0iNyIgcj0iNCIgc3Ryb2tlPSIjNTAzMjE0IiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L3N2Zz4KPC9zdmc+" class="w-10 h-10 rounded-full border-2 border-primary-cream">
                        <div>
                            <p class="text-sm font-bold" id="nombreUsuario"><?php echo $user['nombre'] . ' ' . $user['apellido']; ?></p>
                            <p class="text-xs text-primary-cream"><?php echo $user['vip'] ? 'Cliente VIP' : 'Cliente'; ?></p>
                        </div>
                    </div>

                    <button class="bg-primary-brown hover:bg-opacity-80 text-white px-4 py-2 rounded-lg transition-colors duration-300">
                        <a href="../index.html">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </a>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Notificaciones -->
    <div id="notifications" class="fixed m-[1rem] top-51 right-4 z-40 space-y-2"></div>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Información Personal -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-lg">
                <div class="bg-primary-red text-white p-6 rounded-t-xl">
                    <h2 class="text-2xl font-bold">👤 Mi Perfil</h2>
                    <p class="text-primary-cream">Información personal y preferencias</p>
                </div>

                <div class="p-6">
                    <form id="perfilForm" class="space-y-6">
                        <!-- Foto de Perfil -->
                        <div class="flex items-center gap-6">
                            <div class="relative">
                                <img id="fotoPerfilGrande" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iNDAiIGN5PSI0MCIgcj0iNDAiIGZpbGw9IiNmNWViZDIiLz4KPHN2ZyB4PSIxNiIgeT0iMTYiIHdpZHRoPSI0OCIgaGVpZ2h0PSI0OCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIj4KPHBhdGggZD0iTTIwIDIxdi0yYTQgNCAwIDAgMC00LTRIOGE0IDQgMCAwIDAtNCA0djIiIHN0cm9rZT0iIzUwMzIxNCIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPGNpcmNsZSBjeD0iMTIiIGN5PSI3IiByPSI0IiBzdHJva2U9IiM1MDMyMTQiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo8L3N2Zz4=" class="w-20 h-20 rounded-full border-4 border-primary-cream">
                                <input type="file" id="inputFoto" class="hidden" accept="image/*" onchange="cambiarFoto(this)">
                                <button type="button" onclick="document.getElementById('inputFoto').click()" class="absolute bottom-0 right-0 bg-primary-red text-white p-2 rounded-full hover:bg-red-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-primary-brown"><?php echo $user['nombre'] . ' ' . $user['apellido']; ?></h3>
                                <p class="text-gray-600">Cliente desde: <?php echo date('F Y', strtotime($user['fecha_registro'])); ?></p>
                                <div class="flex items-center gap-2 mt-1">
                                    <?php if ($user['vip']): ?>
                                        <span class="text-primary-red font-medium">⭐ Cliente VIP</span>
                                    <?php else: ?>
                                        <span class="text-primary-red font-medium">Cliente</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Campos del Formulario -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                                <input type="text" id="nombre" value="<?php echo $user['nombre']; ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Apellido *</label>
                                <input type="text" id="apellido" value="<?php echo $user['apellido']; ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono *</label>
                                <input type="tel" id="telefono" value="<?php echo $user['telefono']; ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <input type="email" id="email" value="<?php echo $user['email']; ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                            </div>
                        </div>

                        <!-- Dirección -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección Principal *</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <select id="comidaFavorita" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                                        <option>Av. Libertador 1234</option>
                                        <option>Florida 456, Piso 8</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Daltonismo</label>
                            <select id="daltonismo" class="w-full border border-gray-300 rounded-lg px-4 py-2" name="daltonismo">
                                <option value="normal" <?php echo ($user['daltonismo'] == 'normal') ? 'selected' : ''; ?>>Sin Daltonismo</option>
                                <option value="monocromatico" <?php echo ($user['daltonismo'] == 'monocromatico') ? 'selected' : ''; ?>>Monocromático</option>
                                <option value="deuteranopia" <?php echo ($user['daltonismo'] == 'deuteranopia') ? 'selected' : ''; ?>>Deuteranopia (Verde-Rojo)</option>
                                <option value="protanopia" <?php echo ($user['daltonismo'] == 'protanopia') ? 'selected' : ''; ?>>Protanopia (Rojo-Verde)</option>
                                <option value="tritanopia" <?php echo ($user['daltonismo'] == 'tritanopia') ? 'selected' : ''; ?>>Tritanopia (Azul-Amarillo)</option>
                            </select>
                        </div>

                        <!-- Preferencias -->
                        <div>
                            <h4 class="text-lg font-bold text-primary-brown mb-4">🎯 Preferencias</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Comida Favorita</label>
                                    <select id="comidaFavorita" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                                        <option value="pollo" selected>Pollo a la Parrilla</option>
                                        <option value="pizza">Pizza</option>
                                        <option value="empanadas">Empanadas</option>
                                        <option value="todo">Me gusta todo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" id="notificaciones" checked class="mr-3 w-4 h-4 text-primary-red">
                                    <span class="text-sm text-gray-700">Recibir notificaciones de ofertas y promociones</span>
                                </label>
                            </div>
                        </div>

                        <!-- Seguridad -->
                        <div>
                            <h4 class="text-lg font-bold text-primary-brown mb-4">🔒 Seguridad</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Contraseña</label>
                                    <input type="password" id="nuevaPassword" placeholder="Dejar vacío para no cambiar"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Contraseña</label>
                                    <input type="password" id="confirmarPassword" placeholder="Confirmar nueva contraseña"
                                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex gap-4 pt-6">
                            <button type="button" onclick="cancelarCambios()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 rounded-lg transition-colors">
                                Cancelar Cambios
                            </button>
                            <button type="submit" class="flex-1 bg-primary-red hover:bg-red-600 text-white py-3 rounded-lg transition-colors">
                                💾 Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="space-y-6">
                
                <!-- Estadísticas del Cliente -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="bg-green-600 text-white p-4 rounded-t-xl">
                        <h3 class="text-lg font-bold">📊 Mis Estadísticas</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Pedidos Totales:</span>
                            <span class="font-bold text-green-600">47</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Gastado Total:</span>
                            <span class="font-bold text-green-600">$234,500</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Reseñas Dadas:</span>
                            <span class="font-bold text-green-600">31</span>
                        </div>
                    </div>
                </div>

                <!-- Direcciones -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="bg-purple-600 text-white p-4 rounded-t-xl">
                        <h3 class="text-lg font-bold">📍 Mis Direcciones</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="p-3 border border-gray-200 rounded-lg">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="font-medium">🏠 Casa</p>
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Principal</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Av. Libertador 1234</p>
                                    <p class="text-sm text-gray-600">San Isidro, Buenos Aires</p>
                                    <p class="text-xs text-gray-500 mt-1">Código Postal: 1642</p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <button onclick="editarDireccion(1)" class="text-blue-500 hover:bg-blue-100 p-1 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="eliminarDireccion(1)" class="text-red-500 hover:bg-red-100 p-1 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-3 border border-gray-200 rounded-lg">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="font-medium mb-1">🏢 Trabajo</p>
                                    <p class="text-sm text-gray-600">Florida 456, Piso 8</p>
                                    <p class="text-sm text-gray-600">CABA, Buenos Aires</p>
                                    <p class="text-xs text-gray-500 mt-1">Código Postal: 1005</p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <button onclick="editarDireccion(2)" class="text-blue-500 hover:bg-blue-100 p-1 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="eliminarDireccion(2)" class="text-red-500 hover:bg-red-100 p-1 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button onclick="agregarDireccion()" class="w-full border-2 border-dashed border-gray-300 p-3 rounded-lg hover:border-primary-red hover:bg-red-50 transition-colors">
                            <span class="text-primary-red font-medium">+ Agregar Dirección</span>
                        </button>
                    </div>
                </div>

                <!-- Historial de Pedidos Recientes -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="bg-orange-600 text-white p-4 rounded-t-xl">
                        <h3 class="text-lg font-bold">📦 Pedidos Recientes</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="p-3 border border-gray-200 rounded-lg">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-medium">Pedido #1047</p>
                                    <p class="text-sm text-gray-600">15 de Enero, 2025</p>
                                </div>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Entregado</span>
                            </div>
                            <p class="text-sm text-gray-700">2x Pollo Entero, 1x Pizza Muzzarella</p>
                            <div class="flex justify-between items-center mt-2">
                                <span class="font-bold text-primary-red">$13,200</span>
                                <button onclick="repetirPedido(1047)" class="text-primary-red hover:bg-red-100 px-2 py-1 rounded text-xs">
                                    🔄 Repetir
                                </button>
                            </div>
                        </div>

                        <div class="p-3 border border-gray-200 rounded-lg">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-medium">Pedido #1045</p>
                                    <p class="text-sm text-gray-600">10 de Enero, 2025</p>
                                </div>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Entregado</span>
                            </div>
                            <p class="text-sm text-gray-700">1x Empanadas x12, 2x Coca Cola</p>
                            <div class="flex justify-between items-center mt-2">
                                <span class="font-bold text-primary-red">$8,600</span>
                                <button onclick="repetirPedido(1045)" class="text-primary-red hover:bg-red-100 px-2 py-1 rounded text-xs">
                                    🔄 Repetir
                                </button>
                            </div>
                        </div>

                        <div class="p-3 border border-gray-200 rounded-lg">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-medium">Pedido #1042</p>
                                    <p class="text-sm text-gray-600">5 de Enero, 2025</p>
                                </div>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Entregado</span>
                            </div>
                            <p class="text-sm text-gray-700">3x 1/2 Pollo, 1x Flan Casero</p>
                            <div class="flex justify-between items-center mt-2">
                                <span class="font-bold text-primary-red">$11,700</span>
                                <button onclick="repetirPedido(1042)" class="text-primary-red hover:bg-red-100 px-2 py-1 rounded text-xs">
                                    🔄 Repetir
                                </button>
                            </div>
                        </div>

                        <button onclick="verTodosLosPedidos()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition-colors">
                            Ver Todos los Pedidos
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div id="verTodosLosPedidos" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
            <div class="max-h-[80vh] overflow-y-scroll bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4">
                <div class="bg-primary-red text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold">📦 Todos tus Pedidos</h3>
                        <button onclick="cerrarModalPedidos()" class="text-white hover:bg-red-600 p-2 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-4 space-y-3">
                    <div class="p-3 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-medium">Pedido #1047</p>
                                <p class="text-sm text-gray-600">15 de Enero, 2025</p>
                            </div>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Entregado</span>
                        </div>
                        <p class="text-sm text-gray-700">2x Pollo Entero, 1x Pizza Muzzarella</p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="font-bold text-primary-red">$13,200</span>
                            <button onclick="repetirPedido(1047)" class="text-primary-red hover:bg-red-100 px-2 py-1 rounded text-xs">
                                🔄 Repetir
                            </button>
                        </div>
                    </div>

                    <div class="p-3 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-medium">Pedido #1045</p>
                                <p class="text-sm text-gray-600">10 de Enero, 2025</p>
                            </div>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Entregado</span>
                        </div>
                        <p class="text-sm text-gray-700">1x Empanadas x12, 2x Coca Cola</p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="font-bold text-primary-red">$8,600</span>
                            <button onclick="repetirPedido(1045)" class="text-primary-red hover:bg-red-100 px-2 py-1 rounded text-xs">
                                🔄 Repetir
                            </button>
                        </div>
                    </div>

                    <div class="p-3 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-medium">Pedido #1042</p>
                                <p class="text-sm text-gray-600">5 de Enero, 2025</p>
                            </div>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Entregado</span>
                        </div>
                        <p class="text-sm text-gray-700">3x 1/2 Pollo, 1x Flan Casero</p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="font-bold text-primary-red">$11,700</span>
                            <button onclick="repetirPedido(1042)" class="text-primary-red hover:bg-red-100 px-2 py-1 rounded text-xs">
                                🔄 Repetir
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-4 space-y-3">
                    <div class="p-3 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-medium">Pedido #1047</p>
                                <p class="text-sm text-gray-600">15 de Enero, 2025</p>
                            </div>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Entregado</span>
                        </div>
                        <p class="text-sm text-gray-700">2x Pollo Entero, 1x Pizza Muzzarella</p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="font-bold text-primary-red">$13,200</span>
                            <button onclick="repetirPedido(1047)" class="text-primary-red hover:bg-red-100 px-2 py-1 rounded text-xs">
                                🔄 Repetir
                            </button>
                        </div>
                    </div>

                    <div class="p-3 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-medium">Pedido #1045</p>
                                <p class="text-sm text-gray-600">10 de Enero, 2025</p>
                            </div>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Entregado</span>
                        </div>
                        <p class="text-sm text-gray-700">1x Empanadas x12, 2x Coca Cola</p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="font-bold text-primary-red">$8,600</span>
                            <button onclick="repetirPedido(1045)" class="text-primary-red hover:bg-red-100 px-2 py-1 rounded text-xs">
                                🔄 Repetir
                            </button>
                        </div>
                    </div>

                    <div class="p-3 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-medium">Pedido #1042</p>
                                <p class="text-sm text-gray-600">5 de Enero, 2025</p>
                            </div>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Entregado</span>
                        </div>
                        <p class="text-sm text-gray-700">3x 1/2 Pollo, 1x Flan Casero</p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="font-bold text-primary-red">$11,700</span>
                            <button onclick="repetirPedido(1042)" class="text-primary-red hover:bg-red-100 px-2 py-1 rounded text-xs">
                                🔄 Repetir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <div id="modalDireccion" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4">
                <div class="bg-primary-red text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold">📍 Agregar Dirección</h3>
                        <button onclick="cerrarModalDireccion()" class="text-white hover:bg-red-600 p-2 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <form id="formDireccion" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Etiqueta</label>
                            <select id="etiquetaDireccion" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                                <option value="casa">🏠 Casa</option>
                                <option value="trabajo">🏢 Trabajo</option>
                                <option value="otro">📍 Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección Completa</label>
                            <textarea id="direccionCompleta" rows="2" placeholder="Calle, número, piso, departamento"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ciudad</label>
                                <select id="etiquetaDireccion" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                                    <option value="casa"></option>
                                    <option value="trabajo"></option>
                                    <option value="otro"></option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Código Postal</label>
                                <input type="text" id="codigoPostal" placeholder="1642"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Instrucciones de Entrega</label>
                            <textarea id="instrucciones" rows="2" placeholder="Timbre, piso, referencias..."
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent"></textarea>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="cerrarModalDireccion()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg">
                                Cancelar
                            </button>
                            <button type="submit" class="flex-1 bg-primary-red hover:bg-red-600 text-white py-2 rounded-lg">
                                Guardar Dirección
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
    </main>
</body>
<script>
document.getElementById('daltonismo').addEventListener('change', function() {
    const selectedDaltonismo = this.value;

    // Definir los colores para cada tipo de daltonismo
    const colorSchemes = {
        normal: {
            bg: 'normal-bg',
            text: 'normal-text',
            button: 'normal-button',
            header: 'normal-header'
        },
        monocromatico: {
            bg: 'monocromatico-bg',
            text: 'monocromatico-text',
            button: 'monocromatico-button',
            header: 'monocromatico-header'
        },
        deuteranopia: {
            bg: 'deuteranopia-bg',
            text: 'deuteranopia-text',
            button: 'deuteranopia-button',
            header: 'deuteranopia-header'
        },
        protanopia: {
            bg: 'protanopia-bg',
            text: 'protanopia-text',
            button: 'protanopia-button',
            header: 'protanopia-header'
        },
        tritanopia: {
            bg: 'tritanopia-bg',
            text: 'tritanopia-text',
            button: 'tritanopia-button',
            header: 'tritanopia-header'
        }
    };

    // Aplicar las clases seleccionadas
    const body = document.body;
    const header = document.querySelector('header');
    const buttons = document.querySelectorAll('button');
    const inputs = document.querySelectorAll('input, select, textarea');

    // Eliminar clases antiguas
    body.className = body.className.replace(/(normal-bg|monocromatico-bg|deuteranopia-bg|protanopia-bg|tritanopia-bg|normal-text|monocromatico-text|deuteranopia-text|protanopia-text|tritanopia-text)/g, '');
    header.className = header.className.replace(/(normal-header|monocromatico-header|deuteranopia-header|protanopia-header|tritanopia-header)/g, '');
    buttons.forEach(button => {
        button.className = button.className.replace(/(normal-button|monocromatico-button|deuteranopia-button|protanopia-button|tritanopia-button)/g, '');
    });

    // Añadir nuevas clases
    body.classList.add(colorSchemes[selectedDaltonismo].bg, colorSchemes[selectedDaltonismo].text);
    header.classList.add(colorSchemes[selectedDaltonismo].header);
    buttons.forEach(button => {
        button.classList.add(colorSchemes[selectedDaltonismo].button);
    });

    inputs.forEach(input => {
        input.classList.add('border-gray-300', 'focus:ring-gray-500', 'focus:border-transparent');
    });

    document.querySelectorAll('[class*="text-"]').forEach(element => {
        element.classList.add(colorSchemes[selectedDaltonismo].text);
    });
});
document.getElementById('perfilForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Evitar el envío del formulario por defecto

    // Obtener los valores de los campos del formulario
    const nombre = document.getElementById('nombre').value;
    const apellido = document.getElementById('apellido').value;
    const email = document.getElementById('email').value;
    const telefono = document.getElementById('telefono').value;
    const direccionPrincipal = document.getElementById('direccionPrincipal').value;
    const nuevaPassword = document.getElementById('nuevaPassword').value;
    const confirmarPassword = document.getElementById('confirmarPassword').value;
    const daltonismo = document.getElementById('daltonismo').value; // Añadir el valor de daltonismo

    // Enviar los datos al servidor usando Fetch API
    fetch('../php/procesar_perfil.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            nombre: nombre,
            apellido: apellido,
            email: email,
            telefono: telefono,
            direccionPrincipal: direccionPrincipal,
            nuevaPassword: nuevaPassword,
            confirmarPassword: confirmarPassword,
            daltonismo: daltonismo // Incluir el valor de daltonismo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Datos guardados exitosamente.');
            // Recargar la página para reflejar los cambios
            location.reload();
        } else {
            alert('Error al guardar los datos: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al enviar los datos.');
    });
});

function cancelarCambios() {
    // Restablecer los valores de los campos del formulario a los valores originales
    document.getElementById('nombre').value = '<?php echo $user['nombre']; ?>';
    document.getElementById('apellido').value = '<?php echo $user['apellido']; ?>';
    document.getElementById('email').value = '<?php echo $user['email']; ?>';
    document.getElementById('telefono').value = '<?php echo $user['telefono']; ?>';
    document.getElementById('direccionPrincipal').value = '<?php echo $user['direccion']; ?>';
    document.getElementById('nuevaPassword').value = '';
    document.getElementById('confirmarPassword').value = '';
    document.getElementById('daltonismo').value = '<?php echo $user['daltonismo'] ?? 'normal'; ?>'; // Restablecer el valor de daltonismo
}
</script>
</html>
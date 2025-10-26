<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/inicio.php');
    exit;
}

require_once '../php/conexion.php';

$usuario_id = $_SESSION['user_id'];

// Manejar peticiones AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'verificar_pedidos_pendientes':
            try {
                // Buscar pedidos entregados sin confirmar
                $query = "SELECT p.*, 
                         (SELECT fecha_cambio FROM seguimiento_pedidos 
                          WHERE pedido_id = p.id AND estado_nuevo = 'entregado' 
                          ORDER BY fecha_cambio DESC LIMIT 1) as fecha_entrega,
                         u_rep.nombre as repartidor_nombre, 
                         u_rep.apellido as repartidor_apellido
                         FROM pedidos p
                         LEFT JOIN calificaciones c ON p.id = c.pedido_id
                         LEFT JOIN usuarios u_rep ON p.repartidor_id = u_rep.id
                         WHERE p.usuario_id = ?
                         AND p.estado = 'entregado'
                         AND p.activo = 1
                         AND c.id IS NULL
                         AND TIMESTAMPDIFF(MINUTE, 
                             (SELECT fecha_cambio FROM seguimiento_pedidos 
                              WHERE pedido_id = p.id AND estado_nuevo = 'entregado' 
                              ORDER BY fecha_cambio DESC LIMIT 1), 
                             NOW()) <= 30";
                
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("i", $usuario_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                $pedidos = [];
                while ($row = $resultado->fetch_assoc()) {
                    $pedidos[] = $row;
                }
                
                echo json_encode(['success' => true, 'pedidos' => $pedidos]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
            
        case 'confirmar_recepcion':
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $pedido_id = $data['pedido_id'];
                $calificacion_comida = $data['calificacion_comida'];
                $calificacion_delivery = $data['calificacion_delivery'];
                $comentario = $data['comentario'] ?? '';
                
                $conexion->begin_transaction();
                
                // Verificar que el pedido pertenece al usuario
                $query = "SELECT repartidor_id FROM pedidos 
                         WHERE id = ? AND usuario_id = ? AND estado = 'entregado'";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("ii", $pedido_id, $usuario_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                if ($resultado->num_rows == 0) {
                    throw new Exception('Pedido no encontrado');
                }
                
                $pedido = $resultado->fetch_assoc();
                
                // Insertar calificación
                $query = "INSERT INTO calificaciones 
                         (pedido_id, usuario_id, calificacion_comida, calificacion_delivery, 
                          comentario, repartidor_id)
                         VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("iiiisi", 
                    $pedido_id, 
                    $usuario_id, 
                    $calificacion_comida, 
                    $calificacion_delivery,
                    $comentario,
                    $pedido['repartidor_id']
                );
                $stmt->execute();
                
                $conexion->commit();
                
                echo json_encode(['success' => true, 'message' => '¡Gracias por tu calificación!']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
            
        case 'reportar_no_recibido':
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $pedido_id = $data['pedido_id'];
                $motivo = $data['motivo'] ?? 'No especificado';
                
                $conexion->begin_transaction();
                
                // Verificar que el pedido pertenece al usuario
                $query = "SELECT id FROM pedidos 
                         WHERE id = ? AND usuario_id = ? AND estado = 'entregado'";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("ii", $pedido_id, $usuario_id);
                $stmt->execute();
                
                if ($stmt->get_result()->num_rows == 0) {
                    throw new Exception('Pedido no encontrado');
                }
                
                // Cambiar estado a "cancelado" por problema
                $query = "UPDATE pedidos SET estado = 'cancelado' WHERE id = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("i", $pedido_id);
                $stmt->execute();
                
                // Registrar el reporte en seguimiento
                $comentario = "Cliente reportó no recepción. Motivo: " . $motivo;
                $query = "INSERT INTO seguimiento_pedidos 
                         (pedido_id, estado_anterior, estado_nuevo, usuario_cambio_id, comentarios)
                         VALUES (?, 'entregado', 'cancelado', ?, ?)";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("iis", $pedido_id, $usuario_id, $comentario);
                $stmt->execute();
                
                $conexion->commit();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Reporte registrado. Un administrador revisará tu caso.'
                ]);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
    }
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pedido - El Buen Gusto</title>
    <link rel="stylesheet" href="../css/notifications.css">
    <link rel="stylesheet" href="../css/confirmar_pedido.css">
</head>
<body>
    <div class="modal-calificacion" id="modalPrincipal">
        <div class="modal-header">
            <h1>✨ ¡Tu pedido llegó!</h1>
            <p>Ayúdanos a mejorar calificando tu experiencia</p>
        </div>

        <div class="modal-body">
            <div class="pedido-info" id="pedidoInfo">
                <p><strong>Cargando información del pedido...</strong></p>
            </div>

            <div class="rating-section">
                <h3>🍽️ Calidad de la comida</h3>
                <div class="stars-container" id="starsComida" data-rating="0">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
            </div>

            <div class="rating-section">
                <h3>🚚 Servicio de entrega</h3>
                <div class="stars-container" id="starsDelivery" data-rating="0">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
            </div>

            <div class="rating-section">
                <h3>💬 Comentarios (opcional)</h3>
                <textarea 
                    id="comentarioCalificacion" 
                    class="comentario-textarea"
                    placeholder="Cuéntanos sobre tu experiencia..."></textarea>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-confirmar" id="btnConfirmar">
                <span>✓</span>
                <span>Confirmar recepción</span>
            </button>
            <button class="btn btn-problema" id="btnProblema">
                <span>⚠</span>
                <span>Mi pedido no llegó</span>
            </button>
        </div>
    </div>

    <!-- Modal de reporte de problema -->
    <div class="overlay" id="overlayReporte">
        <div class="modal-reporte">
            <div class="modal-reporte-header">
                <h2>⚠️ Reportar problema</h2>
                <p style="font-size: 0.9rem; opacity: 0.9; margin-top: 8px;">¿Qué sucedió con tu pedido?</p>
            </div>
            <div class="modal-reporte-body">
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="motivo" value="No llegó el pedido" checked>
                        <span>No llegó el pedido</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="motivo" value="Pedido incompleto">
                        <span>Pedido incompleto</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="motivo" value="Pedido en mal estado">
                        <span>Pedido en mal estado</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="motivo" value="Otro problema">
                        <span>Otro problema</span>
                    </label>
                </div>
                <div class="btn-group">
                    <button class="btn btn-cancelar" id="btnCancelarReporte">Cancelar</button>
                    <button class="btn btn-enviar" id="btnEnviarReporte">Enviar reporte</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/notifications.js"></script>
    <script>
        let pedidoActual = null;
        let ratingComida = 0;
        let ratingDelivery = 0;

        // Verificar pedidos pendientes al cargar
        document.addEventListener('DOMContentLoaded', function() {
            verificarPedidosPendientes();
            configurarEstrellas();
            configurarBotones();
        });

        function verificarPedidosPendientes() {
            fetch('confirmar_pedido.php?action=verificar_pedidos_pendientes')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.pedidos.length > 0) {
                        pedidoActual = data.pedidos[0]; // Tomar el primero
                        mostrarInfoPedido(pedidoActual);
                    } else {
                        // No hay pedidos pendientes, redirigir
                        window.location.href = 'inicio.php';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    notify.error('Error al cargar información del pedido');
                });
        }

        function mostrarInfoPedido(pedido) {
            const pedidoInfo = document.getElementById('pedidoInfo');
            pedidoInfo.innerHTML = `
                <p><strong>Pedido #${pedido.numero_pedido}</strong></p>
                <p>Total: $${parseFloat(pedido.total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</p>
                <p>Repartidor: ${pedido.repartidor_nombre} ${pedido.repartidor_apellido}</p>
            `;
        }

        function configurarEstrellas() {
            configurarGrupoEstrellas('starsComida', (rating) => { ratingComida = rating; });
            configurarGrupoEstrellas('starsDelivery', (rating) => { ratingDelivery = rating; });
        }

        function configurarGrupoEstrellas(containerId, callback) {
            const container = document.getElementById(containerId);
            const stars = container.querySelectorAll('.star');

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const value = parseInt(this.getAttribute('data-value'));
                    container.setAttribute('data-rating', value);
                    callback(value);

                    stars.forEach(s => {
                        const starValue = parseInt(s.getAttribute('data-value'));
                        if (starValue <= value) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });

                star.addEventListener('mouseenter', function() {
                    const value = parseInt(this.getAttribute('data-value'));
                    stars.forEach(s => {
                        const starValue = parseInt(s.getAttribute('data-value'));
                        if (starValue <= value) {
                            s.style.color = '#fbbf24';
                        } else {
                            s.style.color = '#ddd';
                        }
                    });
                });
            });

            container.addEventListener('mouseleave', function() {
                const currentRating = parseInt(container.getAttribute('data-rating'));
                stars.forEach(s => {
                    const starValue = parseInt(s.getAttribute('data-value'));
                    if (starValue <= currentRating) {
                        s.style.color = '#fbbf24';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        }

        function configurarBotones() {
            document.getElementById('btnConfirmar').addEventListener('click', confirmarRecepcion);
            document.getElementById('btnProblema').addEventListener('click', abrirModalReporte);
            document.getElementById('btnCancelarReporte').addEventListener('click', cerrarModalReporte);
            document.getElementById('btnEnviarReporte').addEventListener('click', enviarReporte);
        }

        function confirmarRecepcion() {
            if (ratingComida === 0 || ratingDelivery === 0) {
                notify.warning('Por favor califica tanto la comida como el servicio de entrega');
                return;
            }

            const btnConfirmar = document.getElementById('btnConfirmar');
            btnConfirmar.disabled = true;
            btnConfirmar.innerHTML = '<span>⏳</span><span>Enviando...</span>';

            const comentario = document.getElementById('comentarioCalificacion').value;

            fetch('confirmar_pedido.php?action=confirmar_recepcion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    pedido_id: pedidoActual.id,
                    calificacion_comida: ratingComida,
                    calificacion_delivery: ratingDelivery,
                    comentario: comentario
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notify.success(data.message);
                    setTimeout(() => {
                        window.location.href = 'inicio.php';
                    }, 2000);
                } else {
                    notify.error(data.message);
                    btnConfirmar.disabled = false;
                    btnConfirmar.innerHTML = '<span>✓</span><span>Confirmar recepción</span>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                notify.error('Error al enviar calificación');
                btnConfirmar.disabled = false;
                btnConfirmar.innerHTML = '<span>✓</span><span>Confirmar recepción</span>';
            });
        }

        function abrirModalReporte() {
            document.getElementById('overlayReporte').classList.add('active');
        }

        function cerrarModalReporte() {
            document.getElementById('overlayReporte').classList.remove('active');
        }

        function enviarReporte() {
            const motivo = document.querySelector('input[name="motivo"]:checked').value;
            
            const btnEnviar = document.getElementById('btnEnviarReporte');
            btnEnviar.disabled = true;
            btnEnviar.textContent = 'Enviando...';

            fetch('confirmar_pedido.php?action=reportar_no_recibido', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    pedido_id: pedidoActual.id,
                    motivo: motivo
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notify.success(data.message);
                    cerrarModalReporte();
                    setTimeout(() => {
                        window.location.href = 'inicio.php';
                    }, 2000);
                } else {
                    notify.error(data.message);
                    btnEnviar.disabled = false;
                    btnEnviar.textContent = 'Enviar reporte';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                notify.error('Error al enviar reporte');
                btnEnviar.disabled = false;
                btnEnviar.textContent = 'Enviar reporte';
            });
        }

        // Verificar cada 10 segundos si hay nuevos pedidos
        setInterval(verificarPedidosPendientes, 10000);
    </script>
</body>
</html>
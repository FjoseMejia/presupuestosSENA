// Función para convertir texto en seguro para mostrar en HTML (evita ataques XSS)
function escapeHtml(text) {
  return $('<div>').text(text).html();
}

// Cuando el documento está completamente cargado
$(document).ready(function() {	
    
    // Si no hay año fiscal activo, mostrar el modal de año fiscal
    if(!hayAnioFiscal){
        
        // Esperar un poco para asegurar que todo esté listo
        setTimeout(function() {           
            // Intentar abrir el modal usando Bootstrap
            if (typeof bootstrap !== 'undefined') {
                var modalElement = document.getElementById('modalAnioFiscal');
                if (modalElement) {
                    var modal = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',  // No se puede cerrar haciendo clic fuera
                        keyboard: false     // No se puede cerrar con tecla ESC
                    });
                    modal.show();
                }
            } 
            // Si Bootstrap no funciona, intentar con jQuery
            else if ($.fn.modal) {
                $('#modalAnioFiscal').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: true
                });
            }
            
            // Verificar después de medio segundo si el modal se abrió
            setTimeout(function() {
                if ($('#modalAnioFiscal').is(':visible')) {
                    // Modal visible, todo bien
                } else {
                    // Si no se ve, forzar la visualización
                    console.log('Modal no visible, intentando forzar...');
                    $('#modalAnioFiscal').addClass('show');
                    $('#modalAnioFiscal').css('display', 'block');
                }
            }, 500);
        }, 100);
        
        // Prevenir que el modal se cierre
        $(document).on('hide.bs.modal', '#modalAnioFiscal', function (e) {
            e.preventDefault();
            return false;
        });
    }

    // Cuando se escribe en el campo de valor presupuesto
    $('#valor_presupuesto').on('input', function() {
        let value = $(this).val();
        
        // Eliminar cualquier cosa que no sea número
        value = value.replace(/\D/g, '');

        // Si está vacío, limpiar los campos
        if(value === '') {
            $(this).val('');
            $('#monto_hidden').val('');
            return;
        }

        // Formatear el número con separadores de miles y símbolo de peso
        let formatted = '$' + parseInt(value, 10).toLocaleString('es-CO');

        // Mostrar el valor formateado
        $(this).val(formatted);

        // Guardar el valor numérico limpio en un campo oculto
        $('#monto_hidden').val(value);
    });
  
    // Función para mostrar mensajes temporales (toasts)
    function showToast(message, type = 'info') {
        // Colores según el tipo de mensaje
        const bg = {
            success: 'bg-success text-white',
            error: 'bg-danger text-white', 
            warning: 'bg-warning text-dark',
            info: 'bg-info text-white'
        }[type] || 'bg-info text-white';

        // Asegurarse de que el contenedor de toasts existe
        let container = document.querySelector('#toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '99999';
            document.body.appendChild(container);
        }

        // Verificar si ya existe un toast con el mismo mensaje
        const toastsExistentes = container.querySelectorAll('.toast');
        for (let toast of toastsExistentes) {
            const toastBody = toast.querySelector('.toast-body');
            if (toastBody && toastBody.textContent === message) {
                return; // Ya existe, no crear otro
            }
        }

        // Crear el elemento del toast
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center ${bg} border-0`;
        toastEl.role = 'alert';
        toastEl.ariaLive = 'assertive';
        toastEl.ariaAtomic = 'true';
        toastEl.style.zIndex = '99999';
        
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        container.appendChild(toastEl);

        const bsToast = new bootstrap.Toast(toastEl, { 
            delay: 3000,
            autohide: true
        });
        bsToast.show();

        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }

    // ========== SECCIÓN PARA MANEJAR LOS PERMISOS DE ROLES ==========

    $('.roles-btn-save').on('click', function() {
        
        // Array para guardar todos los permisos seleccionados
        const permisos = [];

        // Recorrer cada ítem del acordeón (cada rol)
        $('.accordion-item').each(function() {
            const $accordionItem = $(this);
            
            // Obtener el ID del rol
            const idRolInput = $accordionItem.find('input[id^="idRol"]');
            const idRol = idRolInput.val();
            
            // Recorrer cada menú dentro del rol
            $accordionItem.find('.list-group-item').each(function() {
                const $menuItem = $(this);
                
                // Obtener el ID del menú
                const idMenuInput = $menuItem.find('input[id^="idMenu"]');
                const idMenu = idMenuInput.val();
                
                // Recorrer cada submenú dentro del menú
                $menuItem.find('.mb-2').each(function() {
                    const $submenuItem = $(this);
                    
                    // Obtener el ID del submenú
                    const idSubMenuInput = $submenuItem.find('input[id^="idSubMenu"]');
                    const idSubMenu = idSubMenuInput.val();
                    
                    // Obtener el radio button seleccionado para este submenú
                    const radioSeleccionado = $submenuItem.find('input[type="radio"]:checked');
                    
                    if (radioSeleccionado.length > 0) {
                        const permisoValue = radioSeleccionado.val();
                        
                        console.log('Procesando permiso:', {
                            idRol: idRol,
                            idMenu: idMenu,
                            idSubMenu: idSubMenu,
                            permiso: permisoValue
                        });
                        
                        // Agregar el permiso del submenú al array
                        permisos.push({
                            idRolFk: idRol,
                            idMenuFk: idMenu,
                            idSubMenuFk: idSubMenu,
                            permiso: permisoValue === '1'  // Convertir a booleano
                        });
                    }
                });
            });
        });
        
        // Si no se seleccionó ningún permiso, mostrar advertencia
        if (permisos.length === 0) {
            showToast('No hay permisos seleccionados para guardar', 'warning');
            return;
        }

        console.log('Permisos a enviar:', permisos);

        // Mostrar estado de "guardando" en el botón
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
        $btn.prop('disabled', true);

        // Enviar los permisos al servidor
        $.ajax({
            url: 'dashboard/actualizar-permiso',  // URL del endpoint
            method: 'POST',
            contentType: 'application/json',      // Enviar como JSON
            data: JSON.stringify({ 
                permisos: permisos,
                _token: '<?= csrf_token() ?>'     // Token de seguridad
            }),
            dataType: 'json',
            success: function(response) {
                // Restaurar el botón a su estado normal
                $btn.html(originalText);
                $btn.prop('disabled', false);
                
                if (response.state === 1) {
                    showToast(response.message, 'success');
                    
                    // Cerrar el modal después de un segundo y medio
                    setTimeout(() => {
                        $('#rolesModal').modal('hide');
                        
                        // Recargar la página después de cerrar el modal
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                        
                    }, 1500);
                } else {
                    // Si hay error, mostrar mensaje del servidor
                    showToast('Error: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                // Restaurar el botón a su estado normal
                $btn.html(originalText);
                $btn.prop('disabled', false);
                console.error('Error:', error);
                // Mostrar error de conexión
                showToast('Error de conexión al guardar permisos', 'error');
            }
        });
    });


    $(document).ready(function() {
        // Función para eliminar semana
        $(document).on('click', '.btn-delete-week', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const semanaId = button.data('semana-id');
            const semanaNombre = button.data('week');
            
            // Mostrar confirmación con SweetAlert2
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Vas a eliminar la ${semanaNombre}. Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    eliminarSemana(semanaId, button);
                }
            });
        });

        // Función para hacer la petición AJAX de eliminación
        function eliminarSemana(idSemana, button) {
            // Mostrar estado de carga en el botón
            const originalHtml = button.html();
            button.html('<i class="fas fa-spinner fa-spin"></i>');
            button.prop('disabled', true);

            $.ajax({
                url: BASE_URL + 'semanas/eliminar', 
                method: 'POST',
                data: {
                    idSemana: idSemana,
                    _token: '<?= csrf_token() ?>' 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: response.message || 'La semana ha sido eliminada correctamente.',
                            icon: 'success',
                            confirmButtonColor: '#39A900'
                        }).then(() => {
                            // Recargar la página o actualizar la tabla
                            location.reload();
                        });
                    } else {
                        // Mostrar error
                        Swal.fire({
                            title: 'Error',
                            text: response.message || 'Error al eliminar la semana.',
                            icon: 'error',
                            confirmButtonColor: '#d33'
                        });
                        // Restaurar botón
                        button.html(originalHtml);
                        button.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al eliminar semana:', error);
                    Swal.fire({
                        title: 'Error de conexión',
                        text: 'No se pudo eliminar la semana. Por favor, intenta nuevamente.',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                    // Restaurar botón
                    button.html(originalHtml);
                    button.prop('disabled', false);
                }
            });
        }
    });

    $('.roles-btn-cancel').on('click', function() {
        $('#rolesModal').modal('hide');
    });


    // Cerrar todos los acordeones cuando se abre el modal de roles
    $('#rolesModal').on('show.bs.modal', function () {
        // Cerrar todos los acordeones dentro del modal
        $(this).find('.accordion-collapse').removeClass('show');
        $(this).find('.accordion-button').addClass('collapsed').attr('aria-expanded', 'false');
    });

    // También asegurar que estén cerrados cuando el modal ya está cargado
    $(document).ready(function() {
        // Cerrar todos los acordeones del modal de roles (si está en el DOM)
        $('#rolesModal .accordion-collapse').removeClass('show');
        $('#rolesModal .accordion-button').addClass('collapsed').attr('aria-expanded', 'false');
    });

});
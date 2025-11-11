
const formularios_ajax=document.querySelectorAll(".FormularioAjax");

// Overlay Global de Carga 
function crearOverlayCarga(){
    let overlay=document.getElementById('global-upload-overlay');
    if(!overlay){
        overlay=document.createElement('div');
        overlay.id='global-upload-overlay';
        overlay.innerHTML=`<div class="loader-box">
            <div class="loader-ring" aria-hidden="true"></div>
            <p class="loader-text">Subiendo reporte...<br><small>No cierres esta ventana</small></p>
        </div>`;
        document.body.appendChild(overlay);
    }
    document.body.classList.add('global-loading');
    overlay.style.display='flex';
}

function ocultarOverlayCarga(){
    const overlay=document.getElementById('global-upload-overlay');
    if(overlay){
        overlay.style.display='none';
    }
    document.body.classList.remove('global-loading');
}

formularios_ajax.forEach(formularios => {

    formularios.addEventListener("submit",function(e){
        
        e.preventDefault();

        Swal.fire({
            title: '¿Estás seguro?',
            text: "Quieres realizar la acción solicitada",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, realizar',
            cancelButtonText: 'No, cancelar'
        }).then((result) => {
            if (result.isConfirmed){

                let data = new FormData(this);
                let method= this.getAttribute("method");
                let action= this.getAttribute("action");

                let encabezados= new Headers();

                let config={
                    method: method,
                    headers: encabezados,
                    mode: 'cors',
                    cache: 'no-cache',
                    body: data
                };

                const esReporte = this.id === 'formReporte';
                if(esReporte){ crearOverlayCarga(); }

                fetch(action,config)
                .then(respuesta => respuesta.json())
                .then(respuesta =>{ 
                    if(esReporte){ ocultarOverlayCarga(); }
                    return alertas_ajax(respuesta);
                })
                .catch(err => {
                    if(esReporte){ ocultarOverlayCarga(); }
                    console.error('Error en la subida:', err);
                    if(window.Swal){
                        Swal.fire('Error','Ocurrió un problema al enviar el formulario.','error');
                    }
                });
            }
        });

    });

});



function alertas_ajax(alerta){
    if(alerta.tipo=="simple"){
        Swal.fire({
            icon: alerta.icono,
            title: alerta.titulo,
            text: alerta.texto,
            confirmButtonText: 'Aceptar'
        });

    }else if(alerta.tipo=="recargar"){
        Swal.fire({
            icon: alerta.icono,
            title: alerta.titulo,
            text: alerta.texto,
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if(result.isConfirmed){
                location.reload();
            }
        });

    }else if(alerta.tipo=="limpiar"){
        Swal.fire({
            icon: alerta.icono,
            title: alerta.titulo,
            text: alerta.texto,
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if(result.isConfirmed){
                document.querySelector(".FormularioAjax").reset();
            }
        });

    }else if(alerta.tipo=="redireccionar"){
        // Mostrar SweetAlert antes de redireccionar
        Swal.fire({
            icon: alerta.icono,
            title: alerta.titulo,
            text: alerta.texto,
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.location.href = alerta.url;
        });
    }
}


// let btn_exit=document.getElementById("btn_exit");

// btn_exit.addEventListener("click", function(e){

//     e.preventDefault();
    
//     Swal.fire({
//         title: '¿Quieres salir del sistema?',
//         text: "La sesión actual se cerrará y saldrás del sistema",
//         icon: 'question',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         confirmButtonText: 'Si, salir',
//         cancelButtonText: 'Cancelar'
//     }).then((result) => {
//         if (result.isConfirmed) {
//             let url=this.getAttribute("href");
//             window.location.href=url;
//         }
//     });

// });
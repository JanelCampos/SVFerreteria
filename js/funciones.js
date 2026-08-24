$(document).ready(function() {
    function login() {
        var usuario = $('#usuario').val();
        var clave = $('#clave').val();
        
        fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                usuario: usuario,
                clave: clave
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.resultado) {
                backup();
                // Redirigir al usuario al sistema
                window.location.href = 'sistema/index.php';
            } else {
                // Mostrar mensaje de error
                $('.alert').removeClass('hidden').text(data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Llamar a la función login cuando se envíe el formulario
    $('.formulario').on('submit', function(e) {
        e.preventDefault(); // Evitar el envío del formulario
        login();
    });

    function backup(){
        $.ajax({
            url: 'script_backup.php', // Ajusta esta ruta al script PHP de backup
            type: 'GET',
            success: function(response) {
                var data = JSON.parse(response);
                if(data.resultado){
                    localStorage.setItem('showMessage', data.mensaje);
                    location.reload();
                } else {
                    mostrarAlertaErrorTiempo(data.mensaje);
                }
            },
            error: function() {
                $('#mensaje').html('<p>Error en la solicitud AJAX.</p>');
            }
        });
    }
});

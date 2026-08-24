//Eventos js 

$(document).ready(function() {
    
    const menuToggle = document.querySelector('.btnMenu');
    const nav = document.querySelector('.menu');
    const menu = document.querySelectorAll('.principal');

    if (menuToggle && nav && menu.length) {
        menu.forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                const submenu = item.querySelector('.subMenu');
                if (!submenu) {
                    return;
                }

                menu.forEach(i => {
                    const otherSub = i.querySelector('.subMenu');
                    if (otherSub && otherSub !== submenu) {
                        otherSub.classList.remove('active');
                    }
                });

                submenu.classList.toggle('active');
            });
        });

        menuToggle.addEventListener('click', () => {
            nav.classList.toggle('viewMenu');
        });

        document.addEventListener('click', (e) => {
            if (!nav.contains(e.target) && !menuToggle.contains(e.target)) {
                nav.classList.remove('viewMenu');
            }
        });
    }

    // const menuItems = document.querySelectorAll('nav ul li');

    // $('.btnMenu').click(function(e){
    //     e.preventDefault();
    //     if($('nav').hasClass('viewMenu')){
    //         $('nav').removeClass('viewMenu');
    //     } else {
    //         $('nav').addClass('viewMenu');
    //     }
    // });

    // $('nav ul li').click(function(){
    //     $('nav ul li ul').slideUp();
    //     $(this).children('ul').slideToggle();
    // });

    $('#buscarNombre').on('keydown',function(e){
        if(e.keyCode === 13){
            e.preventDefault();
            var sistema = getUrl();
            var nombre = $(this).val();
            if(nombre == ""){
                location.href = sistema+'lista_articulos.php';
            }else{
                location.href = sistema+'buscar_articulo.php?nombre='+$(this).val();
            }
        }
    });

    $('#botonBuscarNombre').click(function(e){
        e.preventDefault();
        var sistema = getUrl();
        var nombre = $('#buscarNombre').val();
        var proveedor = $('#nombreProveedor').val();
        if(nombre == "" && proveedor == ""){
            location.href = sistema+'lista_articulos.php';
        }else{
            location.href = sistema+'buscar_articulo.php?nombre='+nombre+'&proveedor='+proveedor;
        }
    });

    //Activar campos para registrar cliente
    $('.btn_new_cliente').click(function(e){
        e.preventDefault();
        $('#nom_cliente').removeAttr('disabled');
        $('#tel_cliente').removeAttr('disabled');
        $('#dir_cliente').removeAttr('disabled');
        $('#cor_cliente').removeAttr('disabled');
        $('#fec_cliente').removeAttr('disabled');

        $('#div_registro_cliente').slideDown();
    });

    //buscar articulos por nombre
    $('#buscar').keyup(function(e){
        if(isNaN('#buscar')){
            e.preventDefault();
        
            var nombreArticulo = $(this).val();
            var action = 'buscarArticulo';
            
            $.ajax({
                url: '../ajax.php',
                type: "POST",
                async: true,
                data: {action:action,buscar:nombreArticulo},
                success: function(response){

                    if(response == 0){
                        $('#tituloTarjeta').html('');
                        $('#resultadoBusqueda').html('');
                    } else {
                        var data = $.parseJSON(response);
                        $('#tituloTarjeta').html("Resultados encontrados" + ' ' + data.length + ' :');

                        // Limpiar el contenido anterior en #resultadoBusqueda
                        $('#resultadoBusqueda').html('');

                        // Utilizar $.each para iterar sobre el array
                        $.each(data, function(i, item) {
                            $('#resultadoBusqueda').append(item.IdArticulo + ' - ' + item.Nombre + ' - ' +
                                item.Cantidad + ' - ' + item.Precio_Unitario + '<br>');
                        });
                    }
                },
                error: function(error){
    
                }
            });
        };

    });

    //Buscar Cliente -socio-
    $('#dni_cliente').keyup(function(e){
        e.preventDefault();

        var cl = $(this).val();
        var action = 'searchCliente';

        $.ajax({
            url: '../ajax.php',
            type: "POST",
            async: true,
            data: {action:action,cliente:cl},

            success: function(response){

                if(response == 0){
                    $('#idcliente').val('');
                    $('#nom_cliente').val('');
                    $('#tel_cliente').val('');
                    $('#dir_cliente').val('');
                    $('#cor_cliente').val('');
                    //Mostrar boton agregar
                    $('.btn_new_cliente').slideDown();
                } else {
                    var data = $.parseJSON(response);
                    $('#idcliente').val(data.Id_Cliente);
                    $('#nom_cliente').val(data.Nombre);
                    $('#tel_cliente').val(data.Telefono);
                    $('#dir_cliente').val(data.Direccion);
                    $('#cor_cliente').val(data.Email);
                    //Ocultar boton agregar
                    $('.btn_new_cliente').slideUp();

                    //Bloque campos
                    $('#nom_cliente').attr('disabled','disabled');
                    $('#tel_cliente').attr('disabled','disabled');
                    $('#dir_cliente').attr('disabled','disabled');
                    $('#cor_cliente').attr('disabled','disabled');
                    $('#fec_cliente').attr('disabled','disabled');

                    //Ocultar boton guardar
                    $('#div_registro_cliente').slideUp();

                }
            },
            error: function(error){

            }
        });
    });

    //Crear cliente - Ventas
    $('#form_new_cliente_venta').submit(function(e){
        e.preventDefault();

        $.ajax({
            url: '../ajax.php',
            type: "POST",
            async: true,
            data: $('#form_new_cliente_venta').serialize(),

            success: function(response){
                if(response != 'error'){
                    //Agregar id a input hidden
                    $('#idcliente').val(response);
                    //Bloque campos
                    $('#nom_cliente').attr('disabled','disabled');
                    $('#tel_cliente').attr('disabled','disabled');
                    $('#dir_cliente').attr('disabled','disabled');
                    $('#cor_cliente').attr('disabled','disabled');
                    $('#fec_cliente').attr('disabled','disabled');

                    //Ocultar boton agregar
                    $('.btn_new_cliente').slideUp();
                    //Ocultar boton guardar
                    $('#div_registro_cliente').slideUp();
                }
            },
            error: function(error){

            }
        });
    });

    //Calcular vuelto
    $('#pagoEfectivo').keyup(function(e){
        e.preventDefault();

        var montoCobrar = parseFloat($('#monto').val());
        var pagoEfectivo = parseFloat($('#pagoEfectivo').val());
        if(isNaN(pagoEfectivo)){
            $('#cambio').val('0');
        }else{
            var vuelto = Math.abs(montoCobrar - pagoEfectivo);

            if(pagoEfectivo < montoCobrar ){
                $('#cambio').val('0');
            }else{
                $('#cambio').val(vuelto.toFixed(2));
            }
        }
        
    });

    //Buscar Producto - Ventas
    $('#txt_cod_producto').keyup(function(e){
        e.preventDefault();

        var producto = $(this).val();
        var action = 'infoProducto';

        if(producto != ''){
            $.ajax({
                url: '../ajax.php',
                type: "POST",
                async: true,
                data: {action:action,articulo:producto},
    
                success: function(response){
                    if(response != 'error'){
                        var info = JSON.parse(response);
                        
                        var existencia = info.Cantidad;
                        
                        if(existencia <= 0){
                            
                            //Bloquear Cantidad
                            $('#txt_cant_producto').attr('disabled','disabled');
                            $('#txt_cant_producto').css('background-color','#ff0000');
                            $('#txt_precio').attr('disabled','disabled');

                            //Ocultar botón agregar
                            $('#add_product_venta').slideUp();
                        }else{

                            $('#txt_nombre').html(info.Nombre);
                            $('#txt_existencia').html(info.Cantidad);
                            $('#txt_cant_producto').val('1');
                            $('#txt_precio').val((info.Precio_Unitario));
                            $('#txt_precio_total').html(info.Precio_Unitario);

                            //Activar Cantidad
                            $('#txt_cant_producto').removeAttr('disabled');
                            $('#txt_cant_producto').css('background-color','');
                            $('#txt_precio').removeAttr('disabled');
                            
                            //Mostrar botón agregar
                            $('#add_product_venta').slideDown();        
                        }
                        
                    } else {
                        $('#txt_nombre').html('-');
                        $('#txt_existencia').html('-');
                        $('#txt_cant_producto').val('0');
                        $('#txt_precio').val('0.00');
                        $('#txt_precio_total').html('0.00');

                        //Bloquear Cantidad
                        $('#txt_cant_producto').attr('disabled','disabled');
                        $('#txt_precio').attr('disabled','disabled');

                        //Ocultar botón agregar
                        $('#add_product_venta').slideUp();
                    }
                },
                error: function(error){
    
                }
            });
        }
    });

    //validar precio de producto
    $('#txt_precio').keyup(function(e){
        e.preventDefault();
        var precio_total = $(this).val() * $('#txt_cant_producto').val();
        var existencia = parseInt($('#txt_existencia').html());
        $('#txt_precio_total').html(precio_total.toFixed(2));

        //Ocultar el boton agregar si el precio no es válido
        if( ($(this).val() <= 0 || isNaN($(this).val())) || (existencia <= 0)){
            $('#add_product_venta').slideUp();
        } else {
            $('#add_product_venta').slideDown();
        }
    });

    //Validar Cantidad del artículo antes de agregar
    $('#txt_cant_producto').keyup(function(e){
        e.preventDefault();
        var precio_total = $(this).val() * $('#txt_precio').val();
        var existencia = parseInt($('#txt_existencia').html());
        $('#txt_precio_total').html(precio_total.toFixed(2));

        //Ocultar el boton agregar si la cantidad es menor que 1
        if( ($(this).val() <= 0 || isNaN($(this).val())) || ($(this).val() > existencia) || (existencia <= 0)){
            $('#add_product_venta').slideUp();
        } else {
            $('#add_product_venta').slideDown();
        }
    });

    //Agregar productos al detalle
    $('#add_product_venta').click(function(e){
        e.preventDefault();

        if($('#txt_cant_producto').val() > 0){
            var codproducto = $('#txt_cod_producto').val();
            var cantidad    = $('#txt_cant_producto').val();
            var precioUnitario = $('#txt_precio').val();
            var action = 'addProductDetalle';

            $.ajax({
                url:'../ajax.php',
                type:"POST",
                async: true,
                data: {action:action,producto:codproducto,cantidad:cantidad,precioUnitario:precioUnitario},

                success: function(response){
                    if (response != 'error'){
                        var info = JSON.parse(response);
                        $('#detalle_venta').html(info.detalle);
                        $('#detalle_totales').html(info.totales);

                        $('#txt_cod_producto').val('');
                        $('#txt_nombre').html('-');
                        $('#txt_existencia').html('-');
                        $('#txt_cant_producto').val('0');
                        $('#txt_precio').val('0.00');
                        $('#txt_precio_total').html('0.00');

                        //Bloquear Cantidad
                        $('#txt_cant_producto').attr('disabled','disabled');
                        $('#txt_precio').attr('disabled','disabled');

                        //Ocultar boton agregar
                        $('#add_product_venta').slideUp();

                        //Añadir monto a datos de venta
                        $('#monto').val(info.total);

                    } else {
                        console.log('no data');
                    }
                    viewProcesar();
                },
                error: function(error){

                }
            });
        }
    });

    //calcular eventos para estructura de metodo de pago
    $('#medioPago').on('change',function (){
        var valorSeccionado = $('#medioPago').val();
        if(valorSeccionado === 'efectivo'){
            $('#pagoEfectivo').removeAttr('disabled');
            $('#pagoEfectivo').attr('required');
            $('#pagoTarjeta').attr('disabled','disabled');
            $('#pagoTarjeta').removeAttr('required');
            $('#pagoTarjeta').val('0');
        }else if(valorSeccionado === 'tarjeta'){
            $('#pagoTarjeta').removeAttr('disabled');
            $('#pagoTarjeta').attr('required');
            $('#pagoEfectivo').attr('disabled','disabled');
            $('#pagoEfectivo').removeAttr('required');
            $('#pagoEfectivo').val('0');
        }else{
            $('#pagoEfectivo').removeAttr('disabled');
            $('#pagoTarjeta').removeAttr('disabled');
            $('#pagoEfectivo').attr('required');
            $('#pagoTarjeta').attr('required');
        }
    });

    //Anular Venta
    $('#btn_anular_venta').click(function(e){
        e.preventDefault();

        var rows = $('#detalle_venta tr').length;
        if(rows > 0){
            var action = 'anularVenta';

            $.ajax({
                url: '../ajax.php',
                type: "POST",
                async: true,
                data: {action:action},

                success: function(response){
                    if(response != 'error'){
                        location.reload();
                    }
                },
                error: function(error){

                }
            })
        }

        var idCliente = $('#dni_cliente').val();
        if(idCliente !== ""){
            var action = 'anularVenta';

            $.ajax({
                url: '../ajax.php',
                type: "POST",
                async: true,
                data: {action:action},

                success: function(response){
                    if(response != 'error'){
                        location.reload();
                    }
                },
                error: function(error){

                }
            })
        }
        
    });

    //Facturar Venta
    $('#btn_facturar_venta').click(function(e){
        e.preventDefault();

        var rows = $('#detalle_venta tr').length;
        if(rows > 0){
            var action = 'procesarVenta';
            var codcliente = $('#idcliente').val();
            if(codcliente != ""){
                var pagoEfectivo = $('#pagoEfectivo').val();
                var pagoTarjeta = $('#pagoTarjeta').val();
                var medioPago = $('#medioPago').val();
                var estadoVenta = $('#estadoVenta').val();
                var fechaVenta = $('#fechaVenta').val();

                $.ajax({
                    url: '../ajax.php',
                    type: "POST",
                    async: true,
                    data: {action:action,codcliente:codcliente,pagoEfectivo:pagoEfectivo,pagoTarjeta:pagoTarjeta,
                            medioPago:medioPago,estadoVenta:estadoVenta,fechaVenta:fechaVenta},
    
                    success: function(response){
                        console.log(response);
                        if(response != 'error'){
                            var info = JSON.parse(response);
                            //console.log(info);
                            generarPDF(info.Id_Cliente,info.IdVenta);
                            location.reload();
                        } else {
                            console.log('no data');
                            $('#alerta').html('Todos los campos son obligatorios');
                        }
                    },
                    error: function(error) {
                    }
                });
            }else{
                $('#alerta').html('El cliente es necesario');
            }
            
        }else{
            $('#alerta').html('Necesita seleccionar algún producto');
        }
    });

    //Cambiar contraseña
    $('.newPass').keyup(function(){
        validPass();
    });

    //Form Cambiar contraseña
    $('#frmChangePass').submit(function(e){
        e.preventDefault();

        var passActual = $('#txtPassUser').val();
        var passNuevo = $('#txtNewPassUser').val();
        var confirmPassNuevo = $('#txtPassConfirm').val();
        var action = "changePassword";

        if(passNuevo != confirmPassNuevo){
            $('.alertChangePass').html('<p style="color:red;">Las contraseñas no son iguales. </p>');
            $('.alertChangePass').slideDown();
            return false;
        }
    
        if(passNuevo.length < 5){
            $('.alertChangePass').html('<p style="color:red;">La nueva contraseña debe ser de 5 caracteres como mínimo. </p>');
            $('.alertChangePass').slideDown();
            return false;
        }

        $.ajax({
            url : '../ajax.php',
            type: "POST",
            async : true,
            data: {action:action,passActual:passActual,passNuevo:passNuevo},

            success: function(response){
                if(response != 'error'){
                    var info = JSON.parse(response);
                    if(info.cod == '00'){
                        $('.alertChangePass').html('<p style="color:green;">'+info.msg+'</p>');
                        $('#frmChangePass')[0].reset();
                    } else {
                        $('.alertChangePass').html('<p style="color:red;">'+info.msg+'</p>');
                    }
                    $('.alertChangePass').slideDown();
                }
            },
            error: function(error){
            }
        });
    });

});

function validPass(){
    var passNuevo = $('#txtNewPassUser').val();
    var confirmPassNuevo = $('#txtPassConfirm').val();
    if(passNuevo != confirmPassNuevo){
        $('.alertChangePass').html('<p style="color:red;">Las contraseñas no son iguales. </p>');
        $('.alertChangePass').slideDown();
        return false;
    }

    if(passNuevo.length < 5){
        $('.alertChangePass').html('<p style="color:red;">La nueva contraseña debe ser de 5 caracteres como mínimo. </p>');
        $('.alertChangePass').slideDown();
        return false;
    }
    $('.alertChangePass').html('');
    $('.alertChangePass').slideUp();
}

function generarPDF(cliente,factura){
    var ancho = 1120;
    var alto = 800;
    //Calcular posicion x,y para centrar la ventana
    var x = parseInt((window.screen.width/2) - (ancho / 2));
    var y = parseInt((window.screen.height/2) - (alto / 2));

    $url = '../operaciones/generarFactura.php?cl='+cliente+'&f='+factura;
    window.open($url,"Factura","left="+x+",top="+y+",height="+alto+",width="+ancho+",scrollbar=si,location=no,resizable=si,menubar=no");
}

function del_product_detalle(correlativo){
    var action = 'delProductDetalle';
    var id_detalle = correlativo;

    $.ajax({
        url: '../ajax.php',
        type: "POST",
        async: true,
        data: {action:action,id_detalle:id_detalle},

        success: function(response){
            if(response != 'error'){
                var info = JSON.parse(response);
                $('#detalle_venta').html(info.detalle);
                $('#detalle_totales').html(info.totales);

                $('#txt_cod_producto').val('');
                $('#txt_nombre').html('-');
                $('#txt_existencia').html('-');
                $('#txt_cant_producto').val('0');
                $('#txt_precio').html('0.00');
                $('#txt_precio_total').html('0.00');

                //Bloquear Cantidad
                $('#txt_cant_product').attr('disabled','disabled');

                //Ocultar boton agregar
                $('#add_product_venta').slideUp();

                //Añadir monto a datos de venta
                $('#monto').val(info.total);
            } else {
                $('#detalle_venta').html('');
                $('#detalle_totales').html('');
                $('#monto').val('0');
            }
            viewProcesar();
        },
        error: function(error){

        }
    })
}

function viewProcesar(){
    if($('#detalle_venta tr').length > 0){
        $('#btn_facturar_venta').show();
    } else {
        $('#btn_facturar_venta').hide();
    }
}

function searchForDetalle(id){
    var action = 'searchForDetalle';
    var user = id;

    $.ajax({
        url: '../ajax.php',
        type: "POST",
        async: true,
        data: {action:action,user:user},

        success: function(response){
            if(response != 'error'){
                var info = JSON.parse(response);
                $('#detalle_venta').html(info.detalle);
                $('#detalle_totales').html(info.totales);
            } else {
                // console.log('no data');
            }
            viewProcesar();
        },
        error: function(error){

        }
    })
}

function getUrl(){
    var loc = window.location;
    var pathName = loc.pathname.substring(0, loc.pathname.lastIndexOf('/') + 1);
    return loc.href.substring(0, loc.href.lenght - ((loc.pathname + loc.search + loc.hash).lenght - pathName.lenght));
}

function ocultarFormulario(idFormulario) {
    if (window.hideLegacyModal) {
        window.hideLegacyModal(idFormulario);
    } else {
        document.getElementById(idFormulario).style.display = 'none';
    }

    if(idFormulario === "procesarPago"){
        location.reload();
    }else if(idFormulario === "cancelarPago"){
        location.reload();
    }else if(idFormulario === "pagarPrestamo"){
        document.getElementById('monto').value = '';
        document.getElementById('idPrestamo').value = '';
        document.getElementById('busquedaPrestamo').focus();
    }else if(idFormulario === 'añadirProducto'){
        document.getElementById('idProducto').value = '';
        document.getElementById('nombreProducto').value = '';
        document.getElementById('stockProducto').value = '';
        document.getElementById('precioVenta').value = '';
        document.getElementById('stockVenta').value = 1;
        document.getElementById('palabraClave').focus();
        document.getElementById('palabraClave').value = '';
        document.getElementById('aplicarDescuento').checked = false;
        if (document.getElementById('unidadVenta')) {
            document.getElementById('unidadVenta').innerHTML = '<option value="">Seleccione</option>';
            document.getElementById('unidadVenta').value = '';
        }
        if (document.getElementById('factorAplicado')) document.getElementById('factorAplicado').value = 1;
        if (document.getElementById('precioMinimoMostrar')) document.getElementById('precioMinimoMostrar').value = '';
        if (document.getElementById('precioMinimoArticulo')) document.getElementById('precioMinimoArticulo').value = 0;
        if (document.getElementById('descuentoMostrar')) document.getElementById('descuentoMostrar').value = 0;
        if (document.getElementById('porcentajeDescuentoAplicado')) document.getElementById('porcentajeDescuentoAplicado').value = 0;
        if (document.getElementById('precioConDescuentoMostrar')) document.getElementById('precioConDescuentoMostrar').value = '';
        if (document.getElementById('subTotalMostrar')) document.getElementById('subTotalMostrar').value = '';
        if (document.getElementById('unidadSeleccionada')) document.getElementById('unidadSeleccionada').value = '';
        if (document.getElementById('infoDescuentosEscalonados')) document.getElementById('infoDescuentosEscalonados').innerHTML = '';
    }else if(idFormulario === 'pagarVenta'){
        document.getElementById('busquedaVenta').focus();
    }else if(idFormulario === 'cambiarRol'){
        document.getElementById('busqueda').focus();
    }else if(idFormulario === 'editarUsuario'){
        document.getElementById('busqueda').focus();
    }else if(idFormulario === 'eliminarUsuario'){
        document.getElementById('busqueda').focus();
    }else if(idFormulario === 'cambiarClave'){
        document.getElementById('busqueda').focus();
    }else if(idFormulario === 'editarProveedor'){
        document.getElementById('busquedaProveedor').focus();
    }else if(idFormulario === 'eliminarProveedor'){
        document.getElementById('busquedaProveedor').focus();
    }else if(idFormulario === 'editarCliente'){
        document.getElementById('busquedaCliente').focus();
    }else if(idFormulario === 'eliminarCliente'){
        document.getElementById('busquedaCliente').focus();
    }else if(idFormulario === 'reiniciarMetricas'){
        document.getElementById('busquedaCliente').focus();
    }else if(idFormulario === 'abrirCaja'){
        location.reload();
    }else if(idFormulario === 'cerrarCaja'){
        location.reload();
    }else if(idFormulario === 'backup'){
        location.reload();
    }else if(idFormulario === 'añadirStock'){
        if (document.getElementById('cantidadOriginalAñadir')) document.getElementById('cantidadOriginalAñadir').value = '';
        if (document.getElementById('cantidadAñadir')) document.getElementById('cantidadAñadir').value = '';
        if (document.getElementById('equivalenteAñadirInfo')) {
            document.getElementById('equivalenteAñadirInfo').style.display = 'none';
            document.getElementById('equivalenteAñadirInfo').innerHTML = '';
        }
        document.getElementById('busquedaArticulo').focus();
    }else if(idFormulario === 'salidaStock'){
        if (document.getElementById('cantidadOriginalSalida')) document.getElementById('cantidadOriginalSalida').value = '';
        if (document.getElementById('cantidadSalida')) document.getElementById('cantidadSalida').value = '';
        if (document.getElementById('equivalenteSalidaInfo')) {
            document.getElementById('equivalenteSalidaInfo').style.display = 'none';
            document.getElementById('equivalenteSalidaInfo').innerHTML = '';
        }
        document.getElementById('busquedaArticulo').focus();
    }else if(idFormulario === 'eliminarPrestamo'){
        document.getElementById('idPrestamoEliminar').value = '';
        document.getElementById('busquedaPrestamo').focus();
    }else if(idFormulario === 'editarArticulo'){
        document.getElementById('busquedaArticulo').focus();
    }else if(idFormulario === 'eliminarArticulo'){
        document.getElementById('busquedaArticulo').focus();
    }else if(idFormulario === 'eliminarVenta'){
        document.getElementById('busquedaVenta').focus();
    }else if(idFormulario === 'anularVenta'){
        document.getElementById('busquedaVenta').focus();
    }else if(idFormulario === 'pagarVentaDia'){
        document.getElementById('busquedaVentaDia').focus();
    }else if(idFormulario === 'anularVentaDia'){
        document.getElementById('busquedaVentaDia').focus();
    }else if(idFormulario === 'eliminarVentaDia'){
        document.getElementById('busquedaVentaMes').focus();
    }else if(idFormulario === 'pagarVentaMes'){
        document.getElementById('busquedaVentaMes').focus();
    }else if(idFormulario === 'anularVentaMes'){
        document.getElementById('busquedaVentaMes').focus();
    }else if(idFormulario === 'eliminarVentaMes'){
        document.getElementById('busquedaVentaMes').focus();
    }else if(idFormulario === 'anularGasto'){
        document.getElementById('busquedaGasto').focus();
    }
}

function mostrarFormulario(idFormulario, id){
    if (window.showLegacyModal) {
        window.showLegacyModal(idFormulario);
    } else {
        document.getElementById(idFormulario).style.display = 'block';
    }

    if(idFormulario === 'eliminarPrestamo'){
        document.getElementById('idPrestamoEliminar').value = id;
    }else if(idFormulario === "eliminarVenta"){
        fetch('../reportes/get_ventas.php?id='+id)
            .then(response => response.json())
            .then(data => {
                if(data.resultado){
                    document.getElementById('idVenta').value = id;
                    document.getElementById('estadoVentaEliminacion').value = data.datos.Estado;
                }else{
                    if (window.hideLegacyModal) {
                        window.hideLegacyModal(idFormulario);
                    } else {
                        document.getElementById(idFormulario).style.display = 'none';
                    }
                    mostrarAlertaErrorTiempo(data.mensaje);
                }
            })
    }else if(idFormulario === "pagarPrestamo"){
        fetch('../prestamos/get_prestamo.php?id='+id)
            .then(response => response.json())
            .then(data => {
                document.getElementById('idPrestamo').value = data.idPrestamo;
                document.getElementById('montoPagar').value = data.montoPagar;
                document.getElementById('monto').value = data.montoPagar;
                document.getElementById('monto').focus();
                document.getElementById('nombrePrestamista').value = data.nombre;
            })
    }else if(idFormulario === "añadirProducto"){
        fetch('../operaciones/get_producto.php?id='+id)
            .then(response => response.json())
            .then(resp => {
                if (!resp.resultado) {
                    mostrarAlertaErrorTiempo('No se pudo cargar el producto');
                    return;
                }
                var data = resp.datos;
                var unidades = resp.unidades || [];
                var descuentos = resp.descuentos || [];
                document.getElementById('idProducto').value = data.IdArticulo;
                document.getElementById('nombreProducto').value = data.Nombre;
                document.getElementById('stockProducto').value = data.Cantidad;
                document.getElementById('cantidadActual').value = data.Cantidad;
                var precioMinimo = parseFloat(data.Precio_Minimo) || 0;
                document.getElementById('precioMinimoArticulo').value = precioMinimo.toFixed(2);
                document.getElementById('precioMinimoMostrar').value = precioMinimo.toFixed(2);
                var unidadBase = data.Unidad_Base || 'unidad';
                var selectUdM = document.getElementById('unidadVenta');
                selectUdM.innerHTML = '';
                unidades.forEach(function(ud) {
                    var opt = document.createElement('option');
                    opt.value = ud.IdUnidad;
                    opt.textContent = ud.Unidad;
                    opt.dataset.unidad = ud.Unidad;
                    opt.dataset.factor = parseFloat(ud.Factor);
                    opt.dataset.CantidadEquivalente = ud.CantidadEquivalente;
                    opt.dataset.predeterminada = ud.Predeterminada;
                    opt.dataset.precMinimo = ud.PrecioMinimo;
                    opt.dataset.precVenta = ud.PrecioVenta;
                    if (ud.Predeterminada == 1) opt.selected = true;
                    selectUdM.appendChild(opt);
                });
                if (selectUdM.options.length == 0) {
                    var opt = document.createElement('option');
                    opt.value = 0;
                    opt.textContent = unidadBase + ' (x1.00)';
                    opt.dataset.unidad = unidadBase;
                    opt.dataset.factor = 1;
                    selectUdM.appendChild(opt);
                }
                window._descuentosArticuloActual = descuentos;
                var infoHtml = '';
                if (descuentos.length > 0) {
                    infoHtml = '<strong>Escalas de descuento configuradas:</strong><br>';
                    descuentos.forEach(function(d){
                        infoHtml += '• ≥ ' + d.CantMinima + ' unidad(es) → ' + parseFloat(d.Porcentaje).toFixed(2) + '%<br>';
                    });
                } else {
                    infoHtml = 'Sin descuentos escalonados configurados para este artículo.';
                }
                document.getElementById('infoDescuentosEscalonados').innerHTML = infoHtml;
                document.getElementById('precioVenta').value = parseFloat(data.Precio_Unitario || 0).toFixed(2);
                document.getElementById('stockVenta').value = 1;
                cambiarUnidadVenta();
            })
    }else if(idFormulario === 'anularVenta'){
        fetch('../reportes/get_ventas.php?id='+id)
            .then(response => response.json())
            .then(data => {
                if(data.resultado){
                    var ventaTotal = data.datos.Total;
                    var saldoVenta = data.datos.saldo;
                    var totalPagado = ventaTotal - saldoVenta;
                    document.getElementById('idVenta').value = data.datos.IdVenta;
                    document.getElementById('montoVenta').value = parseFloat(data.datos.Total);
                    document.getElementById('montoPagado').value = totalPagado;
                    document.getElementById('saldoVenta').value = parseFloat(data.datos.saldo);
                }else{
                    if (window.hideLegacyModal) {
                        window.hideLegacyModal(idFormulario);
                    } else {
                        document.getElementById(idFormulario).style.display = 'none';
                    }
                    mostrarAlertaErrorTiempo(data.mensaje);
                }
            })
    }else if(idFormulario === 'pagarVenta'){
        fetch('../reportes/get_ventas.php?id='+id)
            .then(response => response.json())
            .then(data => {
                if(data.resultado){
                    document.getElementById('idPagarVentaPendiente').value = data.datos.IdVenta;
                    document.getElementById('montoTotalVenta').value = parseFloat(data.datos.saldo.toFixed(2));
                    document.getElementById('efectivoPendiente').value = parseFloat(data.datos.saldo.toFixed(2));
                    document.getElementById('utilidadVentaPendiente').value = parseFloat(data.datos.utilidad);
                }else{
                    if (window.hideLegacyModal) {
                        window.hideLegacyModal(idFormulario);
                    } else {
                        document.getElementById(idFormulario).style.display = 'none';
                    }
                    mostrarAlertaErrorTiempo(data.mensaje);
                }
            })
    }else if(idFormulario === 'editarUsuario'){
        fetch('../usuarios/get_usuario.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.resultado){
                document.getElementById('idUsuario').value = data.empleado.IdEmpleado;
                document.getElementById('nombreUsuario').value = data.empleado.Nombre;
                document.getElementById('dniUsuarioEditar').value = data.empleado.Dni;
                document.getElementById('direccionUsuario').value = data.empleado.Direccion;
                document.getElementById('telefonoUsuario').value = data.empleado.Telefono;
                document.getElementById('correoUsuario').value = data.empleado.Email;
                document.getElementById('usuario').value = data.empleado.Usuario;
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })
    }else if(idFormulario === 'eliminarUsuario'){
        document.getElementById('idUsuarioEliminar').value = id;
    }else if(idFormulario === 'cambiarClave'){
        document.getElementById('idUsuarioClave').value = id;
    }else if(idFormulario === 'cambiarRol'){
        fetch('../usuarios/get_usuario.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.resultado){
                document.getElementById('idUsuarioRol').value = data.empleado.IdEmpleado;
                document.getElementById('rolActual').value = data.empleado.rol;
                idUsuarioClave
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })

        fetch('../usuarios/get_roles.php', {
            method: 'GET',
            headers: {
                'content-type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.resultado) {
                var selectRoles = document.getElementById('nuevoRol');
        
                // Verificar si datos.datos es un array antes de iterar
                if (Array.isArray(data.datos)) {
                    data.datos.forEach(rol => {
                        var option = document.createElement('option');
                        option.value = rol.IdRol;
                        option.textContent = rol.rol;
                        selectRoles.appendChild(option);
                    });
                } else {
                    console.error('Error: datos.datos no es un array');
                    mostrarAlertaErrorTiempo('Error al obtener los roles');
                }
            } else {
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })
    }else if(idFormulario === 'editarCliente'){
        get_cliente(id).then(data => {
            if(data.resultado){
                document.getElementById('idClienteEditar').value = data.datos.Id_Cliente;
                document.getElementById('nombreClienteEditar').value = data.datos.Nombre;
                document.getElementById('dniClienteEditar').value = data.datos.Dni;
                document.getElementById('telefonoClienteEditar').value = data.datos.Telefono;
                document.getElementById('direccionClienteEditar').value = data.datos.direccion;
                document.getElementById('fecha_registroClienteEditar').value = data.datos.Fecha_Registro;
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        }).catch(error => {
            console.error('Error:', error);
        });
    }else if(idFormulario === 'eliminarCliente'){
        get_cliente(id).then(data => {
            if(data.resultado){
                document.getElementById('idClienteEliminar').value = data.datos.Id_Cliente;
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        }).catch(error => {
            console.error('Error:', error);
        });
    }else if(idFormulario === 'reiniciarMetricas'){
        document.getElementById('idClienteMetricas').value = id;
    }else if(idFormulario === 'editarProveedor'){
        fetch('../proveedores/get_proveedor.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.resultado){
                document.getElementById('idProveedoreditar').value = data.datos.IdProveedor;
                document.getElementById('nombreProveedorEditar').value = data.datos.Nombre;
                document.getElementById('direccionProveedorEditar').value = data.datos.Direccion;
                document.getElementById('telefonoProveedorEditar').value = data.datos.Telefono;
                document.getElementById('correoProveedorEditar').value = data.datos.Email;
                document.getElementById('ruc').value = data.datos.ruc;
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })
    }else if(idFormulario === 'eliminarProveedor'){
        document.getElementById('idProveedorEliminar').value = id;
    }else if(idFormulario === 'editarCategoria'){
        fetch('../categorias/get_categoria.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.resultado){
                document.getElementById('idCategoriaeditar').value = data.datos.IdCategoria;
                document.getElementById('nombreCategoriaEditar').value = data.datos.Nombre;
                document.getElementById('descripcionCategoriaEditar').value = data.datos.Descripcion || '';
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })
    }else if(idFormulario === 'eliminarCategoria'){
        document.getElementById('idCategoriaEliminar').value = id;
    }else if(idFormulario === 'procesarPago'){
        fetch('../prestamos/get_cuota.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.resultado){
                document.getElementById('idCuotaPago').value = data.datos.idCuota;
                document.getElementById('idPrestamoPago').value = data.datos.idPrestamo;
                document.getElementById('montoCuotaPago').value = data.datos.montoCuota;
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })
    }else if(idFormulario === 'cancelarPago'){
        fetch('../prestamos/get_cuota.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.resultado){
                document.getElementById('idCuotaCancelar').value = data.datos.idCuota;
                document.getElementById('idPrestamoCancelar').value = data.datos.idPrestamo;
                document.getElementById('montoCuotaCancelar').value = data.datos.montoCuota;
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })
    }else if(idFormulario === 'añadirStock'){
        fetch('../articulos/get_articulo.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.resultado){
                document.getElementById('idArticuloAñadir').value = data.datos.IdArticulo;
                document.getElementById('nombreProductoAñadir').value = data.datos.Nombre;
                document.getElementById('precio_compraAñadir').value = data.datos.Precio_Compra;
                document.getElementById('cantidadActualAñadir').value = data.datos.Cantidad;
                var selA = document.getElementById('unidadAñadirSelect');
                selA.innerHTML = '';
                var ub = (data.datos.Unidad_Presentacion || 'unidad').toString();
                selA.innerHTML += '<option value="__BASE__" data-factor="1" data-unidad="' + ub + '">' + ub + ' (unidad de presentación)</option>';
                if (Array.isArray(data.unidades)) {
                    data.unidades.forEach(function (u) {
                        var nom = (u.Unidad || '').toString();
                        var fac = parseFloat(u.FactorEquivalencia || 0);
                        if (!nom || fac <= 0) return;
                        selA.innerHTML += '<option value="' + nom + '" data-factor="' + fac + '" data-unidad="' + nom + '">' + nom + ' (×' + fac + ' ' + ub + ')</option>';
                    });
                }
                if (selA.options.length > 0) selA.selectedIndex = 0;
                document.getElementById('cantidadOriginalAñadir').value = '';
                document.getElementById('cantidadAñadir').value = '';
                document.getElementById('equivalenteAñadirInfo').style.display = 'none';
                document.getElementById('equivalenteAñadirInfo').innerHTML = '';
                document.getElementById('cantidadOriginalAñadir').focus();
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })
    }else if(idFormulario === 'editarArticulo'){
        fetch('../articulos/get_articulo.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.resultado){
                document.getElementById('idArticuloeditar').value = data.datos.IdArticulo;
                document.getElementById('codigoBarrasEditar').value = data.datos.codigoBarras;
                document.getElementById('nombreProductoEditar').value = data.datos.Nombre;
                document.getElementById('precioCompraEditar').value = data.datos.Precio_Compra;
                document.getElementById('precioVentaEditar').value = data.datos.Precio_Unitario;
                document.getElementById('precioMinimoEditar').value = data.datos.Precio_Minimo || 0;
                document.getElementById('stockAlertaEditar').value = data.datos.Stock_Alerta || 5;
                document.getElementById('unidadActual').value = data.datos.Unidad_Presentacion || 'unidad';
                document.getElementById('proveedorActual').value = data.datos.nombreProveedor;
                document.getElementById('categoriaActual').value = data.datos.nombreCategoria || 'Sin asignar';
                var categoriasSelect = $('#nuevaCategoria');
                categoriasSelect.empty();
                categoriasSelect.append('<option value="">Mantener categoria actual</option>');
                if (data.categorias && Array.isArray(data.categorias)) {
                    $.each(data.categorias, function(i, c) {
                        categoriasSelect.append('<option value="' + c.IdCategoria + '">' + c.Nombre + '</option>');
                    });
                }
                var proveedoresSelect = $('#nuevoProveedor');
                proveedoresSelect.empty();
                proveedoresSelect.append('<option value="">Mantener proveedor actual</option>');
                if (data.proveedores && Array.isArray(data.proveedores)) {
                    $.each(data.proveedores, function(index, proveedor) {
                        proveedoresSelect.append('<option value="' + proveedor.IdProveedor + '">' + proveedor.Nombre + '</option>');
                    });
                }
                renderFilasUnidades(data.unidades || []);
                renderFilasDescuentos(data.descuentos || []);
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })
    }else if(idFormulario === 'eliminarArticulo'){
        fetch('../articulos/get_articulo.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.resultado){
                document.getElementById('idArticuloeliminar').value = data.datos.IdArticulo;
                document.getElementById('cantidadEliminar').value = data.datos.Cantidad;
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })
    }else if(idFormulario === 'salidaStock'){
        fetch('../articulos/get_articulo.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.resultado){
                document.getElementById('idArticuloSalida').value = data.datos.IdArticulo;
                document.getElementById('nombreProductoSalida').value = data.datos.Nombre;
                document.getElementById('cantidadActualSalida').value = data.datos.Cantidad;
                var selS = document.getElementById('unidadSalidaSelect');
                selS.innerHTML = '';
                var ub = (data.datos.Unidad_Presentacion || 'unidad').toString();
                selS.innerHTML += '<option value="__BASE__" data-factor="1" data-unidad="' + ub + '">' + ub + ' (×1)</option>';
                if (Array.isArray(data.unidades)) {
                    data.unidades.forEach(function (u) {
                        var nom = (u.Unidad || '').toString();
                        var fac = parseFloat(u.FactorEquivalencia || u.Factor || 0);
                        if (!nom || fac <= 0) return;
                        selS.innerHTML += '<option value="' + nom + '" data-factor="' + fac + '" data-unidad="' + nom + '">' + nom + ' (×' + fac + ')</option>';
                    });
                }
                if (selS.options.length > 0) selS.selectedIndex = 0;
                document.getElementById('cantidadOriginalSalida').value = '';
                document.getElementById('cantidadSalida').value = '';
                document.getElementById('equivalenteSalidaInfo').style.display = 'none';
                document.getElementById('equivalenteSalidaInfo').innerHTML = '';
                document.getElementById('cantidadOriginalSalida').focus();
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })
    }else if(idFormulario === 'abrirCaja'){
        document.getElementById('idAbrirCaja').value = id;
        document.getElementById('montoAbrirCaja').focus();
    }else if(idFormulario === 'cerrarCaja'){
        document.getElementById('idCerrarCaja').value = id;
    }else if(idFormulario === 'anularGasto'){
        document.getElementById('idGastoAnular').value = id;
    }else if(idFormulario === 'anularVentaDia'){
        fetch('../reportes/get_ventas.php?id='+id)
            .then(response => response.json())
            .then(data => {
                if(data.resultado){
                    var ventaTotal = data.datos.Total;
                    var saldoVenta = data.datos.saldo;
                    var totalPagado = ventaTotal - saldoVenta;
                    document.getElementById('idVenta').value = data.datos.IdVenta;
                    document.getElementById('montoVenta').value = parseFloat(data.datos.Total);
                    document.getElementById('montoPagado').value = totalPagado;
                    document.getElementById('saldoVenta').value = parseFloat(data.datos.saldo);
                }else{
                    if (window.hideLegacyModal) {
                        window.hideLegacyModal(idFormulario);
                    } else {
                        document.getElementById(idFormulario).style.display = 'none';
                    }
                    mostrarAlertaErrorTiempo(data.mensaje);
                }
            })
    }else if(idFormulario === 'pagarVentaDia'){
        fetch('../reportes/get_ventas.php?id='+id)
            .then(response => response.json())
            .then(data => {
                if(data.resultado){
                    document.getElementById('idPagarVentaPendiente').value = data.datos.IdVenta;
                    document.getElementById('montoTotalVenta').value = parseFloat(data.datos.saldo.toFixed(2));
                    document.getElementById('efectivoPendiente').value = parseFloat(data.datos.saldo.toFixed(2));
                    document.getElementById('utilidadVentaPendiente').value = parseFloat(data.datos.utilidad);
                }else{
                    if (window.hideLegacyModal) {
                        window.hideLegacyModal(idFormulario);
                    } else {
                        document.getElementById(idFormulario).style.display = 'none';
                    }
                    mostrarAlertaErrorTiempo(data.mensaje);
                }
            })
    }else if(idFormulario === 'eliminarVentaDia'){
        fetch('../reportes/get_ventas.php?id='+id)
            .then(response => response.json())
            .then(data => {
                if(data.resultado){
                    document.getElementById('idVenta').value = id;
                    document.getElementById('estadoVentaEliminacion').value = data.datos.Estado;
                }else{
                    if (window.hideLegacyModal) {
                        window.hideLegacyModal(idFormulario);
                    } else {
                        document.getElementById(idFormulario).style.display = 'none';
                    }
                    mostrarAlertaErrorTiempo(data.mensaje);
                }
            })
    }else if(idFormulario === 'eliminarVentaMes'){
        fetch('../reportes/get_ventas.php?id='+id)
            .then(response => response.json())
            .then(data => {
                if(data.resultado){
                    document.getElementById('idVenta').value = id;
                    document.getElementById('estadoVentaEliminacion').value = data.datos.Estado;
                }else{
                    if (window.hideLegacyModal) {
                        window.hideLegacyModal(idFormulario);
                    } else {
                        document.getElementById(idFormulario).style.display = 'none';
                    }
                    mostrarAlertaErrorTiempo(data.mensaje);
                }
            })
    }else if(idFormulario === 'pagarVentaMes'){
        fetch('../reportes/get_ventas.php?id='+id)
            .then(response => response.json())
            .then(data => {
                if(data.resultado){
                    document.getElementById('idPagarVentaPendiente').value = data.datos.IdVenta;
                    document.getElementById('montoTotalVenta').value = parseFloat(data.datos.saldo.toFixed(2));
                    document.getElementById('efectivoPendiente').value = parseFloat(data.datos.saldo.toFixed(2));
                    document.getElementById('utilidadVentaPendiente').value = parseFloat(data.datos.utilidad);
                }else{
                    if (window.hideLegacyModal) {
                        window.hideLegacyModal(idFormulario);
                    } else {
                        document.getElementById(idFormulario).style.display = 'none';
                    }
                    mostrarAlertaErrorTiempo(data.mensaje);
                }
            })
    }else if(idFormulario === 'anularVentaMes'){
        fetch('../reportes/get_ventas.php?id='+id)
            .then(response => response.json())
            .then(data => {
                if(data.resultado){
                    var ventaTotal = data.datos.Total;
                    var saldoVenta = data.datos.saldo;
                    var totalPagado = ventaTotal - saldoVenta;
                    document.getElementById('idVenta').value = data.datos.IdVenta;
                    document.getElementById('montoVenta').value = parseFloat(data.datos.Total);
                    document.getElementById('montoPagado').value = totalPagado;
                    document.getElementById('saldoVenta').value = parseFloat(data.datos.saldo);
                }else{
                    if (window.hideLegacyModal) {
                        window.hideLegacyModal(idFormulario);
                    } else {
                        document.getElementById(idFormulario).style.display = 'none';
                    }
                    mostrarAlertaErrorTiempo(data.mensaje);
                }
            })
    }else if(idFormulario === 'eliminarCotizacionModal'){
        document.getElementById('IdCotizacionEliminar').value = id;
        console.log(document.getElementById('IdCotizacionEliminar').value);
    }
}

async function get_articulo(id) {
    const response = await fetch('../articulos/get_articulo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idCliente: id
        })
    });
    
    const data = await response.json();
    return data;
}


    function calcularVueltoPendiente(efectivo, tarjeta){
        var montoTotalVenta = parseFloat($('#montoTotalVenta').val());

        if(isNaN(efectivo)){
            efectivo = 0;
        }
        if (isNaN(tarjeta)) {
            tarjeta = 0;
        }
        var montoPagar = efectivo + tarjeta;
        
        if(montoPagar < 0){
            var saldo = montoTotalVenta - montoPagar;
            mostrarAlertaErrorTiempo('El monto no puede ser menor a cero');
            document.getElementById('efectivoPendiente').value = '';
            document.getElementById('saldoPendiente').value = montoTotalVenta;
            document.getElementById('utilidadVentaPendiente').value = '';
        }else{
            if(montoPagar > montoTotalVenta){
                document.getElementById('vuelto_pendiente_container').style.display = 'block';
                document.getElementById('saldo_container').style.display = 'none';
                var vuelto = montoPagar - montoTotalVenta;
                document.getElementById('vueltoPendiente').value = vuelto;
                document.getElementById('saldoPendiente').value = 0;
            }else{
                var saldo = montoTotalVenta - montoPagar;
                document.getElementById('vuelto_pendiente_container').style.display = 'none';
                document.getElementById('saldo_container').style.display = 'block';
                document.getElementById('vueltoPendiente').value = '';
                document.getElementById('saldoPendiente').value = saldo;
                document.getElementById('vueltoPendiente').value = 0;
            }
        }
    }  

    $('#efectivoPendiente').on('input', function() {
        var efectivo = parseFloat($(this).val());
        var tarjeta = parseFloat($('#tarjetaPendiente').val());
        calcularVueltoPendiente(efectivo, tarjeta);
    })

    $('#tarjetaPendiente').on('input', function() {
        var tarjeta = parseFloat($(this).val());
        var efectivo = parseFloat($('#efectivoPendiente').val());
        calcularVueltoPendiente(efectivo,tarjeta);
    })  

    $('#metodoPagoPendiente').change(function() {
        var metodoPago = $(this).val();
        var montoTotalVenta = $('#montoTotalVenta').val();
        if(metodoPago === 'efectivo'){
            document.getElementById('efectivo_container').style.display = 'block';
            document.getElementById('tarjeta_container').style.display = 'none';
            document.getElementById('tarjetaPendiente').value = '';
            document.getElementById('efectivoPendiente').value = '';
            document.getElementById('vueltoPendiente').value = '';
            document.getElementById('saldoPendiente').value = montoTotalVenta;
            document.getElementById('efectivoPendiente').focus();
        }else if(metodoPago === 'tarjeta'){
            document.getElementById('efectivo_container').style.display = 'none';
            document.getElementById('tarjeta_container').style.display = 'block';
            document.getElementById('efectivoPendiente').value = '';
            document.getElementById('vueltoPendiente').value = '';
            document.getElementById('tarjetaPendiente').value = '';
            document.getElementById('saldoPendiente').value = montoTotalVenta;
            document.getElementById('tarjetaPendiente').focus();
        }else{
            document.getElementById('efectivo_container').style.display = 'block';
            document.getElementById('tarjeta_container').style.display = 'block';
            document.getElementById('efectivoPendiente').value = '';
            document.getElementById('vueltoPendiente').value = '';
            document.getElementById('tarjetaPendiente').value = '';
            document.getElementById('saldoPendiente').value = montoTotalVenta;
            document.getElementById('efectivoPendiente').focus();
        }
    })

    function traerClienteVenta(dniCliente){
        fetch('../operaciones/get_cliente.php?dni='+dniCliente)
        .then(response => response.json())
        .then(data => {
            if(data === null){
                document.getElementById('idCliente').value = '';
                document.getElementById('nombreCliente').value = '';
                document.getElementById('direccionCliente').value = '';
                document.getElementById('telefonoCliente').value = '';
                document.getElementById('fechaRegistroCliente').value = new Date().toISOString().split('T')[0];
                $('#nombreCliente').removeAttr('disabled');
                $('#direccionCliente').removeAttr('disabled');
                $('#telefonoCliente').removeAttr('disabled');
                $('#fechaRegistroCliente').removeAttr('disabled');
            }else{
                document.getElementById('idCliente').value = data.Id_Cliente;
                document.getElementById('dniCliente').value = data.Dni;
                document.getElementById('nombreCliente').value = data.Nombre;
                document.getElementById('direccionCliente').value = data.Direccion;
                document.getElementById('telefonoCliente').value = data.Telefono;
                document.getElementById('fechaRegistroCliente').value = data.Fecha_Registro;
                $('#nombreCliente').attr('disabled','disabled');
                $('#direccionCliente').attr('disabled','disabled');
                $('#telefonoCliente').attr('disabled','disabled');
                $('#fechaRegistroCliente').attr('disabled','disabled');
            }
        })
    }

    $('#dniCliente').on('input', function() {
        var dniCliente = $('#dniCliente').val();
        if(dniCliente.length == 8){
            traerClienteVenta(dniCliente);
        }else{
            document.getElementById('idCliente').value = '';
            document.getElementById('nombreCliente').value = '';
            document.getElementById('direccionCliente').value = '';
            document.getElementById('telefonoCliente').value = '';
            document.getElementById('fechaRegistroCliente').value = new Date().toISOString().split('T')[0];
            $('#nombreCliente').removeAttr('disabled');
            $('#direccionCliente').removeAttr('disabled');
            $('#telefonoCliente').removeAttr('disabled');
            $('#fechaRegistroCliente').removeAttr('disabled');
        }
    })

    $('#palabraClave').on('input', function() {
        var palabraClave = $(this).val();
        
        buscarProducto(palabraClave);
    });

    function buscarProducto(palabraClave){
        if(palabraClave.length >= 3){
            fetch('../operaciones/get_articulos.php?palabra='+palabraClave)
            .then(response => response.json())
            .then(data => {
                mostrarResultados(data);
            })
        }else{
            $('#resultados').html('');
        }
    }

    function mostrarResultados(data) {
        const rolUsuario = document.getElementById('rol').dataset.rol;
        let resultadosHtml = '<ul> <h5>Resultados encontrados: '+ data.length + '</h5>';
        data.forEach(item => {
            resultadosHtml += `
                <button type="button" class="list-group-item list-group-item-action list-group-item-info mt-1 rounded-end"  onclick="mostrarFormulario('añadirProducto',${item.IdArticulo})">${item.Nombre} - ${item.Cantidad} - ${rolUsuario == 1 ? item.Precio_Compra : ""}</button>
            `;
        });
        resultadosHtml += '</ul>';
        $('#resultados').html(resultadosHtml);
    }

    $('#estadoVenta').change(function() {
        var estadoVenta = $(this).val();
        if(estadoVenta === 'pagado'){
            document.getElementById('efectivo').value = '';
            document.getElementById('tarjeta').value = '';
            document.getElementById('metodoPago').selectedIndex = 0;
            document.getElementById('metodoPagoContainer').style.display = 'block';
            document.getElementById('montoEfectivo').style.display = 'block';
            document.getElementById('montoTarjeta').style.display = 'none';   
            calcularVuelto();
            calcularSaldo();
        }else if(estadoVenta === 'pendiente'){
            document.getElementById('efectivo').value = '';
            document.getElementById('tarjeta').value = '';
            document.getElementById('montoTarjeta').style.display = 'none';
            document.getElementById('montoEfectivo').style.display = 'none';
            document.getElementById('metodoPagoContainer').style.display = 'none';
            calcularVuelto();
            calcularSaldo();
        }else{
            document.getElementById('efectivo').value = '';
            document.getElementById('tarjeta').value = '';
            document.getElementById('metodoPagoContainer').style.display = 'block';
            document.getElementById('montoEfectivo').style.display = 'block';   
            document.getElementById('metodoPago').selectedIndex = 0;
            document.getElementById('montoTarjeta').style.display = 'none';   
            calcularVuelto();
            calcularSaldo();
        }
    })

    $('#metodoPago').change(function() {
        var metodoPago = $(this).val();
        var totalVenta =  parseFloat($('#totalVenta').data('total'));
        if(metodoPago === 'efectivo'){
            document.getElementById('tarjeta').value = '';
            document.getElementById('montoTarjeta').style.display = 'none';
            document.getElementById('montoEfectivo').style.display = 'block';
            document.getElementById('efectivo').value = totalVenta;
            calcularVuelto();
            calcularSaldo();
        }else if(metodoPago === 'tarjeta'){
            document.getElementById('efectivo').value = '';
            document.getElementById('montoTarjeta').style.display = 'block';
            document.getElementById('montoEfectivo').style.display = 'none';
            document.getElementById('tarjeta').value = totalVenta;
            calcularVuelto();
            calcularSaldo();
        }else{
            document.getElementById('tarjeta').value = '';
            document.getElementById('efectivo').value = '';
            document.getElementById('montoTarjeta').style.display = 'block';
            document.getElementById('montoEfectivo').style.display = 'block';
            calcularVuelto();
            calcularSaldo();
        }
    })
    $('#efectivo').on('input', function() {
        calcularVuelto();
        calcularSaldo();
    })

    $('#tarjeta').on('input', function() {
        calcularVuelto();
        calcularSaldo();
    })

    function calcularSaldo(){
        var pagoEfectivo = parseFloat($('#efectivo').val());
        var pagoTarjeta = parseFloat($('#tarjeta').val());
        var totalVenta =  parseFloat($('#totalVenta').data('total'));
        if(isNaN(totalVenta)){
            totalVenta = 0;
        }
        if (isNaN(pagoEfectivo)) {
            pagoEfectivo = 0;
        }
        if (isNaN(pagoTarjeta)) {
            pagoTarjeta = 0;
        }
        var saldo = totalVenta - (pagoEfectivo + pagoTarjeta);
        if(saldo > 0){
            document.getElementById('saldoContainer').style.display = 'block';
        }else{
            document.getElementById('saldo').value = '';
            document.getElementById('saldoContainer').style.display = 'none';
        }
        $('#saldo').val(saldo.toFixed(2));
    }

    function calcularVuelto(){
        var pagoEfectivo = parseFloat($('#efectivo').val());
        var pagoTarjeta = parseFloat($('#tarjeta').val());
        var totalVenta =  parseFloat($('#totalVenta').data('total'));
        if(isNaN(totalVenta)){
            totalVenta = 0;
        }
        if (isNaN(pagoEfectivo)) {
            pagoEfectivo = 0;
        }
        if (isNaN(pagoTarjeta)) {
            pagoTarjeta = 0;
        }
        var vuelto = (pagoEfectivo + pagoTarjeta) - totalVenta;
        if(vuelto > 0){
            document.getElementById('vueltoContainer').style.display = 'block';
            document.getElementById('metodoVueltoContainer').style.display = 'block';
        }else{
            document.getElementById('metodoVueltoContainer').style.display = 'none';
            document.getElementById('vueltoContainer').style.display = 'none';
        }
        $('#vuelto').val(vuelto.toFixed(2));
    }
    


function quitarCliente(){
    fetch('../operaciones/quitarCliente.php',{
        method: 'DELETE',
        headers: {
            'content-type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            $('#dniCliente').removeAttr('disabled');
            $('#nombreCliente').removeAttr('disabled');
            $('#direccionCliente').removeAttr('disabled');
            $('#telefonoCliente').removeAttr('disabled');
            $('#fechaRegistroCliente').removeAttr('disabled');
            $('#registrarCliente').removeAttr('disabled');

            document.getElementById('dniCliente').value = '';
            document.getElementById('nombreCliente').value = '';
            document.getElementById('direccionCliente').value = '';
            document.getElementById('telefonoCliente').value = '';
            document.getElementById('fechaRegistroCliente').value = new Date().toISOString().split('T')[0];
            mostrarAlertaExitoTiempo(data.message);
        }else{
            mostrarAlertaErrorTiempo(data.message);
        }
    })
}

function añadirCliente(){
    var dniCliente = $('#dniCliente').val();
    var nombreCliente = $('#nombreCliente').val();
    if(dniCliente !== '' && nombreCliente !== ''){
        var direccionCliente = $('#direccionCliente').val();
        var telefonoCliente = $('#telefonoCliente').val();
        var fechaRegistro = $('#fechaRegistroCliente').val();
        fetch('../operaciones/añadir_cliente_temp.php?dni='+dniCliente+'&nombre='+nombreCliente+'&direccion='+direccionCliente+'&telefono='+telefonoCliente+'&fecha='+fechaRegistro)
        .then(response => response.json())
        .then(data => {
            mostrarAlertaExitoTiempo('Cliente añadido correctamente');
            $('#dniCliente').attr('disabled','disabled');
            $('#nombreCliente').attr('disabled','disabled');
            $('#direccionCliente').attr('disabled','disabled');
            $('#telefonoCliente').attr('disabled','disabled');
            $('#fechaRegistroCliente').attr('disabled','disabled');
            $('#registrarCliente').attr('disabled','disabled');
        })
    }else{
        mostrarAlertaErrorTiempo('El Dni y el nombre no pueden estar vacios');
    }
}

function calcularEquivalenteAñadir() {
    var cantEl = document.getElementById('cantidadOriginalAñadir');
    var selEl = document.getElementById('unidadAñadirSelect');
    var infoEl = document.getElementById('equivalenteAñadirInfo');
    var convEl = document.getElementById('cantidadAñadir');
    if (!cantEl || !selEl) return;
    var cant = parseFloat(cantEl.value || 0);
    var opt = selEl.options[selEl.selectedIndex];
    var factor = opt ? parseFloat(opt.dataset.factor || 1) : 1;
    var udm = opt ? (opt.dataset.unidad || '') : '';
    if (cant > 0 && factor > 0) {
        var convertida = cant / factor;
        if (convEl) convEl.value = convertida.toFixed(4).replace(/\.?0+$/, '');
        if (infoEl) {
            infoEl.style.display = 'block';
            infoEl.innerHTML = 'Equivalente en unidad de presentacion: <span class="text-primary">' + convertida.toLocaleString(undefined, { maximumFractionDigits: 4 }) + ' ' + (udm ? '' : '') + '</span> (se usará para actualizar el stock)';
        }
    } else {
        if (convEl) convEl.value = '';
        if (infoEl) {
            infoEl.style.display = 'none';
            infoEl.innerHTML = '';
        }
    }
}

function calcularEquivalenteSalida() {
    var cantEl = document.getElementById('cantidadOriginalSalida');
    var selEl = document.getElementById('unidadSalidaSelect');
    var infoEl = document.getElementById('equivalenteSalidaInfo');
    var convEl = document.getElementById('cantidadSalida');
    if (!cantEl || !selEl) return;
    var cant = parseFloat(cantEl.value || 0);
    var opt = selEl.options[selEl.selectedIndex];
    var factor = opt ? parseFloat(opt.dataset.factor || 1) : 1;
    var udm = opt ? (opt.dataset.unidad || '') : '';
    if (cant > 0 && factor > 0) {
        var convertida = cant / factor;
        if (convEl) convEl.value = convertida.toFixed(4).replace(/\.?0+$/, '');
        if (infoEl) {
            infoEl.style.display = 'block';
            infoEl.innerHTML = 'Equivalente en unidad de presentacion (se descontará del stock): <span class="text-danger">' + convertida.toLocaleString(undefined, { maximumFractionDigits: 4 }) + '</span>';
        }
    } else {
        if (convEl) convEl.value = '';
        if (infoEl) {
            infoEl.style.display = 'none';
            infoEl.innerHTML = '';
        }
    }
}

function cambiarUnidadVenta(){
    var selectUdM = document.getElementById('unidadVenta');
    var cantidadActual = document.getElementById('cantidadActual');
    if (!selectUdM || !selectUdM.value) return;
    var opt = selectUdM.options[selectUdM.selectedIndex];
    var factor = parseFloat(opt.dataset.factor) || 1;
    var cantEquivalente = parseFloat(opt.dataset.CantidadEquivalente) || 1;
    var unidad = opt.dataset.unidad || '';
    var precioVentaUdM = parseFloat(opt.dataset.precVenta || 0);
    var precioMinimo = parseFloat(opt.dataset.precMinimo || 0);

    document.getElementById('factorAplicado').value = factor.toFixed(4);
    document.getElementById('unidadSeleccionada').value = unidad;
    document.getElementById('precioVenta').value = precioVentaUdM;
    document.getElementById('precioMinimoMostrar').value = precioMinimo;
    document.getElementById('stockProducto').value = (parseFloat(cantidadActual.value) * factor).toFixed(2);
    document.getElementById('precioMinimoArticulo').value = precioMinimo;

    calcularDescuentoVenta();
    actualizarEquivalenteVenta();
}

function actualizarEquivalenteVenta() {
    var infoEl = document.getElementById('equivalenteVentaInfo');
    if (!infoEl) return;
    var cantEl = document.getElementById('stockVenta');
    var factEl = document.getElementById('factorAplicado');
    var udmEl = document.getElementById('unidadSeleccionada');
    var cant = parseFloat((cantEl && cantEl.value) || 0);
    var fact = parseFloat((factEl && factEl.value) || 0);
    var udm = (udmEl && udmEl.value) || '';
    if (cant > 0 && fact > 0) {
        var convertida = cant / fact;
        infoEl.style.display = 'block';
        infoEl.innerHTML = 'Equivalente en unidad de presentacion (afecta stock): <span class="text-primary">' + convertida.toLocaleString(undefined, { maximumFractionDigits: 4 }) + '</span>' + (udm ? ' · Venta mostrada al cliente: <strong>' + cant + ' ' + udm + '</strong>' : '');
    } else {
        infoEl.style.display = 'none';
        infoEl.innerHTML = '';
    }
}

function calcularDescuentoVenta(){
    var precioUdM = parseFloat(document.getElementById('precioVenta').value) || 0;
    var cantidad = parseFloat(document.getElementById('stockVenta').value) || 0;
    var factor = parseFloat(document.getElementById('factorAplicado').value) || 1;
    var precioMinimo = parseFloat(document.getElementById('precioMinimoArticulo').value) || 0;
    var descuentos = window._descuentosArticuloActual || [];
    var porcentajeDto = 0;
    var cantidadEnBase = cantidad / factor;
    for (var i = descuentos.length - 1; i >= 0; i--) {
        var d = descuentos[i] || {};
        var umbral = parseFloat(d.CantMinima || d.CantidadMinima || 0);
        var pDto = parseFloat(d.Porcentaje || d.PorcentajeDescuento || 0);
        if (cantidadEnBase >= umbral && umbral > 0) {
            porcentajeDto = pDto;
            break;
        }
    }
    var precioConDescuento = precioUdM * (1 - (porcentajeDto / 100));
    var subTotal = precioConDescuento * cantidad;
    document.getElementById('descuentoMostrar').value = porcentajeDto.toFixed(2);
    document.getElementById('porcentajeDescuentoAplicado').value = porcentajeDto.toFixed(4);
    document.getElementById('precioConDescuentoMostrar').value = precioConDescuento.toFixed(2);
    document.getElementById('subTotalMostrar').value = 'S/. ' + subTotal.toFixed(2);
    var inpPrecioVenta = document.getElementById('precioVenta');
    var inpSubTotal = document.getElementById('subTotalMostrar');
    if (precioMinimo > 0 && precioConDescuento < precioMinimo) {
        inpPrecioVenta.classList.add('border', 'border-danger');
        inpSubTotal.classList.add('border', 'border-danger');
    } else {
        inpPrecioVenta.classList.remove('border', 'border-danger');
        inpSubTotal.classList.remove('border', 'border-danger');
    }
    actualizarEquivalenteVenta();
}

function añadirProducto(){
    var idArticulo = $('#idProducto').val();
    var precioVenta = $('#precioVenta').val();
    var stockVenta = $('#stockVenta').val();
    var unidad = $('#unidadSeleccionada').val();
    var factorAplicado = $('#factorAplicado').val();
    var porcentajeDescuento = $('#porcentajeDescuentoAplicado').val();
    var precioConDescuento = $('#precioConDescuentoMostrar').val();
    var precioMinimo = parseFloat($('#precioMinimoArticulo').val()) || 0;
    var aplicarDescuento = $('#aplicarDescuento').is(':checked');
    if (precioMinimo > 0 && parseFloat(precioConDescuento) < precioMinimo) {
        mostrarAlertaError('El precio con descuento (S/.' + precioConDescuento + ') está por debajo del precio mínimo permitido (S/.' + precioMinimo.toFixed(2) + ').');
        return;
    }
    fetch('../operaciones/get_producto.php?id='+idArticulo)
            .then(response => response.json())
            .then(resp => {
                if (!resp.resultado) {
                    mostrarAlertaErrorTiempo('No se pudo verificar el stock');
                    return;
                }
                var data = resp.datos;
                var cantidadReal = parseFloat(stockVenta) * parseFloat(factorAplicado);
                var stockPorFactor = data.Cantidad * parseFloat(factorAplicado);
                if(parseFloat(stockPorFactor) >= stockVenta){
                    if(parseFloat(precioVenta) > 0){
                        var url = '../operaciones/añadir_producto_al_temp.php?id='+idArticulo
                            +'&precioVenta='+encodeURIComponent(precioVenta)
                            +'&stockVenta='+encodeURIComponent(stockVenta)
                            +'&unidad='+encodeURIComponent(unidad)
                            +'&factorAplicado='+encodeURIComponent(factorAplicado)
                            +'&porcentajeDescuento='+encodeURIComponent(porcentajeDescuento)
                            +'&precioMinimo='+encodeURIComponent(precioMinimo)
                            +'&precioConDescuento='+encodeURIComponent(precioConDescuento)
                            +'&aplicarDescuento='+encodeURIComponent(aplicarDescuento);
                        fetch(url)
                        .then(response => response.json())
                        .then(resp2 => {
                            if(resp2.resultado === true){
                                mostrarDatosVenta(resp2.datos);
                                ocultarFormulario('añadirProducto');
                                buscarProducto('');
                                mostrarAlertaExitoTiempo(resp2.mensaje);
                            }else{
                                mostrarAlertaErrorTiempo(resp2.mensaje);
                            }
                        })
                    }else{
                        mostrarAlertaErrorTiempo('El monto tiene que ser mayor a cero');
                    }
                }else{
                    mostrarAlertaErrorTiempo('El stock es insuficiente (disponible: ' + data.Cantidad + ' en unidad base, requiere: ' + cantidadReal.toFixed(2) + ')');
                }
            })
}

function mostrarDatosVenta(data) {
    let resultadosHtml = '';
    let totalVenta = 0;
    data.forEach(item => {
        const nombreArticulo = item.nombreArticulo || '';
        const cantidad = parseFloat(item.cantidad) || 0;
        const precioUnitario = parseFloat(item.PrecioConDescuento != null && item.PrecioConDescuento > 0 ? item.PrecioConDescuento : item.precio_venta) || 0;
        const unidad = item.Unidad || '-';
        const porcentajeDto = parseFloat(item.PorcentajeDescuento) || 0;
        const subTotal = cantidad * precioUnitario;
        totalVenta += subTotal;
        resultadosHtml += `
            <tr>
                <td class="text-truncate" style="max-width:180px;" title="${nombreArticulo}">${nombreArticulo}</td>
                <td>${cantidad}</td>
                <td>${unidad}</td>
                <td>S/. ${precioUnitario.toFixed(2)}</td>
                <td>${porcentajeDto > 0 ? porcentajeDto.toFixed(1) + '%' : '-'}</td>
                <td>S/. ${subTotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="quitarProducto(${item.correlativo})"><i class="far fa-trash-alt"></i></button>
                </td>
            </tr>
        `;
    });
    resultadosHtml += `
        <tr>
            <td colspan="5" class="text-end"><strong>Total</strong></td>
            <td id="totalVenta" data-total="${totalVenta}"><strong>S/. ${totalVenta.toFixed(2)}</strong></td>
            <td></td>
        </tr>
    `;
    $('#datosVenta').html(resultadosHtml);
    if (document.getElementById('efectivo')) {
        document.getElementById('efectivo').value = totalVenta.toFixed(2);
    }
}

function quitarProducto(correlativo){
    var palabraClave = $('#palabraClave').val();
    fetch('../operaciones/quitar_producto_del_temp.php?id='+correlativo)
            .then(response => response.json())
            .then(data => {
                if(data.resultado){
                    mostrarDatosVenta(data.datos);
                    buscarProducto(palabraClave);
                    mostrarAlertaExitoTiempo(data.mensaje);
                    limpiarDatosDePago();
                }else{
                    mostrarDatosVenta(data.datos);
                    buscarProducto(palabraClave);
                    mostrarAlertaExitoTiempo(data.mensaje);
                    limpiarDatosDePago();
                }
            })
}

function limpiarDatosDePago(){
    document.getElementById('efectivo').value = '';
    document.getElementById('tarjeta').value = '';
    document.getElementById('vuelto').value = '';
    document.getElementById('saldo').value = '';
    document.getElementById('montoTarjeta').style.display = 'none';
    document.getElementById('vueltoContainer').style.display = 'none';
    document.getElementById('metodoVueltoContainer').style.display = 'none';
    document.getElementById('saldoContainer').style.display = 'none';
    document.getElementById('montoEfectivo').style.display = 'block';
    document.getElementById('estadoVenta').selectedIndex = 0;
    document.getElementById('metodoPago').selectedIndex = 0;
}

function mostrarAlertaError(mensaje){
    Swal.fire({
        title: 'Error',
        text: mensaje,
        icon: 'error',
        confirmButtonText: 'Aceptar'
    });
}

function mostrarAlertaExito(mensaje) {
    Swal.fire({
        position: 'top',
        title: 'Éxito',
        text: mensaje,
        icon: 'success',
        showConfirmButton: false,
        timer: 2000, // Tiempo en milisegundos que la alerta se mostrará antes de desaparecer
        toast: true // Esto permite que la alerta se muestre como un toast en la parte superior
    });
}

function mostrarAlertaExitoTiempo(mensaje) {
    Swal.fire({
        position: 'top',
        icon: 'success',
        title: 'Éxito',
        text: mensaje,
        showConfirmButton: false,
        timer: 3000, // Tiempo en milisegundos que la alerta se mostrará antes de desaparecer
        toast: true // Esto permite que la alerta se muestre como un toast en la parte superior
    });
}

function mostrarAlertaErrorTiempo(mensaje) {
    Swal.fire({
        position: 'top',
        icon: 'error',
        title: 'Error',
        text: mensaje,
        showConfirmButton: false,
        timer: 3000, // Tiempo en milisegundos que la alerta se mostrará antes de desaparecer
        toast: true // Esto permite que la alerta se muestre como un toast en la parte superior
    });
}

function procesarVenta() {
    var estadoVenta = $('#estadoVenta').val();
    var metodoPago = $('#metodoPago').val();
    var efectivo = parseFloat($('#efectivo').val()) || 0;
    var tarjeta = parseFloat($('#tarjeta').val()) || 0; 
    var vuelto = parseFloat($('#vuelto').val()) || 0;
    var metodoVuelto = $('#metodoVuelto').val();
    var fechaVenta = $('#fechaVenta').val();

    fetch('../operaciones/procesarVenta.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            estadoVenta: estadoVenta,
            metodoPago: metodoPago,
            efectivo: efectivo,
            tarjeta: tarjeta,
            vuelto: vuelto,
            metodoVuelto: metodoVuelto,
            fechaVenta: fechaVenta
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            if(data.estado === 'pagado'){
                // generarPDF(data.dniCliente,data.idVenta);
                generarTicketVenta(data.idVenta);
                window.location.replace('../reportes/ventasDelDia.php');
            }else if(data.estado === 'pendiente'){
                window.location.replace('../reportes/ventasDelDia.php');
            }else {
                window.location.replace('../reportes/ventasDelDia.php');
            }
        }else{
            mostrarAlertaErrorTiempo(data.message);
        }
    })
}

function limpiarVenta() {
    fetch("../operaciones/anularVenta.php")
        .then(response => response.json())
        .then(data => {
            if(data.resultado){
                localStorage.setItem('showMessage', data.mensaje);
                location.reload();
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })
}

function anularGasto(){
    var idGasto = $('#idGastoAnular').val();
    fetch('../reportes/anularGasto.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idGasto: idGasto
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('anularGasto');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function anularVenta(){
    var idVenta = $('#idVenta').val();
    var metodoPago = $('#metodoAnulacion').val();
    var tipoFiltro = $('#tipoFiltro').val();
    fetch('../reportes/anularVenta.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idVenta: idVenta,
            metodoPago: metodoPago
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            if(tipoFiltro === 'dia'){
                ocultarFormulario('anularVentaDia');
            }else if(tipoFiltro === 'mes'){
                ocultarFormulario('anularVentaMes');
            } else if(tipoFiltro === 'total'){
                ocultarFormulario('anularVenta');
            }
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}   

function eliminarVenta(){
    var idVenta = $('#idVenta').val();
    var estadoVenta = $('#estadoVentaEliminacion').val();
    fetch('../reportes/eliminar_venta.php', {
        method: 'POST',
        headers: {
            'content-type': 'application/json'
        },
        body: JSON.stringify({
            idVenta: idVenta,
            estadoVenta: estadoVenta
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            if(tipoFiltro === 'dia'){
                ocultarFormulario('eliminarVentaDia');
            } else if(tipoFiltro === 'total'){
                ocultarFormulario('eliminarVenta');
            } else if(tipoFiltro === 'mes'){
                ocultarFormulario('eliminarVentaMes');
            }
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

document.addEventListener('DOMContentLoaded', function() {
    // Comprobar si hay un mensaje almacenado en localStorage
    const message = localStorage.getItem('showMessage');
    if (message) {
        // Mostrar el mensaje usando SweetAlert2
        mostrarAlertaExitoTiempo(message);
        // Eliminar el mensaje de localStorage para no volver a mostrarlo en la próxima recarga
        localStorage.removeItem('showMessage');
    }

    const dniClienteInput = document.getElementById('dniCliente');

    if (dniClienteInput && dniClienteInput.value.length === 8) {
        traerClienteVenta(dniClienteInput.value);
    }

});

 function pagarVenta(){
    var idVenta = $('#idPagarVentaPendiente').val();
    var totalVenta = $('#montoTotalVenta').val() || 0;
    var metodoPago = $('#metodoPagoPendiente').val();
    var efectivo = $('#efectivoPendiente').val() || 0;
    var tarjeta = $('#tarjetaPendiente').val() || 0;
    var vuelto = $('#vueltoPendiente').val() || 0;
    var metodoPagoVuelto = $('#metodoVueltoPendiente').val();
    var saldo = $('#saldoPendiente').val() || 0;
    var utilidad = $('#utilidadVentaPendiente').val() || 0;
    var tipoFiltro = $('#tipoFiltro').val();

    if(metodoPago === 'efectivo' && efectivo == 0){
        mostrarAlertaErrorTiempo('El monto en efectivo tiene que ser mayor a cero');
    }else if(metodoPago === 'tarjeta' && tarjeta == 0){
        mostrarAlertaErrorTiempo('El monto en tarjeta tiene que ser mayor a cero');
    }else if(metodoPago === 'ambos' && (tarjeta == 0 || efectivo == 0)){
        mostrarAlertaErrorTiempo('El monto tiene que ser mayor a cero');
    }else{
        fetch('../reportes/pagar_venta.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/json'
            },
            body: JSON.stringify({
                idVenta: idVenta,
                totalVenta: totalVenta,
                metodoPago: metodoPago,
                efectivo: efectivo,
                tarjeta: tarjeta,
                vuelto: vuelto,
                metodoPagoVuelto: metodoPagoVuelto,
                saldo: saldo,
                utilidad: utilidad
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.resultado){
                if(tipoFiltro === 'dia'){
                    ocultarFormulario('pagarVentaDia');
                }else if(tipoFiltro === 'mes'){
                    ocultarFormulario('pagarVentaMes');
                }else if(tipoFiltro === 'total'){
                    ocultarFormulario('pagarVenta');
                }
                localStorage.setItem('showMessage', data.mensaje);
                location.reload();
            }else{
                mostrarAlertaErrorTiempo(data.mensaje);
            }
        })
    }
 }

function generarTicketVenta(idVenta) {
    const url = `../reportes/generar_ticket_pdf.php?idVenta=${idVenta}`;
    window.open(url, '_blank', 'width=1000,height=800');
}

function generarTicket(idVenta) {
    const url = `generar_ticket_pdf.php?idVenta=${idVenta}`;
    window.open(url, '_blank', 'width=1000,height=800');
}

function editarUsuario(){
    var idUsuario = $('#idUsuario').val();
    var nombreUsuario = $('#nombreUsuario').val();
    var dniUsuario = $('#dniUsuarioEditar').val();
    var direccionUsuario = $('#direccionUsuario').val();
    var telefonoUsuario = $('#telefonoUsuario').val();
    var correoUsuario = $('#correoUsuario').val();
    var usuario = $('#usuario').val();
    
    fetch('../usuarios/editar_usuario.php', {
        method: 'POST',
        headers: {
            'content-type': 'application/json'
        },
        body: JSON.stringify({
            idUsuario: idUsuario,
            nombreUsuario: nombreUsuario,
            dniUsuario: dniUsuario,
            direccionUsuario: direccionUsuario,
            telefonoUsuario: telefonoUsuario,
            correoUsuario: correoUsuario,
            usuario: usuario
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('editarUsuario');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function eliminarUsuario(){
    var idUsuarioEliminar = $('#idUsuarioEliminar').val();
    fetch('../usuarios/eliminar_usuario.php', {
        method: 'DELETE',
        headers: {
            'content-type': 'application/json'
        },
        body: JSON.stringify({
            idUsuarioEliminar: idUsuarioEliminar
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('eliminarUsuario');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function verificarClave(){
    var claveActual = $('#claveActual').val();
    var idUsuario = $('#idUsuarioClave').val();
    fetch('../usuarios/verificar_clave.php', {
        method: 'POST',
        headers: {
            'content-type': 'application/json'
        },
        body: JSON.stringify({
            idUsuario: idUsuario,
            claveActual: claveActual
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            document.getElementById('nuevaClave_container').style.display = 'block';
            document.getElementById('cambiarClave_container').style.display = 'block';
            document.getElementById('verificarClave_container').style.display = 'none';
            document.getElementById('claveActual_container').style.display = 'none';
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function cambiarClave(){
    var idUsuario = $('#idUsuarioClave').val();
    var nuevaClave = $('#nuevaClave').val();
    var claveRepetida = $('#nuevaClaveRepetida').val();
    fetch('../usuarios/cambiar_clave.php', {
        method: 'POST',
        headers: {
            'content-type': 'application/json'
        },
        body: JSON.stringify({
            idUsuario: idUsuario,
            nuevaClave: nuevaClave,
            claveRepetida: claveRepetida  
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('cambiarClave');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

$('#claveActual').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        verificarClave();
    }
});

function cambiarRol(){
    var idUsuario = $('#idUsuarioRol').val();
    var nuevoRol = $('#nuevoRol').val();
    fetch('../usuarios/cambiar_rol.php', {
        method: 'POST',
        headers: {
            'content-type': 'application/json'
        },
        body: JSON.stringify({
            idUsuario: idUsuario,
            nuevoRol: nuevoRol
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('cambiarRol');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

$('#busqueda').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarUsuario();
    }
});

$('#busquedaCliente').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarCliente();
    }
});

$('#busquedaProveedor').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarProveedor();
    }
});

$('#busquedaPrestamo').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarPrestamo();
    }
});

$('#nombreArticulo').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarPMV();
    }
});

$('#busquedaVenta').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarVenta();
    }
});

$('#busquedaVentaDia').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarVentaDia();
    }
});

$('#busquedaNombreProducto').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarVenta();
    }
});

$('#busquedaNombreProductoDia').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarVentaDia();
    }
});

$('#busquedaVentaMes').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarVentaMes();
    }
});

$('#busquedaNombreProductoMes').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarVentaMes();
    }
});

$('#busquedaCategoria').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarCategoria();
    }
});

function buscarVenta(page = 1){
    var busqueda = document.getElementById('busquedaVenta').value.trim();
    var nombreProducto = document.getElementById('busquedaNombreProducto').value.trim();
    var medioPago = document.getElementById('busquedaMedioPago').value.trim();
    var estado = document.getElementById('busquedaEstado').value.trim();
    var periodo = document.getElementById('period').value.trim();
    var year = document.getElementById('year').value.trim();
    var month = document.getElementById('month').value.trim();
    var start_date = document.getElementById('start_date').value.trim();
    var end_date = document.getElementById('end_date').value.trim();
    const rolUsuario = document.getElementById('rol').dataset.rol;

    fetch('../reportes/buscar_venta.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            nombreProducto: nombreProducto,
            medioPago: medioPago,
            estado: estado,
            periodo: periodo,
            year: year,
            month: month,
            start_date: start_date,
            end_date: end_date,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaVentas tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(item) {
                var estadoVenta = item.Estado;
                var fila = `<tr>
                    <td>${item.IdVenta}</td>
                    <td>${item.Fecha}</td>
                    <td>${item.nempl}</td>
                    <td>${item.dniCliente}</td>
                    <td>${item.Nombre}</td>
                    <td>S/. ${item.Total}</td>
                    ${rolUsuario == 1 ? `<td>S/. ${item.utilidad}</td>` : ""}
                    <td>
                        <div class="div_acciones">
                            <div>
                                ${estadoVenta == 'pagado' || estadoVenta == 'pendiente' || estadoVenta == 'saldo' ? `
                                    <button class="btn btn-primary" type="button" onclick="generarPDF(${item.dniCliente}, ${item.IdVenta})"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-primary" type="button" onclick="generarTicket(${item.IdVenta})"><i class="fas fa-tag"></i></button>
                                ` : ''}
                            </div>
                        </div>
                    </td>
                    <td class="${estadoVenta}">${item.Estado}</td>
                    <td>${item.Medio_Pago}</td>
                    <td>${item.saldo.toFixed(2)}</td>
                    <td>
                        <div class="btn-group dropend">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Acciones
                            </button>
                            <ul class="dropdown-menu">
                                ${estadoVenta === 'pagado' ? `
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('anularVenta', ${item.IdVenta})">Anular</button></li>
                                ` : estadoVenta === 'pendiente' || estadoVenta === 'saldo' ? `
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('pagarVenta', ${item.IdVenta})">Pagar</button></li>
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('anularVenta', ${item.IdVenta})">Anular</button></li>
                                ` : `
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarVenta', ${item.IdVenta})">Eliminar</button></li>
                                `}
                            </ul>
                        </div>
                    </td>
                </tr>`;
                document.querySelector('#tablaVentas tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, busqueda, 'buscarVenta');
            document.getElementById('paginador').style.display = 'none';
        } else {
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarVenta');
            document.getElementById('paginador').style.display = 'none';
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaVentas tbody').innerHTML = '<tr><td colspan="12" class="text-center">No se encontraron resultados.</td></tr>';
        }
    })
    .catch(error => {
        // Maneja errores de la consulta fetch
        console.error('Error en la consulta fetch: ', error);
    });
}

function buscarVentaDia(page = 1){
    var busqueda = document.getElementById('busquedaVentaDia').value.trim();
    var nombreProducto = document.getElementById('busquedaNombreProductoDia').value.trim();
    var medioPago = document.getElementById('busquedaMedioPagoDia').value.trim();
    var estado = document.getElementById('busquedaEstadoDia').value.trim();
    const rolUsuario = document.getElementById('rol').dataset.rol;

    fetch('../reportes/buscar_ventasDelDia.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            nombreProducto: nombreProducto,
            medioPago: medioPago,
            estado: estado,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaVentas tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(item) {
                var estadoVenta = item.Estado;
                var fila = `<tr>
                    <td>${item.IdVenta}</td>
                    <td>${item.Fecha}</td>
                    <td>${item.nempl}</td>
                    <td>${item.dniCliente}</td>
                    <td>${item.Nombre}</td>
                    <td>S/. ${item.Total}</td>
                    ${rolUsuario == 1 ? `<td>S/. ${item.utilidad}</td>` : ""}
                    <td>
                        <div class="div_acciones">
                            <div>
                                ${estadoVenta == 'pagado' || estadoVenta == 'pendiente' || estadoVenta == 'saldo' ? `
                                    <button class="btn btn-primary" type="button" onclick="generarPDF(${item.dniCliente}, ${item.IdVenta})"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-primary" type="button" onclick="generarTicket(${item.IdVenta})"><i class="fas fa-tag"></i></button>
                                ` : ''}
                            </div>
                        </div>
                    </td>
                    <td class="${estadoVenta}">${item.Estado}</td>
                    <td>${item.Medio_Pago}</td>
                    <td>${item.saldo.toFixed(2)}</td>
                    <td>
                        <div class="btn-group dropend">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Acciones
                            </button>
                            <ul class="dropdown-menu">
                                ${estadoVenta === 'pagado' ? `
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('anularVentaDia', ${item.IdVenta})">Anular</button></li>
                                ` : estadoVenta === 'pendiente' || estadoVenta === 'saldo' ? `
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('pagarVentaDia', ${item.IdVenta})">Pagar</button></li>
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('anularVentaDia', ${item.IdVenta})">Anular</button></li>
                                ` : `
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarVentaDia', ${item.IdVenta})">Eliminar</button></li>
                                `}
                            </ul>
                        </div>
                    </td>
                </tr>`;
                document.querySelector('#tablaVentas tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, busqueda, 'buscarVentaDia');
            document.getElementById('paginador').style.display = 'none';
        } else {
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarVentaDia');
            document.getElementById('paginador').style.display = 'none';
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaVentas tbody').innerHTML = '<tr><td colspan="12" class="text-center">No se encontraron resultados.</td></tr>';
        }
    })
    .catch(error => {
        // Maneja errores de la consulta fetch
        console.error('Error en la consulta fetch: ', error);
    });
}

function buscarVentaMes(page = 1){
    var busqueda = document.getElementById('busquedaVentaMes').value.trim();
    var nombreProducto = document.getElementById('busquedaNombreProductoMes').value.trim();
    var medioPago = document.getElementById('busquedaMedioPagoMes').value.trim();
    var estado = document.getElementById('busquedaEstadoMes').value.trim();
    const rolUsuario = document.getElementById('rol').dataset.rol;

    fetch('../reportes/buscar_ventasDelMes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            nombreProducto: nombreProducto,
            medioPago: medioPago,
            estado: estado,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaVentas tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(item) {
                var estadoVenta = item.Estado;
                var fila = `<tr>
                    <td>${item.IdVenta}</td>
                    <td>${item.Fecha}</td>
                    <td>${item.nempl}</td>
                    <td>${item.dniCliente}</td>
                    <td>${item.Nombre}</td>
                    <td>S/. ${item.Total}</td>
                    ${rolUsuario == 1 ? `<td>S/. ${item.utilidad}</td>` : ""}
                    <td>
                        <div class="div_acciones">
                            <div>
                                ${estadoVenta == 'pagado' || estadoVenta == 'pendiente' || estadoVenta == 'saldo' ? `
                                    <button class="btn btn-primary" type="button" onclick="generarPDF(${item.dniCliente}, ${item.IdVenta})"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-primary" type="button" onclick="generarTicket(${item.IdVenta})"><i class="fas fa-tag"></i></button>
                                ` : ''}
                            </div>
                        </div>
                    </td>
                    <td class="${estadoVenta}">${item.Estado}</td>
                    <td>${item.Medio_Pago}</td>
                    <td>${item.saldo.toFixed(2)}</td>
                    <td>
                        <div class="btn-group dropend">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Acciones
                            </button>
                            <ul class="dropdown-menu">
                                ${estadoVenta === 'pagado' ? `
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('anularVentaMes', ${item.IdVenta})">Anular</button></li>
                                ` : estadoVenta === 'pendiente' || estadoVenta === 'saldo' ? `
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('pagarVentaMes', ${item.IdVenta})">Pagar</button></li>
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('anularVentaMes', ${item.IdVenta})">Anular</button></li>
                                ` : `
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarVentaMes', ${item.IdVenta})">Eliminar</button></li>
                                `}
                            </ul>
                        </div>
                    </td>
                </tr>`;
                document.querySelector('#tablaVentas tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, busqueda, 'buscarVentaMes');
            document.getElementById('paginador').style.display = 'none';
        } else {
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarVentaMes');
            document.getElementById('paginador').style.display = 'none';
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaVentas tbody').innerHTML = '<tr><td colspan="12" class="text-center">No se encontraron resultados.</td></tr>';
        }
    })
    .catch(error => {
        // Maneja errores de la consulta fetch
        console.error('Error en la consulta fetch: ', error);
    });
}

function buscarPMV(page = 1){
    var busqueda = document.getElementById('nombreArticulo').value.trim();
    var proveedor = document.getElementById('nombreProveedor').value.trim();
    var estadistica = document.getElementById('estadistica').value.trim();
    var period = document.getElementById('period').value.trim();
    var year = document.getElementById('year').value.trim();
    var month = document.getElementById('month').value.trim();
    var start_date = document.getElementById('start_date').value.trim();
    var end_date = document.getElementById('end_date').value.trim();

    fetch('../reportes/buscar_pmv.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            proveedor: proveedor,
            estadistica: estadistica,
            period: period,
            year: year,
            month: month,
            start_date: start_date,
            end_date: end_date,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaPMV tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(item) {
                var fila = `<tr>
                    <td>${item.Cod_Articulo}</td>
                    <td>${item.Nombre}</td>
                    <td>${item.Cantidad}</td>
                    <td>${item.Precio_Compra}</td>
                    <td>${item.Precio_Unitario}</td>
                    <td>${item.nombreProv}</td>
                    <td>${item.cantidadVendida}</td>
                    <td>${item.gananciaGenerada}</td>
                </tr>`;
                document.querySelector('#tablaPMV tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, busqueda, 'buscarPMV');
            document.getElementById('paginador').style.display = 'none';
        } else {
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarPMV');
            document.getElementById('paginador').style.display = 'none';
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaPMV tbody').innerHTML = '<tr><td colspan="10" class="text-center">No se encontraron resultados.</td></tr>';
        }
    })
    .catch(error => {
        // Maneja errores de la consulta fetch
        console.error('Error en la consulta fetch: ', error);
    });
}

$('#busquedaGasto').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarGasto();
    }
});

$('#busquedaGastoMes').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarGastoMes();
    }
});

function buscarGastoMes(page = 1){
    var busqueda = document.getElementById('busquedaGastoMes').value.trim();
    var medioPago = document.getElementById('busquedaMedioPago').value.trim();
    var tipoGasto = document.getElementById('tipoGasto').value.trim();

    fetch('../reportes/buscar_gasto_mes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            medioPago: medioPago,
            tipoGasto: tipoGasto,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaGastos tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(item) {
                var fila = `<tr>
                    <td>${item.idGastos}</td>
                    <td>${item.descripcion}</td>
                    <td>${item.montoGasto.toFixed(2)}</td>
                    <td>${item.fechaGasto}</td>
                    <td>${item.medioPago}</td>
                    <td>${item.tipoGasto}</td>
                    <td>
                        <div class="btn-group dropend">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Acciones
                            </button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('anularGasto', ${item.idGastos})">Anular gasto</button></li>
                            </ul>
                        </div>
                    </td>
                </tr>`;
                document.querySelector('#tablaGastos tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, busqueda, 'buscarGastoMes');
            document.getElementById('paginador').style.display = 'none';
        } else {
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarGastoMes');
            document.getElementById('paginador').style.display = 'none';
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaGastos tbody').innerHTML = '<tr><td colspan="11" class="text-center">No se encontraron resultados.</td></tr>';
        }
    })
    .catch(error => {
        // Maneja errores de la consulta fetch
        console.error('Error en la consulta fetch: ', error);
    });
}

function buscarGasto(page = 1){
    var busqueda = document.getElementById('busquedaGasto').value.trim();
    var medioPago = document.getElementById('busquedaMedioPago').value.trim();
    var tipoGasto = document.getElementById('tipoGasto').value.trim();
    var period = document.getElementById('period').value.trim();
    var year = document.getElementById('year').value.trim();
    var month = document.getElementById('month').value.trim();
    var start_date = document.getElementById('start_date').value.trim();
    var end_date = document.getElementById('end_date').value.trim();

    fetch('../reportes/buscar_gasto.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            medioPago: medioPago,
            tipoGasto: tipoGasto,
            period: period,
            year: year,
            month: month,
            start_date: start_date,
            end_date: end_date,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaGastos tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(item) {
                var fila = `<tr>
                    <td>${item.idGastos}</td>
                    <td>${item.descripcion}</td>
                    <td>${item.montoGasto.toFixed(2)}</td>
                    <td>${item.fechaGasto}</td>
                    <td>${item.medioPago}</td>
                    <td>${item.tipoGasto}</td>
                    <td>
                        <div class="btn-group dropend">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Acciones
                            </button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('anularGasto', ${item.idGastos})">Anular gasto</button></li>
                            </ul>
                        </div>
                    </td>
                </tr>`;
                document.querySelector('#tablaGastos tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, busqueda, 'buscarGasto');
            document.getElementById('paginador').style.display = 'none';
        } else {
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarGasto');
            document.getElementById('paginador').style.display = 'none';
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaGastos tbody').innerHTML = '<tr><td colspan="11" class="text-center">No se encontraron resultados.</td></tr>';
        }
    })
    .catch(error => {
        // Maneja errores de la consulta fetch
        console.error('Error en la consulta fetch: ', error);
    });
}

function buscarGastoDia(page = 1){
    var busqueda = document.getElementById('busquedaGasto').value.trim();
    var medioPago = document.getElementById('busquedaMedioPago').value.trim();
    var tipoGasto = document.getElementById('tipoGasto').value.trim();

    fetch('../reportes/buscar_gasto_dia.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            medioPago: medioPago,
            tipoGasto: tipoGasto,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaGastos tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(item) {
                var fila = `<tr>
                    <td>${item.idGastos}</td>
                    <td>${item.descripcion}</td>
                    <td>${item.montoGasto.toFixed(2)}</td>
                    <td>${item.fechaGasto}</td>
                    <td>${item.medioPago}</td>
                    <td>${item.tipoGasto}</td>
                    <td>
                        <div class="btn-group dropend">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Acciones
                            </button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('anularGasto', ${item.idGastos})">Anular gasto</button></li>
                            </ul>
                        </div>
                    </td>
                </tr>`;
                document.querySelector('#tablaGastos tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, busqueda, 'buscarGastoDia');
            document.getElementById('paginador').style.display = 'none';
        } else {
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarGastoDia');
            document.getElementById('paginador').style.display = 'none';
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaGastos tbody').innerHTML = '<tr><td colspan="11" class="text-center">No se encontraron resultados.</td></tr>';
        }
    })
    .catch(error => {
        // Maneja errores de la consulta fetch
        console.error('Error en la consulta fetch: ', error);
    });
}

function buscarCajaDia(page = 1){
    var busqueda = document.getElementById('busqedaActividadDia').value.trim();

    fetch('../caja/buscar_caja_dia.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaCajaDia tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(item) {
                var fila = `<tr>
                    <td>${item.IdCaja}</td>
                    <td>${item.FechaApertura}</td>
                    <td>${item.Actividad}</td>
                    <td>S/. ${item.Monto_inicial}</td>
                    <td>S/. ${item.Monto_salida}</td>
                    <td>S/. ${item.totalCajaDia}</td>
                    <td>${item.Cod_Empleado}.${item.Nombre}</td>
                </tr>`;
                document.querySelector('#tablaCajaDia tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, busqueda, 'buscarCajaDia');
            document.getElementById('paginador').style.display = 'none';
        } else {
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarCajaDia');
            document.getElementById('paginador').style.display = 'none';
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaCajaDia tbody').innerHTML = '<tr><td colspan="11" class="text-center">No se encontraron resultados.</td></tr>';
        }
    })
    .catch(error => {
        // Maneja errores de la consulta fetch
        console.error('Error en la consulta fetch: ', error);
    });
}

function buscarCaja(page = 1) {
    var busqueda = document.getElementById('busqedaActividad').value.trim();
    var period = document.getElementById('period').value.trim();
    var year = document.getElementById('year').value.trim();
    var month = document.getElementById('month').value.trim();
    var startDate = document.getElementById('start_date').value.trim();
    var endDate = document.getElementById('end_date').value.trim();

    fetch('../caja/buscar_caja.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            page: page,
            period: period,
            year: year,
            month: month,
            startDate: startDate,
            endDate: endDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaCaja tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(item) {
                var fila = `<tr>
                    <td>${item.IdCaja}</td>
                    <td>${item.FechaApertura}</td>
                    <td>${item.Actividad}</td>
                    <td>S/. ${item.Monto_inicial}</td>
                    <td>S/. ${item.Monto_salida}</td>
                    <td>S/. ${item.Total_caja}</td>
                    <td>${item.Cod_Empleado}.${item.Nombre}</td>
                </tr>`;
                document.querySelector('#tablaCaja tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, busqueda, 'buscarCaja');
            document.getElementById('paginador').style.display = 'none';
        } else {
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarCaja');
            document.getElementById('paginador').style.display = 'none';
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaCaja tbody').innerHTML = '<tr><td colspan="11" class="text-center">No se encontraron resultados.</td></tr>';
        }
    })
    .catch(error => {
        // Maneja errores de la consulta fetch
        console.error('Error en la consulta fetch: ', error);
    });
}

$('#busquedaArticulo').keydown(function(event) {
    if (event.keyCode === 13) { // 13 es el código de tecla para Enter
        buscarArticulo();
    }
});

function buscarArticulo(page = 1){
    var busqueda = document.getElementById('busquedaArticulo').value.trim();
    var proveedor = document.getElementById('nombreProveedor').value.trim();
    var categoria = document.getElementById('nombreCategoria') ? document.getElementById('nombreCategoria').value.trim() : '';
    var stock = document.getElementById('stockArticulo').value.trim();
    const rolUsuario = document.getElementById('rol').dataset.rol;

    fetch('../articulos/buscar_articulo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            IdProveedor: proveedor,
            IdCategoria: categoria,
            page: page,
            stock: stock
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            document.querySelector('#tablaArticulos tbody').innerHTML = '';
            data.datos.forEach(function(data) {
                var badgeStock = '';
                if (data.Cantidad <= 0) {
                    badgeStock = ' <span class="badge bg-danger">Agotado</span>';
                } else if (data.Cantidad <= data.Stock_Alerta) {
                    badgeStock = ' <span class="badge bg-warning text-dark">Bajo</span>';
                } else if(data.Cantidad > data.Stock_Alerta){
                    badgeStock = ' <span class="badge bg-success">Bien</span>';
                }
                var fila = `<tr id="fila-${data.IdArticulo}">
                    <td>${data.IdArticulo}</td>
                    <td>${data.nombreA}</td>
                    <td>${data.nombreC ? data.nombreC : '<span class="text-muted">Sin asignar</span>'}</td>
                    <td>${data.Cantidad}${badgeStock}</td>
                    <td>${data.Stock_Alerta}</td>
                    <td>${data.Unidad_Presentacion}</td>
                    ${rolUsuario == 1 ? `<td>S/. ${Number(data.Precio_Compra).toFixed(2)}</td>` : "" }
                    <td>S/. ${Number(data.Precio_Unitario).toFixed(2)}</td>
                    <td>S/. ${Number(data.Precio_Minimo || 0).toFixed(2)}</td>
                    ${rolUsuario == 1 ? `<td>${data.nombreP}</td>` : "" }
                    <td>
                        <div class="btn-group dropend">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Acciones
                            </button>
                            <ul class="dropdown-menu">
                                ${rolUsuario == 1 ? `<li><button class="dropdown-item" type="button" onclick="mostrarFormulario('editarArticulo', ${data.IdArticulo})">Editar</button></li>` : ''}
                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('añadirStock', ${data.IdArticulo})">Añadir stock</button></li>
                                ${rolUsuario == 1 ? `<li><button class="dropdown-item" type="button" onclick="mostrarFormulario('salidaStock', ${data.IdArticulo})">Salida de stock</button></li>` : ''}
                                ${data.Cantidad <= 0 ? `<li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarArticulo', ${data.IdArticulo})">Eliminar</button></li>` : ''}
                            </ul>
                        </div>
                    </td>
                </tr>`;
                document.querySelector('#tablaArticulos tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, busqueda, 'buscarArticulo');
            if (document.getElementById('paginador')) {
                document.getElementById('paginador').style.display = 'none';
            }
            if (window.initializeBootstrapBridge) { window.initializeBootstrapBridge(); }
        } else {
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarArticulo');
            if (document.getElementById('paginador')) {
                document.getElementById('paginador').style.display = 'none';
            }
            var colspan = rolUsuario == 1 ? 11 : 8;
            document.querySelector('#tablaArticulos tbody').innerHTML = '<tr><td colspan="'+colspan+'">' + (data.mensaje || 'No se encontraron resultados.') + '</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error en la consulta fetch: ', error);
    });
}

function buscarPrestamo(page = 1){
    var busqueda = document.getElementById('busquedaPrestamo').value.trim();

    fetch('../prestamos/buscar_prestamo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaPrestamos tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(data) {
                var fila = `<tr>
                    <td>${data.idPrestamo}</td>
                    <td>${data.nombre}</td>
                    <td>S/. ${data.monto}</td>
                    <td>${data.cuotas}</td>
                    <td>S/. ${data.montoCuota}</td>
                    <td>S/. ${data.montoPagar}</td>
                    <td>${data.fechaPrestamo}</td>
                    ${data.estado == 1 ? `<td class="pagado">Pagado</td>` : `<td class="pendiente">Pendiente</td>`}
                    <td>
                        <div class="btn-group dropend">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Acciones
                            </button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item" type="button" onclick="window.location.href='lista_cuotas.php?id=${data.idPrestamo}'">Ver cuotas</button></li>
                                ${data.estado == 0 ? `<li><button class="dropdown-item" type="button" onclick="mostrarFormulario('pagarPrestamo', ${data.idPrestamo})">Pagar</button></li>` : ''}
                                ${data.estado == 1 ? `<li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarPrestamo', ${data.idPrestamo})">Eliminar</button></li>` : ''}
                            </ul>
                        </div>
                    </td> 
                </tr>`;
                document.querySelector('#tablaPrestamos tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, busqueda, 'buscarPrestamo');
            document.getElementById('paginador').style.display = 'none';
        } else {
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarPrestamo');
            document.getElementById('paginador').style.display = 'none';
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaPrestamos tbody').innerHTML = '<tr><td colspan="9">No se encontraron resultados.</td></tr>';
        }
    })
}

function pagarPrestamo(){
    var idPrestamo = $('#idPrestamo').val();
    var nombrePrestamista = $('#nombrePrestamista').val();
    var montoPagar = $('#montoPagar').val();
    var monto = $('#monto').val();
    var metodoDePago = $('#metodoDePago').val();
    fetch('../prestamos/pagar_prestamo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idPrestamo: idPrestamo,
            nombrePrestamista: nombrePrestamista,
            montoPagar: montoPagar,
            monto: monto,
            metodoDePago: metodoDePago
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('pagarPrestamo');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function renderPaginator(total_records, results_per_page, current_page, busqueda, funcionCallback) {

    const totalPages = Math.ceil(total_records / results_per_page);
    const pageRange = 2;
    let paginatorHTML = '';

    paginatorHTML += '<nav aria-label="Page navigation" class="pagination-nav">';
    paginatorHTML += '<ul class="pagination flex-wrap justify-content-center">';

    // =====================================================
    // ENLACE A LA PRIMERA PÁGINA
    // =====================================================

    paginatorHTML +=
        '<li class="page-item">' +
        '<a class="page-link" href="javascript:' +
        funcionCallback +
        '(1, \'' +
        busqueda +
        '\')">Primera</a>' +
        '</li>';


    // =====================================================
    // ENLACE A LA PÁGINA ANTERIOR
    // =====================================================

    if (current_page > 1) {

        paginatorHTML +=
            '<li class="page-item">' +
            '<a class="page-link" href="javascript:' +
            funcionCallback +
            '(' +
            (current_page - 1) +
            ', \'' +
            busqueda +
            '\')">Anterior</a>' +
            '</li>';
    }


    // =====================================================
    // CALCULAR RANGO DE PÁGINAS
    // =====================================================

    const startPage = Math.max(1, current_page - pageRange);
    const endPage = Math.min(totalPages, current_page + pageRange);


    // =====================================================
    // PRIMERA PÁGINA + ...
    // =====================================================

    if (startPage > 1) {

        paginatorHTML +=
            '<li class="page-item">' +
            '<a class="page-link" href="javascript:' +
            funcionCallback +
            '(1, \'' +
            busqueda +
            '\')">1</a>' +
            '</li>';

        if (startPage > 2) {

            paginatorHTML +=
                '<li class="page-item disabled">' +
                '<a class="page-link" href="#">...</a>' +
                '</li>';
        }
    }


    // =====================================================
    // NÚMEROS DE PÁGINA
    // =====================================================

    for (let i = startPage; i <= endPage; i++) {

        const activeClass = current_page === i ? 'active' : '';

        paginatorHTML +=
            '<li class="page-item ' +
            activeClass +
            '">' +
            '<a class="page-link" href="javascript:' +
            funcionCallback +
            '(' +
            i +
            ', \'' +
            busqueda +
            '\')">' +
            i +
            '</a>' +
            '</li>';
    }


    // =====================================================
    // ... + ÚLTIMA PÁGINA
    // =====================================================

    if (endPage < totalPages) {

        if (endPage < totalPages - 1) {

            paginatorHTML +=
                '<li class="page-item disabled">' +
                '<a class="page-link" href="#">...</a>' +
                '</li>';
        }

        paginatorHTML +=
            '<li class="page-item">' +
            '<a class="page-link" href="javascript:' +
            funcionCallback +
            '(' +
            totalPages +
            ', \'' +
            busqueda +
            '\')">' +
            totalPages +
            '</a>' +
            '</li>';
    }


    // =====================================================
    // ENLACE A LA PÁGINA SIGUIENTE
    // =====================================================

    if (current_page < totalPages) {

        paginatorHTML +=
            '<li class="page-item">' +
            '<a class="page-link" href="javascript:' +
            funcionCallback +
            '(' +
            (current_page + 1) +
            ', \'' +
            busqueda +
            '\')">Siguiente</a>' +
            '</li>';
    }


    // =====================================================
    // ENLACE A LA ÚLTIMA PÁGINA
    // =====================================================

    paginatorHTML +=
        '<li class="page-item">' +
        '<a class="page-link" href="javascript:' +
        funcionCallback +
        '(' +
        totalPages +
        ', \'' +
        busqueda +
        '\')">Última</a>' +
        '</li>';


    // =====================================================
    // INSERTAR PAGINADOR
    // =====================================================

    paginatorHTML += '</ul>';
    paginatorHTML += '</nav>';

    document.getElementById('paginator').innerHTML = paginatorHTML;
}

function buscarProveedor(page = 1){
    var busqueda = document.getElementById('busquedaProveedor').value.trim();

    fetch('../proveedores/buscar_proveedor.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaProveedores tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(data) {
                var fila = `<tr>
                    <td>${data.IdProveedor}</td>
                    <td>${data.ruc}</td>
                    <td>${data.Nombre}</td>
                    <td>${data.Direccion}</td>
                    <td>${data.Telefono}</td>
                    <td>${data.Email}</td>
                    <td>
                        <div class="btn-group dropend">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Acciones
                            </button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('editarProveedor', ${data.IdProveedor})">Editar</button></li>
                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarProveedor', ${data.IdProveedor})">Eliminar</button></li>
                            </ul>
                        </div>
                    </td> 
                </tr>`;
                document.querySelector('#tablaProveedores tbody').insertAdjacentHTML('beforeend', fila);
            });     
        } else {
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaProveedores tbody').innerHTML = '<tr><td colspan="9">No se encontraron resultados.</td></tr>';
        }
    })
    .catch(error => {
        // Maneja errores de la consulta fetch
        console.error('Error en la consulta fetch: ', error);
    });
}

function buscarCategoria(page = 1){
    var busqueda = document.getElementById('busquedaCategoria').value.trim();
    fetch('../categorias/buscar_categoria.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            document.querySelector('#tablaCategorias tbody').innerHTML = '';
            data.datos.forEach(function(data) {
                var fila = `<tr>
                    <td>${data.IdCategoria}</td>
                    <td>${data.Nombre}</td>
                    <td>${data.Descripcion ? data.Descripcion : '-'}</td>
                    <td>${new Date(data.FechaCreacion).toLocaleDateString('es-PE')}</td>
                    <td><span class="badge bg-primary">${data.CantArticulos || 0}</span></td>
                    <td>
                        <div class="btn-group dropend">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Acciones
                            </button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('editarCategoria', ${data.IdCategoria})">Editar</button></li>
                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarCategoria', ${data.IdCategoria})">Eliminar</button></li>
                            </ul>
                        </div>
                    </td>
                </tr>`;
                document.querySelector('#tablaCategorias tbody').insertAdjacentHTML('beforeend', fila);
            });
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarCategoria');
            if (document.getElementById('paginador')) {
                document.getElementById('paginador').style.display = 'none';
            }
            if (window.initializeBootstrapBridge) {
                window.initializeBootstrapBridge();
            }
        } else {
            document.querySelector('#tablaCategorias tbody').innerHTML = '<tr><td colspan="6">' + (data.mensaje || 'No se encontraron resultados.') + '</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error en la consulta fetch: ', error);
    });
}

function obtenerFiltrosClientes() {
    return {
        busqueda: document.getElementById('busquedaCliente') ? document.getElementById('busquedaCliente').value.trim() : '',
        filtrosVarios: document.getElementById('filtrosVarios') ? document.getElementById('filtrosVarios').value.trim() : '',
        period: document.getElementById('period') ? document.getElementById('period').value.trim() : 'year',
        year: document.getElementById('year') ? document.getElementById('year').value.trim() : '',
        month: document.getElementById('month') ? document.getElementById('month').value.trim() : '',
        start_date: document.getElementById('start_date') ? document.getElementById('start_date').value.trim() : '',
        end_date: document.getElementById('end_date') ? document.getElementById('end_date').value.trim() : ''
    };
}

function buscarCliente(page = 1){
    var filtros = obtenerFiltrosClientes();
    fetch('../clientes/buscar_cliente.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.assign({}, filtros, { page: page }))
        })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaClientes tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(data) {
                var fila = `<tr>
                    <td><a href="../operaciones/venta_articulo.php?dni=${data.Dni}">${data.Dni}</a></td>
                    <td>${data.Nombre}</td>
                    <td>${data.direccion}</td>
                    <td>${data.Telefono}</td>
                    <td>${data.Fecha_Registro}</td>
                    <td>${data.cantidadCompras}</td>
                    <td>S/. ${parseFloat(data.montoCompras || 0).toFixed(2)}</td>
                    <td>S/. ${parseFloat(data.gananciaGenerada || 0).toFixed(2)}</td>
                    <td>
                        <div class="btn-group dropend">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Acciones
                            </button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item" onclick="mostrarFormulario('editarCliente', ${data.Id_Cliente})">Editar</button></button></li>
                                <li><button class="dropdown-item" onclick="mostrarFormulario('reiniciarMetricas', ${data.Id_Cliente})">Reiniciar metricas</button></li>
                                <li><button class="dropdown-item" onclick="mostrarFormulario('eliminarCliente', ${data.Id_Cliente})">Eliminar</button></li>
                            </ul>
                        </div>
                    </td>
                </tr>`;
                document.querySelector('#tablaClientes tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, filtros['busqueda'], 'buscarCliente');
            document.getElementById('paginador').style.display = 'none';
            if (window.initializeBootstrapBridge) {
                window.initializeBootstrapBridge();
            }
        } else {
            renderPaginator(data.total_records, 10, page, filtros['busqueda'], 'buscarCliente');
            document.getElementById('paginador').style.display = 'none';
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaClientes tbody').innerHTML = '<tr><td colspan="9">' + data.mensaje + '</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error en la consulta fetch: ', error);
    });
}

function buscarUsuario(page = 1){
    var busqueda = document.getElementById('busqueda').value.trim();

    fetch('../usuarios/buscar_usuario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            busqueda: busqueda,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.resultado) {
            // Limpia la tabla de resultados actuales
            document.querySelector('#tablaUsuarios tbody').innerHTML = '';

            // Itera sobre los datos recibidos y agrega filas a la tabla
            data.datos.forEach(function(data) {
                var fila = `<tr>
                    <td>${data.IdEmpleado}</td>
                    <td>${data.Nombre}</td>
                    <td>${data.Dni}</td>
                    <td>${data.Direccion}</td>
                    <td>${data.Telefono}</td>
                    <td>${data.Email}</td>
                    <td>${data.Usuario}</td>
                    <td>${data.rol}</td>
                    <td>
                        <div class="btn-group dropend">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Acciones
                            </button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('editarUsuario', ${data.IdEmpleado})">Editar</button></li>
                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('cambiarClave', ${data.IdEmpleado})">Cambiar clave</button></li>
                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('cambiarRol', ${data.IdEmpleado})">Cambiar rol</button></li>
                                <?php if($data.Rol != 1 && data.IdEmpleado != 1) { ?>
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarUsuario', ${data.IdEmpleado})">Eliminar</button></li>
                                <?php
                                    }
                                ?>
                            </ul>
                        </div>
                    </td>
                </tr>`;
                document.querySelector('#tablaUsuarios tbody').insertAdjacentHTML('beforeend', fila);
            });

            renderPaginator(data.total_records, 10, page, busqueda, 'buscarUsuario');
            document.getElementById('paginador').style.display = 'none';
        } else {
            renderPaginator(data.total_records, 10, page, busqueda, 'buscarUsuario');
            document.getElementById('paginador').style.display = 'none';
            // Maneja el caso en que no se encontraron resultados
            document.querySelector('#tablaUsuarios tbody').innerHTML = '<tr><td colspan="9">No se encontraron resultados.</td></tr>';
        }
    })
}

function editarCliente(){
    var idCliente = $('#idClienteEditar').val();
    var nombreCliente = $('#nombreClienteEditar').val();
    var dniCliente = $('#dniClienteEditar').val();
    var telefonoCliente = $('#telefonoClienteEditar').val();
    var direccionCliente = $('#direccionClienteEditar').val();
    var fechaRegistroCliente = $('#fecha_registroClienteEditar').val();
    fetch('../clientes/editar_cliente.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idCliente: idCliente,
            nombreCliente: nombreCliente,
            dniCliente: dniCliente,
            telefonoCliente: telefonoCliente,
            direccionCliente: direccionCliente,
            fechaRegistroCliente: fechaRegistroCliente
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('editarCliente');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function eliminarCliente(){
    var idCliente = $('#idClienteEliminar').val();
    fetch('../clientes/eliminar_cliente.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idCliente: idCliente
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('eliminarCliente');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function reiniciarMetricas(){
    var idCliente = $('#idClienteMetricas').val();
    fetch('../clientes/reiniciar_metricas.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idCliente: idCliente
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('reiniciarMetricas');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function editarProveedor(){
    var IdProveedor = $('#idProveedoreditar').val();
    var nombre = $('#nombreProveedorEditar').val();
    var direccion = $('#direccionProveedorEditar').val();
    var telefono = $('#telefonoProveedorEditar').val();
    var correo = $('#correoProveedorEditar').val();
    var ruc = $('#ruc').val();
    fetch('../proveedores/editar_proveedor.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            IdProveedor: IdProveedor,
            nombre: nombre,
            direccion: direccion,
            telefono: telefono,
            correo: correo,
            ruc: ruc
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('editarProveedor');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function editarCategoria(){
    var IdCategoria = $('#idCategoriaeditar').val();
    var nombre = $('#nombreCategoriaEditar').val();
    var descripcion = $('#descripcionCategoriaEditar').val();
    if (!nombre || nombre.trim() === '') {
        mostrarAlertaErrorTiempo('El nombre es obligatorio');
        return;
    }
    fetch('../categorias/editar_categoria.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            IdCategoria: IdCategoria,
            nombre: nombre,
            descripcion: descripcion
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('editarCategoria');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function eliminarProveedor(){
    var idProveedor = $('#idProveedorEliminar').val();
    fetch('../proveedores/eliminar_proveedor.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idProveedor: idProveedor
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('eliminarProveedor');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function eliminarCategoria(){
    var idCategoria = $('#idCategoriaEliminar').val();
    fetch('../categorias/eliminar_categoria.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idCategoria: idCategoria
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('eliminarCategoria');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function procesarPagoCuota(){
    var idCuota = $('#idCuotaPago').val();
    var idPrestamo = $('#idPrestamoPago').val();
    var montoCuota = $('#montoCuotaPago').val();
    var metodoPago = $('#metodoPagoCuota').val();
    fetch('../prestamos/procesar_pago_cuota.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idCuota: idCuota,
            idPrestamo: idPrestamo,
            montoCuota: montoCuota,
            metodoPago: metodoPago
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('procesarPago');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function cancelarPagoCuota(){
    var idCuota = $('#idCuotaCancelar').val();
    var idPrestamo = $('#idPrestamoCancelar').val();
    var montoCuota = $('#montoCuotaCancelar').val();
    var metodoPago = $('#metodoPagoCancelar').val();
    fetch('../prestamos/cancelar_pago_cuota.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idCuota: idCuota,
            idPrestamo: idPrestamo,
            montoCuota: montoCuota,
            metodoPago: metodoPago
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('cancelarPago');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function eliminarPretamo(){
    var idPrestamo = $('#idPrestamoEliminar').val();
    fetch('../prestamos/eliminar_prestamo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idPrestamo: idPrestamo
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('eliminarPrestamo');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function añadirStock(){
    var idArticulo = $('#idArticuloAñadir').val();
    var nombre = $('#nombreProductoAñadir').val();
    var precioCompra = $('#precio_compraAñadir').val();
    var cantidad = $('#cantidadActualAñadir').val();
    var CantidadAñadir = $('#cantidadAñadir').val();
    var cantidadOriginalEl = document.getElementById('cantidadOriginalAñadir');
    var unidadSelEl = document.getElementById('unidadAñadirSelect');
    var CantidadOriginal = 0;
    var unidadSeleccionada = '';
    var factorAplicado = 0;
    if (cantidadOriginalEl) {
        CantidadOriginal = parseFloat(cantidadOriginalEl.value || 0);
    }
    if (unidadSelEl && unidadSelEl.options && unidadSelEl.selectedIndex >= 0) {
        var optA = unidadSelEl.options[unidadSelEl.selectedIndex];
        unidadSeleccionada = (optA.dataset.unidad || '').toString();
        factorAplicado = parseFloat(optA.dataset.factor || 0);
    }
    fetch('../articulos/añadir_stock.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idArticulo: idArticulo,
            nombre: nombre,
            precioCompra: precioCompra,
            cantidad: cantidad,
            CantidadAñadir: CantidadAñadir,
            CantidadOriginal: CantidadOriginal,
            unidadSeleccionada: unidadSeleccionada,
            factorAplicado: factorAplicado
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            fetch('../articulos/get_articulo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: idArticulo
                })
            })
            .then(response => response.json())
            .then(articulo => {
                ocultarFormulario('añadirStock');
                document.getElementById('fila-' + idArticulo).querySelector('td:nth-child(4)').textContent = articulo.datos.Cantidad;
                mostrarAlertaExito(data.mensaje);
            })
             .catch(error => {
                console.error('Error al obtener el artículo actualizado: ', error);
                mostrarAlertaErrorTiempo('Stock añadido, pero no se pudo actualizar la tabla.');
            });
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function guardarNuevoArticulo() {
    if (!document.getElementById('formNuevoArticulo')) return;
    var proveedor = parseInt((document.getElementById('proveedor') || {}).value || 0, 10);
    var categoria = parseInt((document.getElementById('categoria') || {}).value || 0, 10);
    var codigoBarras = (document.getElementById('codigoBarras') || {}).value || '';
    var nombre = (document.getElementById('nombre') || {}).value || '';
    var cantidad = (document.getElementById('cantidad') || {}).value || 0;
    var stockAlerta = (document.getElementById('stock_alerta') || {}).value || 5;
    var precioCompra = (document.getElementById('precio_compra') || {}).value || 0;
    var precioVenta = (document.getElementById('precio_unitario') || {}).value || 0;
    var precioMinimo = (document.getElementById('precio_minimo') || {}).value || 0;
    var unidadPresentacion = (document.getElementById('unidad_presentacion') || {}).value || 'unidad';
    var unidades = [];
    var rowsUd = document.querySelectorAll('#unidadesContainer .unidad-row');
    if (rowsUd && rowsUd.length > 0) {
        rowsUd.forEach(function (row) {
            unidades.push({
                Unidad: (row.querySelector('[name="u_unidad"]') || {}).value || '',
                FactorEquivalencia: (row.querySelector('[name="u_factor"]') || {}).value || 1,
                PrecioVenta: (row.querySelector('[name="u_precio"]') || {}).value || 0,
                PrecioMinimo: (row.querySelector('[name="u_minimo"]') || {}).value || 0,
                EsPredeterminada: !!(row.querySelector('[name="u_pred"]') || {}).checked
            });
        });
    }
    var descuentos = [];
    var rowsDesc = document.querySelectorAll('#descuentosContainer .descuento-row');
    if (rowsDesc && rowsDesc.length > 0) {
        rowsDesc.forEach(function (row) {
            descuentos.push({
                CantidadMinima: (row.querySelector('[name="d_cant"]') || {}).value || 0,
                PorcentajeDescuento: (row.querySelector('[name="d_porc"]') || {}).value || 0
            });
        });
    }
    if (categoria <= 0 || proveedor <= 0 || !nombre || !precioVenta) {
        mostrarAlertaErrorTiempo('Complete: categoría, proveedor, nombre y precio unitario');
        return;
    }
    fetch('registro_articulo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            proveedor: proveedor, categoria: categoria, codigoBarras: codigoBarras,
            nombre: nombre, cantidad: cantidad, stockAlerta: stockAlerta,
            precioCompra: precioCompra, precioVenta: precioVenta, precioMinimo: precioMinimo,
            unidadPresentacion: unidadPresentacion, unidades: unidades, descuentos: descuentos
        })
    })
    .then(r => r.json())
    .then(d => {
        if (d && d.resultado) {
            window.location.href = 'lista_articulos.php';
            mostrarAlertaExitoTiempo(d.mensaje || 'Artículo guardad correctamente');
        } else {
            mostrarAlertaErrorTiempo((d && d.mensaje) ? d.mensaje : 'Error al guardar');
        }
    })
    .catch(function (e) { mostrarAlertaErrorTiempo('Error de conexión: ' + e.message); });
}

function editarArticulo(){
    const idArticulo = $('#idArticuloeditar').val();
    const codigoBarras = $('#codigoBarrasEditar').val();
    const nombre = $('#nombreProductoEditar').val();
    const precioCompra = $('#precioCompraEditar').val();
    const precioVenta = $('#precioVentaEditar').val();
    const precioMinimo = $('#precioMinimoEditar').val();
    const stockAlerta = $('#stockAlertaEditar').val();
    const nuevaUnidad = $('#nuevaUnidad').val();
    const nuevoProveedor = $('#nuevoProveedor').val();
    const nuevaCategoria = $('#nuevaCategoria').val();
    const unidadOtro = $('#unidadOtro').val();
    var unidades = [];
    document.querySelectorAll('#unidadesContainer .unidad-row').forEach(function(row){
        unidades.push({
            id: row.querySelector('[name="u_id"]').value,
            Unidad: row.querySelector('[name="u_unidad"]').value,
            FactorEquivalencia: row.querySelector('[name="u_factor"]').value,
            PrecioVenta: row.querySelector('[name="u_precio"]').value,
            PrecioMinimo: row.querySelector('[name="u_minimo"]').value,
            EsPredeterminada: row.querySelector('[name="u_pred"]').checked
        });
    });
    var descuentos = [];
    document.querySelectorAll('#descuentosContainer .descuento-row').forEach(function(row){
        descuentos.push({
            CantidadMinima: row.querySelector('[name="d_cant"]').value,
            PorcentajeDescuento: row.querySelector('[name="d_porc"]').value
        });
    });

    fetch('../articulos/editar_articulo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idArticulo: idArticulo,
            codigoBarras: codigoBarras,
            nombre: nombre,
            precioCompra: precioCompra,
            precioVenta: precioVenta,
            precioMinimo: precioMinimo,
            stockAlerta: stockAlerta,
            nuevaUnidad: nuevaUnidad,
            nuevoProveedor: nuevoProveedor,
            nuevaCategoria: nuevaCategoria,
            unidadOtro: unidadOtro,
            unidades: unidades,
            descuentos: descuentos
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            fetch('../articulos/get_articulo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: idArticulo
                })
            })
            .then(response => response.json())
            .then(articulo => {
                ocultarFormulario('editarArticulo');
                localStorage.setItem('showMessage', data.mensaje);
                location.reload();
            })
             .catch(error => {
                console.error('Error al obtener el artículo actualizado: ', error);
                mostrarAlertaErrorTiempo('Artículo editado correctamente.');
                setTimeout(function(){ location.reload(); }, 1200);
            });
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function renderFilasUnidades(unidades){
    var container = document.getElementById('unidadesContainer');
    if (!container) return;
    container.innerHTML = '';
    if (!unidades || unidades.length === 0) {
        agregarFilaUnidad();
        return;
    }
    unidades.forEach(function(u){ agregarFilaUnidad(u); });
}

function agregarFilaUnidad(data){
    var container = document.getElementById('unidadesContainer');
    if (!container) return;
    var u = data || { Unidad: '', FactorEquivalencia: 1, PrecioVenta: 0, PrecioMinimo: 0, EsPredeterminada: false };
    var html = '<div class="row g-2 align-items-center unidad-row mb-2">';
    html += '<div><input type="hidden" name="u_id" value="'+(u.IdUnidad||0)+'"></div>';
    html += '<div class="col-3"><select class="form-control form-control-sm" name="u_unidad">';
    html += '<option value="">Seleccionar</option>';
    html += '<option value="unidad" '+(u.Unidad==='unidad'?'selected':'')+'>Unidad</option>';
    html += '<option value="kilogramo" '+(u.Unidad==='kilogramo'?'selected':'')+'>Kilogramo (KG)</option>';
    html += '<option value="gramo" '+(u.Unidad==='gramo'?'selected':'')+'>Gramo (G)</option>';
    html += '<option value="metro" '+(u.Unidad==='metro'?'selected':'')+'>Metro (M)</option>';
    html += '<option value="centimetro" '+(u.Unidad==='centimetro'?'selected':'')+'>Centímetro (CM)</option>';
    html += '<option value="litro" '+(u.Unidad==='litro'?'selected':'')+'>Litro (L)</option>';
    html += '<option value="mililitro" '+(u.Unidad==='mililitro'?'selected':'')+'>Mililitro (ML)</option>';
    html += '<option value="metro_cuadrado" '+(u.Unidad==='metro_cuadrado'?'selected':'')+'>Metro cuadrado (M²)</option>';
    html += '<option value="metro_cubico" '+(u.Unidad==='metro_cubico'?'selected':'')+'>Metro cúbico (M³)</option>';
    html += '<option value="par" '+(u.Unidad==='par'?'selected':'')+'>Par (PAR)</option>';
    html += '<option value="docena" '+(u.Unidad==='docena'?'selected':'')+'>Docena (DOC)</option>';
    html += '<option value="galon" '+(u.Unidad==='galon'?'selected':'')+'>Galón (GAL)</option>';
    html += '<option value="otra" '+(u.Unidad==='otra'?'selected':'')+'>Otra</option>';
    html += '</select></div>';
    html += '<div class="col-2"><input type="number" step="0.0001" class="form-control form-control-sm" name="u_factor" placeholder="Factor x unidad base" value="'+(u.FactorEquivalencia||1)+'"></div>';
    html += '<div class="col-2"><input type="number" step="0.01" class="form-control form-control-sm" name="u_precio" placeholder="Precio venta" value="'+(u.PrecioVenta||0)+'"></div>';
    html += '<div class="col-2"><input type="number" step="0.01" class="form-control form-control-sm" name="u_minimo" placeholder="Precio mínimo" value="'+(u.PrecioMinimo||0)+'"></div>';  
    html += '<div class="col-1 text-center"><input type="checkbox" name="u_pred" '+(u.EsPredeterminada?'checked':'')+'></div>';
    html += '<div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.unidad-row\').remove()"><i class="fas fa-trash"></i></button></div>';
    html += '</div>';
    container.insertAdjacentHTML('beforeend', html);
}

function renderFilasDescuentos(descuentos){
    var container = document.getElementById('descuentosContainer');
    if (!container) return;
    container.innerHTML = '';
    if (!descuentos || descuentos.length === 0) {
        agregarFilaDescuento();
        return;
    }
    descuentos.forEach(function(d){ agregarFilaDescuento(d); });
}

function agregarFilaDescuento(data){
    var container = document.getElementById('descuentosContainer');
    if (!container) return;
    var d = data || { CantidadMinima: 1, PorcentajeDescuento: 0 };
    var html = '<div class="row g-2 align-items-center descuento-row mb-2">';
    html += '<div class="col-5"><div class="input-group input-group-sm"><span class="input-group-text">Desde</span><input type="number" min="1" class="form-control" name="d_cant" placeholder="Cantidad mín." value="'+(d.CantidadMinima||1)+'"></div></div>';
    html += '<div class="col-5"><div class="input-group input-group-sm"><input type="number" step="0.01" min="0" max="100" class="form-control" name="d_porc" placeholder="% descuento" value="'+(d.PorcentajeDescuento||0)+'"><span class="input-group-text">%</span></div></div>';
    html += '<div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'.descuento-row\').remove()"><i class="fas fa-trash"></i></button></div>';
    html += '</div>';
    container.insertAdjacentHTML('beforeend', html);
}

function eliminarArticulo(){
    var idArticulo = $('#idArticuloeliminar').val();
    var cantidad = $('#cantidadEliminar').val();
    fetch('../articulos/eliminar_articulo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idArticulo: idArticulo,
            cantidad: cantidad
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('eliminarArticulo');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function salidaStock(){
    var idArticulo = $('#idArticuloSalida').val();
    var cantidadActual = $('#cantidadActualSalida').val();
    var cantidadSalida = $('#cantidadSalida').val();
    var descripcion = $('#descripcionSalida').val();
    var fecha = $('#fechaSalida').val();
    var cantidadOriginalEl = document.getElementById('cantidadOriginalSalida');
    var unidadSelEl = document.getElementById('unidadSalidaSelect');
    var CantidadOriginal = 0;
    var unidadSeleccionada = '';
    var factorAplicado = 0;
    if (cantidadOriginalEl) {
        CantidadOriginal = parseFloat(cantidadOriginalEl.value || 0);
    }
    if (unidadSelEl && unidadSelEl.options && unidadSelEl.selectedIndex >= 0) {
        var optS = unidadSelEl.options[unidadSelEl.selectedIndex];
        unidadSeleccionada = (optS.dataset.unidad || '').toString();
        factorAplicado = parseFloat(optS.dataset.factor || 0);
    }
    fetch('../articulos/salida_stock.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idArticulo: idArticulo,
            cantidadActual: cantidadActual,
            cantidadSalida: cantidadSalida,
            descripcion: descripcion,
            fecha: fecha,
            cantidadOriginal: CantidadOriginal,
            unidadSeleccionada: unidadSeleccionada,
            factorAplicado: factorAplicado
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            fetch('../articulos/get_articulo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: idArticulo
                })
            })
            .then(response => response.json())
            .then(articulo => {
                ocultarFormulario('salidaStock');
                document.getElementById('fila-' + idArticulo).querySelector('td:nth-child(4)').textContent = articulo.datos.Cantidad;
                mostrarAlertaExito(data.mensaje);
            })
             .catch(error => {
                console.error('Error al obtener el artículo actualizado: ', error);
                mostrarAlertaErrorTiempo('Stock retirado, pero no se pudo actualizar la tabla.');
            });
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function abrirCajaDeOtraPagina(){
    var monto = $('#montoAbrirCaja').val();
    var id = $('#idAbrirCaja').val();

    fetch('../caja/abrir_caja.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            monto: monto,
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('abrirCaja');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function abrirCaja(){
    var monto = $('#montoAbrirCaja').val();
    var id = $('#idAbrirCaja').val();

    fetch('caja/abrir_caja.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            monto: monto,
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('abrirCaja');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function cerrarCaja(){
    var id = $('#idCerrarCaja').val();

    fetch('caja/cerrar_caja.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.resultado){
            ocultarFormulario('cerrarCaja');
            localStorage.setItem('showMessage', data.mensaje);
            location.reload();
        }else{
            mostrarAlertaErrorTiempo(data.mensaje);
        }
    })
}

function exportarEstadisticas_EXCEL() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimirEstadisticas.php';
    form.target = '_blank';

    const datos = {
        nExcel: 1,
        busqueda: document.getElementById('nombreArticulo').value.trim(),
        IdProveedor: document.getElementById('nombreProveedor').value.trim(),
        estadistica: document.getElementById('estadistica').value.trim(),
        period: document.getElementById('period').value.trim(),
        year: document.getElementById('year').value.trim(),
        month: document.getElementById('month').value.trim(),
        start_date: document.getElementById('start_date').value.trim(),
        end_date: document.getElementById('end_date').value.trim()
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarEstadisticas_PDF() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimirEstadisticas.php';
    form.target = '_blank';

    const datos = {
        nPdf: 1,
        busqueda: document.getElementById('nombreArticulo').value.trim(),
        IdProveedor: document.getElementById('nombreProveedor').value.trim(),
        estadistica: document.getElementById('estadistica').value.trim(),
        period: document.getElementById('period').value.trim(),
        year: document.getElementById('year').value.trim(),
        month: document.getElementById('month').value.trim(),
        start_date: document.getElementById('start_date').value.trim(),
        end_date: document.getElementById('end_date').value.trim()
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarArticulosPDF() {
    var busqueda = $('#busquedaArticulo').val();
    var IdProveedor = $('#nombreProveedor').val();
    var IdCategoria = $('#nombreCategoria') ? $('#nombreCategoria').val() : '';
    var stock = $('#stockArticulo').val();

    var url = 'imprimir_articulo.php?nPdf=1&busqueda=' + encodeURIComponent(busqueda) + '&IdProveedor=' + encodeURIComponent(IdProveedor) + '&IdCategoria=' + encodeURIComponent(IdCategoria) + '&stock=' + encodeURIComponent(stock);
    window.open(url, '_blank');
}

function exportarArticulosEXCEL() {
    var busqueda = $('#busquedaArticulo').val();
    var IdProveedor = $('#nombreProveedor').val();
    var IdCategoria = $('#nombreCategoria') ? $('#nombreCategoria').val() : '';
    var stock = $('#stockArticulo').val();

    var url = 'imprimir_articulo.php?nExcel=1&busqueda=' + encodeURIComponent(busqueda) + '&IdProveedor=' + encodeURIComponent(IdProveedor) + '&IdCategoria=' + encodeURIComponent(IdCategoria) + '&stock=' + encodeURIComponent(stock);
    window.open(url, '_blank');
}

function exportarClientesPDF() {
    var filtros = obtenerFiltrosClientes();
    var url = 'imprimir_cliente.php?nPdf=1'
        + '&busqueda=' + encodeURIComponent(filtros.busqueda)
        + '&filtrosVarios=' + encodeURIComponent(filtros.filtrosVarios)
        + '&period=' + encodeURIComponent(filtros.period)
        + '&year=' + encodeURIComponent(filtros.year)
        + '&month=' + encodeURIComponent(filtros.month)
        + '&start_date=' + encodeURIComponent(filtros.start_date)
        + '&end_date=' + encodeURIComponent(filtros.end_date);
    window.open(url, '_blank');
}

function exportarClientesEXCEL() {
    var filtros = obtenerFiltrosClientes();
    var url = 'imprimir_cliente.php?nExcel=1'
        + '&busqueda=' + encodeURIComponent(filtros.busqueda)
        + '&filtrosVarios=' + encodeURIComponent(filtros.filtrosVarios)
        + '&period=' + encodeURIComponent(filtros.period)
        + '&year=' + encodeURIComponent(filtros.year)
        + '&month=' + encodeURIComponent(filtros.month)
        + '&start_date=' + encodeURIComponent(filtros.start_date)
        + '&end_date=' + encodeURIComponent(filtros.end_date);
    window.open(url, '_blank');
}

function exportarUsuariosPDF() {
    var busqueda = $('#busqueda').val();

    var url = 'imprimir_usuario.php?nPdf=1&busqueda=' + encodeURIComponent(busqueda);
    window.open(url, '_blank');
}

function exportarUsuariosEXCEL() {
    var busqueda = $('#busqueda').val();

    var url = 'imprimir_usuario.php?nExcel=1&busqueda=' + encodeURIComponent(busqueda);
    window.open(url, '_blank');
}

function exportarProveedoresPDF() {
    var busqueda = $('#busquedaProveedor').val();

    var url = 'imprimir_proveedor.php?nPdf=1&busqueda=' + encodeURIComponent(busqueda);
    window.open(url, '_blank');
}

function exportarProveedoresEXCEL() {
    var busqueda = $('#busquedaProveedor').val();

    var url = 'imprimir_proveedor.php?nExcel=1&busqueda=' + encodeURIComponent(busqueda);
    window.open(url, '_blank');
}

function exportarCategoriasPDF() {
    var busqueda = document.getElementById('busquedaCategoria') ? document.getElementById('busquedaCategoria').value : '';
    var url = 'imprimir_categoria.php?nPdf=1&busqueda=' + encodeURIComponent(busqueda);
    window.open(url, '_blank');
}

function exportarCategoriasEXCEL() {
    var busqueda = document.getElementById('busquedaCategoria') ? document.getElementById('busquedaCategoria').value : '';
    var url = 'imprimir_categoria.php?nExcel=1&busqueda=' + encodeURIComponent(busqueda);
    window.open(url, '_blank');
}

function exportarPrestamosPDF() {
    var busqueda = $('#busquedaPrestamo').val();

    var url = 'imprimir_prestamos.php?nPdf=1&busqueda=' + encodeURIComponent(busqueda);
    window.open(url, '_blank');
}

function exportarPrestamosEXCEL() {
    var busqueda = $('#busquedaPrestamo').val();

    var url = 'imprimir_prestamos.php?nExcel=1&busqueda=' + encodeURIComponent(busqueda);
    window.open(url, '_blank');
}

function exportarGastosMesEXCEL() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimir_gastos_mes.php';
    form.target = '_blank';

    const datos = {
        nExcel: 1,
        busqueda: document.getElementById('busquedaGastoMes').value.trim(),
        medioPago: document.getElementById('busquedaMedioPago').value.trim(),
        tipoGasto: document.getElementById('tipoGasto').value.trim(),
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarGastosMesPDF() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimir_gastos_mes.php';
    form.target = '_blank';

    const datos = {
        nPdf: 1,
        busqueda: document.getElementById('busquedaGastoMes').value.trim(),
        medioPago: document.getElementById('busquedaMedioPago').value.trim(),
        tipoGasto: document.getElementById('tipoGasto').value.trim(),
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarGastosEXCEL() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimir_gastos.php';
    form.target = '_blank';

    const datos = {
        nExcel: 1,
        busqueda: document.getElementById('busquedaGasto').value.trim(),
        medioPago: document.getElementById('busquedaMedioPago').value.trim(),
        tipoGasto: document.getElementById('tipoGasto').value.trim(),
        period: document.getElementById('period').value.trim(),
        year: document.getElementById('year').value.trim(),
        month: document.getElementById('month').value.trim(),
        start_date: document.getElementById('start_date').value.trim(),
        end_date: document.getElementById('end_date').value.trim()
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarGastosPDF() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimir_gastos.php';
    form.target = '_blank';

    const datos = {
        nPdf: 1,
        busqueda: document.getElementById('busquedaGasto').value.trim(),
        medioPago: document.getElementById('busquedaMedioPago').value.trim(),
        tipoGasto: document.getElementById('tipoGasto').value.trim(),
        period: document.getElementById('period').value.trim(),
        year: document.getElementById('year').value.trim(),
        month: document.getElementById('month').value.trim(),
        start_date: document.getElementById('start_date').value.trim(),
        end_date: document.getElementById('end_date').value.trim()
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarGastosDiaEXCEL() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimir_gastos_dia.php';
    form.target = '_blank';

    const datos = {
        nExcel: 1,
        busqueda: document.getElementById('busquedaGasto').value.trim(),
        medioPago: document.getElementById('busquedaMedioPago').value.trim(),
        tipoGasto: document.getElementById('tipoGasto').value.trim(),
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarGastosDiaPDF() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimir_gastos_dia.php';
    form.target = '_blank';

    const datos = {
        nPdf: 1,
        busqueda: document.getElementById('busquedaGasto').value.trim(),
        medioPago: document.getElementById('busquedaMedioPago').value.trim(),
        tipoGasto: document.getElementById('tipoGasto').value.trim(),
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarVentasPDF() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimir_ventas.php';
    form.target = '_blank';

    const datos = {
        nPdf: 1,
        busqueda: document.getElementById('busquedaVenta').value.trim(),
        medioPago: document.getElementById('busquedaMedioPago').value.trim(),
        estado: document.getElementById('busquedaEstado').value.trim(),
        nombreProducto: document.getElementById('busquedaNombreProducto').value.trim(),
        periodo: document.getElementById('period').value.trim(),
        year: document.getElementById('year').value.trim(),
        month: document.getElementById('month').value.trim(),
        start_date: document.getElementById('start_date').value.trim(),
        end_date: document.getElementById('end_date').value.trim()
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarVentasEXCEL() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimir_ventas.php';
    form.target = '_blank';

    const datos = {
        nExcel: 1,
        busqueda: document.getElementById('busquedaVenta').value.trim(),
        medioPago: document.getElementById('busquedaMedioPago').value.trim(),
        estado: document.getElementById('busquedaEstado').value.trim(),
        nombreProducto: document.getElementById('busquedaNombreProducto').value.trim(),
        periodo: document.getElementById('period').value.trim(),
        year: document.getElementById('year').value.trim(),
        month: document.getElementById('month').value.trim(),
        start_date: document.getElementById('start_date').value.trim(),
        end_date: document.getElementById('end_date').value.trim()
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarVentasPDFDia() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimir_ventas_dia.php';
    form.target = '_blank';

    const datos = {
        nPdf: 1,
        busqueda: document.getElementById('busquedaVentaDia').value.trim(),
        medioPago: document.getElementById('busquedaMedioPagoDia').value.trim(),
        estado: document.getElementById('busquedaEstadoDia').value.trim(),
        nombreProducto: document.getElementById('busquedaNombreProductoDia').value.trim(),
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarVentasEXCELDia() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimir_ventas_dia.php';
    form.target = '_blank';

    const datos = {
        nExcel: 1,
        busqueda: document.getElementById('busquedaVentaDia').value.trim(),
        medioPago: document.getElementById('busquedaMedioPagoDia').value.trim(),
        estado: document.getElementById('busquedaEstadoDia').value.trim(),
        nombreProducto: document.getElementById('busquedaNombreProductoDia').value.trim(),
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarVentasPDFMes() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimir_ventas_mes.php';
    form.target = '_blank';

    const datos = {
        nPdf: 1,
        busqueda: document.getElementById('busquedaVentaMes').value.trim(),
        medioPago: document.getElementById('busquedaMedioPagoMes').value.trim(),
        estado: document.getElementById('busquedaEstadoMes').value.trim(),
        nombreProducto: document.getElementById('busquedaNombreProductoMes').value.trim(),
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarVentasEXCELMes() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'imprimir_ventas_mes.php';
    form.target = '_blank';

    const datos = {
        nExcel: 1,
        busqueda: document.getElementById('busquedaVentaMes').value.trim(),
        medioPago: document.getElementById('busquedaMedioPagoMes').value.trim(),
        estado: document.getElementById('busquedaEstadoMes').value.trim(),
        nombreProducto: document.getElementById('busquedaNombreProductoMes').value.trim(),
    };

    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function buscarLogin(page) {
    if (!document.getElementById('tablaLoginsBody')) return;
    page = page || 1;
    var busqueda = document.getElementById('busquedaLogin').value;
    var IdEmpleado = document.getElementById('filtroEmpleado').value;
    var Exito = document.getElementById('filtroExito').value;
    var Dispositivo = document.getElementById('filtroDispositivo').value;
    var FechaDesde = '';
    var FechaHasta = '';
    if (document.getElementById('filtroFechaDesde')) FechaDesde = document.getElementById('filtroFechaDesde').value;
    if (document.getElementById('filtroFechaHasta')) FechaHasta = document.getElementById('filtroFechaHasta').value;

    fetch('buscar_logins.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            busqueda: busqueda, IdEmpleado: IdEmpleado, Exito: Exito,
            Dispositivo: Dispositivo, FechaDesde: FechaDesde, FechaHasta: FechaHasta, page: page
        })
    })
    .then(r => r.json())
    .then(resp => {
        var tbody = document.getElementById('tablaLoginsBody');
        var alerta = document.getElementById('alertaSinResultadosLogin');
        var pagHtml = '';
        if (!resp.resultado) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al cargar datos</td></tr>';
            return;
        }
        if (resp.datos.length === 0) {
            tbody.innerHTML = '';
            alerta.classList.remove('d-none');
        } else {
            alerta.classList.add('d-none');
            var rows = '';
            resp.datos.forEach(function(rr) {
                var nomEmp = rr.NombreEmpleado ? rr.NombreEmpleado : '—';
                var usrEmp = rr.UsuarioEmpleado ? rr.UsuarioEmpleado : '—';
                var badge = rr.Exito
                    ? '<span class="badge text-bg-success">Éxito</span>'
                    : '<span class="badge text-bg-danger">Fallo</span>';
                var motivo = rr.MotivoFallo ? rr.MotivoFallo : (rr.Exito ? 'Ingreso exitoso' : '—');
                rows += '<tr>'
                    + '<td>' + rr.FechaHora + '</td>'
                    + '<td title="' + usrEmp + '">' + nomEmp + '<br><small class="text-muted">@' + usrEmp + '</small></td>'
                    + '<td>' + rr.IP + '</td>'
                    + '<td>' + rr.Dispositivo + '</td>'
                    + '<td>' + badge + '</td>'
                    + '<td class="text-truncate" style="max-width:280px;" title="' + motivo + '">' + motivo + '</td>'
                    + '</tr>';
            });
            tbody.innerHTML = rows;
        }
        pagHtml = renderPaginadorLogins(resp.paginaActual, resp.totalPaginas, resp.total);
        document.getElementById('paginadorLogin').innerHTML = pagHtml;
    });
}

function renderPaginadorLogins(pagActual, totalPag, total) {
    if (totalPag <= 1) return '<div class="small text-muted">Total de registros: ' + total + '</div>';
    var html = '<nav aria-label="Paginacion logins"><ul class="pagination pagination-sm mb-0 me-2">';
    html += '<li class="page-item ' + (pagActual <= 1 ? 'disabled' : '') + '"><button class="page-link" onclick="buscarLogin(' + (pagActual - 1) + ')">«</button></li>';
    var maxBotones = 5;
    var ini = Math.max(1, pagActual - 2);
    var fin = Math.min(totalPag, ini + maxBotones - 1);
    ini = Math.max(1, fin - maxBotones + 1);
    for (var p = ini; p <= fin; p++) {
        html += '<li class="page-item ' + (p === pagActual ? 'active' : '') + '"><button class="page-link" onclick="buscarLogin(' + p + ')">' + p + '</button></li>';
    }
    html += '<li class="page-item ' + (pagActual >= totalPag ? 'disabled' : '') + '"><button class="page-link" onclick="buscarLogin(' + (pagActual + 1) + ')">»</button></li>';
    html += '</ul><div class="small text-muted align-self-center">Total: ' + total + ' registro(s)</div></nav>';
    return html;
}

function limpiarFiltrosLogins() {
    document.getElementById('busquedaLogin').value = '';
    document.getElementById('filtroEmpleado').value = '';
    document.getElementById('filtroExito').value = '';
    document.getElementById('filtroDispositivo').value = '';
    if (document.getElementById('filtroFechaDesde')) document.getElementById('filtroFechaDesde').value = '';
    if (document.getElementById('filtroFechaHasta')) document.getElementById('filtroFechaHasta').value = '';
    buscarLogin(1);
}

function obtenerFiltrosLogins() {
    return {
        busqueda: document.getElementById('busquedaLogin').value,
        IdEmpleado: document.getElementById('filtroEmpleado').value,
        Exito: document.getElementById('filtroExito').value,
        Dispositivo: document.getElementById('filtroDispositivo').value,
        FechaDesde: document.getElementById('filtroFechaDesde') ? document.getElementById('filtroFechaDesde').value : '',
        FechaHasta: document.getElementById('filtroFechaHasta') ? document.getElementById('filtroFechaHasta').value : ''
    };
}

function exportarLoginsPDF() {
    if (!document.getElementById('busquedaLogin')) return;
    var f = obtenerFiltrosLogins();
    var url = 'imprimir_logins.php?nPdf=1'
        + '&busqueda=' + encodeURIComponent(f.busqueda)
        + '&IdEmpleado=' + encodeURIComponent(f.IdEmpleado)
        + '&Exito=' + encodeURIComponent(f.Exito)
        + '&Dispositivo=' + encodeURIComponent(f.Dispositivo)
        + '&FechaDesde=' + encodeURIComponent(f.FechaDesde)
        + '&FechaHasta=' + encodeURIComponent(f.FechaHasta);
    window.open(url, '_blank');
}

function exportarLoginsEXCEL() {
    if (!document.getElementById('busquedaLogin')) return;
    var f = obtenerFiltrosLogins();
    var url = 'imprimir_logins.php?nExcel=1'
        + '&busqueda=' + encodeURIComponent(f.busqueda)
        + '&IdEmpleado=' + encodeURIComponent(f.IdEmpleado)
        + '&Exito=' + encodeURIComponent(f.Exito)
        + '&Dispositivo=' + encodeURIComponent(f.Dispositivo)
        + '&FechaDesde=' + encodeURIComponent(f.FechaDesde)
        + '&FechaHasta=' + encodeURIComponent(f.FechaHasta);
    window.open(url, '_blank');
}

window._descuentosCotizacionActual = [];
window._cotizacionBase = (function () {
    var p = window.location.pathname;
    return p.indexOf('/cotizaciones/') >= 0 ? './' : 'cotizaciones/';
})();
window._operacionesBase = (function () {
    var p = window.location.pathname;
    if (p.indexOf('/cotizaciones/') >= 0) return '../operaciones/';
    if (p.indexOf('/operaciones/') >= 0) return './';
    return 'operaciones/';
})();

function obtenerFiltrosCotizaciones() {
    if (!document.getElementById('busquedaCot')) return {};
    return {
        busqueda: document.getElementById('busquedaCot').value,
        IdEmpleado: document.getElementById('IdEmpleado').value,
        Estado: document.getElementById('filtroEstado').value,
        FechaDesde: document.getElementById('fechaDesde') ? document.getElementById('fechaDesde').value : '',
        FechaHasta: document.getElementById('fechaHasta') ? document.getElementById('fechaHasta').value : ''
    };
}

function buscarCotizacion(page) {
    if (!document.getElementById('tablaCotizacionesBody')) return;
    page = page || 1;
    var f = obtenerFiltrosCotizaciones();
    f.page = page;
    fetch(window._cotizacionBase + 'buscar_cotizaciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(f)
    })
    .then(r => r.json())
    .then(resp => {
        var tbody = document.getElementById('tablaCotizacionesBody');
        var alerta = document.getElementById('alertaSinResultadosCot');
        if (!resp.resultado) { tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al cargar cotizaciones</td></tr>'; return; }
        if (resp.datos.length === 0) {
            tbody.innerHTML = '';
            if (alerta) alerta.classList.remove('d-none');
        } else {
            if (alerta) alerta.classList.add('d-none');
            var rows = '';
            resp.datos.forEach(function(rr) {
                var est = rr.Estado || 'vigente';
                var badge = '';
                if (est === 'vigente') badge = 'primary';
                else if (est === 'aprobada') badge = 'success';
                else if (est === 'anulada') badge = 'danger';
                else badge = 'warning';
                var cliente = rr.NombreCliente ? rr.NombreCliente : '—';
                if (rr.DniCliente) cliente += ' <small class="text-muted">(' + rr.DniCliente + ')</small>';
                var vendedor = rr.NombreEmpleado ? rr.NombreEmpleado : '—';
                var total = 'S/. ' + (parseFloat(rr.Total) || 0).toFixed(2);
                var vigencia = rr.VigenciaHasta ? rr.VigenciaHasta : '—';
                var acciones = '<div class="btn-group btn-group-sm">'
                    + '<a class="btn btn-sm btn-outline-danger" title="PDF" target="_blank" href="' + window._cotizacionBase + 'imprimir_cotizacion.php?idCotizacion=' + rr.IdCotizacion + '&nPdf=1"><i class="fa-solid fa-file-pdf"></i></a>'
                    + '<a class="btn btn-sm btn-outline-success ms-1" title="Excel" target="_blank" href="' + window._cotizacionBase + 'imprimir_cotizacion.php?idCotizacion=' + rr.IdCotizacion + '&nExcel=1"><i class="fa-solid fa-file-excel"></i></a>';
                if ((est === 'vigente' || est === 'aprobada') && document.getElementById('rol')) {
                    var rol = parseInt(document.getElementById('rol').dataset.rol || 0, 10);
                    if (rol === 1) {
                        acciones += '<button class="btn btn-sm btn-outline-secondary ms-1" title="Anular" onclick="anularCotizacion(' + rr.IdCotizacion + ')"><i class="fa-solid fa-ban"></i></button>';
                    }
                }
                acciones += '</div>';
                rows += '<tr>'
                    + '<td>#' + String(rr.IdCotizacion).padStart(6,'0') + '</td>'
                    + '<td>' + rr.Fecha + '</td>'
                    + '<td>' + cliente + '</td>'
                    + '<td>' + vendedor + '</td>'
                    + '<td class="text-end">' + total + '</td>'
                    + '<td><span class="badge text-bg-' + badge + '">' + est + '</span></td>'
                    + '<td>' + vigencia + '</td>'
                    + '<td>' + acciones + '</td>'
                    + '</tr>';
            });
            tbody.innerHTML = rows;
        }
        document.getElementById('paginadorCotizacion').innerHTML = renderPaginadorCotizaciones(resp.paginaActual, resp.totalPaginas, resp.total);
    });
}

function renderPaginadorCotizaciones(pagActual, totalPag, total) {
    if (totalPag <= 1) return '<div class="small text-muted">Total: ' + total + ' registro(s)</div>';
    var html = '<nav><ul class="pagination pagination-sm mb-0 me-2">';
    html += '<li class="page-item ' + (pagActual <= 1 ? 'disabled' : '') + '"><button class="page-link" onclick="buscarCotizacion(' + (pagActual - 1) + ')">«</button></li>';
    var ini = Math.max(1, pagActual - 2), fin = Math.min(totalPag, ini + 4);
    ini = Math.max(1, fin - 4);
    for (var p = ini; p <= fin; p++) {
        html += '<li class="page-item ' + (p === pagActual ? 'active' : '') + '"><button class="page-link" onclick="buscarCotizacion(' + p + ')">' + p + '</button></li>';
    }
    html += '<li class="page-item ' + (pagActual >= totalPag ? 'disabled' : '') + '"><button class="page-link" onclick="buscarCotizacion(' + (pagActual + 1) + ')">»</button></li>';
    html += '</ul><div class="small text-muted align-self-center">Total: ' + total + ' registro(s)</div></nav>';
    return html;
}

function limpiarFiltrosCotizaciones() {
    if (document.getElementById('busquedaCot')) document.getElementById('busquedaCot').value = '';
    if (document.getElementById('IdEmpleado')) document.getElementById('IdEmpleado').value = '';
    if (document.getElementById('filtroEstado')) document.getElementById('filtroEstado').value = '';
    if (document.getElementById('fechaDesde')) document.getElementById('fechaDesde').value = '';
    if (document.getElementById('fechaHasta')) document.getElementById('fechaHasta').value = '';
    buscarCotizacion(1);
}

function exportarCotizacionesPDF() {
    var f = obtenerFiltrosCotizaciones();
    var url = window._cotizacionBase + 'imprimir_lista_cotizaciones.php?nPdf=1'
        + '&busqueda=' + encodeURIComponent(f.busqueda || '')
        + '&IdEmpleado=' + encodeURIComponent(f.IdEmpleado || '')
        + '&Estado=' + encodeURIComponent(f.Estado || '')
        + '&FechaDesde=' + encodeURIComponent(f.FechaDesde || '')
        + '&FechaHasta=' + encodeURIComponent(f.FechaHasta || '');
    window.open(url, '_blank');
}

function exportarCotizacionesEXCEL() {
    var f = obtenerFiltrosCotizaciones();
    var url = window._cotizacionBase + 'imprimir_lista_cotizaciones.php?nExcel=1'
        + '&busqueda=' + encodeURIComponent(f.busqueda || '')
        + '&IdEmpleado=' + encodeURIComponent(f.IdEmpleado || '')
        + '&Estado=' + encodeURIComponent(f.Estado || '')
        + '&FechaDesde=' + encodeURIComponent(f.FechaDesde || '')
        + '&FechaHasta=' + encodeURIComponent(f.FechaHasta || '');
    window.open(url, '_blank');
}

function anularCotizacion(idCot) {
    Swal.fire({
        title: 'Anular cotizacion #' + idCot + '?',
        text: 'Esta accion no se puede revertir.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, anular',
        cancelButtonText: 'Cancelar'
    }).then((res) => {
        if (!res.isConfirmed) return;
        fetch(window._cotizacionBase + 'anular_cotizacion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ IdCotizacion: idCot })
        }).then(r => r.json()).then(d => {
            if (d.resultado) {
                mostrarAlertaExitoTiempo(d.mensaje || 'Anulada');
                buscarCotizacion();
            } else {
                mostrarAlertaErrorTiempo(d.mensaje || 'Error al anular');
            }
        });
    });
}

function traerClienteCotizacion() {
    if (!document.getElementById('dniCliente')) return;
    var dni = String(document.getElementById('dniCliente').value || '').trim();
    if (dni.length < 8) { mostrarAlertaErrorTiempo('Ingrese DNI valido (8 digitos)'); return; }
    fetch(window._cotizacionBase + 'get_cliente_por_dni.php?dni=' + encodeURIComponent(dni))
    .then(r => r.json())
    .then(resp => {
        if (!resp.resultado) {
            mostrarAlertaExitoTiempo('Cliente no encontrado - puede completar los datos manualmente');
            return;
        }
        var d = resp.datos || {};
        if (document.getElementById('nombreCliente')) document.getElementById('nombreCliente').value = d.Nombre || d.NombreCliente || '';
        if (document.getElementById('direccionCliente')) document.getElementById('direccionCliente').value = d.Direccion || d.DireccionCliente || '';
        if (document.getElementById('telefonoCliente')) document.getElementById('telefonoCliente').value = d.Telefono || d.TelefonoCliente || '';
        if (document.getElementById('emailCliente')) document.getElementById('emailCliente').value = d.Email || d.EmailCliente || '';
        mostrarAlertaExitoTiempo('Cliente cargado');
    });
}

function limpiarClienteCotizacion() {
    ['dniCliente','nombreCliente','direccionCliente','telefonoCliente','emailCliente'].forEach(function (id) {
        if (document.getElementById(id)) document.getElementById(id).value = '';
    });
    if (document.getElementById('observaciones')) document.getElementById('observaciones').value = '';
}

function buscarArticulosCot() {
    if (!document.getElementById('palabraClave') || !document.getElementById('resultados')) return;
    var q = document.getElementById('palabraClave').value.trim();
    if (q.length < 1) { document.getElementById('resultados').innerHTML = ''; return; }
    fetch(window._cotizacionBase + 'buscar_articulos.php?busqueda=' + encodeURIComponent(q))
    .then(r => r.json())
    .then(resp => {
        var el = document.getElementById('resultados');
        if (!resp.resultado || resp.datos.length === 0) { el.innerHTML = '<div class="list-group-item small text-muted">Sin resultados</div>'; return; }
        var html = '';
        resp.datos.forEach(function(a) {
            var stock = parseFloat(a.Cantidad) || 0;
            var precio = 'S/. ' + (parseFloat(a.Precio_Unitario) || 0).toFixed(2);
            html += '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start" onclick="mostrarFormularioCot(\'añadirProductoCotizacion\',' + a.IdArticulo + ')">'
                + '<div><div class="fw-semibold">' + a.Nombre + '</div><div class="small text-muted">Stock: ' + stock + ' ' + (a.Unidad_Base || 'u.') + (a.Cod_Barra ? ' • Cod: ' + a.Cod_Barra : '') + '</div></div>'
                + '<span class="badge text-bg-primary rounded-pill ms-2">' + precio + '</span></button>';
        });
        el.innerHTML = html;
    });
}

function buscarProductoTempCotizacion() {
    if (!document.getElementById('datosCotizacion')) return;
    fetch(window._cotizacionBase + 'get_productos_temp_cotizacion.php')
    .then(r => r.json())
    .then(d => {
        if (d.resultado) mostrarDatosCotizacion(d.datos || []);
    });
}

function mostrarDatosCotizacion(data) {
    if (!document.getElementById('datosCotizacion')) return;
    var rows = '';
    var total = 0;
    if (data && data.length > 0) {
        data.forEach(function(item) {
            var cant = parseFloat(item.cantidad) || 0;
            var prec = parseFloat(item.PrecioConDescuento) || parseFloat(item.precio_venta) || 0;
            var dto = parseFloat(item.PorcentajeDescuento) || 0;
            var udm = item.Unidad || 'u.';
            var subt = cant * prec;
            total += subt;
            rows += '<tr>'
                + '<td>' + item.nombreArticulo + '</td>'
                + '<td class="text-end">' + cant + '</td>'
                + '<td><span class="badge text-bg-light text-dark border">' + udm + '</span></td>'
                + '<td class="text-end">S/. ' + prec.toFixed(2) + '</td>'
                + '<td class="text-end">' + (dto > 0 ? dto.toFixed(2) + '%' : '-') + '</td>'
                + '<td class="text-end fw-semibold">S/. ' + subt.toFixed(2) + '</td>'
                + '<td><button class="btn btn-sm btn-outline-danger" onclick="quitarProductoTempCotizacion(' + item.correlativo + ')"><i class="fa-solid fa-trash"></i></button></td>'
                + '</tr>';
        });
    } else {
        rows = '<tr><td colspan="7" class="text-center small text-muted py-3">No hay articulos agregados. Busque y agregue productos.</td></tr>';
    }
    document.getElementById('datosCotizacion').innerHTML = rows;
    if (document.getElementById('totalCotizacion')) document.getElementById('totalCotizacion').value = 'S/. ' + total.toFixed(2);
}

function ocultarFormularioCot(nombre) {
    if (nombre === 'añadirProductoCotizacion') {
        var ids = ['correlativoArticuloCot','codigoBarraCot','nombreArticuloCot','categoriaArticuloCot','stockActualCot','precioVentaMostrarCot','cantidadVentaCot','unidadVentaCot','factorMostrarCot','precioMinimoMostrarCot','descuentoMostrarCot','precioConDescuentoMostrarCot','subTotalMostrarCot','escalasDescuentoInfoCot','factorAplicadoCot','porcentajeDescuentoAplicadoCot','precioMinimoArticuloCot','unidadSeleccionadaCot'];
        ids.forEach(function(i) { if (document.getElementById(i)) document.getElementById(i).value = ''; });
        window._descuentosCotizacionActual = [];
    }
}

function mostrarFormularioCot(nombre, idArticulo) {
    if (nombre !== 'añadirProductoCotizacion') return;
    if (document.getElementById('formularioañadirProductoCotizacion')) {
        document.getElementById('formularioañadirProductoCotizacion').classList.add('show');
        document.getElementById('formularioañadirProductoCotizacion').style.display = 'block';
    }
    fetch(window._operacionesBase + 'get_producto.php?id=' + idArticulo)
    .then(r => r.json())
    .then(resp => {
        if (!resp.resultado) { mostrarAlertaErrorTiempo('Articulo no encontrado'); return; }
        var d = resp.datos || {};
        if (document.getElementById('correlativoArticuloCot')) document.getElementById('correlativoArticuloCot').value = d.IdArticulo || '';
        if (document.getElementById('codigoBarraCot')) document.getElementById('codigoBarraCot').value = d.Cod_Barra || '';
        if (document.getElementById('nombreArticuloCot')) document.getElementById('nombreArticuloCot').value = d.Nombre || '';
        if (document.getElementById('categoriaArticuloCot')) document.getElementById('categoriaArticuloCot').value = d.NombreCategoria || '';
        var stock = parseFloat(d.Cantidad) || 0;
        if (document.getElementById('stockActualCot')) document.getElementById('stockActualCot').value = stock + ' ' + (d.Unidad_Base || 'u.');
        if (document.getElementById('precioVentaMostrarCot')) document.getElementById('precioVentaMostrarCot').value = (parseFloat(d.Precio_Unitario) || 0).toFixed(2);
        if (document.getElementById('precioMinimoArticuloCot')) document.getElementById('precioMinimoArticuloCot').value = (parseFloat(d.Precio_Minimo) || 0).toFixed(2);
        if (document.getElementById('precioMinimoMostrarCot')) document.getElementById('precioMinimoMostrarCot').value = (parseFloat(d.Precio_Minimo) || 0).toFixed(2);
        if (document.getElementById('cantidadVentaCot')) document.getElementById('cantidadVentaCot').value = 1;
        var unidades = resp.unidades && resp.unidades.length ? resp.unidades : [{Unidad: (d.Unidad_Base || 'u.'), Factor: 1}];
        var sel = document.getElementById('unidadVentaCot');
        if (sel) {
            sel.innerHTML = '';
            unidades.forEach(function(u) {
                var opt = document.createElement('option');
                opt.value = u.Unidad;
                opt.dataset.factor = u.Factor || 1;
                opt.dataset.precio = u.PrecioVenta || (parseFloat(d.Precio_Unitario) * (u.Factor || 1));
                opt.textContent = u.Unidad + (u.Factor && u.Factor !== 1 ? ' (x' + u.Factor + ')' : '');
                if (u.Predeterminada || u.EsPredeterminada) opt.selected = true;
                sel.appendChild(opt);
            });
            if (!sel.querySelector('option[selected]') && sel.options[0]) sel.options[0].selected = true;
        }
        window._descuentosCotizacionActual = resp.descuentos || [];
        var info = '';
        if (window._descuentosCotizacionActual.length > 0) {
            info = '<div class="small"><div class="fw-semibold mb-1">Escalas de descuento:</div><ul class="mb-0 ps-3">';
            window._descuentosCotizacionActual.forEach(function(esc) {
                info += '<li>A partir de ' + (esc.CantidadMinima || esc.CantMinima || 0) + ' un. base → ' + (esc.PorcentajeDescuento || 0) + '%</li>';
            });
            info += '</ul></div>';
        } else {
            info = '<div class="small text-muted">Sin descuentos escalonados configurados para este articulo.</div>';
        }
        if (document.getElementById('escalasDescuentoInfoCot')) document.getElementById('escalasDescuentoInfoCot').innerHTML = info;
        cambiarUnidadVentaCotizacion();
    });
}

function cambiarUnidadVentaCotizacion() {
    var sel = document.getElementById('unidadVentaCot');
    if (!sel) return;
    var opt = sel.selectedOptions[0];
    if (!opt) return;
    var factor = parseFloat(opt.dataset.factor) || 1;
    var precioUdM = parseFloat(opt.dataset.precio) || 0;
    if (document.getElementById('factorMostrarCot')) document.getElementById('factorMostrarCot').value = factor.toFixed(4);
    if (document.getElementById('factorAplicadoCot')) document.getElementById('factorAplicadoCot').value = factor.toFixed(4);
    if (document.getElementById('unidadSeleccionadaCot')) document.getElementById('unidadSeleccionadaCot').value = opt.value;
    if (document.getElementById('precioVentaMostrarCot')) document.getElementById('precioVentaMostrarCot').value = precioUdM.toFixed(2);
    calcularDescuentoVentaCotizacion();
    actualizarEquivalenteVentaCot();
}

function actualizarEquivalenteVentaCot() {
    var infoEl = document.getElementById('equivalenteVentaInfo');
    if (!infoEl) return;
    var cantEl = document.getElementById('cantidadVentaCot');
    var factEl = document.getElementById('factorAplicadoCot');
    var udmEl = document.getElementById('unidadSeleccionadaCot');
    var cant = parseFloat((cantEl && cantEl.value) || 0);
    var fact = parseFloat((factEl && factEl.value) || 0);
    var udm = (udmEl && udmEl.value) || '';
    if (cant > 0 && fact > 0) {
        var convertida = cant * fact;
        infoEl.style.display = 'block';
        infoEl.innerHTML = 'Equivalente en unidad base (referencia): <span class="text-primary">' + convertida.toLocaleString(undefined, { maximumFractionDigits: 4 }) + '</span>' + (udm ? ' · Cotización muestra: <strong>' + cant + ' ' + udm + '</strong>' : '') + ' <span class="text-muted">(no modifica stock)</span>';
    } else {
        infoEl.style.display = 'none';
        infoEl.innerHTML = '';
    }
}

function calcularDescuentoVentaCotizacion() {
    var factor = parseFloat(document.getElementById('factorAplicadoCot') ? document.getElementById('factorAplicadoCot').value : 1) || 1;
    var cantidad = parseFloat(document.getElementById('cantidadVentaCot') ? document.getElementById('cantidadVentaCot').value : 1) || 1;
    var precioUdM = parseFloat(document.getElementById('precioVentaMostrarCot') ? document.getElementById('precioVentaMostrarCot').value : 0) || 0;
    var precioMin = parseFloat(document.getElementById('precioMinimoArticuloCot') ? document.getElementById('precioMinimoArticuloCot').value : 0) || 0;
    var cantidadEnBase = cantidad * factor;
    var porcentajeDto = 0;
    var dtos = Array.isArray(window._descuentosCotizacionActual) ? window._descuentosCotizacionActual.slice() : [];
    dtos.sort(function(a, b) { return parseFloat(b.PorcentajeDescuento || b.Porcentaje || 0) - parseFloat(a.PorcentajeDescuento || a.Porcentaje || 0); });
    for (var i = 0; i < dtos.length; i++) {
        var min = parseFloat(dtos[i].CantMinima || dtos[i].CantidadMinima || 0);
        if (cantidadEnBase >= min) { porcentajeDto = parseFloat(dtos[i].PorcentajeDescuento || dtos[i].Porcentaje || 0); break; }
    }
    var precioConDescuento = precioUdM * (1 - (porcentajeDto / 100));
    var sub = precioConDescuento * cantidad;
    if (document.getElementById('descuentoMostrarCot')) document.getElementById('descuentoMostrarCot').value = porcentajeDto.toFixed(2) + '%';
    if (document.getElementById('porcentajeDescuentoAplicadoCot')) document.getElementById('porcentajeDescuentoAplicadoCot').value = porcentajeDto.toFixed(2);
    if (document.getElementById('precioConDescuentoMostrarCot')) document.getElementById('precioConDescuentoMostrarCot').value = precioConDescuento.toFixed(2);
    if (document.getElementById('subTotalMostrarCot')) document.getElementById('subTotalMostrarCot').value = sub.toFixed(2);
    actualizarEquivalenteVentaCot();
}

function añadirProductoCotizacion() {
    var idArticulo = document.getElementById('correlativoArticuloCot') ? parseInt(document.getElementById('correlativoArticuloCot').value || 0, 10) : 0;
    var cantidad = document.getElementById('cantidadVentaCot') ? parseFloat(document.getElementById('cantidadVentaCot').value || 0) : 0;
    var precioVentaUdM = parseFloat(document.getElementById('precioVentaMostrarCot') ? document.getElementById('precioVentaMostrarCot').value : 0) || 0;
    var unidad = document.getElementById('unidadSeleccionadaCot') ? document.getElementById('unidadSeleccionadaCot').value : 'u.';
    var factor = parseFloat(document.getElementById('factorAplicadoCot') ? document.getElementById('factorAplicadoCot').value : 1) || 1;
    var porcentajeDto = parseFloat(document.getElementById('porcentajeDescuentoAplicadoCot') ? document.getElementById('porcentajeDescuentoAplicadoCot').value : 0) || 0;
    var precioConDescuento = parseFloat(document.getElementById('precioConDescuentoMostrarCot') ? document.getElementById('precioConDescuentoMostrarCot').value : 0) || 0;
    if (!idArticulo || idArticulo <= 0) { mostrarAlertaErrorTiempo('Seleccione un articulo'); return; }
    if (cantidad <= 0) { mostrarAlertaErrorTiempo('Cantidad invalida'); return; }
    var url = window._cotizacionBase + 'añadir_producto_temp_cotizacion.php'
        + '?id=' + encodeURIComponent(idArticulo)
        + '&cantidad=' + encodeURIComponent(cantidad)
        + '&precioCompra=0'
        + '&precioVenta=' + encodeURIComponent(precioVentaUdM.toFixed(2))
        + '&unidad=' + encodeURIComponent(unidad)
        + '&factorAplicado=' + encodeURIComponent(factor.toFixed(4))
        + '&porcentajeDescuento=' + encodeURIComponent(porcentajeDto.toFixed(2))
        + '&precioConDescuento=' + encodeURIComponent(precioConDescuento.toFixed(2));
    fetch(url).then(r => r.json()).then(d => {
        if (d.resultado) {
            mostrarDatosCotizacion(d.datos || []);
            if (document.getElementById('formularioañadirProductoCotizacion')) {
                document.getElementById('formularioañadirProductoCotizacion').classList.remove('show');
                document.getElementById('formularioañadirProductoCotizacion').style.display = 'none';
            }
            if (document.getElementById('palabraClave')) document.getElementById('palabraClave').value = '';
            if (document.getElementById('resultados')) document.getElementById('resultados').innerHTML = '';
            mostrarAlertaExitoTiempo(d.mensaje || 'Agregado');
        } else {
            mostrarAlertaErrorTiempo(d.mensaje || 'Error al agregar');
        }
    });
}

function quitarProductoTempCotizacion(correlativo) {
    fetch(window._cotizacionBase + 'quitar_producto_temp_cotizacion.php?id=' + correlativo)
    .then(r => r.json())
    .then(d => {
        if (d.resultado) mostrarDatosCotizacion(d.datos || []);
        else mostrarAlertaErrorTiempo(d.mensaje || 'Error al quitar');
    });
}

function limpiarDetalleCotizacion() {
    Swal.fire({
        title: 'Limpiar detalle?',
        text: 'Quitara todos los articulos de la cotizacion actual.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Si, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((res) => {
        if (!res.isConfirmed) return;
        fetch(window._cotizacionBase + 'get_productos_temp_cotizacion.php?limpiar=1')
        .then(r => r.json())
        .then(d => {
            if (!d.resultado) {
                fetch(window._cotizacionBase + 'get_productos_temp_cotizacion.php')
                .then(r2 => r2.json()).then(d2 => {
                    var arr = d2.datos || [];
                    var chain = Promise.resolve();
                    arr.forEach(function(it) {
                        chain = chain.then(() => fetch(window._cotizacionBase + 'quitar_producto_temp_cotizacion.php?id=' + it.correlativo).then(r3 => r3.json()));
                    });
                    chain.then(() => { mostrarDatosCotizacion([]); mostrarAlertaExitoTiempo('Detalle limpiado'); });
                });
            } else {
                mostrarDatosCotizacion([]);
                mostrarAlertaExitoTiempo('Detalle limpiado');
            }
        });
    });
}

function guardarCotizacion() {
    var dni = document.getElementById('dniCliente') ? String(document.getElementById('dniCliente').value || '').trim() : '';
    var nombre = document.getElementById('nombreCliente') ? document.getElementById('nombreCliente').value : '';
    if (dni.length < 8) { mostrarAlertaErrorTiempo('Ingrese DNI valido del cliente'); return; }
    if (!nombre || nombre.length < 2) { mostrarAlertaErrorTiempo('Ingrese nombre del cliente'); return; }
    var body = {
        dniCliente: dni,
        nombreCliente: nombre,
        direccionCliente: document.getElementById('direccionCliente') ? document.getElementById('direccionCliente').value : '',
        telefonoCliente: document.getElementById('telefonoCliente') ? document.getElementById('telefonoCliente').value : '',
        emailCliente: document.getElementById('emailCliente') ? document.getElementById('emailCliente').value : '',
        fechaVigencia: document.getElementById('fechaVigencia') ? document.getElementById('fechaVigencia').value : '',
        observaciones: document.getElementById('observaciones') ? document.getElementById('observaciones').value : ''
    };
    fetch(window._cotizacionBase + 'procesar_cotizacion.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    }).then(r => r.json()).then(d => {
        if (d.resultado) {
            Swal.fire({
                icon: 'success',
                title: 'Cotizacion guardada #' + d.idCotizacion,
                html: '<a class="btn btn-sm btn-danger me-1" target="_blank" href="' + window._cotizacionBase + 'imprimir_cotizacion.php?idCotizacion=' + d.idCotizacion + '&nPdf=1">Descargar PDF</a>'
                    + '<a class="btn btn-sm btn-success" target="_blank" href="' + window._cotizacionBase + 'imprimir_cotizacion.php?idCotizacion=' + d.idCotizacion + '&nExcel=1">Descargar Excel</a>',
                showConfirmButton: true,
                confirmButtonText: 'Nueva cotizacion'
            }).then(() => {
                limpiarClienteCotizacion();
                mostrarDatosCotizacion([]);
                buscarProductoTempCotizacion();
            });
        } else {
            mostrarAlertaErrorTiempo(d.mensaje || 'Error al guardar');
        }
    });
}


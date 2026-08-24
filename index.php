<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PachacuTEC Technology Soft | Login</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
    <link rel="icon" href="img/SVpachacutec.ico" type="image/ico">
    <link href="css/style.css" rel="stylesheet">
    <script type="text/javascript" src="sistema/js/jquery-3.6.0.min.js"></script>
</head>
<body>
<section id="container">
    <form class="formulario" method="post" action="">
        <h3>INICIAR SESIÓN</h3>
        <div class="contenedor">
            <div class="input-contenedor">
                <i class="fas fa-user icon"></i>
                <input type="text" id="usuario" name="Usuario" placeholder="Usuario" />
            </div>
            <div class="input-contenedor">
                <i class="fas fa-key icon"></i>
                <input type="password" id="clave" name="Clave" placeholder="Contraseña" />
            </div>
            <div class="alert hidden"></div><br>
            <input type="submit" name="submit" value="Ingresar" class="button" />
        </div>
    </form>
</section>

<script src="js/funciones.js"></script>
</body>
</html>

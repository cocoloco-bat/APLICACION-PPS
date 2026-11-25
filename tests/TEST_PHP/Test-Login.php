<?php
<?php

$host = "localhost";
$usuario_bd = "root";
$clave_bd = "";
$nombre_bd = "bebidas";

try {
    $conexion = mysqli_connect($host, $usuario_bd, $clave_bd, $nombre_bd);
} catch (mysqli_sql_exception $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage() . "\n");
}


mysqli_query($conexion, "DROP TABLE IF EXISTS usuarios_test");
mysqli_query($conexion, "CREATE TABLE usuarios_test (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)");

$hash = password_hash("1234", PASSWORD_DEFAULT);
mysqli_query($conexion, "INSERT INTO usuarios_test (usuario, password) VALUES ('admin', '$hash')");


function login($conexion, $usuario, $password)
{
    
    if (empty($usuario) || empty($password)) {
        return false;
    }

    
    $sql = mysqli_prepare($conexion, "SELECT password FROM usuarios_test WHERE usuario = ?");
    mysqli_stmt_bind_param($sql, "s", $usuario);
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql);
    $fila = mysqli_fetch_assoc($resultado);

    
    if (!$fila) {
        return false;
    }

    
    return password_verify($password, $fila['password']);
}



function comprobar($nombre, $resultado_obtenido, $resultado_esperado)
{
    if ($resultado_obtenido === $resultado_esperado) {
        echo "OK    - $nombre<br>\n";
    } else {
        echo "FALLO - $nombre<br>\n";
    }
}


// ================= TESTS =================

echo "<h2>Tests del login</h2>\n";


comprobar("Login con usuario y contraseña correctos",
    login($conexion, "admin", "1234"), true);


comprobar("Login con contraseña incorrecta",
    login($conexion, "admin", "malapass"), false);


comprobar("Login con usuario que no existe",
    login($conexion, "pepe", "1234"), false);


comprobar("Login con el usuario vacío",
    login($conexion, "", "1234"), false);


comprobar("Login con la contraseña vacía",
    login($conexion, "admin", ""), false);


$res = mysqli_query($conexion, "SELECT * FROM usuarios_test WHERE usuario = 'admin'");
comprobar("El usuario admin existe en la base de datos",
    mysqli_num_rows($res) == 1, true);


$fila = mysqli_fetch_assoc($res);
comprobar("La contraseña no se guarda en texto plano",
    $fila['password'] != "1234", true);


comprobar("Inyección SQL no permite entrar",
    login($conexion, "' OR '1'='1", "' OR '1'='1"), false);



mysqli_query($conexion, "DROP TABLE usuarios_test");
mysqli_close($conexion);

?>
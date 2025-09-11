<?php
    session_start();

    $conexion = new mysqli("localhost", "root", "", "calera");
    $conexion->autocommit(FALSE); //Desactiva el autocommit que efectua una consulta ni bien se realiza

    function errorFatal(){
        echo "Error fatal";
    }

    function consultaSelect(&$conexion, $consulta, $tipo_d, $parametros){
        $cursor = $conexion->prepare($consulta);
        $cursor->bind_param($tipo_d, ...$parametros);

        $cursor->execute();
        $resultado = $cursor->get_result();

        $lista_resultados = [];

        if($resultado->num_rows > 0){
            while($fila = $resultado->fetch_assoc()){
                array_push($lista_resultados, $fila);
            }
            return $lista_resultados;
        }else{
            return 1;
        }
    } //Devuelve un array de indices numericos que contien arrays con indices alfanumericos, siendo cada uno de estos un registro de la base de datos y los indices alfanumericos las columnas de la base de datos

    function consultaInsert(&$conexion, $consulta, $tipo_d, $parametros){
        $cursor = $conexion->prepare($consulta);
        $cursor->bind_param($tipo_d, ...$parametros);

        $cursor->execute();
        $resultado = $cursor->get_result();

        if(!$conexion->commit()){
            $conexion->rollback();
            errorFatal();
        }

        return $resultado;
    }

    print_r(consultaSelect($conexion, "SELECT * FROM usuarios WHERE nombre = ? AND permisos = ?;", "ss", ["Bruno", "PR"]));
    // print_r(consultaInsert($conexion, "INSERT INTO `usuarios`(`nombre`, `permisos`) VALUES (?, ?);", "ss", ["Bruno", "PR"]));
?>
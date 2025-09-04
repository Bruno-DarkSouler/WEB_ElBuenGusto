<?php
    session_start();

    $conexion = new mysqli("localhost", "root", "", "calera");

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
    }

    print_r(consultaSelect($conexion, "SELECT * FROM usuarios WHERE nombre = ? AND id <= ?;", "si", ["Bruno", 7]));
?>
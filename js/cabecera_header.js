const nombre_ubicacion_extension = location.pathname.split("/").at(-1);
const nombre_ubicacion = nombre_ubicacion_extension.split(".").at(0);

const secciones_menu_inferior = document.getElementsByClassName("contenedor_seccion");

switch(nombre_ubicacion){
    case "perfil":
        secciones_menu_inferior[0].classList.add("contenedor_seccion_actual");
        break;
    case "admin":
        secciones_menu_inferior[1].classList.add("contenedor_seccion_actual");
        break;
    case "repartidor":
        secciones_menu_inferior[2].classList.add("contenedor_seccion_actual");
        break;
    case "cajero":
        secciones_menu_inferior[3].classList.add("contenedor_seccion_actual");
        break;
    case "cocina":
        secciones_menu_inferior[4].classList.add("contenedor_seccion_actual");
        break;
    case "inicio":
        secciones_menu_inferior[5].classList.add("contenedor_seccion_actual");
        break;
}


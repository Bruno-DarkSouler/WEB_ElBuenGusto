function abrir_login(){
    var login = document.getElementById("login");
    var registro = document.getElementById("registro");
    login.style.display = "flex";
    registro.style.display = "none";
}
function abrir_registro(){
    var login = document.getElementById("login");
    var registro = document.getElementById("registro");
    login.style.display = "none";
    registro.style.display = "flex";
}
function cerrar_modal(){
    var login = document.getElementById("login");
    var registro = document.getElementById("registro");
    login.style.display = "none";
    registro.style.display = "none";
}
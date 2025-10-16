<!-- footer.php -->
<footer style="
    background-color: rgb(200, 30, 45);
    color: rgb(245, 235, 210);
    font-family: 'Poppins', sans-serif;
    padding: 45px 0 25px;
    box-shadow: 0 -4px 10px rgba(0,0,0,0.25);
">
    <div style="
        max-width: 1300px;
        margin: auto;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        align-items: flex-start;
        text-align: left;
        gap: 40px;
        padding: 0 60px;
        font-size: 1.05em;
    ">

        <!-- Columna 1: Logo -->
        <div style="display:flex; align-items:center; gap:12px;">
            <img src='../img/logo.png' alt='Logo El Buen Gusto' style='width:80px; height:80px; border-radius:40%; background:rgb(245,235,210); padding:5px;'>
            <h2 style='margin:0; font-size:2em; font-weight:700;'>El Buen Gusto</h2>
        </div>

        <!-- Columna 2: Contacto -->
        <div style="line-height:1.6;">
        <h3 style="margin-bottom: 10px;">Contacto</h3>
            <p style="margin:6px 0;"><strong>📍 Domicilio:</strong> Cerrito 3966, Buenos Aires</p>
            <p style="margin:6px 0;"><strong>📞 Tel:</strong> <a href='tel:+541162165019' style='color:rgb(245,235,210); text-decoration:none;'>+54 11 6216-5019</a></p>
            <p style="margin:6px 0;"><strong>✉️ Email:</strong> <a href='mailto:contacto@elbuengusto.com' style='color:rgb(245,235,210); text-decoration:none;'>contacto@elbuengusto.com</a></p>
        </div>

        <!-- Columna 3: Redes Sociales -->
        <div style="flex: 1; min-width: 250px;">
            <h3 style="margin-bottom: 10px;">Seguinos</h3>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a href="https://www.instagram.com/elbuengusto" target="_blank" title="Instagram" style="color:rgb(245,235,210); text-decoration:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 0C5.8 0 5.5 0 4.7.1 3.9.2 3.3.4 2.8.6a4.6 4.6 0 0 0-1.6 1.6C.9 2.7.7 3.3.6 4.1.5 4.9.5 5.2.5 7.4s0 2.5.1 3.3c.1.8.3 1.4.6 1.9a4.6 4.6 0 0 0 1.6 1.6c.5.3 1.1.5 1.9.6.8.1 1.1.1 3.3.1s2.5 0 3.3-.1c.8-.1 1.4-.3 1.9-.6a4.6 4.6 0 0 0 1.6-1.6c.3-.5.5-1.1.6-1.9.1-.8.1-1.1.1-3.3s0-2.5-.1-3.3c-.1-.8-.3-1.4-.6-1.9a4.6 4.6 0 0 0-1.6-1.6C12.7.9 12.1.7 11.3.6 10.5.5 10.2.5 8 .5 5.8.5 5.5.5 4.7.6ZM8 3.9a4.1 4.1 0 1 1 0 8.2 4.1 4.1 0 0 1 0-8.2Zm0 6.7a2.6 2.6 0 1 0 0-5.2 2.6 2.6 0 0 0 0 5.2ZM12.3 3.4a1 1 0 1 1 0-2.1 1 1 0 0 1 0 2.1Z"/>
                    </svg>
                </a>
                <a href="https://www.facebook.com/elbuengusto" target="_blank" title="Facebook" style="color:rgb(245,235,210); text-decoration:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.94 8.66H7.58v6.94H5.5V8.66H4.5V6.92H5.5V5.83c0-1 .5-2.6 2.6-2.6h1.8v1.8h-1c-.5 0-1 .2-1 .9v.9h2l-.2 1.7z"/>
                    </svg>
                </a>
                <a href="https://wa.me/541162165019" target="_blank" title="WhatsApp" style="color:rgb(245,235,210); text-decoration:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M13.601 2.326A7.802 7.802 0 0 0 8.001.043 7.85 7.85 0 0 0 .148 7.783a7.7 7.7 0 0 0 1.056 3.896L.034 16l4.45-1.145a7.857 7.857 0 0 0 3.519.846h.003a7.86 7.86 0 0 0 5.595-13.375ZM8.001 14.7h-.002a6.42 6.42 0 0 1-3.275-.9l-.234-.139-2.64.68.703-2.574-.15-.238a6.27 6.27 0 0 1-.97-3.35 6.42 6.42 0 0 1 10.94-4.566 6.42 6.42 0 0 1-4.372 10.087Zm3.57-4.84c-.197-.098-1.166-.574-1.348-.639-.18-.066-.311-.098-.443.098-.133.197-.508.639-.623.77-.115.131-.23.148-.426.049-.197-.098-.832-.306-1.584-.977a5.866 5.866 0 0 1-1.085-1.34c-.115-.197-.012-.303.086-.4.088-.088.197-.23.295-.344.098-.115.131-.197.197-.328.066-.131.033-.246-.016-.344-.049-.098-.443-1.07-.607-1.465-.16-.385-.324-.332-.443-.338-.115-.006-.246-.007-.377-.007a.73.73 0 0 0-.525.246c-.18.197-.689.672-.689 1.639 0 .967.707 1.902.807 2.037.098.131 1.392 2.129 3.373 2.986.471.203.84.324 1.127.414.473.15.904.129 1.246.078.381-.057 1.166-.477 1.33-.937.164-.459.164-.852.115-.936-.049-.082-.18-.13-.377-.229Z"/>
                    </svg>
                </a>
                <a href="https://www.tiktok.com/@elbuengusto" target="_blank" title="TikTok" style="color:rgb(245,235,210); text-decoration:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M9.5 0a5 5 0 0 0 5 5v2a7 7 0 0 1-4.5-1.6V11a5 5 0 1 1-5-5h1.5v2H5.9A3 3 0 1 0 9 11V0h.5z"/>
                    </svg>
                </a>
                <a href="https://www.youtube.com/@elbuengusto" target="_blank" title="YouTube" style="color:rgb(245,235,210); text-decoration:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.051 1.999h-.102C3.917 1.999 1 4.916 1 8.5c0 3.584 2.917 6.501 6.949 6.501h.102c4.034 0 6.949-2.917 6.949-6.501 0-3.584-2.917-6.501-6.949-6.501zM6.545 11.466V5.535l4.546 2.965-4.546 2.966z"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Columna 4: Delivery -->
        <div style="line-height:1.6;">
        <h3 style="margin-bottom: 10px;">Delivery rapido y confiable</h3>
            <p style="margin:6px 0;">Envíos dentro de la zona sur, norte y centro</p>
            <p style="margin:6px 0;"><a href="https://maps.google.com?q=Cerrito+3966+Buenos+Aires" target="_blank" style="color:rgb(245,235,210); text-decoration:underline;">📍 Ver en Google Maps</a></p>
        </div>
    </div>

    <!-- Línea divisoria -->
    <hr style="border: 1px solid rgb(80, 50, 20); margin: 35px auto 15px; width: 90%;">

    <div style="text-align:center; font-size:1em; opacity:0.85;">
        &copy; <?php echo date('Y'); ?> <strong>El Buen Gusto</strong> — Todos los derechos reservados.
    </div>
</footer>

<?php
session_start();
include("conexion.php");

// inicializamos la variable de mensaje como null para evitar que se muestre al cargar la página
$message = null;
$messageType = 'info'; // tipo de mensaje para SweetAlert2 (info, success, warning, error)

// Mostrar mensaje solo si fue por timeout
if (isset($_GET['reason']) && $_GET['reason'] == 'timeout') {
    $message = "Su sesión se cerró automáticamente por inactividad";
    $messageType = 'warning';
    // limpiar el parámetro de la URL
    echo "<script>
        if (window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>";
}

// Mostrar mensaje de cambio de contraseña
if(isset($_GET['source']) && $_GET['source'] == 'password_update' && isset($_SESSION['password_change_message'])) {
    $password_message = $_SESSION['password_change_message'];
    unset($_SESSION['password_change_message']);
    $message = htmlspecialchars($password_message, ENT_QUOTES);
    $messageType = 'success';
    // Limpiar el parámetro de la URL
    echo "<script>
        if (window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>";
}

// Verificamos si se envió el formulario (si se presionó el submit)
if (isset($_POST['submit'])) {
    // Primero obtenemos los datos del formulario
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // No aplicar htmlspecialchars aquí para la verificación del hash
    $captcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

    // Verificamos primero el reCAPTCHA
    if(empty($captcha)) {
        $message = "Por favor, completa el reCAPTCHA";
        $messageType = 'warning';
    } else {
        $secretkey = "6LdVkrIrAAAAALXGfzMhYeIlaTLb1wDkX8NgHTVI";
        $ip = $_SERVER['REMOTE_ADDR'];
        $answer = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretkey&response=$captcha&remoteip=$ip");
        $atribute = json_decode($answer, TRUE);

        if(!$atribute['success']){
            $message = "Verificación de reCAPTCHA fallida";
            $messageType = 'error';
        } else {
            // Solo si el reCAPTCHA es válido, verificamos las credenciales
            $stmt = $conn->prepare("SELECT * FROM user WHERE gmail = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $consul = $stmt->get_result();
            $num_filas = $consul->num_rows;

            if ($num_filas != 1) {
                $message = "Usuario o contraseña incorrectos";
                $messageType = 'error';
            } else {
                $result = $consul->fetch_assoc();
                $attempts = $result['attempts'];
                $active = $result['active'];
                $hashed_password_db = $result['password']; // Obtenemos la contraseña hasheada de la BD

                // 2. Verificar intentos máximos (ahora > 3 en lugar de >= 3)
                if ($attempts > 3) {
                    $message = "Sobrepasó el número de intentos permitidos, por favor actualice su contraseña";
                    $messageType = 'error';
                } else if ($active != 1){
                    $message = "El usuario se encuentra inhabilitado";
                    $messageType = 'error';
                } else {
                    // 3. Verificar contraseña hasheada
                    if (password_verify($password, $hashed_password_db)) {
                        // 4. Verificar rol del usuario
                        if ($result['rol'] == 1) {
                            $_SESSION['user'] = $result['name'];
                            $_SESSION['id'] = $result['user_id'];
                            $_SESSION['email'] = $result['gmail'];
                            $_SESSION['seccion'] = true;
                            $_SESSION['rol'] = 1;
                            $reset_value = 0;
                            $update_stmt = $conn->prepare("UPDATE user SET attempts = ? WHERE user_id = ?");
                            $update_stmt->bind_param("ii", $reset_value, $_SESSION['id']);
                            $update_stmt->execute();
                            $message = "Bienvenido administrador";
                            $messageType = 'success';
                            echo "<script>
                                setTimeout(function() {
                                    window.location.href = 'dashboard.php';
                                }, 2000);
                            </script>";
                        } else {
                            $_SESSION['user'] = $result['name'];
                            $_SESSION['id'] = $result['user_id'];
                            $_SESSION['email'] = $result['gmail'];
                            $_SESSION['seccion'] = true;
                            $_SESSION['rol'] = 2;
                            $reset_value = 0;
                            $update_stmt = $conn->prepare("UPDATE user SET attempts = ? WHERE user_id = ?");
                            $update_stmt->bind_param("ii", $reset_value, $_SESSION['id']);
                            $update_stmt->execute();
                            $message = "Bienvenido usuario";
                            $messageType = 'success';
                            echo "<script>
                                setTimeout(function() {
                                    window.location.href = 'user.php';
                                }, 2000);
                            </script>";
                        }
                    } else {
                        // 5. Incrementar intentos fallidos
                        $new_attempts = $attempts + 1;
                        $update_stmt = $conn->prepare("UPDATE user SET attempts = ? WHERE user_id = ?");
                        $update_stmt->bind_param("ii", $new_attempts, $result['user_id']);
                        $admin = $result['rol'];

                        if ($admin == 1){
                            $message = "Se ha equivocado de contraseña vuelva a intentar";
                            $messageType = 'error';
                        } else{
                            if ($update_stmt->execute()) {
                                if ($new_attempts > 3) {
                                    $message = "Ha excedido el límite de intentos. Por favor actualice su contraseña";
                                    $messageType = 'error';
                                } else {
                                    $remaining_attempts = 3 - $attempts; // Muestra intentos restantes antes del nuevo intento
                                    $message = "Clave incorrecta. Intentos restantes: " . $remaining_attempts;
                                    $messageType = 'warning';
                                }
                            } else {
                                $message = "Error al actualizar los intentos";
                                $messageType = 'error';
                            }
                        }
                    }
                }
            }
            $stmt->close(); // Cerramos la consulta preparada
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>BuildSafe System</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/x-icon" href="src/brickwall.png">
        <link href="css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/style.css">
        <link rel="stylesheet" type="text/css" href="CSS/captcha.css">
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <script src="script.js"></script> 
        <!-- SweetAlert2 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body x-data="main" class="relative overflow-x-hidden font-nunito text-sm font-normal antialiased" :class="[ $store.app.sidebar ? 'toggle-sidebar' : '', $store.app.theme === 'dark' || $store.app.isDarkMode ?  'dark' : '', $store.app.menu, $store.app.layout,$store.app.rtlClass]">
        <!-- screen loader -->
        <div class="screen_loader animate__animated fixed inset-0 z-[60] grid place-content-center bg-[#fafafa] dark:bg-[#060818]">
            <svg width="64" height="64" viewbox="0 0 135 135" xmlns="http://www.w3.org/2000/svg" fill="#4361ee">
                <path d="M67.447 58c5.523 0 10-4.477 10-10s-4.477-10-10-10-10 4.477-10 10 4.477 10 10 10zm9.448 9.447c0 5.523 4.477 10 10 10 5.522 0 10-4.477 10-10s-4.478-10-10-10c-5.523 0-10 4.477-10 10zm-9.448 9.448c-5.523 0-10 4.477-10 10 0 5.522 4.477 10 10 10s10-4.478 10-10c0-5.523-4.477-10-10-10zM58 67.447c0-5.523-4.477-10-10-10s-10 4.477-10 10 4.477 10 10 10 10-4.477 10-10z">
                    <animatetransform attributename="transform" type="rotate" from="0 67 67" to="-360 67 67" dur="2.5s" repeatcount="indefinite"></animatetransform>
                </path>
                <path d="M28.19 40.31c6.627 0 12-5.374 12-12 0-6.628-5.373-12-12-12-6.628 0-12 5.372-12 12 0 6.626 5.372 12 12 12zm30.72-19.825c4.686 4.687 12.284 4.687 16.97 0 4.686-4.686 4.686-12.284 0-16.97-4.686-4.687-12.284-4.687-16.97 0-4.687 4.686-4.687 12.284 0 16.97zm35.74 7.705c0 6.627 5.37 12 12 12 6.626 0 12-5.373 12-12 0-6.628-5.374-12-12-12-6.63 0-12 5.372-12 12zm19.822 30.72c-4.686 4.686-4.686 12.284 0 16.97 4.687 4.686 12.285 4.686 16.97 0 4.687-4.686 4.687-12.284 0-16.97-4.685-4.687-12.283-4.687-16.97 0zm-7.704 35.74c-6.627 0-12 5.37-12 12 0 6.626 5.373 12 12 12s12-5.374 12-12c0-6.63-5.373-12-12-12zm-30.72 19.822c-4.686-4.686-12.284-4.686-16.97 0-4.686 4.687-4.686 12.285 0 16.97 4.686 4.687 12.284 4.687 16.97 0 4.687-4.685 4.687-12.283 0-16.97zm-35.74-7.704c0-6.627-5.372-12-12-12-6.626 0-12 5.373-12 12s5.374 12 12 12c6.628 0 12-5.373 12-12zm-19.823-30.72c4.687-4.686 4.687-12.284 0-16.97-4.686-4.686-12.284-4.686-16.97 0-4.687 4.686-4.687 12.284 0 16.97 4.686 4.687 12.284 4.687 16.97 0z">
                    <animatetransform attributename="transform" type="rotate" from="0 67 67" to="360 67 67" dur="8s" repeatcount="indefinite"></animatetransform>
                </path>
            </svg>
        </div>
        <!-- scroll to top button -->
        <div class="fixed bottom-6 right-6 z-50" x-data="scrollToTop">
            <template x-if="showTopButton">
                <button type="button" class="btn btn-outline-primary animate-pulse rounded-full p-2" @click="goToTop">
                    <svg width="24" height="24" class="h-4 w-4" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd" d="M12 20.75C12.4142 20.75 12.75 20.4142 12.75 20L12.75 10.75L11.25 10.75L11.25 20C11.25 20.4142 11.5858 20.75 12 20.75Z" fill="currentColor"></path>
                        <path d="M6.00002 10.75C5.69667 10.75 5.4232 10.5673 5.30711 10.287C5.19103 10.0068 5.25519 9.68417 5.46969 9.46967L11.4697 3.46967C11.6103 3.32902 11.8011 3.25 12 3.25C12.1989 3.25 12.3897 3.32902 12.5304 3.46967L18.5304 9.46967C18.7449 9.68417 18.809 10.0068 18.6929 10.287C18.5768 10.5673 18.3034 10.75 18 10.75L6.00002 10.75Z" fill="currentColor"></path>
                    </svg>
                </button>
            </template>
        </div>
        <div class="main-container min-h-screen text-black dark:text-white-dark">
            <div x-data="auth">
                <div class="absolute inset-0">
                    <img src="assets/images/auth/bg-gradient.png" alt="image" class="h-full w-full object-cover">
                </div>
                <div class="relative flex min-h-screen items-center justify-center bg-[url(../images/auth/map.png)] bg-cover bg-center bg-no-repeat px-6 py-10 dark:bg-[#060818] sm:px-16">
                    <img src="assets/images/auth/coming-soon-object1.png" alt="image" class="absolute left-0 top-1/2 h-full max-h-[893px] -translate-y-1/2">
                    <img src="assets/images/auth/coming-soon-object2.png" alt="image" class="absolute left-24 top-0 h-40 md:left-[30%]">
                    <img src="assets/images/auth/coming-soon-object3.png" alt="image" class="absolute right-0 top-0 h-[300px]">
                    <img src="assets/images/auth/polygon-object.svg" alt="image" class="absolute bottom-0 end-[28%]">
                    <div class="relative w-full max-w-[870px] rounded-md bg-[linear-gradient(45deg,#fff9f9_0%,rgba(255,255,255,0)_25%,rgba(255,255,255,0)_75%,_#fff9f9_100%)] p-2 dark:bg-[linear-gradient(52.22deg,#0E1726_0%,rgba(14,23,38,0)_18.66%,rgba(14,23,38,0)_51.04%,rgba(14,23,38,0)_80.07%,#0E1726_100%)]">
                        <div class="relative flex flex-col justify-center rounded-md bg-white/60 backdrop-blur-lg dark:bg-black/50 px-6 lg:min-h-[758px] py-20">
                            <div class="mx-auto w-full max-w-[440px]">
                                <div class="mb-10">
                                    <h1 class="text-3xl font-extrabold uppercase !leading-snug text-primary md:text-4xl">Bienvenido</h1>
                                    <p class="text-base font-bold leading-normal text-white-dark">Por favor coloque sus datos</p>
                                </div>
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="space-y-5 dark:text-white">
                                    <div>
                                        <label for="Email">Email</label>
                                        <div class="relative text-white-dark">
                                            <input type="email" id="email" name="email" required placeholder="Ingrese Email" class="form-input ps-10 placeholder:text-white-dark">
                                            <span class="absolute start-4 top-1/2 -translate-y-1/2">
                                                <svg width="18" height="18" viewbox="0 0 18 18" fill="none">
                                                    <path opacity="0.5" d="M10.65 2.25H7.35C4.23873 2.25 2.6831 2.25 1.71655 3.23851C0.75 4.22703 0.75 5.81802 0.75 9C0.75 12.182 0.75 13.773 1.71655 14.7615C2.6831 15.75 4.23873 15.75 7.35 15.75H10.65C13.7613 15.75 15.3169 15.75 16.2835 14.7615C17.25 13.773 17.25 12.182 17.25 9C17.25 5.81802 17.25 4.22703 16.2835 3.23851C15.3169 2.25 13.7613 2.25 10.65 2.25Z" fill="currentColor"></path>
                                                    <path d="M14.3465 6.02574C14.609 5.80698 14.6445 5.41681 14.4257 5.15429C14.207 4.89177 13.8168 4.8563 13.5543 5.07507L11.7732 6.55931C11.0035 7.20072 10.4691 7.6446 10.018 7.93476C9.58125 8.21564 9.28509 8.30993 9.00041 8.30993C8.71572 8.30993 8.41956 8.21564 7.98284 7.93476C7.53168 7.6446 6.9973 7.20072 6.22761 6.55931L4.44652 5.07507C4.184 4.8563 3.79384 4.89177 3.57507 5.15429C3.3563 5.41681 3.39177 5.80698 3.65429 6.02574L5.4664 7.53583C6.19764 8.14522 6.79033 8.63914 7.31343 8.97558C7.85834 9.32604 8.38902 9.54743 9.00041 9.54743C9.6118 9.54743 10.1425 9.32604 10.6874 8.97558C11.2105 8.63914 11.8032 8.14522 12.5344 7.53582L14.3465 6.02574Z" fill="currentColor"></path>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="Password">Contraseña</label>
                                        <div class="relative text-white-dark">
                                            <input type="password" id="password" name="password" required minlength="6" placeholder="Ingrese Contraseña" class="form-input ps-10 placeholder:text-white-dark">
                                            <span class="absolute start-4 top-1/2 -translate-y-1/2">
                                                <svg width="18" height="18" viewbox="0 0 18 18" fill="none">
                                                    <path opacity="0.5" d="M1.5 12C1.5 9.87868 1.5 8.81802 2.15901 8.15901C2.81802 7.5 3.87868 7.5 6 7.5H12C14.1213 7.5 15.182 7.5 15.841 8.15901C16.5 8.81802 16.5 9.87868 16.5 12C16.5 14.1213 16.5 15.182 15.841 15.841C15.182 16.5 14.1213 16.5 12 16.5H6C3.87868 16.5 2.81802 16.5 2.15901 15.841C1.5 15.182 1.5 14.1213 1.5 12Z" fill="currentColor"></path>
                                                    <path d="M6 12.75C6.41421 12.75 6.75 12.4142 6.75 12C6.75 11.5858 6.41421 11.25 6 11.25C5.58579 11.25 5.25 11.5858 5.25 12C5.25 12.4142 5.58579 12.75 6 12.75Z" fill="currentColor"></path>
                                                    <path d="M9 12.75C9.41421 12.75 9.75 12.4142 9.75 12C9.75 11.5858 9.41421 11.25 9 11.25C8.58579 11.25 8.25 11.5858 8.25 12C8.25 12.4142 8.58579 12.75 9 12.75Z" fill="currentColor"></path>
                                                    <path d="M12.75 12C12.75 12.4142 12.4142 12.75 12 12.75C11.5858 12.75 11.25 12.4142 11.25 12C11.25 11.5858 11.5858 11.25 12 11.25C12.4142 11.25 12.75 11.5858 12.75 12Z" fill="currentColor"></path>
                                                    <path d="M5.0625 6C5.0625 3.82538 6.82538 2.0625 9 2.0625C11.1746 2.0625 12.9375 3.82538 12.9375 6V7.50268C13.363 7.50665 13.7351 7.51651 14.0625 7.54096V6C14.0625 3.20406 11.7959 0.9375 9 0.9375C6.20406 0.9375 3.9375 3.20406 3.9375 6V7.54096C4.26488 7.51651 4.63698 7.50665 5.0625 7.50268V6Z" fill="currentColor"></path>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="g-recaptcha" data-sitekey="6LdVkrIrAAAAAIIptDJMRKTPhhtG5cueEVnQV5fR" ></div>
                                    <button type="submit" name="submit" value="Ingresar" class="btn btn-gradient !mt-6 w-full border-0 uppercase shadow-[0_10px_20px_-10px_rgba(67,97,238,0.44)]">
                                        Iniciar sesión
                                    </button>
                                </form>
                                <div class="relative my-7 text-center md:mb-9">
                                    <span class="absolute inset-x-0 top-1/2 h-px w-full -translate-y-1/2 bg-white-light dark:bg-white-dark"></span>
                                </div>
                                <div class="text-center dark:text-white">
                                    ¿Olvidó su contraseña?
                                    <a href="recuperar.php" class="uppercase text-primary underline transition hover:text-black dark:hover:text-white">Recuperar</a>

                                                                    <!-- Licencia Creative Commons BY-NC-SA 4.0 -->
                                <div class="mt-8 text-center text-xs text-gray-500 dark:text-gray-400 border-t pt-4">
                                    <p>
                                        Este contenido está bajo licencia 
                                        <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/" 
                                           target="_blank" 
                                           rel="license" 
                                           class="text-blue-500 hover:underline">
                                            Creative Commons Atribución-NoComercial-CompartirIgual 4.0 Internacional
                                        </a>.
                                    </p>
                                    <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/" 
                                       target="_blank" 
                                       rel="license">
                                        <img src="https://mirrors.creativecommons.org/presskit/buttons/88x31/png/by-nc-sa.png" 
                                             alt="Licencia Creative Commons BY-NC-SA 4.0" 
                                             class="inline-block mt-2" 
                                             style="width: 88px; height: 31px;">
                                    </a>
                                    <p class="mt-2">
                                        © 2025 CONFALVI C.A. Todos los derechos reservados.
                                    </p>
                                </div>
                                <!-- Fin de la licencia -->

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            // Mostrar SweetAlert2 si hay un mensaje y fue definido (no solo vacío)
            <?php if(isset($message) && $message !== ''): ?>
                Swal.fire({
                    title: '<?php echo ($messageType == "success") ? "¡Éxito!" : (($messageType == "error") ? "¡Error!" : (($messageType == "warning") ? "¡Advertencia!" : "Información")); ?>',
                    text: '<?php echo addslashes($message); ?>',
                    icon: '<?php echo $messageType; ?>',
                    confirmButtonText: 'Aceptar'
                }).then((result) => {
                    <?php if($messageType == "success"): ?>
                        // Si es un mensaje de éxito y hay redirección programada, no hacer nada
                        // La redirección se maneja por el timeout en PHP
                    <?php endif; ?>
                });
            <?php endif; ?>
        </script>
        
        <script src="assets/js/alpine-collaspe.min.js"></script>
        <script src="assets/js/alpine-persist.min.js"></script>
        <script defer="" src="assets/js/alpine-ui.min.js"></script>
        <script defer="" src="assets/js/alpine-focus.min.js"></script>
        <script defer="" src="assets/js/alpine.min.js"></script>
        <script src="assets/js/custom.js"></script>
        <script>
            // main section
            document.addEventListener('alpine:init', () => {
                Alpine.data('scrollToTop', () => ({
                    showTopButton: false,
                    init() {
                        window.onscroll = () => {
                            this.scrollFunction();
                        };
                    },
                    scrollFunction() {
                        if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
                            this.showTopButton = true;
                        } else {
                            this.showTopButton = false;
                        }
                    },
                    goToTop() {
                        document.body.scrollTop = 0;
                        document.documentElement.scrollTop = 0;
                    },
                }));
            });
        </script>
    </body>
</html>
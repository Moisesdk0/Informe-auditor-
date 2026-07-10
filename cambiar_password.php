<?php
session_start();
include("conexion.php");

// 1. Validar parámetros GET
if(empty($_GET['token']) || empty($_GET['email'])) {
    $_SESSION['error'] = "Enlace incompleto";
    header("Location: recuperar.php");
    exit();
}

$token = $_GET['token'];
$email = urldecode($_GET['email']);

// 2. Verificar token válido (sin invalidarlo aún)
$stmt = $conn->prepare("SELECT user_id, security_question FROM user WHERE gmail = ? AND token_recuperacion = ? AND token_expiracion > NOW()");
$stmt->bind_param("ss", $email, $token);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$quest = $row['security_question'];


if($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // INVALIDAR EL TOKEN INMEDIATAMENTE
    $invalidate_stmt = $conn->prepare("UPDATE user SET token_recuperacion = NULL, token_expiracion = NULL WHERE user_id = ?");
    $invalidate_stmt->bind_param("i", $user['user_id']);
    $invalidate_stmt->execute();
    


    // Guardar en sesión (opcional, para verificación posterior)
    $_SESSION['token_verification'] = [
        'user_id' => $user['user_id'],
        'email' => $email,
        'token' => $token, // Cambiar de 'original_token' a 'token'
        'valid_until' => time() + 3600 // Añadir 1 hora de validez


    ];
    

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Dashboard Template</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/x-icon" href="favicon.png">
        <link href="css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/style.css">
        <script src="script.js"></script> 
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
                                    <h1 class="text-3xl font-extrabold uppercase !leading-snug text-primary md:text-4xl">CAMBIO DE CONTRASEÑA</h1>
                                    <p class="text-base font-bold leading-normal text-white-dark">Por favor coloque los datos solicitado</p>
                                </div>
                                <form method="POST" action="actualizar_password.php" class="space-y-5 dark:text-white">
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

                                <div>
                                    <label for="nueva_password">Ingrese la nueva contraseña</label>
                                    <div class="relative text-white-dark">
                                        <input 
                                            type="password" 
                                            id="nueva_password"
                                            name="nueva_password" 
                                            required 
                                            minlength="8"
                                            pattern="^(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}$"
                                            placeholder="Mínimo 16 caracteres, 1 mayúscula y 1 símbolo" 
                                            class="form-input ps-10 placeholder:text-white-dark pr-10"
                                            oninput="validatePassword()"
                                        >
                                        <span class="absolute start-4 top-1/2 -translate-y-1/2">
                                            <svg width="18" height="18" viewbox="0 0 18 18" fill="none">
                                                <path opacity="0.5" d="M1.5 12C1.5 9.87868 1.5 8.81802 2.15901 8.15901C2.81802 7.5 3.87868 7.5 6 7.5H12C14.1213 7.5 15.182 7.5 15.841 8.15901C16.5 8.81802 16.5 9.87868 16.5 12C16.5 14.1213 16.5 15.182 15.841 15.841C15.182 16.5 14.1213 16.5 12 16.5H6C3.87868 16.5 2.81802 16.5 2.15901 15.841C1.5 15.182 1.5 14.1213 1.5 12Z" fill="currentColor"></path>
                                                <path d="M6 12.75C6.41421 12.75 6.75 12.4142 6.75 12C6.75 11.5858 6.41421 11.25 6 11.25C5.58579 11.25 5.25 11.5858 5.25 12C5.25 12.4142 5.58579 12.75 6 12.75Z" fill="currentColor"></path>
                                                <path d="M9 12.75C9.41421 12.75 9.75 12.4142 9.75 12C9.75 11.5858 9.41421 11.25 9 11.25C8.58579 11.25 8.25 11.5858 8.25 12C8.25 12.4142 8.58579 12.75 9 12.75Z" fill="currentColor"></path>
                                                <path d="M12.75 12C12.75 12.4142 12.4142 12.75 12 12.75C11.5858 12.75 11.25 12.4142 11.25 12C11.25 11.5858 11.5858 11.25 12 11.25C12.4142 11.25 12.75 11.5858 12.75 12Z" fill="currentColor"></path>
                                                <path d="M5.0625 6C5.0625 3.82538 6.82538 2.0625 9 2.0625C11.1746 2.0625 12.9375 3.82538 12.9375 6V7.50268C13.363 7.50665 13.7351 7.51651 14.0625 7.54096V6C14.0625 3.20406 11.7959 0.9375 9 0.9375C6.20406 0.9375 3.9375 3.20406 3.9375 6V7.54096C4.26488 7.51651 4.63698 7.50665 5.0625 7.50268V6Z" fill="currentColor"></path>
                                            </svg>
                                        </span>
                                        <span 
                                            id="togglePassword1" 
                                            class="absolute end-4 top-1/2 -translate-y-1/2 cursor-pointer text-sm"
                                            onclick="togglePasswordVisibility('nueva_password', this)"
                                        >
                                            ver
                                        </span>
                                    </div>
                                    <div id="passwordError" class="text-red-500 text-sm mt-1"></div>
                                </div>

                                <div>
                                    <label for="confirmar_password">Confirmar Contraseña</label>
                                    <div class="relative text-white-dark">
                                        <input 
                                            type="password" 
                                            id="confirmar_password"
                                            name="confirmar_password" 
                                            required 
                                            minlength="8"
                                            placeholder="Repita la contraseña" 
                                            class="form-input ps-10 placeholder:text-white-dark pr-10"
                                            oninput="validatePasswordConfirmation()"
                                        >
                                        <span class="absolute start-4 top-1/2 -translate-y-1/2">
                                            <svg width="18" height="18" viewbox="0 0 18 18" fill="none">
                                                <path opacity="0.5" d="M1.5 12C1.5 9.87868 1.5 8.81802 2.15901 8.15901C2.81802 7.5 3.87868 7.5 6 7.5H12C14.1213 7.5 15.182 7.5 15.841 8.15901C16.5 8.81802 16.5 9.87868 16.5 12C16.5 14.1213 16.5 15.182 15.841 15.841C15.182 16.5 14.1213 16.5 12 16.5H6C3.87868 16.5 2.81802 16.5 2.15901 15.841C1.5 15.182 1.5 14.1213 1.5 12Z" fill="currentColor"></path>
                                                <path d="M6 12.75C6.41421 12.75 6.75 12.4142 6.75 12C6.75 11.5858 6.41421 11.25 6 11.25C5.58579 11.25 5.25 11.5858 5.25 12C5.25 12.4142 5.58579 12.75 6 12.75Z" fill="currentColor"></path>
                                                <path d="M9 12.75C9.41421 12.75 9.75 12.4142 9.75 12C9.75 11.5858 9.41421 11.25 9 11.25C8.58579 11.25 8.25 11.5858 8.25 12C8.25 12.4142 8.58579 12.75 9 12.75Z" fill="currentColor"></path>
                                                <path d="M12.75 12C12.75 12.4142 12.4142 12.75 12 12.75C11.5858 12.75 11.25 12.4142 11.25 12C11.25 11.5858 11.5858 11.25 12 11.25C12.4142 11.25 12.75 11.5858 12.75 12Z" fill="currentColor"></path>
                                                <path d="M5.0625 6C5.0625 3.82538 6.82538 2.0625 9 2.0625C11.1746 2.0625 12.9375 3.82538 12.9375 6V7.50268C13.363 7.50665 13.7351 7.51651 14.0625 7.54096V6C14.0625 3.20406 11.7959 0.9375 9 0.9375C6.20406 0.9375 3.9375 3.20406 3.9375 6V7.54096C4.26488 7.51651 4.63698 7.50665 5.0625 7.50268V6Z" fill="currentColor"></path>
                                            </svg>
                                        </span>
                                        <span 
                                            id="togglePassword2" 
                                            class="absolute end-4 top-1/2 -translate-y-1/2 cursor-pointer text-sm"
                                            onclick="togglePasswordVisibility('confirmar_password', this)"
                                        >
                                            ver
                                        </span>
                                    </div>
                                    <div id="confirmPasswordError" class="text-red-500 text-sm mt-1"></div>
                                </div>

                                    <div>
                                        <label for="Password">Pregunta de seguridad</label>
                                        <div class="relative text-white-dark">
                                        <input type="text" name="answer" placeholder="<?php echo $quest?>" class="form-input ps-10 placeholder:text-white-dark">
                                            <span class="absolute start-4 top-1/2 -translate-y-1/2">
                                            <svg width="18" height="18" viewbox="0 0 18 18" fill="none">
                                                    <circle cx="9" cy="4.5" r="3" fill="#888EA8"></circle>
                                                    <path opacity="0.5" d="M15 13.125C15 14.989 15 16.5 9 16.5C3 16.5 3 14.989 3 13.125C3 11.261 5.68629 9.75 9 9.75C12.3137 9.75 15 11.261 15 13.125Z" fill="#888EA8"></path>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <button type="submit" name="submit" value="Ingresar" class="btn btn-gradient !mt-6 w-full border-0 uppercase shadow-[0_10px_20px_-10px_rgba(67,97,238,0.44)]">
                                        VERIFICAR
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <script src="assets/js/alpine-collaspe.min.js"></script>
        <script src="assets/js/alpine-persist.min.js"></script>
        <script defer="" src="assets/js/alpine-ui.min.js"></script>
        <script defer="" src="assets/js/alpine-focus.min.js"></script>
        <script defer="" src="assets/js/alpine.min.js"></script>

        <script src="assets/js/custom.js"></script>

    <script>
// Función para mostrar/ocultar contraseña
function togglePasswordVisibility(inputId, toggleElement) {
    const input = document.getElementById(inputId);
    
    if (input.type === 'password') {
        input.type = 'text';
        toggleElement.textContent = 'ocultar';
    } else {
        input.type = 'password';
        toggleElement.textContent = 'ver';
    }
}

// Validación de contraseña principal
function validatePassword() {
    const password = document.getElementById("nueva_password").value;
    const errorElement = document.getElementById("passwordError");
    
    const hasUpperCase = /[A-Z]/.test(password);
    const hasSymbol = /[!@#$%^&*]/.test(password);
    const isLongEnough = password.length >= 16;
    
    if (password.length > 0) {
        let errors = [];
        if (!isLongEnough) errors.push("16+ caracteres");
        if (!hasUpperCase) errors.push("1 mayúscula");
        if (!hasSymbol) errors.push("1 símbolo");
        
        if (errors.length > 0) {
            errorElement.textContent = "Requisitos faltantes: " + errors.join(", ");
        } else {
            errorElement.textContent = "✓ Contraseña válida";
            errorElement.className = "text-green-500 text-sm mt-1";
            // Validar confirmación si ya tiene valor
            if(document.getElementById("confirmar_password").value.length > 0) {
                validatePasswordConfirmation();
            }
        }
    } else {
        errorElement.textContent = "";
    }
}

// Validación de coincidencia de contraseñas
function validatePasswordConfirmation() {
    const password = document.getElementById("nueva_password").value;
    const confirmPassword = document.getElementById("confirmar_password").value;
    const errorElement = document.getElementById("confirmPasswordError");
    
    if (confirmPassword.length > 0) {
        if (password !== confirmPassword) {
            errorElement.textContent = "❌ Las contraseñas no coinciden";
            errorElement.className = "text-red-500 text-sm mt-1";
        } else if (password.length > 0 && document.getElementById("passwordError").textContent === "✓ Contraseña válida") {
            errorElement.textContent = "✓ Las contraseñas coinciden";
            errorElement.className = "text-green-500 text-sm mt-1";
        }
    } else {
        errorElement.textContent = "";
    }
}

// Validación al enviar el formulario
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById("nueva_password").value;
    const confirmPassword = document.getElementById("confirmar_password").value;
    
    // Validar requisitos de contraseña
    const regex = /^(?=.*[A-Z])(?=.*[!@#$%^&*]).{16,}$/;
    if (!regex.test(password)) {
        e.preventDefault();
        alert("La contraseña debe tener:\n- Mínimo 16 caracteres\n- Al menos 1 mayúscula\n- Al menos 1 símbolo (!@#$%^&*)");
        return;
    }
    
    // Validar coincidencia
    if (password !== confirmPassword) {
        e.preventDefault();
        alert("Las contraseñas no coinciden");
    }
});
</script>


    </body>
    </html>
    <?php
} else {
    $_SESSION['error'] = "Enlace inválido o expirado";
    header("Location: recuperar.php");
    exit();
}
?>
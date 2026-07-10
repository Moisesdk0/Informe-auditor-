<?php
include("head.php");


?>

<link rel="stylesheet" type="text/css" href="CSS/dashboard_style.css">
  <!-- Íconos con Lucide (ligero y moderno) -->
  <script src="https://unpkg.com/lucide@latest"></script>
<body>
  <div class="container">
    <h1 class="welcome-title">BIENVENIDO</h1>

    <!-- Footer -->
    
      © <span id="footer-year">2025</span>. CONFALVI C.A.
    </footer>
  </div>

  <script>
    // Año dinámico
    document.getElementById('footer-year').textContent = new Date().getFullYear();

    // Inicializar íconos de Lucide
    lucide.createIcons();
  </script>
</body>
</html>
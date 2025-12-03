<?php
// Ubicación: app/controllers/DashboardController.php

class DashboardController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function mostrarDashboard() {
        // Definir variables para el layout
        $title = 'Dashboard - D\'alicias';
        $activeSection = 'dashboard';
        
        // Capturar el contenido del dashboard
        ob_start();
        ?>
        <h1>Panel de Administración - D'alicias</h1>
        <p>Selecciona una opción del menú lateral para comenzar.</p>
        <div class="quick-stats">
            <div class="stat-card">
                <h3>Pedidos Hoy</h3>
                <p>0</p>
            </div>
            <div class="stat-card">
                <h3>Clientes</h3>
                <p>3</p>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        
        // Incluir el layout base
        include __DIR__ . '/../views/layout/layout.php';
    }
}
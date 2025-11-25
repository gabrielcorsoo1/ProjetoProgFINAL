<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<header class="header">
    <div class="header-content">
        <a href="<?php 
            $tipo = $_SESSION['usuario_tipo'];
            if ($tipo == 'admin') {
                echo 'admin/dashboard.php';
            } else if ($tipo == 'barbeiro') {
                echo 'barbeiro/dashboard.php';
            } else {
                echo 'cliente/dashboard.php';
            }
        ?>" class="logo">Barbearia Moderna</a>
        <nav class="nav-links">
            <span>Olá, <?php echo $_SESSION['usuario_nome']; ?>!</span>
            <?php if ($_SESSION['usuario_tipo'] != 'admin') { ?>
                <a href="../admin/dashboard.php">Admin</a>
            <?php } ?>
            <a href="../logout.php">Sair</a>
        </nav>
    </div>
</header>


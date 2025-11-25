<?php
spl_autoload_register(function ($classe) {
    $arquivo = __DIR__ . '/' . $classe . '.php';
    if (file_exists($arquivo)) {
        require_once $arquivo;
    }
});

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/IAutenticavel.php';
require_once __DIR__ . '/IGerenciavel.php';
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Cliente.php';
require_once __DIR__ . '/Barbeiro.php';
require_once __DIR__ . '/Admin.php';
require_once __DIR__ . '/Servico.php';
require_once __DIR__ . '/Agendamento.php';
require_once __DIR__ . '/Mensagem.php';
require_once __DIR__ . '/SistemaBarbearia.php';
?>


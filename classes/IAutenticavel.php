<?php
interface IAutenticavel {
    public function autenticar($email, $senha);
    public function verificarSessao();
}
?>


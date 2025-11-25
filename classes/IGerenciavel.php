<?php
interface IGerenciavel {
    public function criar($dados);
    public function listar();
    public function buscarPorId($id);
    public function atualizar($id, $dados);
    public function deletar($id);
}
?>


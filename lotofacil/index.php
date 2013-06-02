<?php
require_once 'lotofacil.class.php';
$lf = new Lotofacil();
$lf->conferir_jogos();
$res = $lf->extrai_resultados(911); //número do sorteio (concurso).
if ($res) var_dump($res);
if ($lf->tem_erros()){
    var_dump($lf->pega_erros());
}
unset($lf);
?>
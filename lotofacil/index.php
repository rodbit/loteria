<?php

require_once 'Loteria.php';

$l = new Loteria();
$l->loteria('lotofacil');
$l->executa(918); // número do concurso

if (!$l->temErros()) {
    var_dump($l->numerosSorteados());
} else {
    $erros = $l->pegaErros();
    var_dump($erros);
}


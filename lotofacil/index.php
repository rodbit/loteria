<?php
require_once 'lotofacil.class.php';
$lf = new Lotofacil();
$lf->init();
$lf->conferir_jogos();
$res = $lf->extrai_resultados(911);
if ($res) var_dump($res);
if ($lf->tem_erros()){
    var_dump($lf->pega_erros());
}
exit;
?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8" />
        <title> Lotofácil </title>
        <link rel="stylesheet" type="text/css" href="estilo.css">
    </head>
    <body>
       
    </body>
</html> 
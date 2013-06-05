Lotofacil
=========


Classe que faz download do arquivo de resultados da Lotofácil, no site da Caixa, lê e processa seu conteúdo, confere jogos pré-cadastrados, gera estatísticas, entre outros recursos.

Exemplo de uso (testes):

$lf = new Lotofacil();
$lf->conferir_jogos();
$res = $lf->extrai_resultados(911); //número do sorteio (concurso).
if ($res) var_dump($res);



---
Tá quase pronta! rs

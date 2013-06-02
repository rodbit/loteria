LOTOFACIL
=========

Classe que faz download do site da CEF e lê arquivo de resultados da Lotofácil, confere jogos pré-cadastrados, gera estatísticas, etc.

Exemplo de uso (testes):

$lf = new Lotofacil();
$lf->conferir_jogos();
$res = $lf->extrai_resultados(911); //número do sorteio (concurso).
if ($res) var_dump($res);

---
Tá quase pronta! rs

<?php

/**
 * Faz download do arquivo de resultados da Lotofácil do site da Caixa,
 * lê e processa as informações, confere jogos, exibe estatísticas, etc.
 *
 * @author Marcos Aurelio
 * @version 1.2.0
 * 
 */
class Loteria {
    private $tipo; // tipo escolhido para manipulação: megasena, lotofacil, ... 
    private $tipos; // tipos de loterias existentes (lotofacil, megasena)
    private $erro; // mensagens de erro
    private $jogos; // jogos do usuário
    private $vetor; // vetor com resultado importado da Caixa
    private $path_resultados; // diretorio onde ficam os arquivos .json com resultados
    private $ultimo_concurso; // dados do último concurso
    private $concurso; // concurso solicitado
    private $resultado; // resultado do concurso solicitado
    private $numeros; // numeros sorteados
        
    
    /**
     * Construtor
     */
    public function __construct() {
        if (!defined('DS')) { 
            define('DS', DIRECTORY_SEPARATOR); 
        }
        $this->tipos = [
            'lotofacil'=>[
                'nome'=>'LOTOFÁCIL',
                'url'=>'http://www1.caixa.gov.br/loterias/loterias/lotofacil/lotofacil_pesquisa_new.asp?submeteu=sim&opcao=concurso&txtConcurso=',
                'url_zip'=>'http://www1.caixa.gov.br/loterias/_arquivos/loterias/D_lotfac.zip'
            ]
        ];
        $this->vetor = [];
        $this->erro = [];
        $this->jogos = [];
        $this->tipo = '';
        $this->ultimo_concurso = [];
        $this->concurso = '';
        $this->resultado = [];
        $this->numeros = [];
        $this->path_resultados = $path = $_SERVER['DOCUMENT_ROOT'] . DS . 'resultados' . DS;
    }

    
    /**
     * Seta ou Retorna o jogo desejado
     * 
     * @param string $l Tipo: lotofacil, megasena
     * @return boolean
     */
    public function loteria($l) {
        if (!$l) {
            return $this->tipo;
        }
        $l = strtolower($l);
        if (!array_key_exists($l, $this->tipos)) {
            $this->erro[] = 'Loteria inv&aacute;lida';
            return false;
        }
        $this->tipo = $l;
        $this->path_resultados .= $l . DS;
    }
    
    /**
     * Retorna resultado
     * @return array
     */
    public function resultado() {
        return $this->resultado;
    }
    
    
    /**
     * Retorna resultado do concurso específico
     * 
     * @param int $concurso Numero do concurso
     * @return array Resultado do concurso
     */
    public function executa($concurso='') {
        if (!strlen($this->tipo)) {
            $this->erro = 'Loteria alvo não informada. Ex.: Lotofacil, Megasena, etc.';
            return [];
        }
        $this->concurso = $concurso;
        $this->ultimo_concurso = $this->ultimoConcurso();
        $this->resultado = $this->dadosConcurso();
        // extrai os números sorteados
        for ($x=3; $x < 18; $x++) {
            $this->numeros[] = $this->resultado[$x];
        }
    }
    
    
    /**
     * Exibe ou atualiza dados do arquivo "ultimo_concurso_[loteria].json
     * 
     * @param array $dados Novos dados a serem atualizados
     * @return array Dados sobre o último concurso registrado
     */
    private function ultimoConcurso($dados = null) {
        $ucf = $this->path_resultados.'ultimo_concurso_' . $this->tipo . '.json';
        if (!$dados) {
            $f = fopen($ucf, 'rb');
            $res = fread($f, filesize($ucf));
            fclose($f);
            return (array) json_decode($res);
        } else {
            unlink($ucf);
            $f = fopen($ucf, 'wr');
            fwrite($f, json_encode($dados));
            fclose($f);
            return $dados;
        }
    }
    
    
    /**
     * Exibe dados do concurso solicitado
     * 
     * @return array Dados do concurso
     */
    private function dadosConcurso() {
        $nom_arquivo = $this->tipo. '_' . $this->concurso . '.json';
        $arq_result = $this->path_resultados . $nom_arquivo;
        if (!file_exists($arq_result)) {
            return $this->baixaConcurso();
        } else {
            $f = fopen($arq_result, 'rb');
            $res = fread($f, filesize($arq_result));
            fclose($f);
            return (array) json_decode($res);
        }
    }

    
    /**
     * Baixa dados do concurso solicitado, caso não exista o arquivo em disco
     * 
     * @return array Dados do concurso
     */
    private function baixaConcurso() {
        $content = file_get_contents($this->tipos[$this->tipo]['url'] . $this->concurso);
        $v = explode('|', $content);
        if (strlen($v[0])) {
            $nom_arquivo = $this->tipo . '_' . $this->concurso . '.json';
            $arq_result = $this->path_resultados . $nom_arquivo;
            try {
                $fp = fopen($arq_result, 'wr');
                fwrite($fp, json_encode($v));
                fclose($fp);
            } catch (Exception $e) {
                $this->erro[] = $e->getMessage();
                $v = [];
            }
            return $v;
        } else {
            $this->erro[] = 'Nenhum resultado para este concurso';
            return [];
        }
    }
    
    
    /**
     * Baixa resultados do site da Caixa
     * Necessita ampliar max_execution_time no php.ini
     * 
     * Função não finalizada
     */
    private function baixaTudo() {
        $x = (int) $this->ultimo_concurso[$this->tipo];
        $x ++;
        $y = true;
        while ($y) {
            $content = file_get_contents($this->tipos[$this->tipo]['url'] . $x);
            $v = explode('|', $content);
            if (strlen($v[1]) > 50) {
                unset($v[1]);
                $nom_arquivo = $this->tipo . '_' . $x . '.json';
                $arq_result = $this->path_resultados . $nom_arquivo;
                try {
                    $fp = fopen($arq_result, 'wr');
                    fwrite($fp, json_encode($v));
                    fclose($fp);
                    $x++;
                } catch (Exception $e) {
                    $this->erro[] = $e->getMessage();
                    var_dump($this->erro); // excluir
                }
            } else {
                $y = false;
            }
        }
    }
    
    
    public function numerosSorteados() {
        return $this->numeros;
    }

        /**
     * Exibe mensagens de erro e as limpa em seguida
     * @return array
     */
    public function pegaErros(){
        return $this->erro;
        $this->erro = [];
    }
    
    
    /**
     * Verifica existência de erros
     * @return boolean True se possui erros
     */
    public function temErros(){
        return count($this->erro) > 0;
    }
    
    
    /***
     * Jogos a serem conferidos.
     * Posteriormente esses números serão armazenados em um .txt e importados
     */
    private function carregaJogos(){
        $a = ''; // teste
    }
    
    
}

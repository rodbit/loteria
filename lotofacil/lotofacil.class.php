<?php

/**
 * Faz download do arquivo de resultados da Lotofácil do site da Caixa,
 * lê e processa as informações, confere jogos, exibe estatísticas, etc.
 *
 * @author Marcos Web
 * @version 1.0.1
 * 
 */
class Lotofacil {
    
    private $_erro;
    private $_jogos;
    private $_path;
    private $_sorteios;
    private $_resultado;
    private $_mais_sorteados;
    
    
    
    
    public function __construct(){
        $this->_erro = [];
        $this->_jogos = [];
        $this->_path = $_SERVER['DOCUMENT_ROOT'].'/';
        $this->_sorteios = [];
        $this->_resultado = [[]];
        for($x=1; $x<16;$x++){
            $this->_mais_sorteados = [$x=>0];
        }
        $this->init();
    }
    
    
    
    public function pega_erros(){
        return $this->_erro;
        $this->_erro = [];
    }
    
    
    
    public function tem_erros(){
        return count($this->_erro) > 0;
    }
    
    
    /***
     * Faz download do arquivo do site da Caixa, caso não encontre o COOKIE.
     * O cookie expira com 48 horas
     */
    public function download(){
        if(!file_exists($this->_path."D_LOTFAC.HTM") || !isset($_COOKIE['lotofacil'])){
            echo '<br>fazendo download...'; // *****************************
            $url = 'http://www1.caixa.gov.br/loterias/_arquivos/loterias/D_lotfac.zip';
            $down = file_put_contents("lotofacil.zip", file_get_contents($url));
            if (!$down){
                $this->_erro[] = 'Erro ao fazer download de '.$url;
                return false;
            }
            $zip = new ZipArchive;
            $res = $zip->open('lotofacil.zip');
            if ($res === TRUE) {
                echo '<br>descompactando arquivo...'; // *****************************
                $zip->extractTo('.');
                $zip->close();
                unlink($this->_path.'lotofacil.zip');
                setcookie('lotofacil',date('d/m/Y'), time()+172800); // dura 2 dias
            } else {
                $this->_erro[] = 'Falha ao extrair arquivo lotofacil.zip';
            }
        }
    }
    
    
    
    /***
     * Jogos a serem conferidos.
     * Posteriormente esses números serão armazenados em um .txt e importados
     */
    private function carrega_jogos(){
        $this->_jogos = 
        [ 
            ['02','03','05','08','09','11','12','15','17','18','19','21','22','23','25'],
            ['01','02','04','06','07','08','09','10','12','13','16','18','22','24','25'],
            ['01','02','03','07','08','09','12','13','14','16','18','19','22','23','25'],
            ['01','02','04','11','12','13','14','15','17','19','21','22','23','24','25'],
            ['02','03','05','07','09','11','12','15','16','17','19','21','22','23','24'],
            ['02','03','05','06','09','11','12','14','17','18','19','21','23','24','25']
        ];
    }
    
    
    
    public function conferir_jogos(){
        foreach ($this->_sorteios as $k => $v){
            $data = $v['dt'];
            $numeros = $v['nr'];
            // percorre todos os jogos cadastrados
            for( $z=0; $z < count( $this->_jogos ); $z++){
                $num = $this->_jogos[$z];
                //percorre os numeros de cada jogo cadastrado
                for($y=0; $y<count($num); $y++){
                    // verifica se o numero atual do bilhete 
                    // está entre os numeros sorteados
                    if ( in_array( $this->_jogos[$z][$y], $numeros ) ){
                        // computa nr de acertos na matriz de resultados.
                        // $k: [nr_concurso] $z:[indice do bilhete
                        $this->_resultado[$k][$z] = isset($this->_resultado[$k][$z])
                        ? $this->_resultado[$k][$z] += 1 : 1;
                    }
                }
            }
        }
    }
    
    
    
    /***
     * Retorna vetor de resultados por concurso
     */
    public function extrai_resultados($concurso=null){
        if($concurso===null){
            return '';
        }
        if (!isset($this->_resultado[$concurso])  ){
            $this->_erro[] = 'Resultado '.$concurso.' idisponível';
            return '';
        }
        return $this->_resultado[$concurso];
    }
    
    
    
    private function init(){
        $this->download();
        $this->carrega_jogos();
        $this->processa_arquivo();
    }
    
    
    
    /***
     * Lê o arquivo de resultados baixado e armazena dados no Vetor
     */
    private function processa_arquivo(){
        $arq = $this->_path."D_LOTFAC.HTM";
        if (!is_file($arq)){
            $this->_erro[] = "Arquivo D_LOTFAC.HTM não encontrado";
            return false;
        }
        $handle = fopen ($arq, 'r');
        $conteudo = fread ($handle, filesize($arq));
        fclose ($handle);
        $c = explode('<tr', $conteudo);
        array_shift($c);
        array_shift($c);
        
        foreach ($c as $key => $val){
            $t = explode('<td>',$val);
            array_shift($t); // remove primeiro item (irrelevante)
            $tmp = '';
            foreach( $t as $k => $v ){
                $tmp .= trim( $v ) . '#';
            }
            $tmp = str_replace('</td>', '', $tmp );
            $t = explode( '#', $tmp );
            
            // numero do concurso (sorteio)
            $conc = trim( $t[0] );
            
            // data do sorteio
            $this->_sorteios[$conc]['dt'] = trim( $t[1] );
            
            // numeros sorteados
            $this->_sorteios[$conc]['nr'] = array_slice($t, 2, 15);
        }
    }
    
    
    /***
     * ordena numeros sorteados em ordem crescente
     */
    private function ordena_numeros($num=null){
        if (num===null ){
            return [];
        }
        if (!is_array($num)){
            return [];
        }
        if (count($num)!=15){
            return[];
        }
        $ret = sort( $num['nr'] );
        return $ret;
    }
    
    
    
    
}

?>

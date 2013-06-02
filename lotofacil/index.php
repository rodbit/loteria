<?php

$r  = isset($_GET['r']) ? $_GET['r'] : '';
$ok = 1;

if (! strlen( $r ) ){
    $url = 'http://www1.caixa.gov.br/loterias/_arquivos/loterias/D_lotfac.zip';
    $ok = file_put_contents("lotofacil.zip", file_get_contents($url));
}

if ( $ok ){
    unset($ok);
    $zip = new ZipArchive;
    $res = $zip->open('lotofacil.zip');//File name in server.
    if ($res === TRUE) {
        $zip->extractTo('extraidos/');//Destination directory
        $zip->close();
        echo 'extraído com sucesso';
    } else {
        echo 'falha ao extrair arquivo lotofacil.zip';
    }
    
    // Cartelas que serão conferidas
    $jogos[] = ['02','03','05','08','09','11','12','15','17','18','19','21','22','23','25'];
    $jogos[] = ['01','02','04','06','07','08','09','10','12','13','16','18','22','24','25'];
    $jogos[] = ['01','02','03','07','08','09','12','13','14','16','18','19','22','23','25'];
    $jogos[] = ['01','02','04','11','12','13','14','15','17','19','21','22','23','24','25'];
    $jogos[] = ['02','03','05','07','09','11','12','15','16','17','19','21','22','23','24'];
    $jogos[] = ['02','03','05','06','09','11','12','14','17','18','19','21','23','24','25'];

}

?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8" />
        <title> Lotofácil </title>
        <link rel="stylesheet" type="text/css" href="estilo.css">
    </head>
    <body>
        <ul class="users">
            <li><a href=?r=>Marcos</a></li>
            <li><a href=?r=tia>Tia Maria</a></li>
            <li><a href=?r=go>Gó</a></li>
        </ul>
        <table>
            <tr>
                <th> NR </th>
                <th> DATA </th>
                <th> RESULTADOS </th>
                <th colspan="<?php echo count($jogos) ?>"> PONTOS </th>
                <th> PREMIO </th>
            </tr>
        <?php 
        
        $filename = "extraidos/D_LOTFAC.HTM";
        if (! is_file( $filename ) ){
            echo "arquivo $filanem não encontrado";
            exit(0);
        }
        $handle = fopen ($filename, "r");
        $conteudo = fread ($handle, filesize ($filename));
        fclose ($handle);
        $c = explode('<tr', $conteudo);
        unset( $conteudo, $handle );
        array_shift($c);
        array_shift($c);

        for( $x=0; $x < count($c); $x++){
            $t = explode('<td>',$c[$x]);
            array_shift($t); // remove primeiro item 
            $tmp = '';
            foreach( $t as $k => $v ){
                $tmp .= trim( $v ) . '#';
            }
            $tmp = str_replace('</td>', '', $tmp );
            $t = explode( '#', $tmp );
            $linha[$x]['id'] = trim( $t[0] );
            $linha[$x]['dt'] = trim( $t[1] );
            $linha[$x]['nr'] = array_slice( $t, 2, 15 );
            // numeros que mais sairam
            $linha[$x]['15'] = trim( $t[23] );
            $linha[$x]['14'] = trim( $t[24] );
            $linha[$x]['13'] = trim( $t[25] );
            $linha[$x]['12'] = trim( $t[26] );
            $linha[$x]['11'] = trim( $t[27] );
            sort( $linha[$x]['nr'] );
            
            for( $y=0; $y < count( $linha[$x]['nr'] ); $y++ ){ // varre resultado
                $i = (string) $linha[$x]['nr'][$y]; // nr atual
                $mais[ $i ] = isset( $mais[ $i ] ) ? intval( $mais[ $i ] ) + 1 : 1; // acumula nrs que mais sairam
                for( $z=0; $z < count( $jogos ); $z++){ // varre jogos do cliente
                    if ( in_array( $i, $jogos[$z] ) ){ // acumula pontos do jogo1
                        $linha[$x]['pontos'][$z] = isset( $linha[$x]['pontos'][$z] ) ? $linha[$x]['pontos'][$z] += 1 : 1;
                    }
                }
            }
        }
        $dados = '';
        
        for( $y = count( $linha ) - 1; $y >= 0; $y--){ 
        $qtde_linhas = 20;
        $tot_linhas = count( $linha ) - 1;
        //for( $y = $tot_linhas; $y > ( $tot_linhas - $qtde_linhas ); $y--){ 
            $num   = implode(', ', $linha[$y]['nr']);
            $class = '';
            $acum  = 0;
            
            for ( $z=0; $z<count($jogos); $z++){
                
                if ( isset( $linha[$y][ $linha[$y]['pontos'][$z] ] ) ){

                    $_valtmp = str_replace('.', '', $linha[$y][ $linha[$y]['pontos'][$z] ] );
                    $_valtmp = str_replace(',', '.', $_valtmp);
                    $acum += floatval( $_valtmp );
                }
                if ( intval( $linha[$y]['pontos'][$z] ) == 15     ){  
                    $class = 'class="super"'; 
                }
                elseif ( intval( $linha[$y]['pontos'][$z] ) == 14 ){  
                    $class = 'class="quase"'; 
                }
                elseif ( intval( $linha[$y]['pontos'][$z] ) >= 11  ){  $class = 'class="acerto"'; }
            } 
            $dados .= '
            <tr>
            <td '.$class.'>' . $linha[$y]['id'] . '</td>
            <td '.$class.'>' . $linha[$y]['dt'] . '</td>
            <td '.$class.'>' . $num             . '</td>';
            for( $k=0; $k < count( $linha[$y]['pontos'] ); $k++){
                $dados .= '<td '.$class.'>' . $linha[$y]['pontos'][$k] . '</td>';
            }
            $dados .= '
            <td '.$class.'>' . number_format( $acum, 2, ',', '.' ). '</td>
            </tr>';    
        }
        $dados .= '</table>';
        unset( $t, $tmp, $c, $k, $v, $x, $y, $z );
        echo $dados;
        arsort( $mais );
        //var_dump(  $mais );
        $m = '';
        foreach( $mais as $k => $v ){
            $m .= "'$k',";
        }
        echo '<h6>OS MAIS SORTEADOS:</h6> <br /><p>'. substr( $m, 0 , strlen($m)-1 ) . '</p>';
        ?>
    </body>
</html> 
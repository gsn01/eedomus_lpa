<?
// Script publié sur Github gsn01 (eedomus_lpa)
// Fonctions
function sdk_multiexplode ($delimiters,$string) {	// Separer une chaine de caracteres suivant plusieurs separateurs
    
    $ready  = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return  $launch;
}
function sdk_decode_ligne ( $ligne) {
// Exemple : Saint Antoine<br><span id='sp1'><span class='sli'>247 places libres</span></span></td>
// Retour : array(nom => 'Saint Antoine',places => 247)
	$tab = sdk_multiexplode ( array('<','>',' places'), $ligne );
	if ( $tab[6] == 'complet' ) $places = 0; else $places = $tab[6];
	return array( "nom" => $tab[0], "places" => $places);
}

$seuil = getarg('seuil', $mandatory=false, $default=10); // Seuil par défaut = 10
$url = "http://www.lyonparking.fr/mobile.php";
$response = httpQuery($url,'GET');

// Exploser la réponse HTML sur les tags td
$exploded = sdk_multiexplode (array("<td\>","<td>"), $response);
//var_dump ($exploded);

$xml = "<parkings>\n";
foreach( $exploded as $ligne ) {
	// Si la ligne contient sli, c'est une ligne avec les disponibilités
	if ( strpos( $ligne, 'sli' ) <> FALSE ) {
		$ret_ligne = sdk_decode_ligne ($ligne);
		if ($ret_ligne["places"] == 0) {
			$dispo = "complet";
		}
		else {
			if ( $ret_ligne["places"] <= $seuil)
				{$dispo = "limite";}
			else {$dispo = "libre";}
		};
		$xml .= "<parking><nom>".utf8_encode($ret_ligne["nom"])."</nom><places>".$ret_ligne["places"]."</places><dispo>".$dispo."</dispo></parking>\n";
	}
}

$xml .= "</parkings>";
echo $xml;
?>
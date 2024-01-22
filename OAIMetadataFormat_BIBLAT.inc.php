<?php

/**
 * @file plugins/oaiMetadataFormats/biblat/OAIMetadataFormat_BIBLAT.inc.php
 *
 * Copyright (c) 2021 UNAM-DGBSDI
 * Copyright (c) 2021 Edgar Durán
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_BIBLAT
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- BIBLAT.
 */
class OAIMetadataFormat_BIBLAT extends OAIMetadataFormat {
	public $records = null;
	public $issues = null;
	public $msjError = '';
	public $muestraRecords = False;
	public $allYears = False;
	public $min = null;
	public $path = '';
	public $years = 2022;
	public $token = null;
	/**
	 * Constructor.
	 */
	function __construct($prefix, $schema, $namespace) {
		parent::__construct($prefix, $schema, $namespace);
		$version = phpversion();
		$version = substr($version, 0, 3);                
		$version = (int) $version;
		$this->version = $version;
		$oai = explode("/oai", $_SERVER['REQUEST_URI']);
		$oai = explode("/", $oai[0]);
		$oai = array_reverse($oai);
		$oai = $oai[0];
        $this->path = $oai;

		#Obtiene años
		$explode_years = explode("years_", $_SERVER['REQUEST_URI']);
		$years = count($explode_years);
		if($years >= 2){
			$explode_years = explode('&', $explode_years[1]);
			$explode_years = explode('-', $explode_years[0]);
			if( count($explode_years) == 1 ){
				$this->years = $explode_years[0];
			}
			else{
				$year1 = intval($explode_years[0]);
				$year2 = intval($explode_years[1]);
				$this->years = $year1.'';
				for($i = $year1 + 1; $i <= $year2; $i++){
					$this->years .= ', '.$i;
				}
			}
		
		
			#Obtiene token
			$explode_token = explode("tk_", $_SERVER['REQUEST_URI']);
			$token = count($explode_token);
			if($token == 2){
				$this->token = intval($explode_token[1]);
		

				if( $this->version >= 7){
					try{
						$this->createBiblat();
					} catch (Throwable $e) {
						$this->msjError .= $e->getMessage();
					}
				}else{
					try{
						$this->createBiblat();
					} catch (Exception $e) {
						$this->msjError .= $e->getMessage();
					}
				}
				if($this->muestraRecords){
					$this->records = $this->getRecords();
				}
			}

		}
	}
	
	function createBiblat(){
		$sub = DAORegistry::getDAO('NotificationDAO');
		
		if( $this->version >= 7){
		
			try{
		
				$sql = "create table if not exists biblat(val int)";
				$result = $sub->update($sql);
				
				$sql = "show columns from biblat like 'revista'";
				$result = $sub->retrieve($sql);
				
				$exists = FALSE;
				foreach($result as $row){
					$exists = TRUE;
				}
				
				if(!$exists){
					$sql = "ALTER TABLE biblat ADD COLUMN revista varchar(200)";
					$result = $sub->update($sql);
				}
				
				$sql = "select val from biblat where revista='".$this->path."'";
				$result = $sub->retrieve($sql);
				foreach ($result as $row) {
					$valor_actual = (array) $row;
				}
				$valor_actual =  (int) $valor_actual['val'];
				
				if($valor_actual == 0){
					$this->muestraRecords = True;
					$sql = "insert into biblat(val, revista) values(".$this->token.",'".$this->path."')";
					$result = $sub->update($sql);
					//echo "Insertó el nuevo valor porque no existe";
				}else{
					if($valor_actual != $this->token){
						$this->muestraRecords = True;
						$sql = "update biblat set val=".$this->token." where revista='".$this->path."'";
						$result = $sub->update($sql);
						//echo "Actualizó el nuevo valor porque es mayor que 1";
					}else{
						$this->muestraRecords = False;
						//echo "No se harán consultas porque la diferencia es menor a 1";
					}
				}
			} catch (Throwable $e) {
				$this->msjError .= $e->getMessage();
				$sql = "select setting_value from journal_settings where journal_id = (select journal_id from journals where path='".$this->path."') and setting_name='biblat'";
				$result = $sub->retrieve($sql);
				foreach ($result as $row) {
					$valor_actual = (array) $row;
				}
				$valor_actual =  (int) $valor_actual['setting_value'];
				if($valor_actual == 0){
					$this->muestraRecords = True;
					$sql = "insert into journal_settings(journal_id, setting_name, setting_value) values((select journal_id from journals where path='".$this->path."'), 'biblat', '".$this->token."')";
					$result = $sub->update($sql);
				}else{
					if($valor_actual != ($this->token.'')){
						$this->muestraRecords = True;
						$sql = "update journal_settings set setting_value='".$this->token."' where journal_id=(select journal_id from journals where path='".$this->path."') and setting_name='biblat'";
						$result = $sub->update($sql);
					}else{
						$this->muestraRecords = False;
					}
				}
			}
		}else{
			try{
				$sql = "create table if not exists biblat(val int)";
				$result = $sub->update($sql);
				
				$sql = "show columns from biblat like 'revista'";
				$result = $sub->retrieve($sql);
				
				$exists = FALSE;
				foreach($result as $row){
					$exists = TRUE;
				}
				
				if(!$exists){
					$sql = "ALTER TABLE biblat ADD COLUMN revista varchar(200)";
					$result = $sub->update($sql);
				}
				
				$sql = "select val from biblat where revista='".$this->path."'";
				$result = $sub->retrieve($sql);
				foreach ($result as $row) {
					$valor_actual = (array) $row;
				}
				$valor_actual =  (int) $valor_actual['val'];
				
				if($valor_actual == 0){
					$this->muestraRecords = True;
					$sql = "insert into biblat(val, revista) values(".$this->token.",'".$this->path."')";
					$result = $sub->update($sql);
					//echo "Insertó el nuevo valor porque no existe";
				}else{
					if($valor_actual != $this->token){
						$this->muestraRecords = True;
						$sql = "update biblat set val=".$this->token." where revista='".$this->path."'";
						$result = $sub->update($sql);
						//echo "Actualizó el nuevo valor porque es mayor que 1";
					}else{
						$this->muestraRecords = False;
						//echo "No se harán consultas porque la diferencia es menor a 1";
					}
				}
			} catch (Exception $e) {
				$this->msjError .= $e->getMessage();
				$sql = "select setting_value from journal_settings where journal_id = (select journal_id from journals where path='".$this->path."') and setting_name='biblat'";
				$result = $sub->retrieve($sql);
				foreach ($result as $row) {
					$valor_actual = (array) $row;
				}
				$valor_actual =  (int) $valor_actual['setting_value'];
				if($valor_actual == 0){
					$this->muestraRecords = True;
					$sql = "insert into journal_settings(journal_id, setting_name, setting_value) values((select journal_id from journals where path='".$this->path."'), 'biblat', '".$this->token."')";
					$result = $sub->update($sql);
				}else{
					if($valor_actual != ($this->token.'')){
						$this->muestraRecords = True;
						$sql = "update journal_settings set setting_value='".$this->token."' where journal_id=(select journal_id from journals where path='".$this->path."') and setting_name='biblat'";
						$result = $sub->update($sql);
					}else{
						$this->muestraRecords = False;
					}
				}
			}
		}
		
	}
	
	function ciph($input)
	{
		return bin2hex($input);
	}
	
	function get_json($tabla, $where){
		if($tabla == 'issues'){
			$sql = "Select * from " . $tabla . $where;
			$sub = DAORegistry::getDAO('NotificationDAO');
			$result = $sub->retrieve($sql);
			$this->issues = array();
			foreach ($result as $row) {
				$row = (array) $row;
				array_push($this->issues, $row);
			}
		}
		
		$sql = "Select * from " . $tabla . $where;
		
		$funcion_cifra = function_exists('openssl_encrypt');
		
		if($funcion_cifra){
			$ciphering = "AES-256-CBC";
			$iv_length = openssl_cipher_iv_length($ciphering);
			$options = 0;
			$encryption_iv = substr(hash('sha256', 'c09f6a9e157d253d0b2f0bcd81d338298950f246'), 0, 16);
			$encryption_key = hash('sha256', 'UNAM - Bibliografia Latinoamericana');
		}
		
		$sql = "Select * from " . $tabla . $where;
		if($tabla == 'count'){
			$sql = "Select count(*) num from issues " . $where;
		}

		$arr_result = array();
		$sub = DAORegistry::getDAO('NotificationDAO');
		
		if(strpos($where, '<issue>')){
			foreach ($this->issues as $issue) {
				$result = $sub->retrieve(str_replace('<issue>', $issue['issue_id'], $sql));
				foreach ($result as $row) {
					$row = (array) $row;
					array_push($arr_result, $row);
				}
			}
		}else{
			$result = $sub->retrieve($sql);
			foreach ($result as $row) {
				$row = (array) $row;
				array_push($arr_result, $row);
			}
		}
		
		if($funcion_cifra){
			$json = $encryption = openssl_encrypt("xxx".json_encode($arr_result)."xxx", $ciphering, $encryption_key, $options, $encryption_iv);
		}else{
			$json = "xxx".json_encode($arr_result)."xxx";
			$json = $this->ciph($json);
		}
		return $json; 
	}
	
	function getRecords(){
		#				0		1				2					3				4					5					6				7			8				9				10				11					12
		$tablas = ['issues', 'journals', 'journal_settings', 'published_articles', 'articles', 'article_settings', 'issue_settings', 'authors', 'author_settings', 'sections', 'section_settings', 'article_galleys', 'article_files', 'count'];
		
		$sel_journal_id = " (select journal_id from journals where path = '".$this->path."') ";
		#			0
		$where = [  " where COALESCE(year, YEAR(date_published)) in (".$this->years.") and journal_id = ".$sel_journal_id." ",
		#			1
					" where path = '".$this->path."' ",
		#			2
                    " where setting_name not in ('emailSignature', 'authorInformation', 'clockssLicense', 'librarianInformation', 'readerInformation', 'submissionChecklist', 'about') and journal_id = ".$sel_journal_id."",
		#			3
                    " where issue_id in ( <issue> ) ",
		#			4
					" where article_id in ( select article_id from published_articles where issue_id in ( <issue> ) ) ", 
		#			5
					" where article_id in ( select article_id from published_articles where issue_id in ( <issue> ) ) ", 
		#			6
					" where issue_id in ( <issue> ) ",
		#			7
					" where submission_id in ( select article_id from published_articles where issue_id in ( <issue> ) ) ",
		#			8
					" where author_id in ( select author_id from authors where submission_id in ( select article_id from published_articles where issue_id in ( <issue> ) ) ) ",
		#			9
					" where journal_id = ".$sel_journal_id." ",
		#			10
					" where section_id in ( select section_id from articles where article_id in ( select article_id from published_articles where issue_id in ( <issue> ) ) ) ",
		#			11
					" where article_id in ( select article_id from published_articles where issue_id in ( <issue> ) ) ",
		#			12
					" where file_id in (select file_id from article_galleys where article_id in ( select article_id from published_articles where issue_id in ( <issue> ) ) ) ",
		#			13
					" where year in (".$this->years.") and journal_id = ".$sel_journal_id." and published=1"
				  ];
                if($this->allYears){
                    $where[0] = " where journal_id = ".$sel_journal_id." ";
                }
		#			0	1		2	3		4	5		6	7		8	 9		10	11		12		13
		$abrev = ['i', 'j', 'j_s', 'ss', 'p', 'p_s', 'i_s', 'a', 'a_s', 's', 's_s', 'p_g', 'p_f', 'num'];
		$result_tablas = array();
		$i=0;
		foreach ($tablas as $tabla) {
			//$rows = PKPString::html2text($this->get_json($tabla));
			if( $this->version >= 7){
				try{
					$rows = $this->get_json($tabla, $where[$i]);
				} catch (Throwable $e) {
					$this->msjError .= $e->getMessage();
					//Error posible tabla en postgres
					if($tabla == 'issues'){
						$where_p = " where COALESCE(year, extract(year from date_published)) in (".$this->years.") and journal_id = ".$sel_journal_id." ";
						$rows = $this->get_json($tabla, $where_p);
					}else{
						$rows = [];
					}
				}
			}else{
				try{
					$rows = $this->get_json($tabla, $where[$i]);
				} catch (Exception $e) {
					$this->msjError .= $e->getMessage();
					//Error posible tabla en postgres
					if($tabla == 'issues'){
						$where_p = " where COALESCE(year, extract(year from date_published)) in (".$this->years.") and journal_id = ".$sel_journal_id." ";
						$rows = $this->get_json($tabla, $where_p);
					}else{
						$rows = [];
					}
				}
			}
			array_push($result_tablas, array('tabla' => $abrev[$i], 'rows' => $rows));
			$i++;
		}
		return $result_tablas;
	}
	/**
	 * @see OAIMetadataFormat#toXml
	 */
	function toXml(&$record, $format = null) {
		$article =& $record->getData('article');
		$journal =& $record->getData('journal');
                
                $version= file_get_contents("dbscripts/xml/version.xml");
		
                $pais = DAORegistry::getDAO('CountryDAO')->getCountries();
                
		$meses = array(	'enero' => 'ene', 'febrero' => 'feb', 'marzo' => 'mar', 'abril' => 'abr', 'mayo' => 'may', 'junio' => 'jun',
						'julio' => 'jul', 'agosto' => 'ago', 'septiembre' => 'sep', 'octubre' => 'oct', 'noviembre' => 'nov', 'diciembre' => 'dic'
		);
		$idiomas = array('spa' => 'Español', 'eng' => 'Inglés', 'es' => 'Español', 'en' => 'Inglés', 'ita' => 'Italiano', 'fra' => 'Francés', 'por' => 'Portugués');
		
		$address = explode("País:", $journal->getSetting('mailingAddress'));
		$address = $address[1];
		$address = explode(";", $address);
		$address = $address[0];
		$address = trim($address);
		
		$art_authors_ac = array();
		$art_authors = array();
		$authors = $article->getAuthors();
		foreach ($authors as $author){
				$affiliation = $author->getAffiliation('es_ES');
                                if(strlen($affiliation) == 0){
                                    $affiliation = $author->getAffiliation($article->getLocale());
                                }
                                if(strlen($affiliation) == 0){
                                    $affiliation = $author->getAffiliation($journal->getPrimaryLocale());
                                }
				$institution = explode("Institución:", $affiliation);
				$institution = $institution[1];
				$institution = explode(";", $institution);
				$institution = $institution[0];
				$institution = trim($institution);
				
				$dependencia = explode("Dependencia:", $affiliation);
				$dependencia = $dependencia[1];
				$dependencia = explode(";", $dependencia);
				$dependencia = $dependencia[0];
				$dependencia = trim($dependencia);
				
				$estado = explode("Estado:", $affiliation);
				$estado = $estado[1];
				$estado = explode(";", $estado);
				$estado = $estado[0];
				$estado = trim($estado);
								
				$ciudad = explode("Ciudad:", $affiliation);
				$ciudad = $ciudad[1];
				$ciudad = explode(";", $ciudad);
				$ciudad = $ciudad[0];
				$ciudad = trim($ciudad);
				
				$country = $pais[$author->getCountry()];
				$ciudad_estado = null;
				if ($ciudad or $estado){
					$ciudad_estado = $estado . ', ' . $ciudad;
				}
				array_push($art_authors, array('institution' => $institution, 'dependencia' => $dependencia, 'ciudad' => $ciudad_estado, 'country' => $country));
			//}
		}
		
		$ident = $record->getData('issue')->getIssueIdentification();
		$art_ident = array();
                //volúmen idioma español
				$volumen = explode("Vol.", $ident);
                $volumen = $volumen[1];
                $volumen = explode("Núm.", $volumen);
                $volumen = $volumen[0];
                $volumen = trim($volumen);
                //volúmen idioma portugués
                if($volumen == ""){
					$volumen = explode("v.", $ident);
                    $volumen = $volumen[1];
                    $volumen = explode("n.", $volumen);
                    $volumen = $volumen[0];
                    $volumen = trim($volumen);
				}
                //volúmen idioma inglés
                if($volumen == ""){
					$volumen = explode("Vol", $ident);
                    $volumen = $volumen[1];
                    $volumen = explode("No", $volumen);
                    $volumen = $volumen[0];
                    $volumen = trim($volumen);
				}
				if($volumen == ""){
                    $volumen = explode("##issue.vol##", $ident);
                    $volumen = $volumen[1];
                    $volumen = explode(", ##issue.no##", $volumen);
                    $volumen = $volumen[0];
                    $volumen = trim($volumen);
                }
				
                //numero idioma español
		$numero = explode("Núm.", $ident);
		$numero = $numero[1];
		$numero = explode("(", $numero);
		$numero = $numero[0];
		$numero = trim($numero);
                //numero idioma portugués
                if($numero == ""){
					$numero = explode("n.", $ident);
                    $numero = $numero[1];
                    $numero = explode("(", $numero);
                    $numero = $numero[0];
                    $numero = trim($numero);
				}
                //numero idioma inglés
                if($numero == ""){
					$numero = explode("No", $ident);
                    $numero = $numero[1];
                    $numero = explode("(", $numero);
                    $numero = $numero[0];
                    $numero = trim($numero);
				}
				
				 //numero idioma no definido
				if($numero == ""){
					$numero = explode("##issue.no##", $ident);
					$numero = $numero[1];
					$numero = explode("(", $numero);
					$numero = $numero[0];
					$numero = trim($numero);
				}
                
		$anio = explode("(", $ident);
		$anio = $anio[1];
		$anio = explode(")", $anio);
		$anio = $anio[0];
		$anio = trim($anio);
		array_push($art_ident, array('vol' => $volumen, 'num' => $numero, 'anio' => $anio));
		
                $issue = $record->getData('issue')->getDescription('es_ES');
                if(strlen($issue) == 0){
                    $issue = $record->getData('issue')->getDescription($article->getLocale());
                }
                if(strlen($issue) == 0){
                    $issue = $record->getData('issue')->getDescription($journal->getPrimaryLocale());
                }
                
		$art_issue = array();
		$month = explode("Mes:", $issue);
		$month = $month[1];
		$month = explode(";", $month);
		$month = $month[0];
		$month = strtolower(trim($month));
		
		$part = explode("Parte:", $issue);
		$part = $part[1];
		$part = explode(";", $part);
		$part = $part[0];
		$part = trim($part);
		array_push($art_issue, array('mes' => $meses[$month], 'parte' => $part));
		
                $types = $article->getType('es_ES');
                if(strlen($types) == 0){
                    $types = $article->getType($article->getLocale());
                }
                if(strlen($types) == 0){
                    $types = $article->getType($journal->getPrimaryLocale());
                }
		$art_type = array();
		$type = explode("Tipo:", $types);
		$type = $type[1];
		$type = explode(";", $type);
		$type = $type[0];
		$type = trim($type);
		
		$focus = explode("Enfoque:", $types);
		$focus = $focus[1];
		$focus = explode(";", $focus);
		$focus = $focus[0];
		$focus = trim($focus);
		
		array_push($art_type, array('type' => $type, 'focus' => $focus));
                
                $keywords = array_merge_recursive(
                        $this->stripAssocArray((array) $article->getSubject(null))
                );
                $keywords = explode(";",$keywords['es_ES']);
                if(!isset($keywords[0])){
                    $keywords = explode(";",$keywords[$article->getLocale()]);
                }
                if(!isset($keywords[0])){
                    $keywords = explode(";",$keywords[$journal->getPrimaryLocale()]);
                }
                $keywordsUS = array_merge_recursive(
                    $this->stripAssocArray((array) $article->getSubject(null))
                );
                $keywordsUS = explode(";",$keywordsUS['en_US']);
                
                $disciplines = array_merge_recursive(
                        $this->stripAssocArray((array) $article->getDiscipline(null))
                );
                $disciplines = explode(";",$disciplines['es_ES']);
                if(!isset($disciplines[0])){
                    $disciplines = explode(";",$disciplines[$article->getLocale()]);
                }
                if(!isset($disciplines[0])){
                    $disciplines = explode(";",$disciplines[$journal->getPrimaryLocale()]);
                }

		$templateMgr = TemplateManager::getManager();
		$templateMgr->assign(array(
			'journal' => $journal,
			'article' => $article,
			'address' => $address,
			'art_authors_ac' => $art_authors_ac,
			'art_authors' => $art_authors,
			'art_issue' => $art_issue,
			'art_ident' => $art_ident,
			'art_type' => $art_type,
			'keywords' => $keywords,
			'keywordsUS' => $keywordsUS,
			'disciplines' => $disciplines,
			'issue' => $record->getData('issue'),
			'section' => $record->getData('section'),
                        'ident' => $ident,
                        'affiliation'=>$affiliation,
                        'prueba_c' => $prueba
		));

		$subjects = array_merge_recursive(
			//$this->stripAssocArray((array) $article->getDiscipline(null)),
			$this->stripAssocArray((array) $article->getSubject(null))
			//$this->stripAssocArray((array) $article->getSubjectClass(null))
		);
                
                

		$abstractLan = array();
		if ($article->getAbstract('es_ES')){
			array_push($abstractLan, $idiomas[AppLocale::get3LetterIsoFromLocale('es_ES')]);
		}
                if ($article->getAbstract('pt_BR')){
			array_push($abstractLan, $idiomas[AppLocale::get3LetterIsoFromLocale('pt_BR')]);
		}
                if ($article->getAbstract('en_US')){
			array_push($abstractLan, $idiomas[AppLocale::get3LetterIsoFromLocale('en_US')]);
		}
                if ($article->getLocale() <> 'en_US' and $article->getLocale() <> 'pt_BR' and $article->getLocale() <> 'es_ES'){
			array_push($abstractLan, $idiomas[AppLocale::get3LetterIsoFromLocale($article->getLocale())]);
                        $abstractO = String::html2text($article->getAbstract($article->getLocale()));
		}
                
                $title = [];
                $title[0] = $article->getTitle('es_ES');
                $title[1] = $article->getTitle('en_US');
                $title[2] = $article->getTitle('pt_BR');
                $title[3] = $article->getTitle('it_IT');
                $title[4] = $article->getTitle('fr_FR');
                
				$version = explode("<release>",$version);
                $version = $version[1];
                $version = explode("</release>",$version);
                $version = $version[0];
                    
		$templateMgr->assign(array(
			'subject' => isset($subjects[$journal->getPrimaryLocale()])?$subjects[$journal->getPrimaryLocale()]:'',
			'abstract' => $article->getAbstract('es_ES'),
			'abstractUS' => $article->getAbstract('en_US'),
                        'abstractPT' => $article->getAbstract('pt_BR'),
                        'abstractO' => $abstractO,
			'abstractLan' => $abstractLan,
                        'prueba' => AppLocale::get3LetterIsoFromLocale($article->getLocale()),
			'language' => $idiomas[AppLocale::get3LetterIsoFromLocale($article->getLocale())],
			'languages' => $journal->getSupportedFormLocaleNames(),
                        'version' => $version,
                        'title' => $title,//,
						'records' => $this->records
                        //'keywords' => isset($subjects[$journal->getPrimaryLocale()])?$subjects[$journal->getPrimaryLocale()]:''
		));

		return $templateMgr->fetch(dirname(__FILE__) . '/record.tpl');
	}
}

?>

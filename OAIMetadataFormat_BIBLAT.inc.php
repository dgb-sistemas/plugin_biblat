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
		$sub = DAORegistry::getDAO('QueryDAO');
		
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
	
	function get_json($tabla, $where){
		if($tabla == 'issues'){
			$sql = "Select * from " . $tabla . $where;
			$sub = DAORegistry::getDAO('QueryDAO');
			$result = $sub->retrieve($sql);
			$this->issues = array();
			foreach ($result as $row) {
				$row = (array) $row;
				array_push($this->issues, $row);
			}
		}
		
		$sql = "Select * from " . $tabla . $where;
		
		$ciphering = "AES-256-CBC";
		$iv_length = openssl_cipher_iv_length($ciphering);
		$options = 0;
		$encryption_iv = substr(hash('sha256', 'c09f6a9e157d253d0b2f0bcd81d338298950f246'), 0, 16);
		$encryption_key = hash('sha256', 'UNAM - Bibliografia Latinoamericana');
		
		$sql = "Select * from " . $tabla . $where;
		if($tabla == 'count'){
			$sql = "Select count(*) num from issues " . $where;
		}
		//["Select * from journals where journal_id = "]
		$arr_result = array();
		$sub = DAORegistry::getDAO('QueryDAO');
		
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
		
		
		$json = openssl_encrypt("xxx".json_encode($arr_result)."xxx", $ciphering, $encryption_key, $options, $encryption_iv);
		//$decryption=openssl_decrypt ($encryption, $ciphering, $encryption_key, $options, $encryption_iv);
		//$json = json_encode($arr_result);
		
		return $json; 
	}
	
	function getRecords(){
		#				0		1				2					3				4					5						6				7				8					9								10				11					12					13
		$tablas = ['issues', 'journals', 'journal_settings', 'submissions', 'published_submissions', 'submission_settings', 'issue_settings', 'authors', 'author_settings', 'controlled_vocab_entry_settings', 'sections', 'section_settings', 'submission_galleys', 'submission_files', 'count'];
		
		$sel_journal_id = " (select journal_id from journals where path = '".$this->path."') ";
		#			0
		$where = [  " where COALESCE(year, YEAR(date_published)) in (".$this->years.") and journal_id = ".$sel_journal_id." ",
		#			1
					" where path = '".$this->path."' ",
		#			2
                    " where setting_name not in ('emailSignature', 'authorInformation', 'librarianInformation', 'readerInformation', 'submissionChecklist', 'about') and journal_id = ".$sel_journal_id."",
		#			3
                    " where submission_id in ( select submission_id from published_submissions where issue_id in ( '<issue>' ) ) ",
		#			4
					" where issue_id in ( '<issue>' ) ",
		#			5
					" where submission_id in ( select submission_id from published_submissions where issue_id in ( '<issue>' ) ) ",
		#			6
					" where issue_id in ( '<issue>' ) ",
		#			7
					" where submission_id in ( select submission_id from published_submissions where issue_id in ( '<issue>' ) ) ",
		#			8
					" where author_id in ( select author_id from authors where submission_id in ( select submission_id from published_submissions where issue_id in ( '<issue>' ) ) ) ",
		#			9
					" c1 inner join controlled_vocab_entries c2 on c2.controlled_vocab_entry_id = c1.controlled_vocab_entry_id and c1.setting_name = 'submissionKeyword' inner join controlled_vocabs c3 on c3.controlled_vocab_id = c2.controlled_vocab_id where c3.symbolic in ( 'submissionKeyword', 'submissionDiscipline' ) and c3.assoc_id in ( select submission_id from published_submissions where issue_id in ( '<issue>' ) )",
		#			10
					" where journal_id = ".$sel_journal_id." ",
		#			11
					" where section_id in ( select section_id from submissions where submission_id in ( select submission_id from published_submissions where issue_id in ( '<issue>' ) ) ) ",
		#			12
					" where submission_id in  ( select submission_id from published_submissions where issue_id in ( '<issue>' ) ) ",
		#			13
					" where assoc_id in ( select published_submission_id from published_submissions where issue_id in ( '<issue>' ) )",
		#			14
					" where year in (".$this->years.") and journal_id = ".$sel_journal_id." and published=1"
				  ];
                if($this->allYears){
                    $where[0] = " where journal_id = ".$sel_journal_id." ";
                }
		#			0	1		2	3		4	5		6	7		8	 9			10	11		12		13
		$abrev = ['i', 'j', 'j_s', 'ss', 'p', 'p_s', 'i_s', 'a', 'a_s', 'c_v_e_s', 's', 's_s', 'p_g', 'p_f', 'num'];
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
	function toXml($record, $format = null) {
            $msjError="";
            
            try{
		$article = $record->getData('article');
		$journal = $record->getData('journal');
            } catch (Exception $e){
                $msjError += "Error en article/journal\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                $version= file_get_contents("dbscripts/xml/version.xml");
            } catch (Exception $e){
                $msjError += "Error en archivo de versión\n";
                $msjError += $e->getMessage() + "\n";
            }
	
			try{
		$meses = array(	'enero' => 'ene', 'febrero' => 'feb', 'marzo' => 'mar', 'abril' => 'abr', 'mayo' => 'may', 'junio' => 'jun',
						'julio' => 'jul', 'agosto' => 'ago', 'septiembre' => 'sep', 'octubre' => 'oct', 'noviembre' => 'nov', 'diciembre' => 'dic'
		);
            } catch (Exception $e){
                $msjError += "Error en asignar meses\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
		$idiomas = array('spa' => 'Español', 'eng' => 'Inglés', 'es' => 'Español', 'en' => 'Inglés', 'ita' => 'Italiano', 'fra' => 'Francés', 'por' => 'Portugués');
            } catch (Exception $e){
                $msjError += "Error en asignar idiomas\n";
                $msjError += $e->getMessage() + "\n";
            }
	
            try{
				$address = explode("País:", $journal->getSetting('mailingAddress'));
                $address = $address[1];
                $address = explode(";", $address);
                $address = $address[0];
                $address = trim($address);
            } catch (Exception $e){
                $msjError += "Error en leer mailingAddress\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
		$art_authors_ac = array();
		$art_authors = array();
		$authors = $article->getAuthors();
            } catch (Exception $e){
                $msjError += "Error al obtener Autores\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
		foreach ($authors as $author){

                            try{
								$affiliation = $author->getAffiliation('es_ES');
                            } catch (Exception $e){
                                $msjError += "Error al obtener Afiliación español\n";
                                $msjError += $e->getMessage() + "\n";
                            }
                            
                            try{
                                if(strlen($affiliation) == 0){
                                    $affiliation = $author->getAffiliation($article->getLocale());
                                }
                            } catch (Exception $e){
                                $msjError += "Error al obtener Afiliación en idioma local del artículo\n";
                                $msjError += $e->getMessage() + "\n";
                            }
                            
                            try{
                                if(strlen($affiliation) == 0){
                                    $affiliation = $author->getAffiliation($journal->getPrimaryLocale());
                                }
                            } catch (Exception $e){
                                $msjError += "Error al obtener Afiliación en idioma de la revista\n";
                                $msjError += $e->getMessage() + "\n";
                            }
                            
                            try{
								$institution = explode("Institución:", $affiliation);
                                $institution = $institution[1];
                                $institution = explode(";", $institution);
                                $institution = $institution[0];
                                $institution = trim($institution);
                            } catch (Exception $e){
                                $msjError += "Error al extraer Institución de Afiliación\n";
                                $msjError += $e->getMessage() + "\n";
                            }
                            
                            try{
								$dependencia = explode("Dependencia:", $affiliation);
                                $dependencia = $dependencia[1];
                                $dependencia = explode(";", $dependencia);
                                $dependencia = $dependencia[0];
                                $dependencia = trim($dependencia);
                            } catch (Exception $e){
                                $msjError += "Error al extraer Dependencia de Afiliación\n";
                                $msjError += $e->getMessage() + "\n";
                            }
                            
                            try{
								$estado = explode("Estado:", $affiliation);
                                $estado = $estado[1];
                                $estado = explode(";", $estado);
                                $estado = $estado[0];
                                $estado = trim($estado);
                            } catch (Exception $e){
                                $msjError += "Error al extraer Estado de Afiliación\n";
                                $msjError += $e->getMessage() + "\n";
                            }
                            
                            try{
								$ciudad = explode("Ciudad:", $affiliation);
                                $ciudad = $ciudad[1];
                                $ciudad = explode(";", $ciudad);
                                $ciudad = $ciudad[0];
                                $ciudad = trim($ciudad);
                            } catch (Exception $e){
                                $msjError += "Error al extraer Ciudad de Afiliación\n";
                                $msjError += $e->getMessage() + "\n";
                            }
                            
                            /*try{
				$country = $pais[$author->getCountry()];
                            } catch (Exception $e){
                                $msjError += "Error al Obtener país\n";
                                $msjError += $e->getMessage() + "\n";
                            }*/
                            
                            try{
				$ciudad_estado = null;
				if ($ciudad or $estado){
					$ciudad_estado = $estado . ', ' . $ciudad;
				}
                            } catch (Exception $e){
                                $msjError += "Error al asignar ciudad o estado\n";
                                $msjError += $e->getMessage() + "\n";
                            }
                            
                            try{
				array_push($art_authors, array('institution' => $institution, 'dependencia' => $dependencia, 'ciudad' => $ciudad_estado, 'country' => ''));
                            } catch (Exception $e){
                                $msjError += "Error al agregar autor\n";
                                $msjError += $e->getMessage() + "\n";
                            }
			//}
		}
            } catch (Exception $e){
                $msjError += "Error al recorrer Autores\n";
                $msjError += $e->getMessage() + "\n";
            }
		
            try{
		$ident = $record->getData('issue')->getIssueIdentification();
            } catch (Exception $e){
                $msjError += "Error al obtener issue\n";
                $msjError += $e->getMessage() + "\n";
            }
		$art_ident = array();
            
            try{
                //volúmen idioma español
                $volumen = explode("Vol.", $ident);
                $volumen = $volumen[1];
                $volumen = explode("Núm.", $volumen);
                $volumen = $volumen[0];
                $volumen = trim($volumen);
            } catch (Exception $e){
                $msjError += "Error al obtener volúmen en español\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                //volúmen idioma portugués
                if($volumen == ""){
                    $volumen = explode("v.", $ident);
                    $volumen = $volumen[1];
                    $volumen = explode("n.", $volumen);
                    $volumen = $volumen[0];
                    $volumen = trim($volumen);
                }
            } catch (Exception $e){
                $msjError += "Error al obtener volúmen en portugués";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                //volúmen idioma inglés
                if($volumen == ""){
                    $volumen = explode("Vol", $ident);
                    $volumen = $volumen[1];
                    $volumen = explode("No", $volumen);
                    $volumen = $volumen[0];
                    $volumen = trim($volumen);
                }
            } catch (Exception $e){
                $msjError += "Error al obtener volúmen en inglés\n";
                $msjError += $e->getMessage() + "\n";
            }
			
			try{
                //volúmen idioma no definido
                if($volumen == ""){
                    $volumen = explode("##issue.vol##", $ident);
                    $volumen = $volumen[1];
                    $volumen = explode(", ##issue.no##", $volumen);
                    $volumen = $volumen[0];
                    $volumen = trim($volumen);
                }
            } catch (Exception $e){
                $msjError += "Error al obtener volúmen en portugués";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                //numero idioma español
				$numero = explode("Núm.", $ident);
                $numero = $numero[1];
                $numero = explode("(", $numero);
                $numero = $numero[0];
                $numero = trim($numero);
            } catch (Exception $e){
                $msjError += "Error al obtener número en español\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                //numero idioma portugués
                if($numero == ""){
                    $numero = explode("n.", $ident);
                    $numero = $numero[1];
                    $numero = explode("(", $numero);
                    $numero = $numero[0];
                    $numero = trim($numero);
                }
            } catch (Exception $e){
                $msjError += "Error al obtener número en portugués\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                //numero idioma inglés
                if($numero == ""){
                    $numero = explode("No", $ident);
                    $numero = $numero[1];
                    $numero = explode("(", $numero);
                    $numero = $numero[0];
                    $numero = trim($numero);
                }
            } catch (Exception $e){
                $msjError += "Error al obtener número en inglés\n";
                $msjError += $e->getMessage() + "\n";
            }
			
			try{
                //numero idioma no definido
				if($numero == ""){
					$numero = explode("##issue.no##", $ident);
					$numero = $numero[1];
					$numero = explode("(", $numero);
					$numero = $numero[0];
					$numero = trim($numero);
				}
            } catch (Exception $e){
                $msjError += "Error al obtener número en español\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                $anio = explode("(", $ident);
                $anio = $anio[1];
                $anio = explode(")", $anio);
                $anio = $anio[0];
                $anio = trim($anio);
            } catch (Exception $e){
                $msjError += "Error al obtener anio\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
		array_push($art_ident, array('vol' => $volumen, 'num' => $numero, 'anio' => $anio));
            } catch (Exception $e){
                $msjError += "Error al construir arreglo anio vol num\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
		$issue = PKPString::html2text($record->getData('issue')->getDescription('es_ES'));
            } catch (Exception $e){
                $msjError += "Error al obtener issue en español\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                if(strlen($issue) == 0){
                    $issue = PKPString::html2text($record->getData('issue')->getDescription($article->getLocale()));
                }
            } catch (Exception $e){
                $msjError += "Error al obtener issue en idioma local del artículo\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                if(strlen($issue) == 0){
                    $issue = PKPString::html2text($record->getData('issue')->getDescription($journal->getPrimaryLocale()));
                }
            } catch (Exception $e){
                $msjError += "Error al obtener issue en idioma local de la revista\n";
                $msjError += $e->getMessage() + "\n";
            }
                
		$art_issue = array();
                
            try{
				$month = explode("Mes:", $issue);
                $month = $month[1];
                $month = explode(";", $month);
                $month = $month[0];
                $month = strtolower(trim($month));
            } catch (Exception $e){
                $msjError += "Error al obtener mes\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
				$part = explode("Parte:", $issue);
                $part = $part[1];
                $part = explode(";", $part);
                $part = $part[0];
                $part = trim($part);
            } catch (Exception $e){
                $msjError += "Error al obtener parte\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
		array_push($art_issue, array('mes' => $meses[$month], 'parte' => $part));
            } catch (Exception $e){
                $msjError += "Error al asignar mes parte\n";
                $msjError += $e->getMessage() + "\n";
            }
		
            try{
                $types = $article->getType('es_ES');
            } catch (Exception $e){
                $msjError += "Error al obtener Tipo en español\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                if(strlen($types) == 0){
                    $types = $article->getType($article->getLocale());
                }
            } catch (Exception $e){
                $msjError += "Error al obtener Tipo en idoma local de artículo\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                if(strlen($types) == 0){
                    $types = $article->getType($journal->getPrimaryLocale());
                }
            } catch (Exception $e){
                $msjError += "Error al obtener Tipo en idioma local de revista\n";
                $msjError += $e->getMessage() + "\n";
            }
            
		$art_type = array();
            
            try{
				$type = explode("Tipo:", $types);
                $type = $type[1];
                $type = explode(";", $type);
                $type = $type[0];
                $type = trim($type);
            } catch (Exception $e){
                $msjError += "Error al obtener tipo\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
				$focus = explode("Enfoque:", $types);
                $focus = $focus[1];
                $focus = explode(";", $focus);
                $focus = $focus[0];
                $focus = trim($focus);
            } catch (Exception $e){
                $msjError += "Error al obtener Enfoque\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
		array_push($art_type, array('type' => $type, 'focus' => $focus));
            } catch (Exception $e){
                $msjError += "Error al asignar tipo enfoque\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                $templateMgr = TemplateManager::getManager();
            } catch (Exception $e){
                $msjError += "Error al obtener template\n";
                $msjError += $e->getMessage() + "\n";
            }
		
            try{
		$templateMgr->assign(array(
                    'journal' => $journal,
                    'article' => $article,
                    'address' => $address,
                    'art_authors_ac' => $art_authors_ac,
                    'art_authors' => $art_authors,
                    'art_issue' => $art_issue,
                    'art_ident' => $art_ident,
                    'art_type' => $art_type,
                    //'keywords' => $keywords,
                    //'keywordsUS' => $keywordsUS,
                    //'disciplines' => $disciplines,
                    'issue' => $record->getData('issue'),
                    'section' => $record->getData('section'),
                    'ident' => $ident,
                    'affiliation'=>$affiliation,
                    'prueba_c' => $prueba
		));
            } catch (Exception $e){
                $msjError += "Error al asignar valores a template\n";
                $msjError += $e->getMessage() + "\n";
            }

            try{
		$subjects = array_merge_recursive(
			stripAssocArray((array) $article->getDiscipline(null)),
			stripAssocArray((array) $article->getSubject(null))
		);
            } catch (Exception $e){
                $msjError += "Error al asignar subject\n";
                $msjError += $e->getMessage() + "\n";
            }
		
		$abstractLan = array();
                
            try{
		if ($article->getAbstract('es_ES')){
			array_push($abstractLan, $idiomas[AppLocale::get3LetterIsoFromLocale('es_ES')]);
		}
            } catch (Exception $e){
                $msjError += "Error al asignar resumen en español\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                if ($article->getAbstract('pt_BR')){
			array_push($abstractLan, $idiomas[AppLocale::get3LetterIsoFromLocale('pt_BR')]);
		}
            } catch (Exception $e){
                $msjError += "Error al asignar resumen en portugués";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                if ($article->getAbstract('en_US')){
			array_push($abstractLan, $idiomas[AppLocale::get3LetterIsoFromLocale('en_US')]);
		}
            } catch (Exception $e){
                $msjError += "Error al asignar resumen en inglés\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                if ($article->getLocale() <> 'en_US' and $article->getLocale() <> 'pt_BR' and $article->getLocale() <> 'es_ES'){
			array_push($abstractLan, $idiomas[AppLocale::get3LetterIsoFromLocale($article->getLocale())]);
                        $abstractO = PKPString::html2text($article->getAbstract($article->getLocale()));
		}
            } catch (Exception $e){
                $msjError += "Error al asignar comparar resúmenes\n";
                $msjError += $e->getMessage() + "\n";
            }
                
                $title = [];
            
            try{
                $title[0] = $article->getTitle('es_ES');
            } catch (Exception $e){
                $msjError += "Error al obtener título en español\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                $title[1] = $article->getTitle('en_US');
            } catch (Exception $e){
                $msjError += "Error al obtener título en inglés\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                $title[2] = $article->getTitle('pt_BR');
            } catch (Exception $e){
                $msjError += "Error al obtener título en español\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                $title[3] = $article->getTitle('it_IT');
            } catch (Exception $e){
                $msjError += "Error al obtener título en italiano\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
                $title[4] = $article->getTitle('fr_FR');
            } catch (Exception $e){
                $msjError += "Error al obtener título en francés\n";
                $msjError += $e->getMessage() + "\n";
            }
			
			try{
                $version = explode("<release>",$version);
                $version = $version[1];
                $version = explode("</release>",$version);
                $version = $version[0];
            } catch (Exception $e) {
                $msjError += "Error al obtener versión\n";
                $msjError += $e->getMessage() + "\n";
            }
            
            try{
		$templateMgr->assign(array(
                    'subject' => isset($subjects[$journal->getPrimaryLocale()])?$subjects[$journal->getPrimaryLocale()]:'',
                    'abstract' => PKPString::html2text($article->getAbstract('es_ES')),
                    'abstractUS' => PKPString::html2text($article->getAbstract('en_US')),
                    'abstractPT' => PKPString::html2text($article->getAbstract('pt_BR')),
                    'abstractO' => $abstractO,
                    'abstractLan' => $abstractLan,
                    'prueba' => AppLocale::get3LetterIsoFromLocale($article->getLocale()),
                    'language' => $idiomas[AppLocale::get3LetterIsoFromLocale($article->getLocale())],
                    'languages' => $journal->getSupportedFormLocaleNames(),
                    'version' => $version,
                    'title' => $title,
					'records' => $this->records,
                    'error' => $msjError
			//'languages' => $journal->getSupportedSubmissionLocaleNames()
		));
            } catch (Exception $e){
                $msjError += "Error al asignar valores a TemplateMgr 2\n";
                $msjError += $e->getMessage() + "\n";
                $templateMgr->assign(array('error' => $msjError));
            }

		return $templateMgr->fetch(dirname(__FILE__) . '/record.tpl');
	}
}

?>

<?php

/**
 * @file plugins/oaiMetadataFormats/biblat/OAIMetadataFormat_BIBLAT.inc.php
 *
 * Copyright (c) 2023 UNAM-DGBSDI
 * Copyright (c) 2023 Edgar Durán
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_BIBLAT
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- BIBLAT.
 */

namespace APP\plugins\oaiMetadataFormats\biblat;

use APP\template\TemplateManager;
use PKP\core\PKPString;
use PKP\i18n\LocaleConversion;
use PKP\oai\OAIMetadataFormat;
use PKP\plugins\PluginRegistry;
use PKP\db\DAORegistry;

class OAIMetadataFormat_BIBLAT extends OAIMetadataFormat
{
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
		
		
		$json = $encryption = openssl_encrypt("xxx".json_encode($arr_result)."xxx", $ciphering, $encryption_key, $options, $encryption_iv);
		//$decryption=openssl_decrypt ($encryption, $ciphering, $encryption_key, $options, $encryption_iv);
		//$json = json_encode($arr_result);
		
		return $json; 
	}
	
	function getRecords(){
		#				0		1				2					3				4					5					6				7			8				9								10				11					12					13
		$tablas = ['issues', 'journals', 'journal_settings', 'submissions', 'publications', 'publication_settings', 'issue_settings', 'authors', 'author_settings', 'controlled_vocab_entry_settings', 'sections', 'section_settings', 'publication_galleys', 'files f inner join submission_files sf on f.file_id = sf.file_id', 'count'];
		
		$sel_journal_id = " (select journal_id from journals where path = '".$this->path."') ";
		#			0
		$where = [  " where COALESCE(year, YEAR(date_published)) in (".$this->years.") and journal_id = ".$sel_journal_id." ",
		#			1
					" where path = '".$this->path."' ",
		#			2
                    " where setting_name not in ('emailSignature', 'authorInformation', 'librarianInformation', 'privacyStatement', 'readerInformation', 'submissionChecklist', 'about') and journal_id = ".$sel_journal_id."",
		#			3
                    " where current_publication_id in ( select publication_id from publication_settings where setting_name = 'issueId' and setting_value in ( '<issue>' ) ) ",
		#			4
					" where publication_id in ( select publication_id from publication_settings where setting_name = 'issueId' and setting_value in ( '<issue>' ) ) ",
		#			5
					" where publication_id in ( select publication_id from publication_settings where setting_name = 'issueId' and setting_value in ( '<issue>' ) ) ",
		#			6
					" where issue_id in ( <issue> ) ",
		#			7
					" where publication_id in  ( select publication_id from publication_settings where setting_name = 'issueId' and setting_value in ( '<issue>' ) ) ",
		#			8
					" where author_id in ( select author_id from authors where publication_id in  ( select publication_id from publication_settings where setting_name = 'issueId' and setting_value in ( '<issue>' ) ) ) ",
		#			9
					" c1 inner join controlled_vocab_entries c2 on c2.controlled_vocab_entry_id = c1.controlled_vocab_entry_id and c1.setting_name = 'submissionKeyword' inner join controlled_vocabs c3 on c3.controlled_vocab_id = c2.controlled_vocab_id where c3.symbolic in ( 'submissionKeyword', 'submissionDiscipline' ) and c3.assoc_id in ( select publication_id from publication_settings where setting_name = 'issueId' and setting_value in ( '<issue>' ) )",
		#			10
					" where journal_id = ".$sel_journal_id." ",
		#			11
					" where section_id in ( select section_id from publications where publication_id in ( select publication_id from publication_settings where setting_name = 'issueId' and setting_value in ( '<issue>' ) ) ) ",
		#			12
					" where publication_id in  ( select publication_id from publication_settings where setting_name = 'issueId' and setting_value in ( '<issue>' ) ) ",
		#			13
					" where f.file_id in (select file_id from submission_files where submission_file_id in ( select submission_file_id from publication_galleys where publication_id in  ( select publication_id from publication_settings where setting_name = 'issueId' and setting_value in ( '<issue>' ) ) ) )",
		#			14
					" where year in (".$this->years.") and journal_id = ".$sel_journal_id." and published=1"
				  ];
                if($this->allYears){
                    $where[0] = " where journal_id = ".$sel_journal_id." ";
                }
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
						$where_p = " where COALESCE(year, YEAR(date_published)) in (".$this->years.") and journal_id = ".$sel_journal_id." ";
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
					if($tabla == 'issues'){
						$where_p = " where COALESCE(year, YEAR(date_published)) in (".$this->years.") and journal_id = ".$sel_journal_id." ";
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
     *
     * @param null|mixed $format
     */
    public function toXml($record, $format = null)
    {
        $article = $record->getData('article');
        $journal = $record->getData('journal');
		
		$version= file_get_contents("dbscripts/xml/version.xml");

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign([
            'journal' => $journal,
            'article' => $article,
            'issue' => $record->getData('issue'),
            'section' => $record->getData('section'),
			'version' => explode("</release>",explode("<release>",$version)[1])[0],
			'records' => $this->records,
			'error' => $this->msjError
        ]);

        $subjects = array_merge_recursive(
            stripAssocArray((array) $article->getDiscipline(null)),
            stripAssocArray((array) $article->getSubject(null))
        );

        $templateMgr->assign([
            'subject' => isset($subjects[$journal->getPrimaryLocale()]) ? $subjects[$journal->getPrimaryLocale()] : '',
            'abstract' => PKPString::html2text($article->getAbstract($article->getLocale())),
            'language' => LocaleConversion::get3LetterIsoFromLocale($article->getLocale())
        ]);

        $plugin = PluginRegistry::getPlugin('oaiMetadataFormats', 'OAIFormatPlugin_BIBLAT');
        return $templateMgr->fetch($plugin->getTemplateResource('record.tpl'));
    }
}

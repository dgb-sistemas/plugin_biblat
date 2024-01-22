{**
 * plugins/oaiMetadataFormats/biblat/record.tpl
 *
 * Copyright (c) 2021 UNAM-DGBSDI
 * Copyright (c) 2021 Edgar Durán
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * BIBLAT-formatted metadata record for an article
 *}
<oai_biblat status="c" type="a" level="m" encLvl="3" catForm="u"
	xmlns="oai_biblat" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="oai_biblat">
	{if $article->getDatePublished()}
		<fixfield id="008">"{$article->getDatePublished()|strtotime|date_format:"%y%m%d %Y"}                        eng  "</fixfield>
	{/if}
        {if $version}
        <varfield id="000" i1="#" i2="#">
            <subfield label="i">3.3.0.11v2.0</subfield>
            <subfield label="v">{$version}</subfield>
	</varfield>
        {/if}
        
	<varfield id="008" i1="#" i2="#">
		<subfield label="e">{$address|escape}</subfield>
	</varfield>
	{if $journal->getSetting('printIssn') or $journal->getSetting('onlineIssn')}
		<varfield id="022" i1="#" i2="#">
			<subfield label="a">{$journal->getSetting('printIssn')|escape}</subfield>
			<subfield label="b">{$journal->getSetting('onlineIssn')|escape}</subfield>
		</varfield>
	{/if}
	
	{if $article->getStoredPubId('doi')}
		<varfield id="024" i1="#" i2="#">
			<subfield label="a">{$article->getStoredPubId('doi')}</subfield>
		</varfield>
	{/if}
	
	{if $language}
		<varfield id="041" i1="#" i2="#">
			<subfield label="a">{$language}</subfield>
		</varfield>
	{/if}
	
	{assign var=authors value=$article->getAuthors()}
	{foreach from=$authors item=author key=key}
			<varfield id="100" i1="#" i2="#">
				<subfield label="a">{$author->getFullName(false,true)|escape}</subfield>
				{**assign var=affiliation value=$author->getAffiliation($journal->getPrimaryLocale())**}				
				{if $author->getEmail()}<subfield label="6">{$author->getEmail()|escape}</subfield>{/if}
				{if $author->getUrl()}<subfield label="0">{$author->getUrl()|escape}</subfield>{/if}
				{if $author->getData('orcid')}<subfield label="0">{$author->getData('orcid')|escape}</subfield>{/if}
				{if strlen($affiliation) != 0 or strlen($art_authors[$key].country) != 0}
					<varfield id="120" i1="#" i2="#">
						{if strlen($art_authors[$key].institution) == 0}
								{if $affiliation} <subfield label="u">{$affiliation|escape}</subfield> {/if}
								{if $art_authors[$key].country} <subfield label="x">{$art_authors[$key].country|escape}</subfield> {/if}
						{* elseif $art_authors[$key].institution or $art_authors[$key].dependencia or $art_authors[$key].ciudad or $art_authors[$key].country *}
						{else}
								{if $art_authors[$key].institution} <subfield label="u">{$art_authors[$key].institution|escape}</subfield> {/if}
								{if $art_authors[$key].dependencia} <subfield label="v">{$art_authors[$key].dependencia|escape}</subfield> {/if}
								{if $art_authors[$key].ciudad} <subfield label="w">{$art_authors[$key].ciudad|escape}</subfield> {/if}
								{if $art_authors[$key].country} <subfield label="x">{$art_authors[$key].country|escape}</subfield> {/if}
						{/if}
					</varfield>
				{/if}
			</varfield>
	{/foreach}
	
	{foreach name=art_author from=$art_authors_ac item=auth}
			<varfield id="110" i1="#" i2="#">
				<subfield label="a">{$auth.institution|escape}</subfield>
				<subfield label="b">{$auth.dependencia|escape}</subfield>
				<subfield label="c">{$auth.country|escape}</subfield>
			</varfield>
	{/foreach}
	
	{assign var=journal_name value=$journal->getName($journal->getPrimaryLocale())}
	{if $journal->getSetting('publisherInstitution')}
		{assign var=publisher value=$journal->getSetting('publisherInstitution')}
	{/if}
	
	{if $journal_name} 
		<varfield id="222" i1="#" i2="#">
			<subfield label="a">{$journal_name|escape}</subfield>
		</varfield>
	{/if}
	
	{if $title['en_US'] and $article->getLocale() <> 'en_US'}
		<varfield id="242" i1="#" i2="#">
			<subfield label="a">{$title['en_US']|escape}</subfield>
			<subfield label="y">eng</subfield>
		</varfield>
	{/if}
        
	{if $title['es_ES'] and $article->getLocale() <> 'es_ES'}
		<varfield id="242" i1="#" i2="#">
			<subfield label="a">{$title['es_ES']|escape}</subfield>
			<subfield label="y">spa</subfield>
		</varfield>
	{/if}
        
	{if $title['pt_BR'] and $article->getLocale() <> 'pt_BR'}
		<varfield id="242" i1="#" i2="#">
			<subfield label="a">{$title['pt_BR']|escape}</subfield>
			<subfield label="y">por</subfield>
		</varfield>
	{/if}
	
	{if $title[$article->getLocale()]}
		<varfield id="245" i1="#" i2="#">
			<subfield label="a">{$title[$article->getLocale()]|escape}</subfield>
		</varfield>
	{/if}
	
	{if $publisher or $art_ident[0].anio}
		<varfield id="260" i1="#" i2="#">
			{if $publisher} <subfield label="b">{$publisher}</subfield> {/if}
			{if $art_ident[0].anio} <subfield label="c">{$art_ident[0].anio}</subfield> {/if}
		</varfield>
	{/if}
	
	{if $art_ident[0].vol or $art_ident[0].num or $art_ident[0].mes or $art_ident[0].parte or $article->getPages()}
		<varfield id="300" i1="#" i2="#">
			{if $art_ident[0].vol} <subfield label="a">V{$art_ident[0].vol|escape}</subfield> {/if}
			{if $art_ident[0].num} <subfield label="b">N{$art_ident[0].num|escape}</subfield> {/if}
			{if $art_issue[0].mes} <subfield label="c">{$art_issue[0].mes|escape}</subfield> {/if}
			{if $art_issue[0].parte} <subfield label="d">{$art_issue[0].parte|escape}</subfield> {/if}
			{if $article->getPages()} <subfield label="e">P{$article->getPages()|escape}</subfield> {/if}
		</varfield>
	{/if}
	
	{if $abstract or $abstractUS or $abstractPT or $abstractO}
		<varfield id="520" i1="#" i2="#">		
			{if $abstract} <subfield label="a">{$abstract|escape}</subfield> {/if}
                        {if $abstractPT} <subfield label="p">{$abstractPT|escape}</subfield> {/if}
			{if $abstractUS} <subfield label="i">{$abstractUS|escape}</subfield> {/if}
                        {if $abstractO} <subfield label="o">{$abstractO|escape}</subfield> {/if}
		</varfield>
	{/if}
	
	{if $abstractLan}
		<varfield id="546" i1="" i2="">
			<subfield label="a">
			{foreach name=languajes from=$abstractLan item=lan}
				{$lan} {if !$smarty.foreach.languajes.last}, {/if}
			{/foreach}
			</subfield>
		</varfield>
	{/if}
	
	{if $art_type[0].type or $art_type[0].focus}
		<varfield id="590" i1="#" i2="#">
			{if $art_type[0].type} <subfield label="a">{$art_type[0].type|escape}</subfield> {/if}
			{if $art_type[0].focus} <subfield label="b">{$art_type[0].focus|escape}</subfield> {/if}
		</varfield>
	{/if}
	
	{if $disciplines}
		<varfield id="650" i1="#" i2="#">
			<subfield label="a">
			{foreach from=$disciplines item=discipline}
				{foreach name=disciplines from=$discipline item=disciplineItem}
					{$disciplineItem|escape}{if !$smarty.foreach.disciplines.last}, {/if}
				{/foreach}
			{/foreach}
			</subfield>
		</varfield>
	{/if}
	
	{if $keywords}
		<varfield id="653" i1="#" i2="#">
			<subfield label="a">
			{foreach from=$keywords item=keyword}
				{foreach name=keywords from=$keyword item=keywordItem}
					{$keywordItem|escape}{if !$smarty.foreach.keywords.last}, {/if}
				{/foreach}
			{/foreach}
			</subfield>
		</varfield>
	{/if}
	
	{if $keywordsUS}
		<varfield id="654" i1="#" i2="#">			
			<subfield label="a">
			{foreach from=$keywordsUS item=keyword}
				{foreach name=keywords from=$keyword item=keywordItem}
					{$keywordItem|escape}{if !$smarty.foreach.keywords.last}, {/if}
				{/foreach}
			{/foreach}
			</subfield>
		</varfield>
	{/if}
	

	{assign var=identifyType value=$section->getIdentifyType($journal->getPrimaryLocale())}
	{if $identifyType}<varfield id="655" i1=" " i2="7">
		<subfield label="a">{$identifyType|escape}</subfield>
	</varfield>{/if}

	{foreach from=$article->getGalleys() item=galley}
		<varfield id="856" i1=" " i2=" ">
			<subfield label="q">{$galley->getFileType()|escape}</subfield>
			<subfield label="u">{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()|escape}/{$galley->getBestGalleyId()}</subfield>
		</varfield>
	{/foreach}
	

	{if $article->getCoverage($journal->getPrimaryLocale())}
		<varfield id="500" i1=" " i2=" ">
			<subfield label="a">{$article->getCoverage($journal->getPrimaryLocale())|escape}</subfield>
		</varfield>
	{/if}
        
        {if $min}
            <varfield id="min" i1="#" i2="#">
                {$min}
            </varfield> 
        {/if}
	
	{if $records}
		<varfield id="db" i1="#" i2="#">
		{foreach from=$records item=record key=key}
			<subfield label="{$record['tabla']}">{$record['rows']}</subfield>
		{/foreach}
		{$records=""}
		</varfield>
	{/if}
	
	{if $error}
		<varfield id="err" i1="#" i2="#">
			<subfield label="e">{$error}</subfield>
		</varfield>
	{/if}

</oai_biblat>

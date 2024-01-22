<?php

/**
 * @file plugins/oaiMetadataFormats/marc/OAIMetadataFormatPlugin_MARC.inc.php
 *
 * Copyright (c) 2021 UNAM-DGBSDI
 * Copyright (c) 2021 Edgar Durán
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormatPlugin_BIBLAT
 * @ingroup oai_format
 * @see OAI
 *
 * @brief marc metadata format plugin for OAI.
 */

import('lib.pkp.classes.plugins.OAIMetadataFormatPlugin');

class OAIMetadataFormatPlugin_BIBLAT extends OAIMetadataFormatPlugin {

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'OAIFormatPlugin_BIBLAT';
	}

	function getDisplayName() {
		$name = 'Formato BIBLAT de Metadatos v2.0';
		if (strpos(__('plugins.oaiMetadata.biblat.displayName'), '##') === false)
			return __('plugins.OAIMetadata.biblat.displayName');
		else
			return $name;
	}

	function getDescription() {
		$description = 'Estructura los metadatos de forma que son consistentes con el formato BIBLAT.';
                if (strpos(__('plugins.OAIMetadata.biblat.description'), '##') === false)
			return __('plugins.OAIMetadata.biblat.description');
		else
			return $description;
	}

	function getFormatClass() {
		return 'OAIMetadataFormat_BIBLAT';
	}

	function getMetadataPrefix() {
		return 'oai_biblat';
	}

	function getSchema() {
		return 'https://biblat.unam.mx';
	}

	function getNamespace() {
		return 'oai_biblat_240v20';
	}
}

?>

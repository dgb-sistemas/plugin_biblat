<?php

/**
 * @file plugins/oaiMetadataFormats/biblat/OAIMetadataFormatPlugin_BIBLAT.inc.php
 *
 * Copyright (c) 2023 UNAM-DGBSDI
 * Copyright (c) 2023 Edgar Durán
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormatPlugin_BIBLAT
 * @ingroup oai_format
 * @see OAI
 *
 * @brief biblat metadata format plugin for OAI.
 */

namespace APP\plugins\oaiMetadataFormats\biblat;

use PKP\plugins\OAIMetadataFormatPlugin;

class OAIMetadataFormatPlugin_BIBLAT extends OAIMetadataFormatPlugin
{
    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     *
     * @return string name of plugin
     */
    public function getName()
    {
        return 'OAIFormatPlugin_BIBLAT';
    }

    public function getDisplayName()
    {
        $name = 'Formato BIBLAT de Metadatos v2.0';
		if (strpos(__('plugins.oaiMetadata.biblat.displayName'), '##') === false)
			return __('plugins.OAIMetadata.biblat.displayName');
		else
			return $name;
    }

    public function getDescription()
    {
        $description = 'Estructura los metadatos de forma que son consistentes con el formato BIBLAT.';
                if (strpos(__('plugins.OAIMetadata.biblat.description'), '##') === false)
			return __('plugins.OAIMetadata.biblat.description');
		else
			return $description;
    }

    public function getFormatClass()
    {
        return '\APP\plugins\oaiMetadataFormats\biblat\OAIMetadataFormat_BIBLAT';
    }

    public static function getMetadataPrefix()
    {
        return 'oai_biblat';
    }

    public static function getSchema()
    {
        return 'https://biblat.unam.mx';
    }

    public static function getNamespace()
    {
        return 'oai_biblat_340v20';
    }
}

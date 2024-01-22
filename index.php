<?php

/**
 * @file plugins/oaiMetadataFormats/biblat/index.php
 *
 * Copyright (c) 2021 UNAM-DGBSDI
 * Copyright (c) 2021 Edgar Durán
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_oaiMetadata
 * @brief Wrapper for the OAI BIBLAT format plugin.
 *
 */

require_once('OAIMetadataFormatPlugin_BIBLAT.inc.php');
require_once('OAIMetadataFormat_BIBLAT.inc.php');

return new OAIMetadataFormatPlugin_BIBLAT();



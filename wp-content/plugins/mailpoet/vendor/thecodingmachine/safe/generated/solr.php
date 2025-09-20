<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\SolrException;
function solr_get_version(): string
{
 error_clear_last();
 $result = \solr_get_version();
 if ($result === false) {
 throw SolrException::createFromPhpError();
 }
 return $result;
}

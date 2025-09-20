<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\LdapException;
function ldap_add_ext($link_identifier, string $dn, array $entry, array $serverctrls = null)
{
 error_clear_last();
 $result = \ldap_add_ext($link_identifier, $dn, $entry, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_add($link_identifier, string $dn, array $entry, array $serverctrls = null): void
{
 error_clear_last();
 $result = \ldap_add($link_identifier, $dn, $entry, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_bind_ext($link_identifier, ?string $bind_rdn = null, ?string $bind_password = null, array $serverctrls = null)
{
 error_clear_last();
 $result = \ldap_bind_ext($link_identifier, $bind_rdn, $bind_password, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_bind($link_identifier, ?string $bind_rdn = null, ?string $bind_password = null): void
{
 error_clear_last();
 $result = \ldap_bind($link_identifier, $bind_rdn, $bind_password);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_control_paged_result_response($link, $result, ?string &$cookie = null, ?int &$estimated = null): void
{
 error_clear_last();
 $result = \ldap_control_paged_result_response($link, $result, $cookie, $estimated);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_control_paged_result($link, int $pagesize, bool $iscritical = false, string $cookie = ""): void
{
 error_clear_last();
 $result = \ldap_control_paged_result($link, $pagesize, $iscritical, $cookie);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_count_entries($link_identifier, $result_identifier): int
{
 error_clear_last();
 $result = \ldap_count_entries($link_identifier, $result_identifier);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_delete_ext($link_identifier, string $dn, array $serverctrls = null)
{
 error_clear_last();
 $result = \ldap_delete_ext($link_identifier, $dn, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_delete($link_identifier, string $dn, array $serverctrls = null): void
{
 error_clear_last();
 $result = \ldap_delete($link_identifier, $dn, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_exop_passwd($link, string $user = "", string $oldpw = "", string $newpw = "", array &$serverctrls = null)
{
 error_clear_last();
 $result = \ldap_exop_passwd($link, $user, $oldpw, $newpw, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_exop_whoami($link): string
{
 error_clear_last();
 $result = \ldap_exop_whoami($link);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_exop($link, string $reqoid, string $reqdata = null, ?array $serverctrls = null, ?string &$retdata = null, ?string &$retoid = null)
{
 error_clear_last();
 $result = \ldap_exop($link, $reqoid, $reqdata, $serverctrls, $retdata, $retoid);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_explode_dn(string $dn, int $with_attrib): array
{
 error_clear_last();
 $result = \ldap_explode_dn($dn, $with_attrib);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_first_attribute($link_identifier, $result_entry_identifier): string
{
 error_clear_last();
 $result = \ldap_first_attribute($link_identifier, $result_entry_identifier);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_first_entry($link_identifier, $result_identifier)
{
 error_clear_last();
 $result = \ldap_first_entry($link_identifier, $result_identifier);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_free_result($result_identifier): void
{
 error_clear_last();
 $result = \ldap_free_result($result_identifier);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_get_attributes($link_identifier, $result_entry_identifier): array
{
 error_clear_last();
 $result = \ldap_get_attributes($link_identifier, $result_entry_identifier);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_get_dn($link_identifier, $result_entry_identifier): string
{
 error_clear_last();
 $result = \ldap_get_dn($link_identifier, $result_entry_identifier);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_get_entries($link_identifier, $result_identifier): array
{
 error_clear_last();
 $result = \ldap_get_entries($link_identifier, $result_identifier);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_get_option($link_identifier, int $option, &$retval): void
{
 error_clear_last();
 $result = \ldap_get_option($link_identifier, $option, $retval);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_get_values_len($link_identifier, $result_entry_identifier, string $attribute): array
{
 error_clear_last();
 $result = \ldap_get_values_len($link_identifier, $result_entry_identifier, $attribute);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_get_values($link_identifier, $result_entry_identifier, string $attribute): array
{
 error_clear_last();
 $result = \ldap_get_values($link_identifier, $result_entry_identifier, $attribute);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_list($link_identifier, string $base_dn, string $filter, array $attributes = null, int $attrsonly = 0, int $sizelimit = -1, int $timelimit = -1, int $deref = LDAP_DEREF_NEVER, array $serverctrls = null)
{
 error_clear_last();
 if ($serverctrls !== null) {
 $result = \ldap_list($link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit, $deref, $serverctrls);
 } elseif ($deref !== LDAP_DEREF_NEVER) {
 $result = \ldap_list($link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit, $deref);
 } elseif ($timelimit !== -1) {
 $result = \ldap_list($link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit);
 } elseif ($sizelimit !== -1) {
 $result = \ldap_list($link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit);
 } elseif ($attrsonly !== 0) {
 $result = \ldap_list($link_identifier, $base_dn, $filter, $attributes, $attrsonly);
 } elseif ($attributes !== null) {
 $result = \ldap_list($link_identifier, $base_dn, $filter, $attributes);
 } else {
 $result = \ldap_list($link_identifier, $base_dn, $filter);
 }
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_mod_add_ext($link_identifier, string $dn, array $entry, array $serverctrls = null)
{
 error_clear_last();
 $result = \ldap_mod_add_ext($link_identifier, $dn, $entry, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_mod_add($link_identifier, string $dn, array $entry, array $serverctrls = null): void
{
 error_clear_last();
 $result = \ldap_mod_add($link_identifier, $dn, $entry, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_mod_del_ext($link_identifier, string $dn, array $entry, array $serverctrls = null)
{
 error_clear_last();
 $result = \ldap_mod_del_ext($link_identifier, $dn, $entry, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_mod_del($link_identifier, string $dn, array $entry, array $serverctrls = null): void
{
 error_clear_last();
 $result = \ldap_mod_del($link_identifier, $dn, $entry, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_mod_replace_ext($link_identifier, string $dn, array $entry, array $serverctrls = null)
{
 error_clear_last();
 $result = \ldap_mod_replace_ext($link_identifier, $dn, $entry, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_mod_replace($link_identifier, string $dn, array $entry, array $serverctrls = null): void
{
 error_clear_last();
 $result = \ldap_mod_replace($link_identifier, $dn, $entry, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_modify_batch($link_identifier, string $dn, array $entry, array $serverctrls = null): void
{
 error_clear_last();
 $result = \ldap_modify_batch($link_identifier, $dn, $entry, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_next_attribute($link_identifier, $result_entry_identifier): string
{
 error_clear_last();
 $result = \ldap_next_attribute($link_identifier, $result_entry_identifier);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_parse_exop($link, $result, ?string &$retdata = null, ?string &$retoid = null): void
{
 error_clear_last();
 $result = \ldap_parse_exop($link, $result, $retdata, $retoid);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_parse_result($link, $result, ?int &$errcode, ?string &$matcheddn = null, ?string &$errmsg = null, ?array &$referrals = null, ?array &$serverctrls = null): void
{
 error_clear_last();
 $result = \ldap_parse_result($link, $result, $errcode, $matcheddn, $errmsg, $referrals, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_read($link_identifier, string $base_dn, string $filter, array $attributes = null, int $attrsonly = 0, int $sizelimit = -1, int $timelimit = -1, int $deref = LDAP_DEREF_NEVER, array $serverctrls = null)
{
 error_clear_last();
 if ($serverctrls !== null) {
 $result = \ldap_read($link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit, $deref, $serverctrls);
 } elseif ($deref !== LDAP_DEREF_NEVER) {
 $result = \ldap_read($link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit, $deref);
 } elseif ($timelimit !== -1) {
 $result = \ldap_read($link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit);
 } elseif ($sizelimit !== -1) {
 $result = \ldap_read($link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit);
 } elseif ($attrsonly !== 0) {
 $result = \ldap_read($link_identifier, $base_dn, $filter, $attributes, $attrsonly);
 } elseif ($attributes !== null) {
 $result = \ldap_read($link_identifier, $base_dn, $filter, $attributes);
 } else {
 $result = \ldap_read($link_identifier, $base_dn, $filter);
 }
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_rename_ext($link_identifier, string $dn, string $newrdn, string $newparent, bool $deleteoldrdn, array $serverctrls = null)
{
 error_clear_last();
 $result = \ldap_rename_ext($link_identifier, $dn, $newrdn, $newparent, $deleteoldrdn, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_rename($link_identifier, string $dn, string $newrdn, string $newparent, bool $deleteoldrdn, array $serverctrls = null): void
{
 error_clear_last();
 $result = \ldap_rename($link_identifier, $dn, $newrdn, $newparent, $deleteoldrdn, $serverctrls);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_sasl_bind($link, string $binddn = null, string $password = null, string $sasl_mech = null, string $sasl_realm = null, string $sasl_authc_id = null, string $sasl_authz_id = null, string $props = null): void
{
 error_clear_last();
 $result = \ldap_sasl_bind($link, $binddn, $password, $sasl_mech, $sasl_realm, $sasl_authc_id, $sasl_authz_id, $props);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_search($link_identifier, string $base_dn, string $filter, array $attributes = null, int $attrsonly = 0, int $sizelimit = -1, int $timelimit = -1, int $deref = LDAP_DEREF_NEVER, array $serverctrls = null)
{
 error_clear_last();
 if ($serverctrls !== null) {
 $result = \ldap_search($link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit, $deref, $serverctrls);
 } elseif ($deref !== LDAP_DEREF_NEVER) {
 $result = \ldap_search($link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit, $deref);
 } elseif ($timelimit !== -1) {
 $result = \ldap_search($link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit);
 } elseif ($sizelimit !== -1) {
 $result = \ldap_search($link_identifier, $base_dn, $filter, $attributes, $attrsonly, $sizelimit);
 } elseif ($attrsonly !== 0) {
 $result = \ldap_search($link_identifier, $base_dn, $filter, $attributes, $attrsonly);
 } elseif ($attributes !== null) {
 $result = \ldap_search($link_identifier, $base_dn, $filter, $attributes);
 } else {
 $result = \ldap_search($link_identifier, $base_dn, $filter);
 }
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
 return $result;
}
function ldap_set_option($link_identifier, int $option, $newval): void
{
 error_clear_last();
 $result = \ldap_set_option($link_identifier, $option, $newval);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}
function ldap_unbind($link_identifier): void
{
 error_clear_last();
 $result = \ldap_unbind($link_identifier);
 if ($result === false) {
 throw LdapException::createFromPhpError();
 }
}

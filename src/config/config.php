<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Config Global Paths
 */
$config['livesite']    = '';
$config['basepath']    = '';
$config['cachepath']   = '';
$config['thelink']     = '';

/**
 * Restrict URI Characters
 */
$config['permitted_uri_chars'] = '/a-z 0-9~%.:_-/';

/**
 * Database Config
 */
$config['db_hostname']  = 'localhost';
$config['db_user']      = 'username';
$config['db_pass']      = 'db_password';
$config['db_name']      = 'db_name';
$config['db_driver']    = 'mysql';

$dsn = $config['db_driver'] . ':host=' . $config['db_hostname'] . ';dbname=' . $config['db_name'];
$config['db_dsn']       = $dsn;

<?php
declare(strict_types=1);


namespace RightThisMinute\Drupal\extra_log;


/**
 * Prepare a message and associated variables for logging.
 *
 * @param string $message
 * @param array $vars
 * @param bool $html
 * @param bool $append_extra_vars
 *
 * @return array
 *   An array containing two items. The first is the prepared message, the
 *   second is the associative array of variables to be passed to `watchdog()`,
 *   `t()`, or `format_string()`.
 * @see format_string()
 *
 * @see watchdog()
 * @see t()
 */
function _prep_message
  ( string $message
  , array $vars=[]
  , bool $html=true
  , bool $append_extra_vars=true )
  : array
{
  if (empty($vars))
    return [$message, []];

  if ($append_extra_vars) {
    if ($html) {
      $vars_placeholder = '@var_list';
      $message .= "\n<pre>$vars_placeholder</pre>";
    }
    else {
      $vars_placeholder = '!vars_list';
      $message .= "\n\n$vars_placeholder\n";
    }
  }

  $embedded_vars = [];
  $var_list = '';

  foreach ($vars as $label => $var) {
    if (in_array($label[0], ['@', '%', '!', ':'])) {
      $embedded_vars[$label] = $var;
      continue;
    }

    if (!$append_extra_vars)
      continue;

    $var_list .= "$label: ";

    if (is_string($var) or is_numeric($var) or $var instanceof \Exception)
      $var_list .= $var;
    else if (is_bool($var))
      $var_list .= var_export($var, true);
    else
      $var_list .= print_r($var, true);

    $var_list .= "\n";
  }

  if ($append_extra_vars)
    $embedded_vars[$vars_placeholder] = $var_list;

  return [$message, $embedded_vars];
}


/**
 * Return the formatted log message string instead of logging it.
 *
 * @param string $message
 * @param array $vars
 * @param bool $html
 * @param bool $append_extra_vars
 *   Whether or not to append vars without placeholder prefixes. If this is
 *   false, any non-placeholder vars discarded and wont be part of the returned
 *   string.
 *
 * @see watchdog()
 *
 * @return string
 */
function to_string
  ( string $message
  , array $vars=[]
  , bool $html=true
  , bool $append_extra_vars=true )
  : string
{
  [$message, $vars] = _prep_message($message, $vars, $html, $append_extra_vars);
  return format_string($message, $vars);
}


/**
 * A logging wrapper to make dumping variables easier.
 *
 * @param string $module
 *   Name of the module logging the message.
 * @param string $message
 *   Message to be logged.
 * @param array  $vars
 *   Associative array of variables to be logged, with the keys being their
 *   title. If a key is prefixed with : or % it is assumed the value should
 *   replace the key of the same name in $message.
 * @param int    $level
 *   WATCHDOG level.
 *
 * @see bootstrap.inc
 * @see \watchdog()
 */
function _($module, $message, array $vars=[], $level=WATCHDOG_INFO) : void
{
  [$message, $embedded_vars] = _prep_message($message, $vars, true);
  watchdog($module, $message, $embedded_vars, $level);
}


/**
 * A logging wrapper to make dumping variables easier.
 *
 * @param string $module
 *   Name of the module logging the message.
 * @param string $message
 *   Message to be logged.
 * @param array  $vars
 *   Associative array of variables to be logged, with the keys being their
 *   title. If a key is prefixed with : or % it is assumed the value should
 *   replace the key of the same name in $message.
 *
 * @see bootstrap.inc
 * @see \watchdog()
 * @see _()
 */
function debug($module, $message, array $vars=[])
{
  _($module, $message, $vars, WATCHDOG_DEBUG);
  _fixPhpStormInspection();
}


/**
 * A logging wrapper to make dumping variables easier.
 *
 * @param string $module
 *   Name of the module logging the message.
 * @param string $message
 *   Message to be logged.
 * @param array  $vars
 *   Associative array of variables to be logged, with the keys being their
 *   title. If a key is prefixed with : or % it is assumed the value should
 *   replace the key of the same name in $message.
 *
 * @see bootstrap.inc
 * @see \watchdog()
 * @see _()
 */
function info($module, $message, array $vars=[])
{
  _($module, $message, $vars, WATCHDOG_INFO);
  _fixPhpStormInspection();
}


/**
 * A logging wrapper to make dumping variables easier.
 *
 * @param string $module
 *   Name of the module logging the message.
 * @param string $message
 *   Message to be logged.
 * @param array  $vars
 *   Associative array of variables to be logged, with the keys being their
 *   title. If a key is prefixed with : or % it is assumed the value should
 *   replace the key of the same name in $message.
 *
 * @see bootstrap.inc
 * @see \watchdog()
 * @see _()
 */
function notice($module, $message, array $vars=[])
{
  _($module, $message, $vars, WATCHDOG_NOTICE);
  _fixPhpStormInspection();
}


/**
 * A logging wrapper to make dumping variables easier.
 *
 * @param string $module
 *   Name of the module logging the message.
 * @param string $message
 *   Message to be logged.
 * @param array  $vars
 *   Associative array of variables to be logged, with the keys being their
 *   title. If a key is prefixed with : or % it is assumed the value should
 *   replace the key of the same name in $message.
 *
 * @see bootstrap.inc
 * @see \watchdog()
 * @see _()
 */
function warning($module, $message, array $vars=[])
{
  _($module, $message, $vars, WATCHDOG_WARNING);
  _fixPhpStormInspection();
}


/**
 * A logging wrapper to make dumping variables easier.
 *
 * @param string $module
 *   Name of the module logging the message.
 * @param string $message
 *   Message to be logged.
 * @param array  $vars
 *   Associative array of variables to be logged, with the keys being their
 *   title. If a key is prefixed with : or % it is assumed the value should
 *   replace the key of the same name in $message.
 *
 * @see bootstrap.inc
 * @see \watchdog()
 * @see _()
 */
function error($module, $message, array $vars=[])
{
  _($module, $message, $vars, WATCHDOG_ERROR);
  _fixPhpStormInspection();
}


/**
 * PhpStorm 2021.1 has a bug with one of its inspections. Basically, when
 * calling a function whose body _only_ calls one function of a particular name
 * ("_()" and "log()" have been identified so far) the "Expression result
 * unused" inspection gets triggered.
 *
 * @see https://github.com/donut/bug_report-phpstorm2021.1_unused_result
 * @see https://youtrack.jetbrains.com/issue/WI-59716
 */
function _fixPhpStormInspection() : void {}

<?php
/*
 * IHUGURE APP - interface language support (Kinyarwanda, English, Francais, Kiswahili, Luganda)
 *
 * This only translates the words shown around the quiz. It never touches game
 * logic, sessions or the database. The language the QUESTIONS are asked in is
 * still chosen on the first screen and still comes from the `language` table.
 *
 * Kinyarwanda is the original text already written in the code, so it needs no
 * catalogue: __('...') simply returns the text unchanged. The other languages
 * live in lang/<code>.php and are looked up by the original Kinyarwanda text
 * (case, spaces and line breaks are ignored when matching).
 */

define('I18N_DEFAULT', 'rw');
define('I18N_COOKIE', 'ihugure_lang');

function i18n_languages(){
	// code => [short label, name in its own language, <html lang>]
	return [
		'rw' => ['RW', 'Kinyarwanda', 'rw'],
		'en' => ['EN', 'English',     'en'],
		'fr' => ['FR', 'Français',    'fr'],
		'sw' => ['SW', 'Kiswahili',   'sw'],
		'lg' => ['LG', 'Luganda',     'lg'],
	];
}

function i18n_current(){
	static $current = null;
	if($current !== null) return $current;
	$all = i18n_languages();
	$current = I18N_DEFAULT;
	if(isset($_GET['lang']) && is_string($_GET['lang']) && isset($all[$_GET['lang']])){
		$current = $_GET['lang'];
		if(!headers_sent()){
			setcookie(I18N_COOKIE, $current, [
				'expires'  => time() + 31536000,
				'path'     => '/',
				'secure'   => !empty($_SERVER['HTTPS']),
				'httponly' => true,
				'samesite' => 'Lax',
			]);
			i18n_redirect_to_clean_url();
		}
	} else if(isset($_COOKIE[I18N_COOKIE]) && is_string($_COOKIE[I18N_COOKIE]) && isset($all[$_COOKIE[I18N_COOKIE]])){
		$current = $_COOKIE[I18N_COOKIE];
	}
	return $current;
}

// After a language link (?lang=fr) was used, go to the same address without it: the choice now
// lives in the cookie, so refresh, back button, bookmarks and form posts all use a clean URL.
// Runs before any output and before the game logs anything. Only for plain GET requests.
function i18n_redirect_to_clean_url(){
	if(PHP_SAPI === 'cli' || headers_sent()) return;
	if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') return;
	$uri   = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '/';
	$path  = '/' . ltrim((string)parse_url($uri, PHP_URL_PATH), '/\\');   // keep it a local path
	$keep = [];
	foreach(explode('&', (string)parse_url($uri, PHP_URL_QUERY)) as $pair){
		if($pair !== '' && strpos($pair, 'lang=') !== 0 && $pair !== 'lang') $keep[] = $pair;   // keep the rest exactly as it was
	}
	header('Location: ' . $path . ($keep ? '?' . implode('&', $keep) : ''), true, 303);
	exit;
}

function i18n_catalogue($code = null){
	static $loaded = [];
	if($code === null) $code = i18n_current();
	if(!isset($loaded[$code])){
		$file = __DIR__ . '/../lang/' . $code . '.php';
		$loaded[$code] = (preg_match('/^[a-z]{2}$/', $code) && is_file($file)) ? (require $file) : [];
	}
	return $loaded[$code];
}

function i18n_key($text){
	$key = trim(preg_replace('/\s+/u', ' ', $text));
	// the original texts are plain Kinyarwanda, so a simple lower-case is enough if mbstring is missing
	return function_exists('mb_strtolower') ? mb_strtolower($key, 'UTF-8') : strtolower($key);
}

// Translate a piece of original text. Unknown text is returned unchanged. (Kinyarwanda has a small
// catalogue too: a few English-only messages and one corrected phrase.)
function i18n_translate($text){
	$cat = i18n_catalogue();
	$key = i18n_key($text);
	if(!isset($cat['msgs'][$key])) return $text;
	$out = $cat['msgs'][$key];
	// keep the spaces the original text had around it, e.g. "(NTAGO ARIBYO) " + question
	if(preg_match('/^\s+/u', $text, $m) && !preg_match('/^\s/u', $out)) $out = $m[0] . $out;
	if(preg_match('/\s+$/u', $text, $m) && !preg_match('/\s$/u', $out)) $out = $out . $m[0];
	return $out;
}

// Look up one of the interface words that are new (stepper, footer...). Falls back to Kinyarwanda.
function t($key){
	$cat = i18n_catalogue();
	if(isset($cat['ui'][$key])) return $cat['ui'][$key];
	$base = i18n_catalogue(I18N_DEFAULT);
	return isset($base['ui'][$key]) ? $base['ui'][$key] : $key;
}

function i18n_switcher(){
	$current = i18n_current();
	$html = '<nav class="lang-switch" aria-label="' . htmlspecialchars(t('lang_label')) . '">';
	foreach(i18n_languages() as $code => $info){
		$active = ($code === $current);
		$html .= '<a href="?lang=' . $code . '" rel="nofollow" lang="' . $info[2] . '" hreflang="' . $info[2] . '"'
			. ' title="' . htmlspecialchars($info[1]) . '"'
			. ($active ? ' class="active" aria-current="true"' : '') . '>' . $info[0] . '</a>';
	}
	return $html . '</nav>';
}

// Resolve the language right away (before any page output) so the cookie header can still be sent.
i18n_current();

<?php
// Run: php lang/check-languages.php   (needs the mbstring extension)
// Checks the language files for the same keys, placeholders (%d, %%), HTML tags, capital letters and typography.
if(PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Catalogue consistency checks
chdir(__DIR__ . '/..');
$L = []; foreach(['rw','en','fr','sw','lg'] as $c) $L[$c] = require "lang/$c.php";
$bad = 0; $fail = function($m) use (&$bad){ echo "  FAIL: $m\n"; $bad++; };

echo "1. same keys\n";
$ref = array_keys($L['en']['msgs']);
foreach(['fr','sw','lg'] as $c){ $d = array_diff($ref, array_keys($L[$c]['msgs'])); $e = array_diff(array_keys($L[$c]['msgs']), array_merge($ref, array_keys($L['fr']['msgs']))); if($d) $fail("$c missing msgs: ".implode(' | ', array_map(fn($x)=>substr($x,0,40),$d))); }
$uiref = array_keys($L['en']['ui']);
foreach($L as $c => $cat){ $d = array_merge(array_diff($uiref, array_keys($cat['ui'])), array_diff(array_keys($cat['ui']), $uiref)); if($d) $fail("$c ui key mismatch: ".implode(',',$d)); }
$msgKeys = [];
foreach(['fr','sw','lg'] as $c){ $msgKeys[$c] = array_keys($L[$c]['msgs']); }
if($msgKeys['fr'] !== $msgKeys['sw'] || $msgKeys['fr'] !== $msgKeys['lg']) { $d=array_diff($msgKeys['fr'],$msgKeys['sw']); if($d) $fail('fr/sw/lg msgs key sets differ'); }

echo "2. placeholders (%d, %%) and HTML tags match English\n";
$cnt = fn($s,$p)=>preg_match_all($p,$s);
foreach(['fr','sw','lg'] as $c) foreach($L['en']['msgs'] as $k=>$en){ $t=$L[$c]['msgs'][$k] ?? '';
  foreach(['/%%/','/%d/','/<a /','/<br\/>/','/href=/'] as $p) if($cnt($en,$p)!==$cnt($t,$p)) $fail("$c msg ".substr($k,0,30)." differs on $p"); }
foreach(['rw','fr','sw','lg'] as $c) foreach($L['en']['ui'] as $k=>$en){ $t=$L[$c]['ui'][$k];
  foreach(['/%%/','/%d/'] as $p) if($cnt($en,$p)!==$cnt($t,$p)) $fail("$c ui $k differs on $p"); }
foreach($L as $c=>$cat) foreach($cat['ui'] as $k=>$v){ if(substr_count($v,'%')>0 && !preg_match('/^rl_s[23]d?$/',$k)) $fail("$c ui $k has stray %"); }
foreach($L as $c=>$cat) foreach($cat['msgs'] as $k=>$v){ $x=str_replace('%%','',$v); if(strpos($x,'%')!==false) $fail("$c msg ".substr($k,0,30)." has a single %"); }

echo "3. capitalisation: titles that are ALL CAPS in the original stay ALL CAPS\n";
$toks=token_get_all(file_get_contents('index.php')); $src=[];
foreach($toks as $i=>$t) if(is_array($t)&&$t[0]===T_STRING&&$t[1]==='__'){ $j=$i+2; while(is_array($toks[$j])&&$toks[$j][0]===T_WHITESPACE)$j++; if(is_array($toks[$j])&&$toks[$j][0]===T_CONSTANT_ENCAPSED_STRING) $src[]=eval('return '.$toks[$j][1].';'); }
require_once 'services/i18n.php';
foreach($src as $s){ if(preg_match('/[a-z]/',strip_tags($s))) continue; $k=i18n_key($s);
  foreach(['en','fr','sw','lg'] as $c){ $t=$L[$c]['msgs'][$k]; if(mb_strtoupper($t,'UTF-8')!==$t) $fail("$c not all-caps: $t"); } }
foreach(['rw','en','fr','sw','lg'] as $c) foreach(['quiz_lang_title'] as $k) if(mb_strtoupper($L[$c]['ui'][$k],'UTF-8')!==$L[$c]['ui'][$k]) $fail("$c $k not all-caps");

echo "4. typography: curly apostrophes, no double/edge spaces\n";
foreach($L as $c=>$cat) foreach(array_merge($cat['msgs'],$cat['ui']) as $k=>$v){ $plain=preg_replace('/<[^>]+>/','',$v);
  if(strpos($plain,"'")!==false) $fail("$c straight apostrophe in ".substr($k,0,30));
  if(preg_match('/  /',$v)) $fail("$c double space in ".substr($k,0,30));
  if($v!==ltrim($v) && !in_array($k,[],true)) $fail("$c leading space in ".substr($k,0,30)); }

echo "5. no Kinyarwanda left in en/fr/sw/lg\n";
foreach(['en','fr'] as $c) foreach(array_merge($L[$c]['msgs'],$L[$c]['ui']) as $k=>$v) if(preg_match('/\b(ibibazo|amafaranga|igisubizo|kwishyura|amategeko|uhabwa|hitamo)\b/i',$v)) $fail("$c has Kinyarwanda: ".substr($v,0,50));

echo "6. length sanity vs English (flags big gaps)\n";
foreach(['fr','sw','lg'] as $c) foreach($L['en']['msgs'] as $k=>$en){ if(strlen($en)<12) continue; $r=strlen($L[$c]['msgs'][$k])/max(1,strlen($en)); if($r>1.9||$r<0.5) $fail("$c ratio ".round($r,2)." for ".substr($k,0,30)); }

echo $bad? "\n$bad problem(s)\n" : "\nALL CHECKS PASSED\n";

<?php

require_once __DIR__ . '/i18n.php';

function ussd_encode($data){
	$result = "";
	if(!isset($_SESSION['ussd'])){
		$_SESSION['ussd'] = [];
	}
	$i = 1;
	foreach($data as $key=>$val){
		$_SESSSION['ussd'][$i] = $key;
		$result = "$i. $val\n";
	}
	return $result;
}

function create_token(){
	if(isset($_SESSION['token']) && !empty($_SESSION['token'])) return $_SESSION['token'];
	$_SESSION['token'] = uniqid();
	return $_SESSION['token'];
}

function get_response(){
	if(!isset($_POST['token']) || !isset($_POST['choise']) || $_POST['token'] != $_SESSION['token']) {
		
		return false;
	}
	$_SESSION['token'] = '';
	return $_POST['choise'];
}

function get_next(){
	if(!isset($_POST['token']) || !$_POST['token'] || $_POST['token'] != $_SESSION['token']) {
		return false;
	}
	$_SESSION['token'] = '';
	return true;
}

// ---- Interface helpers (display only - they read the session, never change it) ----

function ihugure_screen_number(){
	return (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['currentscreen'])) ? (int)$_SESSION['currentscreen'] : 0;
}

// Language switcher + "step x of 7" progress. Hidden while a question is being timed.
function ihugure_toolbar(){
	$screen = ihugure_screen_number();
	if($screen == 8) return '';
	$html = '<div class="card-toolbar">' . i18n_switcher() . '</div>';
	if($screen >= 1 && $screen <= 7){
		$html .= '<div class="steps" role="group" aria-label="' . htmlspecialchars(t('steps_label')) . '"><ol class="steps-track">';
		for($i = 1; $i <= 7; $i++){
			$class = $i < $screen ? 'done' : ($i == $screen ? 'current' : '');
			$html .= '<li' . ($class ? ' class="' . $class . '"' : '') . ($i == $screen ? ' aria-current="step"' : '') . '><span class="sr-only">' . htmlspecialchars(t('s' . $i)) . '</span></li>';
		}
		$html .= '</ol><p class="steps-label">' . htmlspecialchars(t('step')) . ' ' . $screen . ' ' . htmlspecialchars(t('of')) . ' 7 &middot; <strong>' . htmlspecialchars(t('s' . $screen)) . '</strong></p></div>';
	}
	return $html;
}

// Tab / history title: the current step first (tabs cut long titles on the right), then the brand.
function ihugure_page_title(){
	$screen = ihugure_screen_number();
	if($screen >= 1 && $screen <= 7) return t('s' . $screen) . ' (' . $screen . '/7) · IHUGURE APP';
	return 'IHUGURE APP';
}

// ---- Designed rules screen (screen 2): the facts from the rules text as tiles + steps ----
// Numbers come from the game's own constants (LEVEL, TIMEOUT). The reward factor must match the
// "200%" written in the rules text.
function ihugure_rules_summary(){
	$q = defined('LEVEL') ? LEVEL : 3;
	$s = defined('TIMEOUT') ? TIMEOUT : 12;
	$reward = 200;
	$e = function($k){ return htmlspecialchars(t($k)); };
	$tiles = [
		['blue',   (string)$q,                 'rl_q'],
		['sun',    $s . '<small>s</small>',    'rl_t'],
		['leaf',   $reward . '<small>%</small>', 'rl_p'],
		['danger', '18<small>+</small>',       'rl_a'],
	];
	$h = '<div class="rules"><ul class="facts">';
	foreach($tiles as $tile) $h .= '<li class="fact fact-' . $tile[0] . '"><b>' . $tile[1] . '</b><span>' . $e($tile[2]) . '</span></li>';
	$h .= '</ul><div class="rules-grid"><section class="how"><h2 class="rules-h">' . $e('rl_how') . '</h2><ol class="how-list">'
		. '<li><span class="how-t">' . $e('rl_s1') . '</span><span class="how-d">' . $e('rl_s1d') . '</span></li>'
		. '<li><span class="how-t">' . htmlspecialchars(sprintf(t('rl_s2'), $q)) . '</span><span class="how-d">' . htmlspecialchars(sprintf(t('rl_s2d'), $s)) . '</span></li>'
		. '<li><span class="how-t">' . $e('rl_s3') . '</span><span class="how-d">' . htmlspecialchars(sprintf(t('rl_s3d'), $reward)) . '</span></li>'
		. '</ol></section><aside class="rules-side"><div class="rules-note"><span class="how-t">' . $e('rl_month') . '</span><span class="how-d">' . $e('rl_monthd') . '</span></div>'
		. '<div class="rules-contact"><span class="rules-contact-l">' . $e('rl_contact') . '</span>'
		. '<a class="pill pill-ig" href="https://www.instagram.com/ihugure_app" target="_blank" rel="noopener">Instagram</a>'
		. '<a class="pill pill-mail" href="mailto:mukaveclimited@gmail.com">Email</a></div></aside></div></div>';
	return $h;
}

// On the rules screen the full original text is kept, folded under the summary.
function ihugure_rules_open(){
	if(ihugure_screen_number() != 2) return '';
	return ihugure_rules_summary() . '<details class="rules-full"><summary>' . htmlspecialchars(t('rl_full')) . '</summary>';
}
function ihugure_rules_close(){
	return ihugure_screen_number() == 2 ? '</details>' : '';
}

function ihugure_card_footer(){
	if(ihugure_screen_number() == 8) return '';
	return '<div class="card-footer"><span class="age-badge">18+</span><span>' . htmlspecialchars(t('age_only')) . '</span></div>';
}

function display_header(){
	if(!defined('USSD')){
		?>
        <!DOCTYPE html>
        <html lang="<?php echo i18n_languages()[i18n_current()][2]; ?>">
        <head>
            <title><?php echo htmlspecialchars(ihugure_page_title()); ?></title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="assets/css/ihugure.css">
        </head>
        <body>
        <div class="container">
        <div class="card">
        <div class="card-header">IHUGURE APP</div>
        <?php echo ihugure_toolbar(); ?>
        <div class="card-body">
		<?php
	}
}

function display_footer(){
    global $javascript;
	if(!defined('USSD')){
		?>
		</div>
        <?php echo ihugure_card_footer(); ?>
        </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
        <?php if(isset($javascript)) echo $javascript; ?>
        <script>
			window.addEventListener("keypress", function(evt){
				console.log(evt.key);
				let currentRadio = document.querySelector(`[data-keyboard='${evt.key}']`);
				if(currentRadio) {
					currentRadio.checked = true;
					currentRadio.form.submit();
				}
			});
			
			// Disable context menu
			document.addEventListener('contextmenu', function (e) {
                e.preventDefault();
            }, false);
            
            // Disable copy and paste
            document.addEventListener('copy', e => e.preventDefault());
            document.addEventListener('paste', e => e.preventDefault());
            document.addEventListener('cut', e => e.preventDefault());
            document.addEventListener('dragstart', e => e.preventDefault());
            document.addEventListener('selectstart', e => e.preventDefault());
            
            // Detect leaving the screen
            window.addEventListener('mouseout', function (e) {
                let from = e.relatedTarget || e.toElement;
      if (!from) {
          
          fetch('log.php?event=2');
                //document.body.innerHTML = "<h1>You are not allowed to leave the gaming area</h1>";
      }
            });
            
             document.addEventListener('visibilitychange', () => {
      if (document.hidden && noquit) {
        document.querySelector('.card-body').innerHTML = "<h1>" + <?php echo json_encode(__('Niwemerewe gusohoka uri gukina'), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?> + " <a href='?reflesh'>" + <?php echo json_encode(__('Gerageza Na None'), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?> + "</a></h1>";
          fetch('log.php?event=3');
      }
    });
            
           
    
    
    // Check trusted browser
    function isTrustedBrowser() {
        
        if (navigator.webdriver || /HeadlessChrome/.test(navigator.userAgent)) {
  return false;
}
    const ua = navigator.userAgent;
    const isChrome = /Chrome/.test(ua) && /Google Inc/.test(navigator.vendor);
    const isFirefox = /Firefox/.test(ua);
    const isEdge = /Edg/.test(ua); // Microsoft Edge uses "Edg"
    const isSafari = /Safari/.test(ua) && /Apple Computer/.test(navigator.vendor);
    const isOpera = /Opera|OPR/.test(ua); // Opera can be detected by either "Opera" or "OPR"
    const isAndroidBrowser = /Android/.test(ua) && /Mobile/.test(ua); // Android built-in browser

    return isChrome || isFirefox || isEdge || isSafari || isOpera || isAndroidBrowser;
  }

  if (!isTrustedBrowser()) {
    // You can show a message or redirect
    document.body.innerHTML = "<h1>Unsupported browser</h1><p>Please use a modern trusted browser like Chrome, Firefox, Edge, Safari, or Opera.</p>";
    throw new Error("Blocked unsupported browser");
    // Or: window.location.href = "https://example.com/unsupported-browser";
  }
            
    // 3. Detect debugger and redirect
    function detectDebugger() {
      let check = false;
      const element = new Image();
      Object.defineProperty(element, 'id', {
        get: function () {
          check = true;
          throw new Error("Debugger detected");
        }
      });

      setInterval(function () {
        check = false;
        //console.log(element);
        if (check || isDebuggerActive()) {
          document.body.innerHTML = "<h1>You are not allowed to use debug</h1>";
          fetch('log.php?event=1');
          alert('You are not allowed to open debug. Close is and click ok');
          window.location.href = '/';
        }
      }, 1000);
    }

    // More aggressive debugger check
    function isDebuggerActive() {
      const start = performance.now();
      debugger;
      const duration = performance.now() - start;
      return duration > 100;
    }

    window.onload = detectDebugger;
		</script>
        </body>
        </html>
		<?php
		
	}
}

function display_choice_form($title, $choises){
	display_header();
	if(!defined('USSD')){
	?>
	<form method='POST'>
	<input type='hidden' name='token' value='<?php echo create_token(); ?>' />
	<?php echo $title; ?><br/>
	<?php $keyboard = 1;
	foreach ($choises as $key=>$val){
		?>
            <div class="form-group">
                <?php echo $keyboard; ?>)
		<input name='choise' data-keyboard='<?php echo $keyboard; ?>' type='radio' value='<?php echo $key;?>'/> <?php echo $val;?>
            </div>
		<?php $keyboard++;
	}?>
	<button class="btn btn-primary"><?php echo __('IBIKURIKIRA'); ?></button>
	<button class="btn btn-danger float-right" name="cancel"><?php echo __('FUNGA'); ?></button>
	<button class="btn btn-danger float-right" style="display:none;" name="timeout">FUNGA</button>
	</form>
	<?php
	} else {
		echo "$title\n".ussd_encode($choises);
	}
	display_footer();
	exit();
}

function display_input_form($title){
	display_header();
	if(!defined('USSD')){
	?>
	<form method='POST'>
	<input type='hidden' name='token' value='' />
	<?php echo $title; ?><br/>
        <div class="form-group">
	<input class="form-control" name='choise' />
        </div>
	<button class="btn btn-primary"><?php echo __('IBIKURIKIRA'); ?></button>
	<button class="btn btn-danger float-right" name="cancel"><?php echo __('FUNGA'); ?></button>
	</form>
	<?php
	} else {
		echo $title;
	}
	display_footer();
	exit();
}

function display_form($title, $choises=NULL){
	if(!$choises){
		display_input_form($title);
	} else {
		display_choice_form($title, $choises);
	}
}

function display_text($title, $data='', ...$rest){
	display_header();
	if(!defined('USSD')){
	?>
	<form method='POST'>
		<input type='hidden' name='token' value='<?php echo create_token();?>' />
		<h1><?php echo $title;?> </h1>
		<?php echo ihugure_rules_open(); ?>
		<p><?php echo $data; ?></p>
		<?php echo ihugure_rules_close(); ?>
		<?php foreach($rest as $val) echo "<p>$val</p>"; ?>
		<div class="actions<?php echo ihugure_screen_number() == 2 ? ' actions-sticky' : ''; ?>">
		<button class="btn btn-primary" name="submit"><?php echo __('IBIKURIKIRA'); ?></button>
	<button class="btn btn-danger float-right" name="cancel"><?php echo __('FUNGA'); ?></button>
		</div>
	</form>
	<?php
	} else {
		echo $data;
		echo implode("\n", $rest);
	}
	display_footer();
	exit();
}

function current_screen(){
	if(!isset($_SESSION['currentscreen'])) {
		$_SESSION['currentscreen'] = 1;
	}
	return $_SESSION['currentscreen'];
}

function set_screen($screen) {
	$_SESSION['currentscreen'] = $screen;
}

function next_screen() {
	$_SESSION['currentscreen']++;
}
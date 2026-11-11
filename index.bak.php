<?php 
session_start();
function logsome($data) {
	//file_put_contents('logs.txt', json_encode($data, JSON_PRETTY_PRINT)."\n\n\n\n\n", FILE_APPEND);
}
//logsome(['time'=>date('H:i:s', time()+7200), 'server'=>$_SERVER,'data'=>$_POST, 'raw'=>file_get_contents('php://input'), 'session'=>$_SESSION]);
//header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
require_once "services/ui.php";
require_once "services/quiz.php";


$i = 0;
    if(isset($_POST['cancel'])) {
        $_SESSION = [];
        game_log('CANCELLED');
    }
    if(isset($_POST['timeout'])) {
        query_update('game', ['state'=>'TIMEOUT'], ['id'=>$_SESSION['gameid']]);
        game_log('TIMEOUT');
        $_SESSION = [];
    }
	if(DEBUG && isset($_POST['screen'])) set_screen($_POST['screen']);
	if(DEBUG) echo "Current Screen ".current_screen()."<form method='POST'><input name='screen'/></form><br/>\n";

while($i < 5){
	$i++;
	$screen = current_screen();
	$screens = [1=>'LANGUAGE', 2=>'AMATEGEKO', 3=>'CATEGORY', 4=>'AMAFARANGA', 5=>'TELEPHONE', 6=>'PAYMENT', 7=>'KWITEGURA', 8=>'IBIBAZO', 9=>'KUBIKUZA', 10=>'', 11=>'KWICWA', 12=>''];
	game_log('SCREEN', isset($screens[$screen])?$screens[$screen]:$screen);
	if($screen==1) {
		if(($response=get_response())!==false){
			
			set_language($response);
			next_screen();
			continue;
		}
		display_form('', get_available_languages());
	} else if ($screen==2) {
		if(get_next()) {
			next_screen();
			continue;
		}
		display_text(__('AMATEGEKO NA AMABWIRIZA'), __("IHUGURE APP nurubuga rwashyizweho na   MKVEC LTD rubaza abarusura ibibazo bitandukanye ababisubiza neza bakabihemberwa. Haba harimo: 
- Ubumenyi rusange 
- Sport 
- Music wiyandikisha Aho ushaka ukabazwa ibibazo byaho. uhembwa iyo ukoze ibibazo 3 Uba wabajijwe Byose Kuko uhabwa ibibazo n'ibisubizo ugahitamo igisubizo kizima( multiple choice) .  IHUGURE APP iguhemba 200%% yamafaranga wishyuye wiyandikisha. Uru rubuga rwashyizweho muburyo bwo gukomeza gukundisha abanyarwanda gusoma no gukoresha ubumenyi bafite nokwiga gutecyereza neza Kandi Vuba kuko dushyiraho igihe umuntu agomba kuba yasubije ikibazo aba ahawe. icyo gihe cyikaba Ari  amaseganda icumi nabiri (12 secs) kuri buri kibazo. Kandi iyo wishe ikibazo tukwereka kowakishe tukakwereka n'igisubizo kiricyo wari guhitamo Kuko ibibazo tubaza biba kuri murandasi. uretse guhembwa ukimara gutsinda iyo ukwezi gushize duhemba  abasubije ibibazo byishi ndetse tukanahemba abanjyeranjyeje ishuro nyishi. Dukunde gusoma kuko ariho hari izingiro ryubumenyi, duteze imbere igihugu cyacu n'isi muri rusange umuntu uri munsi y'imyaka 18 ntiyemerewe kuza muri rino bazwa. Ufite ikibazo cyangwa inyunganizi watwandikira kuri  Instagram:<a target='_blank' href='https://www.instagram.com/ihugure_app' title='Instagram link'>ihugure_app</a>,email: <a href='mailto:mukaveclimited@gmail.com' alt='email'>mukaveclimited@gmail.com</a>
		<br/>") );
	} else if ($screen==3) {
		if(($response=get_response())!==false){
			set_category($response);
			next_screen();
			continue;
		}
		display_form(__('HITAMO AHO UBAZWA'), get_available_categories());
	} else if ($screen==4) {
		if(($response=get_response())!==false){
			set_investment($response);
			next_screen();
			continue;
		}
		display_form(__('HITAMO AMAFARANGA YO KWIYANDIKISHA'), get_available_investments());
	} else if ($screen==5) {
		if(($response=get_response())!==false){
			try{
				set_user($response);
				init_game();
				request_payment();
				set_screen(6);
			}catch(Expection $e){
				display_text(__('NIMERO MWASHIZEMO NTAGO YEMEWE'));
			}
				continue;
		}
		display_form(__('INJIZAMO NIMERO YA TELEPHONE'));
	} else if ($screen==6) {
		if(!waiting_payment()){
			if(game_paid()) {
				set_screen(7);
				continue;
			} else {
				session_destroy();
				display_text(__('NABWO TURAKIRA AMAFARAGA'));
			}
		}
		/*$javascript = '
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script>
            const socket = io("https://pusher.site.rw");
            socket.on("momo_payment_'.$_SESSION['gameid'].'", (data) => {       
                document.querySelect("[name=submit]").click();
            });
            </script>';*/
		display_text(__('EMEZE KWISHYURA MURI TELEPHONE YAWE'));
	} else if ($screen==7){
		if(get_next()){
			next_question();
			set_screen(8);
			continue;
		}
		display_text(__('URITEGUYE GUTANGIRA IBANZWA'));
	} else if ($screen==8) {
		if(($response=get_response())!==false){
			if(!check_answer($response)){
				
				set_screen(11);
				continue;
			}
			if(can_withdraw()){
				set_screen(9);
				continue;
			}
			next_question();
		}
		
		if(game_ended()){
			set_screen(10);
			continue;
		}
		$question = current_question();
		$javascript = "
        <h1 style='position: fixed; right: 0px; bottom:0px; padding: 10px; font-size: 98px' id='countdown'></h1>
		<script>
		    let isSubmitting = false;
		    let time_remaining = 12;
		    document.querySelector('form').addEventListener('submit', function(){isSubmitting = true;});
		    document.addEventListener('DOMContentLoaded', function() { 
		        setInterval(function(){
		        if(time_remaining >= 0) {
		            document.querySelector('#countdown').innerText = time_remaining--;
		        } else {
                    if(!isSubmitting)fetch('log.php?event=4');
		            if(!isSubmitting)document.querySelector('[name=timeout]').click();
		        }
		    }, 1000);
		    });
		    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        if(!isSubmitting)document.querySelector('[name=timeout]').click();
      }
    });
        </script>";
        /*if(isset($_SESSION['previous_question']) && $_SESSION['previous_question'] == $question['id']) {
            game_log('CANCELLED');
            $_SESSION = [];
            continue;
        }*/
        $_SESSION['previous_question'] = $question['id'];
		display_form($question['title'], $question['answers']);
	} else if ($screen==9){
		// Withdraw
		if(($response=get_response())!==false){
			if($response=='Y'){
				init_withdraw();
				session_destroy();
				display_text(__('KUBIKUZA BYAGENZE NEZA, MURAKOZE'));
			} else {
				set_screen(7);
				next_question();
				continue;
			}
		}
		display_form(__('MURIFUZA KUBIKUZA'), ['Y'=>__('YEGO'), 'N'=>__('HOYA')]);
	} else if ($screen==10){
		// Game End
	} else if ($screen==11){
		$question = check_correct();
		session_destroy();
		display_text(__('(NTAGO ARIBYO) ').$question['title'], __('(IBYANYABYO)').implode(',',$question['correct']), __('(IBYO MWASUBIJE)').$question['wrong']);
	}
}
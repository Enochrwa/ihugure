<?php

require_once 'services/db.php';

function makeanswer($answer){
    $result = [$answer];
    for($i=0;$i<3;$i++){
        $a = rand(1,20);
        $b = rand(1,2);
        if($b==1) $result[] = $answer + $a;
        else $result[] = $answer + $a;
    }
    return $result;
}

function makequestion(){
    $questions = [];
    for($i=0;$i<1000;$i++){
        $op = rand(1,4);
        $a = rand(10,99);
        $b = rand(10,99);
        $question = [];
        if($op==1){$question['question']="$a+$b"; $question['answers']=makeanswer($a+$b);}
        else if($op==2){$question['question']="$a-$b"; $question['answers']=makeanswer($a-$b);}
        else if($op==3){$question['question']="$a*$b"; $question['answers']=makeanswer($a*$b);}
        else {$question['question']="$a/$b"; $question['answers']=makeanswer($a/$b);}
        $questions[] = $question;
    }
    return $questions;
}

$questions = makequestion();

foreach($questions as $question){
    query_insert('inquiry', ['title'=>$question['question'],'complexity'=>'1']);
    $inquiry = query_insert_id();
    query_insert('inquirycategory', ['inquiryid'=>$inquiry,'categoryid'=>1]);
    query_insert('question', ['title'=>$question['question'], 'inquiryid'=>$inquiry, 'languageid'=>'1']);
    $questionid = query_insert_id();
    foreach($question['answers'] as $key=>$answer){
        query_insert('answer', ['title'=>$answer,'correct'=>$key==0?'1':'0','inquiryid'=>$inquiry,'questionid'=>$questionid,'languageid'=>1]);
    }
}
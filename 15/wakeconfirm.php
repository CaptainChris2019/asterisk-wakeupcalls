#!/usr/bin/env php
<?php

require_once("phpagi.php");

$agi = new AGI();
$agi->answer();

$lang = $agi->request['agi_language'];
$callerid = $agi->request['agi_callerid'];
$ext = $agi->request['agi_extension'];

if ($lang == 'ja') {
    sim_playback($agi, "this-is-yr-wakeup-call");
} else {
    sim_playback($agi, "hello&this-is-yr-wakeup-call");
}

$digit = sim_background(
    $agi,
    $lang == 'ja' ?
        "wakeup-menu" :
        "to-cancel-wakeup&press-1&to-snooze-for&digits/5&minutes&press-2&to-snooze-for&digits/10&minutes&press-3&to-snooze-for&digits/15&minutes&press-4",
    "1234",
    1
);

switch ($digit) {
    case "1":
        sim_playback($agi, "wakeup-call-cancelled");
        break;
    case "2":
        scheduleWakeup(300, $callerid, $lang);
        sim_playback($agi, "rqsted-wakeup-for&digits/5&minutes&vm-from&now");
        break;
    case "3":
        scheduleWakeup(600, $callerid, $lang);
        sim_playback($agi, "rqsted-wakeup-for&digits/10&minutes&vm-from&now");
        break;
    case "4":
        scheduleWakeup(900, $callerid, $lang);
        sim_playback($agi, "rqsted-wakeup-for&digits/15&minutes&vm-from&now");
        break;
}

sim_playback($agi, "goodbye");
$agi->hangup();


function scheduleWakeup($offsetSeconds, $callerid, $lang) {
    $timestamp = time() + $offsetSeconds;

    $basename = "wuc.$timestamp.ext.$callerid.call";
    $tmpfile = "/tmp/$basename";
    $finalfile = "/var/spool/asterisk/outgoing/$basename";

    $content = <<<CALL
Channel: Local/$callerid@originate-skipvm
MaxRetries: 3
RetryTime: 60
WaitTime: 60
CallerID: Wake Up Calls <*68>
Set: CHANNEL(language)=$lang
Application: AGI
Data: wakeconfirm.php
AlwaysDelete: Yes
Archive: Yes
CALL;

    file_put_contents($tmpfile, $content);

    touch($tmpfile, $timestamp);

    chown($tmpfile, 'asterisk');

    rename($tmpfile, $finalfile);
}



function sim_playback($AGI, $file) {
    foreach (explode('&', $file) as $f) {
        $AGI->stream_file($f);
    }
}


function sim_background($AGI, $file, $digits = '', $length = 1, $escape = '#', $timeout = 15000, $maxLoops = 1, $loops = 0) {
    $files = explode('&', $file);
    $number = '';
    $lang = $AGI->request['agi_language'];

    foreach ($files as $f) {
        $ret = $AGI->stream_file($f, $digits);
        if ($ret['code'] == 200 && $ret['result'] != 0) {
            $number .= chr($ret['result']);
        }
        if (strlen($number) >= $length) {
            break;
        }
    }

    if (trim($digits) != '' && strlen($number) < $length) {
        while (strlen($number) < $length && $loops < $maxLoops) {
            $ret = $AGI->wait_for_digit($timeout);
            if ($loops > 0) {
                sim_playback($AGI, "please-try-again");
            }

            if ($ret['code'] == 200 && $ret['result'] != 0) {
                $digit = chr($ret['result']);
                if ($digit == $escape) break;
                if (strpos($digits, $digit) !== false) {
                    $number .= $digit;
                } else {
                    sim_playback($AGI, $lang == 'ja' ? "you-entered-bad-digits" : "you-entered&bad&digits");
                }
            } else {
                sim_playback($AGI, "an-error-has-occurred");
            }
            $loops++;
        }
    }

    return trim($number);
}

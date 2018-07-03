<?php
/*
 * Author: Michael aka SossenSystems
 * Credits: Alex aka xLikeAlex and Bluscream
 * Version: myTeamSpeakID-Checker v.1.2
 * Info: for questens, issues and pull requests visit https://github.com/SossenSystems/myTeamSpeakID-Checker
 */

require_once("ts3phpframework-1.1.32/libraries/TeamSpeak3/TeamSpeak3.php");
require_once("config.php");
require_once("myTeamSpeak.php");

try {
    $ts3_VirtualServer = TeamSpeak3::factory("serverquery://" . $query['username'] . ":" . $query['password'] . "@" . $query['ipAddress'] . ":" . $query['port'] . "/?server_port=" . $query['voicePort'] . "&nickname=" . urlencode($query['nickname']) . "&blocking=0");
    TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryWaitTimeout", "onTimeout");
    TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyCliententerview", "onJoin");
    $ts3_VirtualServer->serverGetSelected()->notifyRegister("server");
    ($ts3_VirtualServer->virtualserver_version != "3.3.0 [Build: 1530178919]" ? stopBot() : '');
    while (1) $ts3_VirtualServer->getAdapter()->wait();
}
catch(TeamSpeak3_Transport_Exception $e){
    $erromsg = $e->getMessage();
    if(isset($erromsg) && $erromsg == "Connection refused"){
        print_r("[ERROR] Message from Framework: " . $erromsg . "\nThe query is not accessible.\nThe server is probably offline...\n");
    } else {
        print_r("[ERROR]  " . $e->getMessage() . "\n");
    }
}
catch(Exception $e)
{
    print_r("[ERROR2]  " . $e->getMessage() . "\n");
}

function stopBot(){
    echo "Stopping Bot... Reason you need minimum the Server-Version 3.3.0\nYou can download the latest beta here: http://dl.4players.de/ts/releases/pre_releases/server/?C=M;O=D\n";
    sleep(1);
    exit();
}

function onTimeout($seconds, TeamSpeak3_Adapter_ServerQuery $adapter) {
    $last = $adapter->getQueryLastTimestamp();
    $time = time();
    $newtime = $time-300;
    $update = $last < $newtime;
    //$update_str = ($update) ? 'true' : 'false';
    //print_r("Timeout! seconds=$seconds last=$last time=$time newtime=$newtime update=$update_str\n");
    if($update)
    {
        $adapter->request("clientupdate");
    }
}

function onJoin(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host)
{
    runChecker(getEvent($event, $host), $host);
}
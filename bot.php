<?php
/*
 * Author: Michael aka SossenSystems
 * Credits: Alex aka xLikeAlex and Bluscream
 * Version: myTeamSpeakID-Checker v.1.2
 * Info: for questens, issues and pull requests visit https://github.com/SossenSystems/myTeamSpeakID-Checker
 */
session_start();
require_once("ts3phpframework-1.1.32/libraries/TeamSpeak3/TeamSpeak3.php");
require_once("config.php");
require_once("myTeamSpeak.php");
require_once("lib/color.class.php");

$colors = new Colors();

echo $colors->getColoredString("== START ==", "white"), PHP_EOL;

function connectToServer(&$ts3_VirtualServer)
{
    global $query, $colors;
    try {
        echo $colors->getColoredString("[INFO] Try to initialize connection...", "light_gray"), PHP_EOL;
        $ts3_VirtualServer = TeamSpeak3::factory("serverquery://" . $query['username'] . ":" . $query['password'] . "@" . $query['ipAddress'] . ":" . $query['port'] . "/?server_port=" . $query['voicePort'] . "&nickname=" . urlencode($query['nickname']) . "&blocking=0");
        TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryWaitTimeout", "onTimeout");
        TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyCliententerview", "onJoin");
        TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyTextmessage", "onTextmessage");
        $ts3_VirtualServer->notifyRegister("server");
        $ts3_VirtualServer->notifyRegister("textprivate");
        echo $colors->getColoredString("[INFO] Connection to Virtual Instance \"".$ts3_VirtualServer["virtualserver_name"]."\" successfully established!", "light_gray"), PHP_EOL;
        echo $colors->getColoredString("[INFO] Memory usage: ".convert($ts3_VirtualServer->getParent()->getAdapter()->getProfiler()->getMemUsage(),"MB"), "light_gray"), PHP_EOL;
        $vVersion = $ts3_VirtualServer["virtualserver_version"];
        $ex[1] = explode("]", $vVersion);
        $ex[2] = explode(": ", $ex[1][0]);
        (!"1530178919" >= $ex[2][1] ? stopBot() : '');
        return true;
    }
    catch(TeamSpeak3_Transport_Exception $e){
        $erromsg = $e->getMessage();
        if(isset($erromsg) && $erromsg == "Connection refused"){
            print_r($colors->getColoredString("[ERROR] Message from Framework: " . $erromsg . "\nThe query is not accessible.\nThe server is probably offline...", "white", "red"), PHP_EOL);
        } else {
            print_r($colors->getColoredString("[ERROR]  " . $e->getMessage(), "white", "red"), PHP_EOL);
        }
        return false;
    }
    catch(Exception $e)
    {
        print_r($colors->getColoredString("[ERROR2]  " . $e, "white", "red"), PHP_EOL);
        return false;
    }
}

function autoReconnect(&$ts3_VirtualServer, &$reconnectTime)
{
    global $colors;
    do{
        echo $colors->getColoredString("gc_collect_cycles ".gc_collect_cycles(), "red"), PHP_EOL;
        echo $colors->getColoredString("__destruct ". $ts3_VirtualServer->getAdapter()->__destruct(), "red"), PHP_EOL;
        // sleep for wait to check if server is online
        sleep(intval($reconnectTime));
    }while(!connectToServer($ts3_VirtualServer));
}

$autoReconnectTime = $option['reconnect_int'];

while(true){
    try{
        if(!isset($ts3_VirtualServer)) connectToServer($ts3_VirtualServer);
        try{
            while(1) $ts3_VirtualServer->getAdapter()->wait();
        }
        catch(TeamSpeak3_Transport_Exception $e){
            echo $colors->getColoredString("[EXC1] ".$e->getMessage(), "red"), PHP_EOL;
            autoReconnect($ts3_VirtualServer, $autoReconnectTime);
        }
        catch(Exception $e){
            echo $colors->getColoredString("[EXC2]", "red"), PHP_EOL;
            echo $colors->getColoredString("ErrMsg: ".$e->getMessage(), "red", "white"), PHP_EOL;
            echo $colors->getColoredString("Code: ".$e->getCode(), "white", "yellow"), PHP_EOL;
            echo $colors->getColoredString("Sender: ".$e->getSender(), "red"), PHP_EOL;
        }
    }
    catch(Exception $e){
        echo $colors->getColoredString("[EXC0] ".$e->getMessage(), "red"), PHP_EOL;
        autoReconnect($ts3_VirtualServer, $autoReconnectTime);
    }
}

function stopBot(){
    global $colors;
    echo $colors->getColoredString("[WARNING] Stopping Bot...", "yellow"), PHP_EOL;
    echo $colors->getColoredString("Reason you need minimum the Server-Version 3.3.0", "yellow"), PHP_EOL;
    echo $colors->getColoredString("You can download the latest beta here: http://dl.4players.de/ts/releases/pre_releases/server/?C=M;O=D", "yellow"), PHP_EOL;
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

function onTextmessage(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host)
{
    global $settings, $db, $ts3_VirtualServer, $colors;
    $client = $host->serverGetSelected()->clientGetById($event["invokerid"]);
    $clientInfo = $client->getInfo();
    if ($clientInfo["client_type"] == 0) {
        if ($host->whoami()['client_unique_identifier'] != $event["invokeruid"]) {
            if (isUID($clientInfo['client_unique_identifier'])) {
                if (isCommand("!help", $event["msg"], $client)) {
                    $client->message("[u]Available command commands...[/u]");
                    $client->message("[B]!get info ram [KB|MB|GB][/B] | Shows the current RAM consumption of the bot.");
                    $client->message("[B]!get countKickedUser ofBadges[/B] | Shows the count of all kicked user if there have set badges (only on this session)");
                } else if (isCommand("!get info ram KB", $event["msg"], $client) OR isCommand("!get info ram MB", $event["msg"], $client) OR isCommand("!get info ram GB", $event["msg"], $client)) {
                    if (isCommand("!get info ram KB", $event["msg"], $client)) {
                        $client->message("The RAM consumption is [B]" . convert($ts3_VirtualServer->getParent()->getAdapter()->getProfiler()->getMemUsage(), "KB") . "[/B]");
                    } else if (isCommand("!get info ram MB", $event["msg"], $client)) {
                        $client->message("The RAM consumption is [B]" . convert($ts3_VirtualServer->getParent()->getAdapter()->getProfiler()->getMemUsage(), "MB") . "[/B]");
                    } else if (isCommand("!get info ram GB", $event["msg"], $client)) {
                        $client->message("The RAM consumption is [B]" . convert($ts3_VirtualServer->getParent()->getAdapter()->getProfiler()->getMemUsage(), "GB") . "[/B]");
                    }
                } else if (isCommand("!get info ram", $event["msg"], $client)) {
                    $client->message("Error, please use this command so [B]!get info ram [KB|MB|GB][/B]");
                } else if (isCommand("!get countKickedUser ofBadges", $event["msg"], $client)) {
                    (!isset($_SESSION['countOfKickedBadgesUser']) ? $countKickedUserBadge = 0 : $countKickedUserBadge = $_SESSION['countOfKickedBadgesUser']);
                    $client->message("There were already " . $countKickedUserBadge . " users (in this session) kicked from the server because these badges had set!");
                } else {
                    $client->message("Command not found, use \"!help\" for more informations...");
                }
                echo $colors->getColoredString("[EVENT][INCOMING][textprivate][" . date("d.m.Y h:i:sa") . "] '" . $clientInfo['client_nickname'] . "' send a message: " . $event["msg"], "light_blue"), PHP_EOL;
            } else {
                echo $colors->getColoredString("[" . date("d.m.Y h:i:sa") . "] Invalid Master '" . $clientInfo['client_nickname'] . "' send a message: " . $event["msg"], "light_blue"), PHP_EOL;
                $client->message("You are not my Master!");
            }
        }
    }
}

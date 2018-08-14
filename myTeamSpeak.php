<?php
/*
 * Author: Michael aka SossenSystems
 * Credits: Alex aka xLikeAlex and Bluscream
 * Version: myTeamSpeakID-Checker v.1.2
 * Info: for questens, issues and pull requests visit https://github.com/SossenSystems/myTeamSpeakID-Checker
 */

// This function get the event from the bot.php
function getEvent($event, $host){
    if ($event["client_type"] == 0) {
        $client = $host->serverGetSelected()->clientGetById($event["clid"]);
        $clientInfo = $client->getInfo();
    } else {
        $clientInfo = false;
    }
    return $clientInfo;
}
// This function kicks user from the Server and write them a private message
function kickUser($clientInfo, $host){
    global $colors, $personaldmsg;
    // Selects client from Client ID
    $client = $host->serverGetSelected()->clientGetById($clientInfo["clid"]);
    // Send a private message on kick
    $client->message($personaldmsg['privateMsg']);
    // Kicks the user from server
    $client->kick('5', $personaldmsg['kickMsg']);

    $strOnKickToConsole = str_replace("%nickname%", $clientInfo['client_nickname'], $personaldmsg['kickConsole']);
    echo $colors->getColoredString( "[INFO] ". (isset($strOnKickToConsole) && !empty($strOnKickToConsole) ? $strOnKickToConsole . "\n" : 'User ' . $clientInfo['client_nickname'] . ' kicked!' . "\n"), "white");
}
// This function is the heart of this script :D
function runChecker($clientInfo, $host) {
    global $colors, $personaldmsg, $kickoption, $option;
    $client = $host->serverGetSelected()->clientGetById($clientInfo["clid"]);
    $clientInfo = $client->getInfo();
    if($clientInfo != false) {
        if(isset($clientInfo['client_version']) && isset($kickoption['ClientMinVersion']) && !empty($kickoption['ClientMinVersion']) && $clientInfo['client_version'] >= $kickoption['ClientMinVersion']) {
            $startex = explode('Build: ', $clientInfo['client_version']);
            $endex = explode(']', $startex[1]);
            $endex[1];
            if (isset($clientInfo['client_myteamspeak_id'])) {
                if (strlen($clientInfo['client_myteamspeak_id']) >= 35) {
                    $match = preg_match("/^A[\da-zA-Z\/]{43}$/", $clientInfo['client_myteamspeak_id']);
                    if ($match) {
                        $strNicknameOnSuc = str_replace("%nickname%", $clientInfo['client_nickname'], $personaldmsg['successfulyJoinC']);
                        echo $colors->getColoredString("[INFO] " . $strNicknameOnSuc, "green"), PHP_EOL;
                    } else {
                        kickUser($clientInfo, $host);
                    }
                } else {
                    $strNicknameOnMod = str_replace("%nickname%", $clientInfo['client_nickname'], $personaldmsg['modified_myID']);
                    echo $colors->getColoredString("[SPOOF/MODIFIED] " . $strNicknameOnMod, "light_red"), PHP_EOL;
                    kickUser($clientInfo, $host);
                }
            } else {
                kickUser($clientInfo, $host);
            }
            if($option['antiBadges-active'] == true || 1) {
                if (isset($clientInfo['client_badges'])) {
                    if (preg_match("(([a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}))", $clientInfo['client_badges'])) {
                        echo $colors->getColoredString("[INFO] User \"" . $clientInfo["client_nickname"] . "\" has set badges..."), PHP_EOL;
                        echo $colors->getColoredString("[INFO] User \"" . $clientInfo["client_nickname"] . "\" kicked from the server, Reason: Valid badges..."), PHP_EOL;
                        if (!isset($_SESSION['countOfKickedBadgesUser'])) {
                            $_SESSION['countOfKickedBadgesUser'] = 1;
                        } else {
                            $_SESSION['countOfKickedBadgesUser']++;
                        }
                    }
                }
            }
        } else {
            $strNicknameOnVer = str_replace("%nickname%", $clientInfo['client_nickname'], $kickoption['ClientMinVersionElMsg']);
            echo $colors->getColoredString("[WARNING] " . $strNicknameOnVer, "light_red"), PHP_EOL;
        }
    } else {
        echo $colors->getColoredString("[NOTE] " . $personaldmsg['invalidclientid'], "light_blue"), PHP_EOL;
    }
}
function isCommand($command, $eventText, $client){
    $lenth = strlen($command);
    $command_sub = substr($eventText, 0, $lenth);
    if($command_sub == $command){
        return true;
    }
}

function convert($size, $unit)
{
    if ($unit == "KB") {
        return $fileSize = round($size / 1024, 4) . ' KB';
    }
    if ($unit == "MB") {
        return $fileSize = round($size / 1024 / 1024, 4) . ' MB';
    }
    if ($unit == "GB") {
        return $fileSize = round($size / 1024 / 1024 / 1024, 4) . ' GB';
    }
}

function isUID($uid){
    global $settings;
    foreach ($settings['masterUID'] as $uids){
        if($uid == $uids){
            return true;
        }
    }
}

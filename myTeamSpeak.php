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
    global $personaldmsg;
    // Selects client from Client ID
    $client = $host->serverGetSelected()->clientGetById($clientInfo["clid"]);
    // Send a private message on kick
    $client->message($personaldmsg['privateMsg']);
    // Kicks the user from server
    $client->kick('5', $personaldmsg['kickMsg']);

    $strOnKickToConsole = str_replace("%nickname%", $clientInfo['client_nickname'], $personaldmsg['kickConsole']);

    echo (isset($strOnKickToConsole) && !empty($strOnKickToConsole) ? $strOnKickToConsole . "\n" : 'User ' . $clientInfo['client_nickname'] . ' kicked!');
}
// This function is the heart of this script :D
function runChecker($clientInfo, $host) {
    global $personaldmsg, $kickoption;
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
                        echo $strNicknameOnSuc . "\n";
                    } else {
                        kickUser($clientInfo, $host);
                    }
                } else {
                    $strNicknameOnMod = str_replace("%nickname%", $clientInfo['client_nickname'], $personaldmsg['modified_myID']);
                    echo $strNicknameOnMod . "\n";
                    kickUser($clientInfo, $host);
                }
            } else {
                kickUser($clientInfo, $host);
            }
        } else {
            $strNicknameOnVer = str_replace("%nickname%", $clientInfo['client_nickname'], $kickoption['ClientMinVersionElMsg']);
            echo $strNicknameOnVer . "\n";
        }
    } else {
        echo $personaldmsg['invalidclientid'] . "\n";
    }
}
<?php
/*
 * Author: Michael aka SossenSystems
 * Credits: Alex aka xLikeAlex and Bluscream
 * Version: myTeamSpeakID-Checker v.1.2
 * Info: for questens, issues and pull requests visit https://github.com/SossenSystems/myTeamSpeakID-Checker
 */

/* Server Query Data */

# Query username
$query['username'] = "serveradmin";
# Query password
$query['password'] = "hSRIhSiA";
# Query/Server address
$query['ipAddress'] = "127.0.0.1";
# Query Port
$query['port'] = "10011";
# Server Voice port
$query['voicePort'] = "9987";

/* Personald Data */

# Set the client nickname
$query['nickname'] = "myTeamSpeak-Checker v.1.2";

# Private message
$personaldmsg['privateMsg'] = "Hello, please send your myTeamSpeak ID to our server! Should not you be logged in to myTeamSpeak, login/register at [URL=https://www.myteamspeak.com/register]myTeamSpeak[/URL]!";
# Kick message
$personaldmsg['kickMsg'] = "Missing myTeamSpeak ID!";
# Console message if a user is legit and send the myTeamSpeak ID
$personaldmsg['successfulyJoinC'] = "User %nickname% has a myTeamSpeak Account and send the ID ;)";
# Console message if a client has modified the myTeamSpeak ID
$personaldmsg['modified_myID'] = "User %nickname% has modified the myTeamSpeak ID!";
# Console message on kick
$personaldmsg['kickConsole'] = "User %nickname% kicked, reason: He is not registred on myTeamSpeak!";
# Console message on invalid client
$personaldmsg['invalidclientid'] = "A small hybrid has logged in to the server. We do not check this :D";

# Here you can specify from which minimum version the client should be kicked.
$kickoption['ClientMinVersion'] = '1530280071'; // as Timestamp!
# Console Printing Message
$kickoption['ClientMinVersionElMsg'] = 'The version of %nickname% is too old, he can continue without check!';

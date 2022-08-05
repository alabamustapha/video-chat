<?php
// Get the PHP helper library from https://twilio.com/docs/libraries/php
require_once 'vendor/autoload.php'; // Loads the library
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;
use Twilio\Rest\Client;

// Required for all Twilio access tokens
// To set up environmental variables, see http://twil.io/secure
$twilioAccountSid = 'ACadf9fd2b72eb4f14a55ecc0748899bae';
$twilioApiKey = 'SK05009df2443d72d2dd5dcafe78630a49';
$twilioApiSecret = '3VPn5PYvMg34dSzMM4gv5AZ3nKKiSofQ';
$authToken = '096dad8617e79c6b831fba87c5c64b93';
$roomName = "DailyStandup";


$twilio = new Client($twilioAccountSid, $authToken);

$rooms = $twilio->video->v1->rooms
                           ->read([
                                      "uniqueName" => "DailyStandup",
                                      "status" => "in-progress"
                                  ],
                                  20
                           );

var_dump(count($rooms));

if(count($rooms) == 0){
    // create room
    var_dump("Room doesn't not exist");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://video.twilio.com/v1/Rooms');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "UniqueName=" . $roomName);
    curl_setopt($ch, CURLOPT_USERPWD, $twilioApiKey . ':' . $twilioApiSecret);
    
    $headers = array();
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $result = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
}else{
    var_dump("Room exist");
}

if(isset($_GET['join_room'])){
    // Used as an identifier for each users joining a room - can be anything you'd like
    $identity = 'user-' . uniqid();
    // Create access token, which we will serialize and send to the client
    $token = new AccessToken(
        $twilioAccountSid,
        $twilioApiKey,
        $twilioApiSecret,
        3600,
        $identity
    );

    // Create Video grant
    $videoGrant = new VideoGrant();
    $videoGrant->setRoom($roomName);
    // Add grant to token
    $token->addGrant($videoGrant);

    // render token to string
    echo "<input type='hidden' value='{$token->toJWT()}' id='twilio_token' name='twilio_token' />";
}



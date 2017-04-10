<?php

function getDurationAgo( $ptime )
{
    $estimate_time = time() - strtotime($ptime);

    if( $estimate_time < 1 )
    {
        return 'less than 1 second ago';
    }

    $condition = array( 
                12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
    );

    foreach( $condition as $secs => $str )
    {
        $d = $estimate_time / $secs;

        if( $d >= 1 )
        {
            $r = round( $d );
            return 'about ' . $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . ' ago';
        }
    }
}

function getGeoCode( $address = '')
{
    //Send request and receive json data
    $geocodeFrom = file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.urlencode('da nang').'&sensor=false&region=Poland');
    return json_decode($geocodeFrom);
}


function getDistance($geoCodeFrom, $geoCodeTo, $unit)
{
    //Change address format
    $formattedAddrFrom = str_replace(' ','+',$addressFrom);
    $formattedAddrTo = str_replace(' ','+',$addressTo);

    //Send request and receive json data
    $geocodeFrom = file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$formattedAddrFrom.'&sensor=false');
    $outputFrom = json_decode($geocodeFrom);
    $geocodeTo = file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$formattedAddrTo.'&sensor=false');
    $outputTo = json_decode($geocodeTo);

    //Get latitude and longitude from geo data
    $latitudeFrom = $outputFrom->results[0]->geometry->location->lat;
    $longitudeFrom = $outputFrom->results[0]->geometry->location->lng;
    $latitudeTo = $outputTo->results[0]->geometry->location->lat;
    $longitudeTo = $outputTo->results[0]->geometry->location->lng;

    //Calculate distance from latitude and longitude
    $theta = $longitudeFrom - $longitudeTo;
    $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);
    if ($unit == "K") {
        return ($miles * 1.609344).' km';
    } else if ($unit == "N") {
        return ($miles * 0.8684).' nm';
    } else {
        return $miles.' mi';
    }
}

    /**
     * @param $deviceTokens
     * @param $messages
     * @param array $extraData
     * @return array
     */
function sendiOSNotification($deviceTokens, $messages = 'Notification', $extraData = array())
{
// Provide the Host Information.
    if (env('NOTIFICATION_IOS_SANDBOX', '')) {
        $tHost = 'gateway.sandbox.push.apple.com';
    } else {
        $tHost = 'gateway.push.apple.com';
    }

    if(!is_array($deviceTokens)){
        $deviceTokens = array($deviceTokens);
    }

    $tPort = 2195;

    // Provide the Certificate and Key Data.
    $tCert = public_path().'/'.env('NOTIFICATION_IOS_CRT_FILE', '');

    $tPassphrase = env('NOTIFICATION_IOS_PASS_PHRASE', '');

    // The Badge Number for the Application Icon (integer >=0).
    $tBadge = 1;
    if (!empty($extraData['badge'])) {
        $tBadge = count($extraData['badge']);
        unset($extraData['badge']);
    }

    // Audible Notification Option.
    $tSound = 'default';

    // Create the message content that is to be sent to the device.
    $tBody['aps'] = array(
        'alert' => $messages,
        'badge' => $tBadge,
        'sound' => $tSound
    );
    
    if (!empty($extraData)) {
        foreach ($extraData as $k => $v) {
            $tBody['aps'][$k] = $v;
        }
    }

    // Encode the body to JSON.
    $tBody = json_encode($tBody);

    // Create the Socket Stream.
    $tContext = stream_context_create();
    stream_context_set_option($tContext, 'ssl', 'local_cert', $tCert);

    // Remove this line if you would like to enter the Private Key Passphrase manually.
    // stream_context_set_option($tContext, 'ssl', 'passphrase', $tPassphrase);

    // Open the Connection to the APNS Server.
    $tSocket = stream_socket_client('ssl://' . $tHost . ':' . $tPort, $error, $errstr, 30, STREAM_CLIENT_CONNECT, $tContext);

    // Check if we were able to open a socket.
    if (!$tSocket)

        exit ("APNS Connection Failed: $error $errstr" . PHP_EOL);

    $tResult[] = array();
    foreach ($deviceTokens as $deviceToken) {
        // Build the binary notification
        $msg = chr(0) . chr(0) . chr(32) . pack('H*', $deviceToken) . pack('n', strlen($tBody)) . $tBody;
        $result = fwrite($tSocket, $msg);
        $tResult[] = $result;
        // if (!$result)
        //     echo 'Message not delivered' . PHP_EOL;
        // else
        //     echo 'Message successfully delivered' . PHP_EOL;
    }

    // Close the Connection to the Server.
    fclose($tSocket);
    return 'Message successfully delivered';
}


?>
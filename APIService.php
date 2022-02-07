<?php

ini_set("display_errors", 1);
header("Content-Type: application/json; charset=UTF-8");
if(isset($_GET['task']))
{
    $task =  $_GET['task'];
    switch($task)
    {
        case 1://GetToken
            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $token =  md5(uniqid(time(), true));
                WriteLog("Token Response :".$token);
                echo $token;
                http_response_code(200);
            }
            else
            {
                http_response_code(405); //405 = Method Not Allowed ie if POST is used instead of GET 
            }
            break;

        case 2://GetBookingbyCamera
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                http_response_code(200);
                if(isset($_SERVER['HTTP_AUTHTOKEN']))
                {
                    $data = file_get_contents("php://input");
                    WriteLog("Request: ".$data);
                    $json_request = json_decode($data,true);
                    $medium = $json_request["medium"];
                    $identifier = $json_request["identifier"];
                    $timestamp = $json_request["timestamp"];
                    $con=DBConnect();

                    if($medium == "Plate Number")
                    {
                        $query="select * from  access_whitelist where ticket_id = '".$identifier."' and status = 1";
                    }
                    else if($medium = "QR")
                    {
                        $query="select * from  access_whitelist where booking_id = '".$identifier."' and status = 1";
                    }

                    $result=mysqli_query($con,$query) or die(mysqli_error($con));
                    if(mysqli_num_rows($result) > 0)
                    {      
                        $response['response']['status'] = true;
                        $response['response']['message']  = "Valid Booking";
                    }
                    else { 
                        $response['response']['status'] = false;
                        $response['response']['message']  = "Invalid booking";
                    }

                    mysqli_close($con);       

                    
                    WriteLog("GetBooking Response: ".json_encode($response));
                    echo json_encode($response);
                }
                else
                {
                    $response['response']['status'] = false;
                    $response['response']['message']  = "Auth Failed";
                    WriteLog("GetBooking Response: ".json_encode($response));
                    echo json_encode($response);
                }
                

            }
            else
            {
                http_response_code(405); //405 = Method Not Allowed ie if GET is used instead of POST 
            }
            break;
        default://Invalid request
        {
            http_response_code(400); 
        }
    }
}
 else {
   http_response_code(400); 
 }



function DBConnect()
{
    $conn=null;
    $serverName = "localhost";
    $uid = "parcxservice";
    $pwd = "1fromParcx!19514";
    $databaseName = "ParcxTerminal";
    $conn = new mysqli($serverName, $uid, $pwd,$databaseName);
    // Check connection
    if($conn->connect_error)
    {

        die("Mysql Connection failed: " . $conn->connect_error);
        WriteLog("Mysql Connection Failed. Error:". $conn->connect_error);
    }

    return $conn;
}


function WriteLog($data)
{
    date_default_timezone_set('Asia/Dubai');
    if (!file_exists('Logs')) {
        mkdir('Logs', 0777, true);
    }
    $file = "Logs/".date('Y-m-d');
    $fh = fopen($file, 'a') or die("can't open file");
    fwrite($fh,"\n");
    fwrite($fh,"Date :".date('Y-m-d H:i:s'). " ");
    fwrite($fh,$data);
    fclose($fh);
}

function isJson($string) {
 json_decode($string);
 return (json_last_error() == JSON_ERROR_NONE);
}

?>
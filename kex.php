<?php

include 'db.php';
$db = new db;

$diff_limit = 20;

if (!empty($_POST['timeArrays'])) {

    $data = json_decode($_POST['timeArrays']);
    //echo '<pre>'; print_r($data); echo '</pre>';

    // check if such email already exists
    $sql = "SELECT id FROM users WHERE email = '".$data->text."' LIMIT 1";
    $result = $db->query($sql);
    if ($result->num_rows > 0) {

        echo '<br>Comparing with existing user...<br>';

        // if user found perform check of time data for compatibility
        $user = $result->fetch_assoc();
        $sql_previous_data = "SELECT `signature_data` FROM `entries` WHERE `user_id` = '".$user['id']."' LIMIT 1";
        $result_previous_data = $db->query($sql_previous_data);
        $previous_data = $result_previous_data->fetch_assoc();
        $previous_data = json_decode($previous_data['signature_data']);

        // filter received sequences from backspaces, deletes and arrows
        for ($i=0; $i < count($data->codeArray); $i++) {
            if ($data->codeArray[$i] == 8) {
                echo '<br> backspace found, deleting... ['.$i.'] <br>';
                // if backspace delete this and previous (deleted by backspace) chars
                unset($data->codeArray[$i]);
                unset($data->codeArray[$i-1]);
                // and delete according hold periods
                unset($data->sequenceHold[$i]);
                unset($data->sequenceHold[$i-1]);
                // and delete according between periods from both base and incoming arrays
                unset($data->sequenceBetween[$i]);
                unset($data->sequenceBetween[$i-1]);
                unset($data->sequenceBetween[$i-2]);
                if ($previous_data->sequenceBetween[$i-1]) {
                    unset($previous_data->sequenceBetween[$i-1]);
                }
            }
        }
        // and reindex arrays
        $data->codeArray = array_values($data->codeArray);
        $data->sequenceHold = array_values($data->sequenceHold);
        $data->sequenceBetween = array_values($data->sequenceBetween);
        $previous_data->sequenceBetween = array_values($previous_data->sequenceBetween);

        // print out both data sets
        echo '<div style="float: left;"><pre>'; print_r($previous_data); echo '</pre></div>';
        echo '<span style="padding: 0 30px 40px; float: left;"><pre>'; print_r($data); echo '</pre></span>';

        // check hold periods
        $percent = array(); // this is used to calculate average difference in percents
        for ($i=0; $i < count($previous_data->sequenceHold); $i++) {
            $base = $previous_data->sequenceHold[$i];
            $temp = $data->sequenceHold[$i];
            $diff = abs($base - $temp);
            $percent[] = round(100 * $diff / $base, 2);
        }
        $hold_percent = round(array_sum($percent)/count($percent), 2);
        echo '<br>Hold difference average percent '.$hold_percent.'%<br>';

        // check between periods
        $percent = array(); // this is used to calculate average difference in percents
        for ($i=0; $i < count($previous_data->sequenceBetween); $i++) {
            $base = $previous_data->sequenceBetween[$i];
            $temp = $data->sequenceBetween[$i];
            $diff = abs($base - $temp);
            $percent[] = round(100 * $diff / $base, 2);
        }
        $between_percent = round(array_sum($percent)/count($percent), 2);
        echo '<br>Between difference average percent '.$between_percent.'%<br>';

        // calculate average of both differences
        $total_diff = ($between_percent + $hold_percent) / 2;
        echo '<br>Total difference = '.$total_diff.'%<br>';

        if ($total_diff <= $diff_limit) {
            echo '<br><div style="font-size: 18px; color: darkgreen">You kinda passed the authentication</div>';
        } else {
            echo '<br><div style="font-size: 18px; color: darkred">You kinda failed the authentication</div>';
        }

    } else {
        // if no such user then add him
        $sql_create_user = "INSERT INTO users (`email`) VALUES ('".$data->text."')";
        $result_create_user = $db->query($sql_create_user);

        // and add time data related to this user
        $sql_create_time_data = "INSERT INTO `entries` (`user_id`, `signature_data`, `time_attempt`) VALUES ('".$db->get_insert_id()."', '".$_POST['timeArrays']."', '".date('Y-m-d h:i:s')."')";
        $db->query($sql_create_time_data);

        echo '<br>New user ('.$data->text.') added to database.<br>';
    }

}
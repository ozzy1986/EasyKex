<?php

include 'db.php';
$db = new db;

$diff_limit = 20; // maximum percent of average difference in hold and interval periods
$mismatch_limit = 2; // maximum number of edits (delete and backspace)


if (!empty($_POST['timeArrays'])) {
    $mismatch_count = 0;
    $between_diff = array();
    $hold_diff = array();


    $data = json_decode($_POST['timeArrays'], true);
    //echo '<pre>'; print_r($data); echo '</pre>';

    // check if such email already exists
    $sql = "SELECT id FROM users WHERE email = '".$data['text']."' LIMIT 1";
    $result = $db->query($sql);
    if ($result->num_rows > 0) {

        echo '<br>Comparing with existing user...<br>';

        // if user found perform check of time data for compatibility
        $user = $result->fetch_assoc();
        $sql_previous_data = "SELECT `signature_data` FROM `entries` WHERE `user_id` = '".$user['id']."' LIMIT 1";
        $result_previous_data = $db->query($sql_previous_data);
        $base_data = $result_previous_data->fetch_assoc();
        $base_data = json_decode($base_data['signature_data'] ,true);

        // print out both data sets
        //echo '<div style="float: left;"><pre>'; print_r($base_data); echo '</pre></div>';
        //echo '<span style="padding: 0 30px 40px; float: left;"><pre>'; print_r($data); echo '</pre></span>';


        // let's calculate arrhythmia of typing speed
        $pause_max = max($data['sequenceBetween']);
        $m_pause = array_sum($data['sequenceBetween']) / ($pause_max * count($data['sequenceBetween']));
        $temp = 0;
        foreach($data['sequenceBetween'] as $pause) {
            $temp += pow($pause/$pause_max - $m_pause, 2);
        }
        $pause_arrhythmia = sqrt($temp/(count($data['sequenceBetween']) - 1));
        echo '<br>Arrhythmia of typing speed is '.$pause_arrhythmia.'<br>';


        // let's calculate arrhythmia of holding keys down
        $press_max = max($data['sequenceHold']);
        $m_press = array_sum($data['sequenceHold']) / ($press_max * count($data['sequenceHold']));
        $temp = 0;
        foreach($data['sequenceHold'] as $press) {
            $temp += pow($press/$press_max - $m_press, 2);
        }
        $press_arrhythmia = sqrt($temp/(count($data['sequenceHold']) - 1));
        echo '<br>Arrhythmia of pressing keys is '.$press_arrhythmia.'<br>';


        // typing speed
        $speed = 900*$data['totalTime']/60000;
        echo '<br>Speed = '.$speed.'<br>';

        // overlapping
        $overlap_total = array_sum($data['overlapTime']);
        $overlap_max = max($data['overlapTime']);
        $overlap_average = $overlap_total / (count($data['overlapTime']) * $overlap_max);
        $temp = 0;
        foreach($data['overlapTime'] as $term) {
            $temp += pow($term/$overlap_max - $overlap_average, 2);
        }
        $overlap_deviation = sqrt($temp / (count($data['overlapTime']) - 1));


        // normalized chars
        foreach($data['codeArray'] as $char) {
            $chars_norm[] = ($char - 32) / 223;
        }


        // Vector
        $vector = array_merge(
            $data['sequenceHold'], $data['sequenceBetween'], $chars_norm,
            array($m_pause, $pause_arrhythmia, $m_press, $press_arrhythmia, $speed, $overlap_average, $overlap_deviation)
        );
        $vector_length = 3 * (count($data['codeArray']) - 2);



        // let's compare by walking through the base array and compare each element with the incoming one
        foreach($base_data['between'] as $i => $v1) {
            if ($data['between'][$i]) {
                foreach($v1 as $j => $v2) {
                    if ($data['between'][$i][$j]) {
                        foreach($v2 as $k => $v3) {
                            if ($data['between'][$i][$j][$k]) {
                                // let's compare intervals at last
                                $between_diff[] = round(abs($v3 - $data['between'][$i][$j][$k]) / $v3, 2);
                            } else {
                                $mismatch_count++;
                            }
                        }
                    } else {
                        $mismatch_count++;
                    }
                }
            } else {
                $mismatch_count++;
            }
        }
        $average_between_diff = round(array_sum($between_diff)*100 / count($between_diff), 2);
        echo '<br>Mismatch count = '.$mismatch_count.'<br>';
        echo '<br>Average difference in "between" intervals = '.$average_between_diff.'%<br>';


        // and through hold array now
        foreach($base_data['hold'] as $i => $v1) {
            if ($data['hold'][$i]) {
                foreach($v1 as $j => $v2) {
                    if ($data['hold'][$i][$j]) {
                        // compare hold periods
                        $hold_diff[] = round(abs($v2 - $data['hold'][$i][$j]) / $v2, 2);
                    } else {
                        $mismatch_count++;
                    }
                }
            } else {
                $mismatch_count++;
            }
        }
        $average_hold_diff = round(array_sum($hold_diff)*100 / count($hold_diff), 2);
        echo '<br>Average difference in "hold" intervals = '.$average_hold_diff.'%<br>';



        $total_diff = ($average_between_diff + $average_hold_diff) / 2;
        echo '<br>Total average difference = '.$total_diff.'%<br>';
        if ($total_diff <= $diff_limit and $mismatch_count <= $mismatch_limit) {
            // add keyboard data to this user
            $sql_add_entry = "INSERT INTO `entries` (`user_id`, `signature_data`, `time_attempt`) VALUES ('".$user['id']."', '".$_POST['timeArrays']."', '".date('Y-m-d h:i:s')."')";
            $db->query($sql_add_entry);

            echo '<br><div style="font-size: 18px; color: darkgreen">You kinda passed the authentication</div>';
        } else {
            echo '<br><div style="font-size: 18px; color: darkred">You kinda failed the authentication</div>';
        }

    } else {
        // if no such user then add him
        $sql_create_user = "INSERT INTO users (`email`) VALUES ('".$data['text']."')";
        $result_create_user = $db->query($sql_create_user);

        // and add time data related to this user
        $sql_create_time_data = "INSERT INTO `entries` (`user_id`, `signature_data`, `time_attempt`) VALUES ('".$db->get_insert_id()."', '".$_POST['timeArrays']."', '".date('Y-m-d h:i:s')."')";
        $db->query($sql_create_time_data);

        echo '<br>New user ('.$data['text'].') added to database.<br>';
    }

}
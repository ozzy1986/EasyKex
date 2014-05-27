<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

set_time_limit(300);

require 'data_model.php';
require 'user_model.php';
require 'db.php';
$db = new db;

$diff_limit = 20; // maximum percent of average difference in hold and interval periods
$mismatch_limit = 2; // maximum number of edits (delete and backspace)


if (!empty($_POST['timeArrays'])) {
    $mismatch_count = 0;
    $between_diff = array();
    $hold_diff = array();

    $student_005 = array(
        2 => 6.31,
        3 => 2.92,
        4 => 2.35,
        5 => 2.13,
        6 => 2,02,
        7 => 1.94,
        8 => 1.90,
        9 => 1.86,
        10 => 1.83,
        11 => 1.81,
        12 => 1.80,
        13 => 1.78,
        14 => 1.77,
        15 => 1.76,
        16 => 1.75,
        17 => 1.75,
        18 => 1.74,
        19 => 1.73,
        20 => 1.73,
        21 => 1.73,
        22 => 1.72,
        23 => 1.72,
        24 => 1.71,
        25 => 1.71,
        26 => 1.71,
        27 => 1.71,
        28 => 1.70,
        29 => 1.70,
        30 => 1.70
    );

    $incoming_data = new Data($_POST['timeArrays']);

    // check if such email already exists
    $user = new User;
    if ($user->getUserDataByEmail($incoming_data->getData('text'))) {

        echo '<br>Comparing with existing user...<br>';

        // if user found perform check of time data for compatibility
        $base_data = $user->data['Data'];
        $data = $incoming_data->getData();

        // print out both data sets
        //echo '<div style="float: left;"><pre>'; print_r($base_data); echo '</pre></div>';
        //echo '<span style="padding-left: 30px; padding-right: 100px; float: left;"><pre>'; print_r($data); echo '</pre></span>';


        // let's calculate arrhythmia of typing speed
        $pause_arrhythmia = $incoming_data->getArrhythmiaPause();
        //echo '<br>Arrhythmia of typing speed is '.$pause_arrhythmia.'<br>';


        // let's calculate arrhythmia of holding keys down
        $press_arrhythmia = $incoming_data->getArrhythmiaPress();
        //echo '<br>Arrhythmia of pressing keys is '.$press_arrhythmia.'<br>';


        // typing speed
        $speed = $incoming_data->getTypingSpeed();
        //echo '<br>Speed = '.$speed.'<br>';

        // overlapping
        $overlap_deviation = $incoming_data->getOverlapDeviation();
        $overlap_average = $incoming_data->getParameter('overlap_average');


        // normalized chars
        $chars_norm = $incoming_data->getNormalizedChars();


        // Vector
        $vector = $incoming_data->getVector();
        $vector_dimension = $incoming_data->getParameter('vector_dimension');
        //echo '<br>Vector:<pre>'; print_r($vector); echo '</pre>';

        // THIS IS NOT FINAL
        $vectors = $incoming_data->getFakeVectorsVariety();
        //echo '<br>Vectors<pre>'; print_r($vectors); echo '</pre><br>';




        require 'matrix_class.php';
        $matrix = new matrix;

        // let's calculate covariance matrix
        $covariance = $incoming_data->getCovariance();
        //echo '<br>Covariance matrix<pre>'; print_r($covariance); echo '</pre><br>';


        $inverted_covariance = $matrix->invert($covariance);
        //echo '<br>Inverted covariance matrix<br>'; $matrix->print_matrix($inverted_covariance);


        // get this specific sum
        $vector_surface = 0;
        $vector_variety_expectation = $incoming_data->getParameter('vector_variety_expectation');
        for ($j=0; $j<$vector_dimension; $j++) {
            for ($k=0; $k<$vector_dimension; $k++) {
                $vector_surface += $inverted_covariance[$j][$k] * ($vector[$j] - $vector_variety_expectation[$j]) * ($vector[$k] - $vector_variety_expectation[$k]);
            }
        }

        // number of trueth vectors
        $vector_variety = $incoming_data->getParameter('vector_variety');
        $number_of_vectors = count($vector_variety);

        // Final formula
        $g_V = ($vector_surface) / 2 - pow($student_005[$number_of_vectors], 2);
        echo '<br><br>So the final value is: '.$g_V.'<br><br>';


        if ($g_V < 0 and $mismatch_count <= $mismatch_limit) {
            // add keyboard data to this user
            /*$sql_add_entry = "INSERT INTO `entries` (`user_id`, `signature_data`, `time_attempt`) VALUES ('".$user['id']."', '".$_POST['timeArrays']."', '".date('Y-m-d h:i:s')."')";
            $db->query($sql_add_entry);*/

            echo '<br><div style="font-size: 18px; color: darkgreen">You passed the authentication (vector logic)</div>';
        } else {
            echo '<br><div style="font-size: 18px; color: darkred">You failed the authentication (vector logic)</div>';
        }





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
        //echo '<br>Mismatch count = '.$mismatch_count.'<br>';
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
            /*$sql_add_entry = "INSERT INTO `entries` (`user_id`, `signature_data`, `time_attempt`) VALUES ('".$user['id']."', '".$_POST['timeArrays']."', '".date('Y-m-d h:i:s')."')";
            $db->query($sql_add_entry);*/

            echo '<br><div style="font-size: 18px; color: darkgreen">You passed the authentication (simple logic)</div>';
        } else {
            echo '<br><div style="font-size: 18px; color: darkred">You failed the authentication (simple logic)</div>';
        }

    } else {
        // if no such user then add him
        $user->addUser($incoming_data->getData('text'), $incoming_data->getParameter('json_encoded_data'));

        echo '<br>New user ('.$incoming_data->getData('text').') added to database.<br>';
    }

}
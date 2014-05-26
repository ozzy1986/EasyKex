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



        $test_matrix = array(
            array(38218331408.41, 65064726177.83, 46974411236.62, 54319427306.36, 41723946836.52, 28092241663.31, 215948697961.89),
            array(65064726177.83, 110769320286.57, 79971497756.26, 92476006502.16, 71032854547.38, 47825583801.98, 367641453291.17),
            array(46974411236.62,	79971497756.26,	57736568544.74,	66764377784.05,	51283187017.49,	34528365415.78,	265424014347.29),
            array(54319427306.36,	92476006502.16,	66764377784.05,	77203793942.78,	59301932177.57,	39927292026.34,	306926262046.73),
            array(41723946836.52,	71032854547.38,	51283187017.49,	59301932177.57,	45551118415.24,	30669031181.75,	235756812533.08),
            array(28092241663.31,	47825583801.98,	34528365415.78,	39927292026.34,	30669031181.75,	20649097241.75,	158732283343.17),
            array(215948697961.89,	367641453291.17, 265424014347.29, 306926262046.73, 235756812533.08, 158732283343.17, 1220195608570.71)
        );
        $inverted_covariance = $matrix->invert($test_matrix, true);
        $matrix->print_matrix($inverted_covariance);

        $inverted_covariance = $matrix->invert($covariance, true);
        echo '<br>And this one is the real matrix<br>';
        $matrix->print_matrix($inverted_covariance);


        // Final formula
        //$g_V = () / 2;



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

            echo '<br><div style="font-size: 18px; color: darkgreen">You kinda passed the authentication</div>';
        } else {
            echo '<br><div style="font-size: 18px; color: darkred">You kinda failed the authentication</div>';
        }

    } else {
        // if no such user then add him
        $user->addUser($incoming_data->getData('text'), $incoming_data->getParameter('json_encoded_data'));

        echo '<br>New user ('.$incoming_data->getData('text').') added to database.<br>';
    }

}
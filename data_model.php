<?php

class Data {

    private $data;
    private $json_encoded_data;

    protected $m_pause;
    protected $m_press;
    protected $arrhythmia_pause;
    protected $arrhythmia_press;
    protected $speed;
    protected $overlap_average;
    protected $overlap_deviation;
    protected $chars_norm;
    protected $vector;
    protected $vector_dimension;
    protected $vector_variety;
    protected $vector_variety_expectation;
    protected $covariance;


    public function __construct($json_encoded_data) {
        $this->json_encoded_data = $json_encoded_data;
        $this->data = json_decode($this->json_encoded_data, true);
    }

    public function getData($field=null) {
        if (empty($field)) {
            return $this->data;
        } else {
            if (empty($this->data[$field])) {
                return false;
            } else {
                return $this->data[$field];
            }
        }
    }

    public function getParameter($name) {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            return false;
        }
    }

    public function getExpectedPause() {
        $pause_max = max($this->data['sequenceBetween']);
        $m_pause = array_sum($this->data['sequenceBetween']) / ($pause_max * count($this->data['sequenceBetween']));

        $this->m_pause = $m_pause;
        return $m_pause;
    }

    public function getArrhythmiaPause() {
        $pause_max = max($this->data['sequenceBetween']);
        $m_pause = $this->getExpectedPause();
        $temp = 0;
        foreach($this->data['sequenceBetween'] as $pause) {
            $temp += pow($pause/$pause_max - $m_pause, 2);
        }
        $pause_arrhythmia = sqrt($temp/(count($this->data['sequenceBetween']) - 1));

        $this->arrhythmia_pause = $pause_arrhythmia;
        return $pause_arrhythmia;
    }

    public function getExpectedPress() {
        $press_max = max($this->data['sequenceHold']);
        $m_press = array_sum($this->data['sequenceHold']) / ($press_max * count($this->data['sequenceHold']));

        $this->m_press = $m_press;
        return $m_press;
    }

    public function getArrhythmiaPress() {
        $press_max = max($this->data['sequenceHold']);
        $m_press = $this->getExpectedPress();
        $temp = 0;
        foreach($this->data['sequenceHold'] as $press) {
            $temp += pow($press/$press_max - $m_press, 2);
        }
        $press_arrhythmia = sqrt($temp/(count($this->data['sequenceHold']) - 1));

        $this->press_arrhythmia = $press_arrhythmia;
        return $press_arrhythmia;
    }

    public function getTypingSpeed() {
        $speed = 900 * $this->data['totalTime']/60000;

        $this->speed = $speed;
        return $speed;
    }

    public function getOverlapAverage() {
        if (!empty($this->data['overlapTime']) and count($this->data['overlapTime']) > 0) {
            $overlap_total = array_sum($this->data['overlapTime']);
            $overlap_max = max($this->data['overlapTime']);
            $overlap_average = $overlap_total / (count($this->data['overlapTime']) * $overlap_max);
        } else {
            $overlap_average = 0;
        }

        $this->overlap_average = $overlap_average;
        return $overlap_average;
    }

    public function getOverlapDeviation() {
        if (!empty($this->data['overlapTime']) and count($this->data['overlapTime']) > 0) {
            $overlap_max = max($this->data['overlapTime']);
            $overlap_average = $this->getOverlapAverage();
            $temp = 0;
            foreach($this->data['overlapTime'] as $term) {
                $temp += pow($term/$overlap_max - $overlap_average, 2);
            }
            $overlap_deviation = sqrt($temp / (count($this->data['overlapTime']) - 1));
        } else {
            $overlap_deviation = 0;
            $this->overlap_average = 0;
        }

        $this->overlap_deviation = $overlap_deviation;
        return $overlap_deviation;
    }

    public function getNormalizedChars() {
        $chars_norm = array();
        foreach($this->data['codeArray'] as $char) {
            $chars_norm[] = ($char - 32) / 223;
        }

        $this->chars_norm = $chars_norm;
        return $chars_norm;
    }

    public function getVector() {

        $chars_norm = $this->getNormalizedChars();
        $pause_arrhythmia = $this->getArrhythmiaPause();
        $m_pause = $this->m_pause;
        $press_arrhythmia = $this->getArrhythmiaPress();
        $m_press = $this->m_press;
        $speed = $this->getTypingSpeed();
        $overlap_deviation = $this->getOverlapDeviation();
        $overlap_average = $this->overlap_average;


        if ($overlap_average > 0) {
            $vector = array_merge(
                $this->data['sequenceHold'], $this->data['sequenceBetween'], $chars_norm,
                array($m_pause, $pause_arrhythmia, $m_press, $press_arrhythmia, $speed, $overlap_average, $overlap_deviation)
            );
        } else {
            $vector = array_merge(
                $this->data['sequenceHold'], $this->data['sequenceBetween'], $chars_norm,
                array($m_pause, $pause_arrhythmia, $m_press, $press_arrhythmia, $speed)
            );
        }

        $vector_length = 3 * (count($this->data['codeArray']) - 2);

        $this->vector_dimension = count($vector);
        $this->vector = $vector;
        return $vector;
    }

    public function getFakeVectorsVariety() {
        if ($this->vector) {
            $vector = $this->vector;
        } else {
            $vector = $this->getVector();
        }
        $vector_dimension = $this->vector_dimension;

        $vectors = array();
        for ($n=0; $n < 10; $n++) {
            $temp = array();
            for ($i=0; $i<$vector_dimension; $i++) {
                if (rand()&1 == 1) {
                    $temp[] = $vector[$i] + $vector[$i]*rand(10,20)/100;
                } else {
                    $temp[] = $vector[$i] - $vector[$i]*rand(10,20)/100;
                }
            }
            $vectors[] = $temp;
        }
        array_unshift($vectors, $vector);

        $this->vector_variety = $vectors;
        return $vectors;
    }

    public function getCovariance() {
        if ($this->vector_variety) {
            $vectors = $this->vector_variety;
        } else {
            $vectors = $this->getFakeVectorsVariety();
        }
        $vector_dimension = $this->vector_dimension;

        // calculate sums of each elements
        $sums = array();
        for ($i=0; $i<$vector_dimension; $i++) {
            $temp = 0;
            for ($j=0; $j<count($vectors); $j++) {
                $temp += $vectors[$j][$i];
            }
            $sums[] = $temp;
        }

        // let's calculate covariance matrix
        $covariance = array();
        for ($j=0; $j<$vector_dimension; $j++) {
            for ($k=0; $k<$vector_dimension; $k++) {
                $covariance[$j][$k] = ( $sums[$j] / count($vectors)  -  pow($sums[$j], 2) / count($vectors) ) * ( $sums[$k] / count($vectors)  -  pow($sums[$k], 2) / count($vectors) );

                $covariance[$j][$k] = round($covariance[$j][$k], 4); // let's decrease numbers a bit
            }
        }

        $this->vector_variety_expectation = $sums;

        $this->covariance = $covariance;
        return $covariance;
    }

}
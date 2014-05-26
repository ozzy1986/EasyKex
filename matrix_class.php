<?php

class matrix {

    public function __construct() {

    }

    /**
     * Inverts a given matrix
     *
     * @param array $A matrix to invert
     * @param boolean $debug whether to print out debug info
     *
     * @return array inverted matrix
     */
    public function invert($A, $debug = false) {
        /// @todo check rows = columns

        if ($debug) {
            echo '<br>Initial matrix<br>';
            $this->print_matrix($A);
        }

        $n = count($A);

        // get and append identity matrix
        $I = $this->identity_matrix($n);
        for ($i = 0; $i < $n; ++ $i) {
            $A[$i] = array_merge($A[$i], $I[$i]);
        }

        if ($debug) {
            echo "\nStarting matrix: <br>";
            $this->print_matrix($A);
        }

        // forward run
        for ($j = 0; $j < $n-1; ++ $j) {
            // for all remaining rows (diagonally)
            for ($i = $j+1; $i < $n; ++ $i) {
                // adjust scale to pivot row
                // subtract pivot row from current
                if (empty($A[$i][$j])) {
                    echo '<br>$A['.$i.']['.$j.'] = '.$A[$i][$j].'<br>';
                    echo '|||'; $this->print_matrix($A); echo '|||';
                }
                $scalar = bcdiv($A[$j][$j], $A[$i][$j]); // $scalar = $A[$j][$j] / $A[$i][$j];
                for ($jj = $j; $jj < $n*2; ++ $jj) {
                    $A[$i][$jj] = bcmul($A[$i][$jj], $scalar); // $A[$i][$jj] *= $scalar;
                    $A[$i][$jj] = bcsub($A[$i][$jj], $A[$j][$jj]); // $A[$i][$jj] -= $A[$j][$jj];
                }
            }
            if ($debug) {
                echo "\nForward iteration $j: ";
                $this->print_matrix($A);
            }
        }

        // reverse run
        for ($j = $n-1; $j > 0; -- $j) {
            for ($i = $j-1; $i >= 0; -- $i) {
                $scalar = bcdiv($A[$j][$j], $A[$i][$j]); // $scalar = $A[$j][$j] / $A[$i][$j];
                for ($jj = $i; $jj < $n*2; ++ $jj) {
                    $A[$i][$jj] = bcmul($A[$i][$jj], $scalar); // $A[$i][$jj] *= $scalar;
                    $A[$i][$jj] = bcsub($A[$i][$jj], $A[$j][$jj]); // $A[$i][$jj] -= $A[$j][$jj];
                }
            }
            if ($debug) {
                echo "\nReverse iteration $j: <br>";
                $this->print_matrix($A);
            }
        }

        // last run to make all diagonal 1s
        /// @note this can be done in last iteration (i.e. reverse run) too!
        for ($j = 0; $j < $n; ++ $j) {
            if ($A[$j][$j] !== 1) {
                $scalar = bcdiv(1, $A[$j][$j]); // $scalar = 1 / $A[$j][$j];
                for ($jj = $j; $jj < $n*2; ++ $jj) {
                    $A[$j][$jj] = bcmul($A[$j][$jj], $scalar); // $A[$j][$jj] *= $scalar;
                }
            }
            if ($debug) {
                echo "\n1-out iteration $j: <br>";
                $this->print_matrix($A);
            }
        }

        // take out the matrix inverse to return
        $Inv = array();
        for ($i = 0; $i < $n; ++ $i) {
            $Inv[$i] = array_slice($A[$i], $n);
        }

        return $Inv;
    }

    /**
     * Prints matrix
     *
     * @param array $A matrix
     * @param integer $decimals number of decimals
     */
    public function print_matrix($A, $decimals = 2) {
        echo '<div style="font-size: 12px;"><table>';
        foreach ($A as $row_num => $row) {
            if ($row_num == 0) {
                echo '<tr><td></td><td></td>';
                for($x=0; $x<count($A[$row_num]); $x++) {
                    echo '<td style="text-align: center;">'.$x.'</td>';
                }
                echo '<td></td></tr>';
            }
            echo '<tr><td>'.$row_num.'</td><td>[</td>';
            foreach ($row as $c => $i) {
                $bg_color = 'cornsilk';
                if ($c % 2 == 0) {
                    $bg_color = 'beige';
                }
                echo '<td style="background-color: '.$bg_color.';">' . sprintf("%01.{$decimals}f", round($i, $decimals)) . '</td>';
            }
            echo '<td>]</td></tr>';
        }
        echo '</table></div>';
    }

    /**
     * Prints matrix for http://www.wolframalpha.com/
     *
     * @param array $A matrix
     * @param integer $decimals number of decimals
     */
    public function print_matrix_wolfram($A, $decimals = 2) {
        echo '<div style="font-size: 12px;"><table><tr><td>{</td></tr>';
        foreach ($A as $row_num => $row) {
            echo '<tr><td>'.$row_num.'</td><td>(</td>';
            foreach ($row as $c => $i) {
                $bg_color = 'cornsilk';
                if ($c % 2 == 0) {
                    $bg_color = 'beige';
                }
                echo '<td style="background-color: '.$bg_color.';">' . sprintf("%01.{$decimals}f", round($i, $decimals)) . '</td>';
            }
            echo '<td>)</td></tr>';
        }
        echo '<tr><td>}</td></tr></table></div>';
    }

    /**
     * Produces an identity matrix of given size
     *
     * @param integer $n size of identity matrix
     *
     * @return array identity matrix
     */
    public function identity_matrix($n) {
        $I = array();
        for ($i = 0; $i < $n; ++ $i) {
            for ($j = 0; $j < $n; ++ $j) {
                $I[$i][$j] = ($i == $j) ? 1 : 0;
            }
        }
        return $I;
    }
}


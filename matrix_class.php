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

        $n = count($A);

        // get and append identity matrix
        $I = $this->identity_matrix($n);
        for ($i = 0; $i < $n; ++ $i) {
            $A[$i] = array_merge($A[$i], $I[$i]);
        }

        if ($debug) {
            echo "\nStarting matrix: ";
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
                }
                $scalar = $A[$j][$j] / $A[$i][$j];
                for ($jj = $j; $jj < $n*2; ++ $jj) {
                    $A[$i][$jj] *= $scalar;
                    $A[$i][$jj] -= $A[$j][$jj];
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
                $scalar = $A[$j][$j] / $A[$i][$j];
                for ($jj = $i; $jj < $n*2; ++ $jj) {
                    $A[$i][$jj] *= $scalar;
                    $A[$i][$jj] -= $A[$j][$jj];
                }
            }
            if ($debug) {
                echo "\nReverse iteration $j: ";
                $this->print_matrix($A);
            }
        }

        // last run to make all diagonal 1s
        /// @note this can be done in last iteration (i.e. reverse run) too!
        for ($j = 0; $j < $n; ++ $j) {
            if ($A[$j][$j] !== 1) {
                $scalar = 1 / $A[$j][$j];
                for ($jj = $j; $jj < $n*2; ++ $jj) {
                    $A[$j][$jj] *= $scalar;
                }
            }
            if ($debug) {
                echo "\n1-out iteration $j: ";
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
    public function print_matrix($A, $decimals = 6) {
        foreach ($A as $row) {
            echo "\n\t[";
            foreach ($row as $i) {
                echo "\t" . sprintf("%01.{$decimals}f", round($i, $decimals));
            }
            echo "\t]";
        }
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


class spanishMatrix {
    //global vars
    var $NumFila;
    var $NumColumna;
    var $ArrayData=array();
    //advanced global vars
    var $ArrayMedia;
    var $ArrayMatrizCov;

    /**
     * Contructor de la clase matriz
     *
     * @param array $ArrayDataMatriz
     * @return matrix
     */
    function matrix($ArrayDataMatriz) {
        $this->set_data($ArrayDataMatriz);
        if (!$this->set_properties_matrix())
            return false;
        return true;
    }


    /******************************************/
    /*FUNCIONES DE BASICAS DE LA CLASE MATRIX */

    /**
     * Setea los datos que se le da a la matriz al momento de iniciar el objeto
     *
     * @param array $ArrayDataMatriz
     */
    function set_data($ArrayDataMatriz){
        for ($i=0;$i<count($ArrayDataMatriz);$i++){
            $valor = $ArrayDataMatriz[$i];
            if (count($ArrayDataMatriz[$i])==1){
                $this->ArrayData[$i][0] = $ArrayDataMatriz[$i];
            }
            else
                for ($j=0;$j<count($ArrayDataMatriz[$i]);$j++)
                    $this->ArrayData[$i][$j] = $ArrayDataMatriz[$i][$j];
        }

    }


    /**
     * Setee las propiedades de la matriz como son las filas y columnas
     *
     * @return unknown
     */
    function set_properties_matrix(){
        $this->NumFila = count($this->ArrayData );
        $this->NumColumna = count($this->ArrayData[0]);
        if ($this->ValidaNumColumnasObjMatriz($this->NumFila,$this->NumColumna)){
            return true;
        }
        $this->NumColumna=null;
        $this->NumFila=null;
        return false;
    }

    /**
     * Setee el número de filas de la matriz
     *
     */
    function set_NumFilas(){
        $this->NumFila = count($this->ArrayData[0] );
    }

    /**
     * Setee el número de columna de la matriz
     *
     */
    function set_NumColumnas(){
        $this->NumColumna = count($this->ArrayData);
    }

    /**
     * Obtiene el número de filas que tiene el objeto matriz
     *
     * @return integer
     */
    function get_NumFilas()	{
        return $this->NumFila;
    }

    /**
     * Obtiene el número de columnas que tiene el objeto matriz
     *
     * @return integer
     */
    function get_NumColumnas()	{
        return $this->NumColumna;
    }

    /**
     * Obtiene el arreglo de datos de la matriz media del objeto matriz
     *
     * @return Arraymatriz
     */
    function getMediaMatrix(){
        $this->MediasMatriz();
        return $this->ArrayMedia;
    }

    /**
     * Obtiene el arreglo de datos de la matriz de covarianza
     *
     * @param Arraydata $ArrayData
     * @return ArrayData
     */
    function getCovarianzaMatrix($ArrayData){
        $this->ArrayMatrizCov=$this->CovarianzaMatriz($ArrayData);
        return $this->ArrayMatrizCov;
    }

    /**
     * Obtiene el número de filas que tiene un Arreglo de una matriz
     *
     * @param ArrayData $ArrayDataMatriz
     * @return integer
     */
    function get_NumFilas_ArrayDataMatriz($ArrayDataMatriz){
        //echo "la supesta filas es ".count($ArrayDataMatriz);
        //print_r($ArrayDataMatriz);
        return count($ArrayDataMatriz);
    }

    /**
     * Obtiene el número de columnas que tiene un arreglo de una matriz
     *
     * @param ArrayData $ArrayDataMatriz
     * @return integer
     */
    function get_NumColumnas_ArrayDataMatriz($ArrayDataMatriz){
        //echo "la supesta columan es ".count($ArrayDataMatriz[0]);
        return count ($ArrayDataMatriz[0]);
    }



    /******************************************/
    /*FUNCIONES DE VALIDACIONES DE MATRICES */

    /**
     * Funcion que valida si dos matrices son iguales, es decir que tengan el mismo numero de N y M
     *
     * @param matrix $matrizA
     * @param matrix $matrizB
     * @return bool
     */
    function ValidaMatricesDimenIguales($ObjMatrizA, $ObjMatrizB){
        //valida que las matrices sean v&aacute;lidas
        if ($ObjMatrizA->NumFila==$ObjMatrizB->NumFila and $ObjMatrizA->NumColumna==$ObjMatrizB->NumFila)
            return true;
        else
            return false;
    }


    /**
     * Funcion que valida que la matriz sea de NxN
     *
     * @return bool
     */
    function ValidaMatriz_N_x_N(){
        if ($this->NumFila == $this->NumColumna)
            return true;

        return false;
    }

    /**
     * Valida que el numero de columna de una matriz.. se el mismo en todas sus filas
     *
     * @param integer $NumFilas
     * @param integer $NumColumnas
     * @return bool
     */
    function ValidaNumColumnasObjMatriz($NumFilas,$NumColumnas){
        for ($i=0;$i<$NumFilas;$i++){
            $columna = count($this->ArrayData [$i]);
            if ($NumColumnas != $columna)
                return false;
        }
        return true;
    }

    /**
     * Dado un arreglo de datos de una matriz, valida que el número de
     * columnas de una matriz.. se el mismo en todas sus filas
     *
     * @param ArrayData $ArrayDataMatriz
     * @param integer $NumFilas
     * @param integer $NumColumnas
     * @return unknown
     */
    function ValidaNumColumnasArrayDataMatriz($ArrayDataMatriz,$NumFilas,$NumColumnas){
        for ($i=0;$i<$NumFilas;$i++){
            $columna = count($ArrayDataMatriz[$i]);
            if ($NumColumnas != $columna)
                return false;
        }
        return true;
    }




    /************************************************/
    /*FUNCIONES DE OPERACIONES BASICAS CON MATRICES */

    /**
     * Resta de dos matrices
     * Requisito: tiene que se de iguales valores de NxM
     *
     * @param ArrayData $ArrayDataMatriz1
     * @param ArrayData $ArrayDataMatriz2
     * @return ArrayData
     */
    function RestaMatrices($ArrayDataMatriz1, $ArrayDataMatriz2){
        $filas1 = $this->get_NumFilas_ArrayDataMatriz($ArrayDataMatriz1);
        $filas2 = $this->get_NumFilas_ArrayDataMatriz($ArrayDataMatriz2);
        $columnas1 = $this->get_NumColumnas_ArrayDataMatriz($ArrayDataMatriz1);
        $columnas2 = $this->get_NumColumnas_ArrayDataMatriz($ArrayDataMatriz2);

        for($i=0; $i<$filas1; $i++) {
            for($j=0; $j<$columnas1; $j++){
                $ArrayResta[$i][$j]= $ArrayDataMatriz1[$i][$j]-$ArrayDataMatriz2[$i][$j];
            }
        }
        return $ArrayResta;

    }

    /**
     * Suma de dos matrices
     * Requisito: tiene que se de iguales valores de NxM
     *
     * @param ArrayData $ArrayDataMatriz1
     * @param ArrayData $ArrayDataMatriz2
     * @return ArrayData
     */
    function SumaMatrices($ArrayDataMatriz1, $ArrayDataMatriz2){
        $filas1 = $this->get_NumFilas_ArrayDataMatriz($ArrayDataMatriz1);
        $filas2 = $this->get_NumFilas_ArrayDataMatriz($ArrayDataMatriz2);
        $columnas1 = $this->get_NumColumnas_ArrayDataMatriz($ArrayDataMatriz1);
        $columnas2 = $this->get_NumColumnas_ArrayDataMatriz($ArrayDataMatriz2);

        for($i=0; $i<$filas1; $i++) {
            for($j=0; $j<$columnas1; $j++){
                $ArrayResta[$i][$j]= $ArrayDataMatriz1[$i][$j]+$ArrayDataMatriz2[$i][$j];
            }
        }
        return $ArrayResta;

    }



    /**
     * Calcula la matriz resultante de multiplicar dos matrices
     * Requisito: los datos de las matrices A,B, tiene que cumplir que
     * El # de columnas de A, tienen que se igual a las filas de B.
     * C(pxq) = A(pxm) * B(mxq)
     *
     * @param ArrayData $ArrayDataMatriz1
     * @param ArrayData $ArrayDataMatriz2
     * @return ArrayData
     */
    function MultiplicacionMatrices($ArrayDataMatriz1, $ArrayDataMatriz2) {
        $filas1 = $this->get_NumFilas_ArrayDataMatriz($ArrayDataMatriz1);
        $columnas1 = $this->get_NumColumnas_ArrayDataMatriz($ArrayDataMatriz1);

        $columnas2 = $this->get_NumColumnas_ArrayDataMatriz($ArrayDataMatriz2);
        $filas2 = $this->get_NumFilas_ArrayDataMatriz($ArrayDataMatriz2);

        for($i=0; $i<$filas1; $i++){
            for($j=0; $j<$columnas2; $j++){
                $ArrayMultipli[$i][$j]=0; $sum=0;
                for($M=0;$M<$columnas1;$M++){
                    $ArrayMultipli[$i][$j]  = $ArrayMultipli[$i][$j] + $ArrayDataMatriz1[$i][$M]*$ArrayDataMatriz2[$M][$j];
                }
            }
        }
        return $ArrayMultipli;
    }

    /**
     * Calcula la matriz resultante al dividir una matriz para un escalar
     *
     * @param ArrayData $ArrayDataMatriz
     * @param integer $valor
     * @return ArrayData
     */
    function DivisionMatriz($ArrayDataMatriz, $valor) {
        $filas = $this->get_NumFilas_ArrayDataMatriz($ArrayDataMatriz);
        $columnas = $this->get_NumColumnas_ArrayDataMatriz($ArrayDataMatriz);

        $matriz = array();
        for($i = 0; $i < $filas; $i++) {
            for($j = 0; $j < $columnas; $j++) {
                $matriz[$i][$j] = $ArrayDataMatriz[$i][$j] / $valor;
            }
        }
        return $matriz;
    }

    /**
     * Calcula el Determinante de una matriz.
     * Requisito: Todas filas deben tener el mismo número de columnas.
     * Requisito: La matriz debe ser de NxN
     *
     * @param ArrayData $ArrayDataMatriz
     * @return integer
     */
    function Determinante($ArrayDataMatriz) {
        $filas = $this->get_NumFilas_ArrayDataMatriz($ArrayDataMatriz);
        $columnas = $this->get_NumColumnas_ArrayDataMatriz($ArrayDataMatriz);
        $det = 0;
        if ($filas == 2 && $columnas == 2) {
            $det = $ArrayDataMatriz[0][0] * $ArrayDataMatriz[1][1] - $ArrayDataMatriz[0][1] * $ArrayDataMatriz[1][0];
        } else {
            $matriz = array();
            /* Recorrer las columnas pivotes */
            for($j = 0; $j < $columnas; $j++) {
                /* Se crea una sub matriz */
                $matriz = $this->SubMatriz($ArrayDataMatriz, 0, $j);
                if (fmod($j, 2) == 0) {
                    $det += $ArrayDataMatriz[0][$j]*$this->Determinante($matriz);
                } else {
                    $det -= $ArrayDataMatriz[0][$j]*$this->Determinante($matriz);
                }
            }
        }
        return $det;
    }


    /**
     * Enter description here...
     *
     * @param ArrayData $ArrayDataMatriz
     * @param integer $pivoteX
     * @param integer $pivoteY
     * @return ArrayData
     */
    function SubMatriz($ArrayDataMatriz, $pivoteX, $pivoteY) {
        //echo "determiando SUBMATRIZ<br>";
        $filas = $this->get_NumFilas_ArrayDataMatriz($ArrayDataMatriz);
        $columnas = $this->get_NumColumnas_ArrayDataMatriz($ArrayDataMatriz);
        $matriz = array();
        $p = 0; // indica la fila de la nueva submatriz
        for($i = 0; $i < $filas; $i++) {
            $q = 0; // indica la columna de la nueva submatriz
            if ($pivoteX != $i) {
                for($j = 0; $j < $columnas; $j++) {
                    if ($pivoteY != $j) {
                        $matriz[$p][$q] = $ArrayDataMatriz[$i][$j];
                        $q++;
                    }
                }
                $p++;
            }
        }
        return $matriz;
    }


    /**
     * Calcula la matriz transpuesta de la matriz dada
     *
     * @param ArrayData $ArrayDataMatriz
     * @return ArrayData
     */
    function Transpuesta($ArrayDataMatriz) {
        $filas = $this->get_NumFilas_ArrayDataMatriz($ArrayDataMatriz);
        $columnas = $this->get_NumColumnas_ArrayDataMatriz($ArrayDataMatriz);
        $ArrayTranspuesta = array();
        //echo $filas.",".$columnas;
        for($i = 0; $i < $filas; $i++) {
            for($j = 0; $j < $columnas; $j++) {
                //echo "el dato es ".$ArrayDataMatriz[$i][$j]."<br>";
                $ArrayTranspuesta[$j][$i] = $ArrayDataMatriz[$i][$j];
            }
        }
        return $ArrayTranspuesta;

    }


    /**
     * Calcula la matriz inversa de la matriz dada
     *
     * @param ArrayData $ArrayDataMatriz
     * @return ArrayData
     */
    function InversaMatriz($ArrayDataMatriz) {
        $filas = $this->get_NumFilas_ArrayDataMatriz($ArrayDataMatriz);
        $columnas = $this->get_NumColumnas_ArrayDataMatriz($ArrayDataMatriz);
        //echo "determiando inversa<br>";
        $matriz = array();
        for($i = 0; $i < $filas; $i++) {
            for($j = 0; $j < $columnas; $j++) {
                if (fmod($i + $j, 2) == 0) {
                    $matriz[$i][$j] = $this->Determinante($this->SubMatriz($ArrayDataMatriz, $i, $j));
                } else {
                    $matriz[$i][$j] = -$this->Determinante($this->SubMatriz($ArrayDataMatriz, $i, $j));
                }
            }
        }
        return $this->Transpuesta($this->DivisionMatriz($matriz,$this->Determinante($ArrayDataMatriz)));
    }


    /**************************************************/
    /*FUNCIONES DE OPERACIONES AVANZADAS CON MATRICES */

    /**
     * 	M = mean(A)  return la media de los valores de una dimension del arreglo
     * 	If A is a vector, mean(A) returns the mean value of A.
     * 	If A is a matrix, mean(A) treats the columns of A as vectors, returning a row vector of mean values.
     * 	A = [1 2 3; 3 3 6; 4 6 8; 4 7 7];
     * 	mean(A)= [ 3.0000    4.5000    6.000 ]
     *
     * @return unknown
     */
    function MediasMatriz(){
        //encero los valores para el arreglo que va a almacenar las medias y las sumas
        for ($j=0; $j<$this->NumColumna; $j++){
            $this->ArrayMedia[$j]=0;
            $suma_media[$j]=0;
        }
        for ($j=0; $j<$this->NumColumna; $j++){
            for ($i=0; $i<$this->NumFila; $i++){
                $suma_media[$j]+=$this->ArrayData[$i][$j];
            }
            //echo "suma $i,$j=".$this->ArrayData[$i][$j]."<br>";
            $this->ArrayMedia[$j]=$suma_media[$j]/$this->NumFila;
            $this->ArraySumaMedia[$j]=$suma_media[$j];
        }
        //retorno la matriz con los promedio de cada columna (la matriz de la media)
        return true;
    }




    /**
     * COV(X,Y)
     * Calcula la covarianza entre los vectores x i y
     * C = CovarianzaVector(x) where x,y  are vectors,
     *
     * @param array $vectorX
     * @param array $vectorY
     * @return integer
     */
    function CovarianzaVectores ($vectorX, $vectorY){
        $NumFilas = count($vectorX);
        if ($NumFilas != count($vectorY)) return null;
        $covarianza = 0;$sum=0;
        $mean_x = $this->MediaVector($vectorX);
        $mean_y = $this->MediaVector($vectorY);
        for($i = 0; $i < $NumFilas; $i++) {
            $valor = (($vectorX[$i] - $mean_x) * ($vectorY[$i] - $mean_y));
            $sum += $valor;
        }
        $covarianza = $sum / $NumFilas;
        return $covarianza;
    }


    /**
     * Calcula la matriz de covarianza de una matriz A
     * S = CovarianzaMatriz(x) where A es una matriz.
     * Cada fila es una observacion y cada columna es una variable
     * n = numero de observaciones (# filas)
     * Cov (A)= A * A'
     */
    function CovarianzaMatriz($ArrayData){
        $transpA = $this->Transpuesta($ArrayData);
        $MatrizCov =  $this->MultiplicacionMatrices($ArrayData,$transpA);
        return $MatrizCov;
    }




}
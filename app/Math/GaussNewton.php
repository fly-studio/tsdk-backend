<?php

namespace App\Math;

use Matrix\Matrix;


class GaussNewton {

	const alpha = 1e-7;

	private $x, $y, $findY;


	public function __construct(array $x, array $y, callable $findY = null)
	{
		$this->x = $x;
		$this->y = $y;
		$this->findY = empty($findY) ? function(float $x, array $b){
			return $this->defaultFindY($x, $b);
		} : $findY;
	}

	/**
	 * 高斯牛顿法的主要算法，通过更改参数不断的最小化实际y和预测y'的差值
	 * 1.在b矩阵中设立初始值
	 * 2.通过b的参数去预测y'，并且计算残差res = y-y'
	 * 3.计算Jacobi矩阵J
	 * 4.计算 (JT * J)^-1 * JT，JT是J的transpose矩阵
	 * 5.将步骤4中的矩阵乘以残差矩阵res：(JT * J)^-1 * JT*res
	 * 6.计算new_b = old_b -  (JT * J)^-1 * JT * res
	 * 7.为了防止迭代过程永远不会收敛，乘以gamma值（设定0.01）new_b = old_b -  gamma * (JT * J)^-1 * JT * res
	 * 8.在达到要求的precision之前继续迭代
	 * @param x，y 给定方程值
	 * @param b 参数数列,1d
	 * @return b2 优化后的最优解参数数列
	 */
	public function optimise(int $n): array
	{
		$b = array_fill(0, $n, 1);

		$maxIteration = 2000;
		$oldError = 100;
		$precision = static::alpha;
		$gamma = .01;
		$c = count($this->y);

		for ($i = 0; $i < $maxIteration; $i++) {
			$res = $this->calculateResiduals($b);
			$error = $this->calculateError($res);

			if (abs($oldError - $error) <= $precision)
				break;

			$oldError = $error;
			$jacobs = $this->jacob($b, $c);
			$values = $this->transjacob($jacobs, $res);

			for($j = 0; $j < count($values); $j++)
				$b[$j] = $b[$j] - $gamma * $values[$j][0];
		}

		return $b;
	}

	/**
	 * 计算残差方程 res = y-y',实际值-预测值
	 *
	 * @param x，y 给定方程值
	 * @param b 参数数列
	 * @return res 行数为y的长度，列数为1，res[i][0] = 实际y-预测y
	 */
	protected function calculateResiduals(array $b): array
	{
		$res = $this->make2DArray(count($this->y), 12);

		for ($i = 0; $i < count($res); $i++) {
			$res[$i][0] = call_user_func($this->findY, $this->x[$i], $b) - $this->y[$i];
		}

		return $res;
	}

	/**
	 * 通过残差方程的值，计算均方根误差
	 *
	 * @param res 残差方程
	 * @return 返回平方后的平均误差值
	 */
	protected function calculateError(array $res): float
	{
		$sum = 0;

		for ($i = 0; $i < count($res); $i++) {
			$sum += $res[$i][0] * $res[$i][0];
		}

		return sqrt($sum);
	}

	/**
	 * 计算Jacobi矩阵J
	 * 用x[i]和b[j]去计算偏导，J(i,j) = dy/d(b[j])
	 * @param b 参数数列
	 * @param x 给定的方程值
	 * @param n x的行数
	 * @return 计算好的Jacobi矩阵
	 */
	protected function jacob(array $b, int $n): array
	{
		$m = count($b);
		$jc = $this->make2DArray($n, $m);

		for ($i = 0; $i < $n; $i++) {
			for ($j = 0; $j < $m; $j++) {
				$jc[$i][$j] = $this->derivative($this->x[$i], $b, $j);
			}
		}

		return $jc;
	}

	/**
	 * 给定Jacobi矩阵J，计算  (JT * J)^-1 * JT * res
	 * JT是J的transpose，^-1是计算inverse
 	 *
	 * @param Jinput 给定的Jacobi矩阵
	 * @param res 残差方程
	 * @return (JT * J)^-1 * JT
	 */
	protected function transjacob(array $Jinput, array $res): array
	{
		$r = new Matrix($res); // $r
		$J = new Matrix($Jinput); // $J
		$JT = $J->transpose(); // JT
		$JTJ = $JT->multiply($J); // JT * J
		$JTJ_1 = $JTJ->inverse(); // (JT * J)^-1
		$JTJ_1JT = $JTJ_1->multiply($JT); // (JT * J)^-1 * JT
		$JTJ_1JTr = $JTJ_1JT->multiply($r);

		return $JTJ_1JTr->toArray();
	}

	/**
	 * 给定方程y，计算需要的偏导
	 * @param x 给定方程值
	 * @param b 参数数列
	 * @param 需要计算偏导的数字在b中的位置
	 * @return dy/d(b[bIndex])
	 *
	 */
	protected function derivative(float $x, array $b, int $index): float
	{
		if (!isset($b[$index])) return 0;

		$bCopy = $b;
		$bCopy[$index] += static::alpha;
		$y1 = call_user_func($this->findY, $x, $bCopy);

		$bCopy = $b;
		$bCopy[$index] -= static::alpha;
		$y2 = call_user_func($this->findY, $x, $bCopy);

		return ($y1 - $y2) / (2 * static::alpha);
	}

	/**
	  * 计算y值
	 * @param x 给定方程值
	 * @param b 参数数列
	 * @return 给定x和b[]的值，计算出的y值
	 *
	 */
	public function defaultFindY(float $x, array $b): float
	{
		//return $b[0] * $x * $x * $x + $b[1] * $x * $x + $b[2] * $x + $b[3];
		$sum = 0;
		$c = count($b);
		//拟合方程y = ax^3+bx^2+cx+d
		foreach($b as $i => $v)
		{
			$sum += $v * pow($x, $c - $i - 1);
		}

		return $sum;
	}

	public function make2DArray(int $rows, int $cols, $defaultValue = 0): array
	{
		return array_map(function() use($cols, $defaultValue) {
			return array_fill(0, $cols, $defaultValue);
		}, array_fill(0, $rows, null));
	}

}

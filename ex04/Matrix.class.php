<?php

require_once 'Vertex.class.php';

class Matrix {

	const IDENTITY = "IDENTITY";
	const SCALE = "SCALE";
	const RX = "Ox ROTATION";
	const RY = "Oy ROTATION";
	const RZ = "Oz ROTATION";
	const TRANSLATION = "TRANSLATION";
	const PROJECTION = "PROJECTION";

	private $_array;

	private $_matrix = [
		[0, 0, 0, 0],
		[0, 0, 0, 0],
		[0, 0, 0, 0],
		[0, 0, 0, 0]
		];

	private $_coords = ['x', 'y', 'z', 'w'];

	public static $verbose = FALSE;

	public function __construct(array $array)
	{
		if (!isset($array['preset']) || !in_array($array['preset'], [
			self::IDENTITY, self::SCALE,
			self::RX, self::RY, self::RZ,
			self::TRANSLATION, self::PROJECTION
        	]))
				return FALSE;
		if ($array['preset'] === self::SCALE && !isset($array['scale']))
			return false;
		if (in_array($array['preset'], [self::RY, self::RX, self::RZ]) && !isset($array['angle']))
			return false;
		if ($array['preset'] === self::TRANSLATION && (!isset($array['vtc']) || !($array['vtc'] instanceof Vector)))
			return false;
		if ($array['preset'] === self::PROJECTION && (!isset($array['fov']) || !isset($array['ratio']) || !isset($array['near']) || !isset($array['far'])))
			return false;

		$this->_array = $array;
		$func = "preset" . str_replace(' ', '', ucwords(strtolower($array['preset'])));
		$this->{$func}($array);
		if (self::$verbose && !isset($array['silent']))
			echo "Matrix " . $array['preset'] . ($array['preset'] !== self::IDENTITY ? " preset" : "") . " instance constructed" . PHP_EOL;
	}

	public function __destruct()
	{
		if (self::$verbose && !isset($array['silent']))
			echo "Matrix instance destructed" . PHP_EOL;
	}

	public function __toString()
	{
		$string = "M | vtcX | vtcY | vtcZ | vtxO" . PHP_EOL;
		$string .= "-----------------------------";
		for ($i = 0; $i < count($this->_matrix); $i++)
        {
            $string .= PHP_EOL . "{$this->_coords[$i]}";
            for ($j = 0; $j < count($this->_matrix[$i]); $j++)
				$string .= " | " . number_format($this->_matrix[$i][$j], 2, ".", "");
        }
		return $string;
	}

	public static function doc()
	{
		return file_get_contents('Matrix.doc.txt') . PHP_EOL;
	}

	private function presetIdentity()
	{
		$this->_matrix[0][0] = 1;
		$this->_matrix[1][1] = 1;
		$this->_matrix[2][2] = 1;
		$this->_matrix[3][3] = 1;
	}

	private function presetTranslation($array)
	{
		$this->presetIdentity();
		$this->_matrix[0][3] = $array['vtc']->GetX();
		$this->_matrix[1][3] = $array['vtc']->GetY();
		$this->_matrix[2][3] = $array['vtc']->GetZ();
	}

	private function presetScale($array)
	{
		$this->_matrix[0][0] = $array['scale'];
		$this->_matrix[1][1] = $array['scale'];
		$this->_matrix[2][2] = $array['scale'];
		$this->_matrix[3][3] = 1;
	}

	private function presetOxRotation($array)
	{
		$this->_matrix[0][0] = 1;
		$this->_matrix[3][3] = 1;
		$this->_matrix[1][1] = cos($array['angle']);
		$this->_matrix[1][2] = -1 * sin($array['angle']);
		$this->_matrix[2][1] = sin($array['angle']);
		$this->_matrix[2][2] = cos($array['angle']);
	}

	private function presetOyRotation($array)
	{
		$this->_matrix[1][1] = 1;
		$this->_matrix[3][3] = 1;
		$this->_matrix[0][0] = cos($array['angle']);
		$this->_matrix[2][0] = -1 * sin($array['angle']);
		$this->_matrix[0][2] = sin($array['angle']);
		$this->_matrix[2][2] = cos($array['angle']);
	}

	private function presetOzRotation($array)
	{
		$this->_matrix[2][2] = 1;
		$this->_matrix[3][3] = 1;
		$this->_matrix[0][0] = cos($array['angle']);
		$this->_matrix[0][1] = -1 * sin($array['angle']);
		$this->_matrix[1][0] = sin($array['angle']);
		$this->_matrix[1][1] = cos($array['angle']);
	}

	private function presetProjection($array)
	{
		$scale = 1 / tan($array['fov'] * 0.5 * M_PI / 180);
		$this->_matrix[0][0] = $scale / $array['ratio']; // scale the x coordinates of the projected point
		$this->_matrix[1][1] = $scale; // scale the y coordinates of the projected point
		$this->_matrix[2][2] = -1 * (($array['far'] + $array['near']) / ($array['far'] - $array['near']));
		$this->_matrix[2][3] = -1 * ((2 * $array['far'] * $array['near']) / ($array['far'] - $array['near']));
		$this->_matrix[3][2] = -1;
		$this->_matrix[3][3] = 0;
	}

	public function mult($rhs)
	{
		$new = [];
		for ($i = 0; $i < count($this->_matrix); $i++)
		{
			$new[$i] = [];
			for ($j = 0; $j < count($this->_matrix[$i]); $j++)
			{
				$sum = 0;
				for ($k = 0; $k < count($rhs->_matrix); $k++)
				{
					$sum += $this->_matrix[$i][$k] * $rhs->_matrix[$k][$j];
				}
				$new[$i][$j] = $sum;
			}
		}
		$matrix = new Matrix( array( 'preset' => Matrix::IDENTITY, 'silent' => TRUE ) );
		$matrix->_matrix = $new;
		return $matrix;
	}

	public function transformVertex($vtx)
	{
		$x = $vtx->getX() * $this->_matrix[0][0] +
			$vtx->getY() * $this->_matrix[0][1] +
			$vtx->getZ() * $this->_matrix[0][2] +
			$vtx->getW() * $this->_matrix[0][3];
		$y = $vtx->getX() * $this->_matrix[1][0] +
			$vtx->getY() * $this->_matrix[1][1] +
			$vtx->getZ() * $this->_matrix[1][2] +
			$vtx->getW() * $this->_matrix[1][3];
		$z = $vtx->getX() * $this->_matrix[2][0] +
			$vtx->getY() * $this->_matrix[2][1] +
			$vtx->getZ() * $this->_matrix[2][2] +
			$vtx->getW() * $this->_matrix[2][3];
		$w = $vtx->getX() * $this->_matrix[3][0] +
			$vtx->getY() * $this->_matrix[3][1] +
			$vtx->getZ() * $this->_matrix[3][2] +
			$vtx->getW() * $this->_matrix[3][3];
		return new Vertex( array( 'x' => $x, 'y' => $y, 'z' => $z, 'w' => $w ) );
	}

	public function transpose()
	{
		$temp_verbose = Matrix::$verbose;
		Matrix::$verbose = false;
		$transposed = new Matrix( ['preset' => Matrix::IDENTITY] );
		Matrix::$verbose = $temp_verbose;
		for ( $i = 0; $i < 4; $i++ ) {
			for ( $j = 0; $j < 4; $j++ )
				$transposed->_matrix[$i][$j] = $this->_matrix[$j][$i];
		}
		return ( $transposed );
	}

}
?>

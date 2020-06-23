<?php

require_once 'Vertex.class.php';
require_once 'Vector.class.php';
require_once 'Camera.class.php';

class Camera {

	private $_origin;
	private $_tT;
	private $_tR;
	private $_tRMult;
	private $_proj;
	private $_ratio;
	private $_fov;
	
	public static $verbose = FALSE;

	public function __construct(array $array)
	{
		if (!isset($array['origin']) || !($array['origin'] instanceof Vertex))
			return FALSE;
		if (!isset($array['orientation']) || !($array['orientation'] instanceof Matrix))
			return FALSE;
		if (!isset($array['fov']) || !isset($array['near']) || !isset($array['far'])) 
			return FALSE;
		if (isset($array['ratio']))
			$this->_ratio = $array['ratio'];
		elseif (isset($array['width']) && isset($array['height']))
			$this->_ratio = $array['width'] / $array['height'];
		else
			return FALSE;

		$this->_origin = $array['origin'];
		$this->_fov = $array['fov'];

		$x = $this->_origin->getX() * -1;
		$y = $this->_origin->getY() * -1;
		$z = $this->_origin->getZ() * -1;
		$vtxInv = new Vertex( array( 'x' => $x, 'y' => $y, 'z' => $z ) );
		$vtcInv = new Vector( array( 'dest' => $vtxInv ) );
		$this->_tT  = new Matrix( array( 'preset' => Matrix::TRANSLATION, 'vtc' => $vtcInv ) );

		$this->_tR = $array['orientation']->transpose();

		$this->_tRMult = $this->_tR->mult($this->_tT);

		$this->_proj = new Matrix( array( 'preset' => Matrix::PROJECTION,
			'fov' => $array['fov'],
			'ratio' => $this->_ratio,
			'near' => $array['near'],
			'far' => $array['far'] ) );

	if (self::$verbose)
		echo "Camera instance constructed" . PHP_EOL;
	}

	public function __destruct()
	{
		if (self::$verbose)
			echo "Camera instance destructed" . PHP_EOL;
	}

	public function __toString()
	{
		$string = "Camera(" . PHP_EOL .
			"+ Origine: {$this->_origin->__toString()}" . PHP_EOL .
			"+ tT:" . PHP_EOL .
			$this->_tT->__toString() . PHP_EOL .
			"+ tR:" . PHP_EOL .
			$this->_tR->__toString() . PHP_EOL .
			"+ tR->mult( tT ):" . PHP_EOL .
			$this->_tRMult->__toString() . PHP_EOL .
			"+ Proj:" . PHP_EOL .
			$this->_proj->__toString() . PHP_EOL .
			")";
		return $string;
	}

	public static function doc()
	{
		return file_get_contents('Camera.doc.txt') . PHP_EOL;
	}

	public function watchVertex( $worldVertex )
	{
		$vectLocal = new Vector(array('dest' => $worldVertex));
		$vectOrigin = new Vector(array('dest' => $this->_origin));
		$vectWorld = $vectOrigin->add($vectLocal);
		$vertex = new Vertex(array('x' => $vectWorld->getX(), 'y' => $vectWorld->getY(), 'z' => $vectWorld->getZ()));
		$vertex = $this->_tRMult->transformVertex($vertex);
		return new Vertex( array( 'x' => $vertex->getX() * deg2rad($this->_fov) * $this->_ratio,
									   'y' => $vertex->getY() * deg2rad($this->_fov),
									   'z' => $vertex->getZ(),
									   'color' => $worldVertex->getColor() ) );
	}
	/* : Transforms "world" coordinates
	vertex into a "screen" coordinates vertex (a pixel basically).*/

}
?>

<?php

require_once 'Vertex.class.php';

class Vector {
	private $_x;
	private $_y;
	private $_z;
	private $_w = 0.0;

	public static $verbose = FALSE;

	public function __construct(array $vertex)
	{
		if (!isset($vertex['dest']) || !($vertex['dest'] instanceof Vertex))
			return FALSE;
		if (isset($vertex['orig']) && !($vertex['orig'] instanceof Vertex))
			return FALSE;
		if (!isset($vertex['orig']))
			$vertex['orig'] = new Vertex( array( 'x' => 0.0, 'y' => 0.0, 'z' => 0.0 ) );
		$this->_x = $vertex['dest']->x - $vertex['orig']->x;
		$this->_y = $vertex['dest']->y - $vertex['orig']->y;
		$this->_z = $vertex['dest']->z - $vertex['orig']->z;
		if (self::$verbose) {
			echo $this->__toString() . " constructed" . PHP_EOL;
		}
	}

	public function __destruct()
	{
		if (self::$verbose) {
			echo $this->__toString() . " destructed" . PHP_EOL;
		}
	}

	public function __toString()
	{
		return "Vector( x: " . number_format($this->_x, 2, ".", "") .
			", y:" . number_format($this->_y, 2, ".", "") .
			", z:" . number_format($this->_z, 2, ".", "") .
			", w:" . number_format($this->_w, 2, ".", "") .
			" )";
	}

	public static function doc()
	{
		return file_get_contents('Vector.doc.txt') . PHP_EOL;
	}

	public function getX() { return $this->_x; }
	public function getY() { return $this->_y; }
	public function getZ() { return $this->_z; }
	public function getW() { return $this->_w; }

	public function __get( $property )
    {
		if (property_exists($this, $property))
			return $this->$property;
		$func = 'get'.strtoupper($property);
		if (method_exists($this, $func))
			return $this->$func();
	}
	
	public function __set($property, $value)
	{
		return false;
	}

	public function magnitude()
	{
		$magnitude = sqrt($this->_x **2 + $this->_y **2 + $this->_z **2);
		if ($magnitude == 1)
			return "norm";
		else
			return $magnitude;
	}

	public function normalize()
	{
		$len = $this->magnitude();
		if ($len > 0)
		{
			$inv_len = 1 / $len;
			return new Vector( array( 'dest' => new Vertex([
				'x' => $this->_x * $inv_len,
				'y' => $this->_y * $inv_len,
				'z' => $this->_z * $inv_len
			])));
		}
		else
		{
			return new Vector( array( 'dest' => new Vertex([
				'x' => 0,
				'y' => 0,
				'z' => 0
			])));
		}
	}

	public function add( Vector $rhs )
	{
		return new Vector( array( 'dest' => new Vertex([
			'x' => $this->_x + $rhs->_x,
			'y' => $this->_y + $rhs->_y,
			'z' => $this->_z + $rhs->_z
		])));
	}

	public function sub( Vector $rhs )
	{
		return new Vector( array( 'dest' => new Vertex([
			'x' => $this->_x - $rhs->_x,
			'y' => $this->_y - $rhs->_y,
			'z' => $this->_z - $rhs->_z
		])));
	}

	public function opposite()
	{
		return new Vector( array( 'dest' => new Vertex([
			'x' => $this->_x * -1,
			'y' => $this->_y * -1,
			'z' => $this->_z * -1
		])));
	}

	public function scalarProduct( $k )
	{
		return new Vector( array( 'dest' => new Vertex([
			'x' => $this->_x * $k,
			'y' => $this->_y * $k,
			'z' => $this->_z * $k
		])));
	}

	public function dotProduct( Vector $rhs )
	{
		return ($this->_x * $rhs->_x + $this->_y * $rhs->_y + $this->_z * $rhs->_z);
	}

	public function cos( Vector $rhs )
	{
		$magnitude1 = $this->magnitude();
		$magnitude2 = $rhs->magnitude();
		if ($magnitude1 == "norm" || $magnitude2 == "norm" || $magnitude1 == 0 || $magnitude2 == 0)
			return (0);
		else
			return ($this->dotProduct($rhs) / ($this->magnitude() * $rhs->magnitude()));
	}

	public function crossProduct( Vector $rhs )
	{
		return new Vector( array( 'dest' => new Vertex([
			'x' => $this->_y * $rhs->_z - $this->_z * $rhs->_y,
			'y' => $this->_z * $rhs->_x - $this->_x * $rhs->_z,
			'z' => $this->_x * $rhs->_y - $this->_y * $rhs->_x
		])));
	}
}
?>

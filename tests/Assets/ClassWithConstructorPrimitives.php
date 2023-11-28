<?php

declare(strict_types=1);

namespace PisarevskiiTests\SimpleDIC\Assets;

class ClassWithConstructorPrimitives {

	public SimpleClass $simple_class;

	public array $array;
	public string $string;
	public int $number;

	/**
	 * @var null
	 */
	public $null;

	public function __construct(
		SimpleClass $simple_class,
		array $array = [ 1, 2, 3 ],
		string $string = 'public string',
		int $number = 100,
		$null = null
	) {
		$this->simple_class = $simple_class;
		$this->array = $array;
		$this->string = $string;
		$this->number = $number;
		$this->null = $null;
	}
}
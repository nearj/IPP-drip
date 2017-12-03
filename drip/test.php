<?php

// 3 x + 16 *
// (3 + x) * 16

$input = array();
array_push($input, 'a');
array_push($input, 'b');
array_push($input, 'c');
var_dump($input);
array_pop($input);
var_dump($input);



// class v
// {
//     public $type;
//     public $variable = null;
//     public $kk = 3;
//     public function __construct(bool $b, ...$c)
//     {
//         $this->type = $b;
//         foreach($c as $t) {
//             $this->variable = $t;
//         }
//     }
// }

// class w extends v
// {
//     public function __construct(bool $b, ...$c)
//     {
//         parent::__construct($b, ...$c);
//     }
// }

// $x = new w(true);
// $y = new v(false, 3);
// $z = new v(false, 16);

// if ($x INSTANCEOF w) {
//     print "hi!!";
// }
// // $add = function($a, $b)
// {
//     return $a + $b;
// };

// $mul = function($a, $b)
// {
//     return $a * $b;
// };

// function makeFunc(callable $f, v $a, v $b)
// {
//     if ($a->type)
//     {
//         if ($a->variable == null)
//         {
//             $a->variable = function ($x) use ($f, $b)
//             {
//                 return $f($x, $b->variable);
//             };
//         }
//         else {
//             $c = $a->variable;
//             $a->variable = function($x) use ($f, $c, $b)
//             {
//                 return $f($c($x), $b->variable);
//             };
//         }
//     } else
//     {
//         $a->variable = $f($a->variable, $b->variable);
//     }
// }

// makeFunc($add, $x, $y);
// makeFunc($mul, $x, $z);
// print ($x->variable)(3);

// function a(callable $f, $c, $d)
// {
//     return function ($k) use ($f, $d)
//     {
//         return $f($k, $d);
//     };
// }
// $j = a($add, 3, 4);
// print $j(10);

?>

<?php
/* test suite[1]: sin23.2^(1+9) + 4 * 7

   --normal--
   's', 'i', 'n', '2', '3', '.', '2', '[\pl]', '4', '[\mu]', '7'
   -- superscript --
   ' ', ' ', ' ', ' ', ' ', ' ', '1', '[\pl]', '9'
   */

/* test suite[2]: (3+21^(21))^(9)x
   -- normal --
   '[\pao]', '3', '[\pl]', '2', '1', ' ', ' ', '[\pac]', 'x'
   -- subscript --
    ' ', ' ', ' ', ' ', ' ', ' '
   -- supscript --
    ' ', ' ', ' ', ' ', ' ', '2', '1', ' ', '9',
*/

 // test suite[3]: \in_{3}^4 3 + 4e^x
 // --subscript--
 // ' ', '3'
 // --normal--
 // '[\in]', '3', '[\pl]', '4', 'e'
 // --superscript--
 // '4', ' ', ' ', ' ', ' ', 'x'

/* test suite[4]: \qu_{i=1}^100 ( i + 20 )^98 pi
  --superscript--
  '1', '0', '0', ' ', ' ', ' ', ' ', '9', '8'
  --subscript--
  'i', '=', '1'
  --normal--
  'E', '[\pao]', 'i', '[\pl]', '20', '[\pac]', '[\pi]'
*/

/* test suite[5]:
  -- subscript --

  -- normal --
  'd', '[\pr]', 'd', 'x', '[\pao]', 'x', '[\pl]', 'x', '[\pac]'
  -- superscript --
  ' ', ' ', ' ', ' ', ' ', ' ', ' ', '3', ' ', '2'
 */

include_once "analyzer.php";

$crude = array(
        );

for ($j = count($crude); $j < 20; $j++)
    $crude[] = ' ';

$crude_nor = array (
    '2', '[\pl]', 'd', '[\fr]', 'd', 'x', '[\pao]', 'x', '[\pl]', 'x', '[\pac]', '[\mu]', '9'
        );

$crude = array_merge($crude, $crude_nor);

for ($j = count($crude); $j < 40; $j++)
    $crude[] = ' ';

$crude_sup = array(
    ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '3', ' ', '2'
        );

$crude = array_merge($crude, $crude_sup);
for ($j = count($crude); $j < 60; $j++)
    $crude[] = ' ';


$i = new Analyzer\Lexer\Lexer();
$i->scan($crude);
$k = new Analyzer\Parser\Parser();
$k->scan($i->getSymbolList());

foreach($k->getLHS() as $j)
{
if ($j->type == \Analyzer\TOKEN\TYPE::OPERATOR)
    print $j->lexeme->name() . "@'{$j->pos}' ";
else
    print $j->lexeme . "@'{$j->pos}' ";
}
print "\n";

?>
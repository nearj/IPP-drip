<?php
namespace Analyzer\Lexer
{
    /**
     * @brief Lexical Analyzer Interface
     *
     * @method void scan(string)
     * @method array getSymbolList()
     * @method array getSymbolTabel()
     * @implements
     *
     */
    class Lexer
    {
        // TODO: delete test
        private static $test = 0;

        const MAXLEN		 = 60;
        const MOD            = 20;
        const SUB_LEFT_MOST  = 0;
        const SUB_RIGHT_MOST = 19;
        const NOR_LEFT_MOST  = 20;
        const NOR_RIGHT_MOST = 39;
        const SUP_LEFT_MOST  = 40;
        const SUP_RIGHT_MOST = 59;

        private $operatorList;
        private $symbolList;

        private $SUB_OFFSET;
        private $SUP_OFFSET;
        private $NOR_OFFSET;

        public function __construct() {
            $this->operatorList = new \Analyzer\OP\OpList();
        }

        public function scan(array $crude)
        {
            $this->SUB_OFFSET = Lexer::SUB_LEFT_MOST;
            $this->NOR_OFFSET = Lexer::NOR_LEFT_MOST;
            $this->SUP_OFFSET = Lexer::SUP_LEFT_MOST;

            if (count($crude) != Lexer::MAXLEN)
                throw new \LexerException('Error: Invalid input length', 0);
            $this->scanHelper($crude,
                              Lexer::NOR_LEFT_MOST,
                              Lexer::NOR_RIGHT_MOST,
                              \Analyzer\TOKEN\POS::NOR);
        }

        public function getSymbolList()
        {
            return $this->symbolList;
        }

        private function getOffset(int $pos)
        {
            if ($pos == \Analyzer\TOKEN\POS::SUB)
                return $this->SUB_OFFSET;
            else if ($pos == \Analyzer\TOKEN\POS::NOR)
                return $this->NOR_OFFSET;
            else if ($pos == \Analyzer\TOKEN\POS::SUP)
                return $this->SUP_OFFSET;
            else
                throw new \LexerException('line: ' . __LINE__);
        }

        private function &getOffsetR(int $pos)
        {
            if ($pos == \Analyzer\TOKEN\POS::SUB)
                return $this->SUB_OFFSET;
            else if ($pos == \Analyzer\TOKEN\POS::NOR)
                return $this->NOR_OFFSET;
            else if ($pos == \Analyzer\TOKEN\POS::SUP)
                return $this->SUP_OFFSET;
            else
                throw new \LexerException('line: ' . __LINE__);
        }

        private function scanHelper(array $crude,
                                    int $fst,
                                    int $last,
                                    int $pos)
        {
            if (!($last < Lexer::MAXLEN)) return;

            if (!($pos == \Analyzer\TOKEN\POS::NOR ||
                  $pos == \Analyzer\TOKEN\POS::SUB ||
                  $pos == \Analyzer\TOKEN\POS::SUP))
                throw new \LexerException('line: ' . __LINE__);

            if ($pos == \Analyzer\TOKEN\POS::NOR)
            {
                if ($fst < Lexer::NOR_LEFT_MOST ||
                    $last > Lexer::NOR_RIGHT_MOST)
                    throw new \LexerException('line: ' . __LINE__);
                $offset = &$this->NOR_OFFSET;
            }
            else if ($pos == \Analyzer\TOKEN\POS::SUB)
            {
                if ($fst < Lexer::SUB_LEFT_MOST ||
                    $last > Lexer::SUB_RIGHT_MOST)
                    throw new \LexerException('line: ' . __LINE__);
                $offset = &$this->SUB_OFFSET;
            }
            else
            {
                if ($fst < Lexer::SUP_LEFT_MOST ||
                    $last > Lexer::SUP_RIGHT_MOST)
                    throw new \LexerException('line: ' . __LINE__);
                $offset = &$this->SUP_OFFSET;
            }

            // TODO: test case
            // for ($i = $fst; $i <= $last; $i++)
            //     print $i . ":" . $crude[$i] . " ";
            // print " ::$last\n";

            $idx = $fst;
            $op = null;

            for(;;)
            {
                if ($idx > $last)
                    return;
                else if (ctype_digit($crude[$idx]) || $crude[$idx] == '.')
                    // Number
                {
                    $idx = $this->processNumber($crude, $idx, $last, $pos);
                    continue;
                }
                else if (ctype_alpha($crude[$idx]))
                    // Identifier r, S, d, e, sin, cos,
                {
                    $ident = $crude[$idx];

                    // ===== exception point ===== //
                    /*!< exception of identifier - as operator !>*/
                    if ($ident == 'd')
                    {
                        if ($crude[$idx + 1] == '[\fr]' && $crude[$idx + 2] == 'd')
                        {
                            $op = $this->operatorList->getOp('\de');
                            $this->processOperator($crude, $idx, $pos, $op);
                            $idx += 3;
                        }
                        else
                        {
                            $op = $this->operatorList->getOp('\di');
                            $this->processOperator($crude, $idx++, $pos, $op);
                        }
                        continue;
                    }
                    if ($ident == 'D')
                    {
                        $op = $this->operatorList->getOp('\de');
                        $this->processOperator($crude, $idx++ , $pos, $op);
                        continue;
                    }
                    // ===== exception point ===== //

                    while (!(($idx + 1) > $last) && ctype_alpha($crude[$idx + 1]))
                        $ident .= $crude[++$idx];

                    // ===== exception point ===== //
                    /*!< exception of identifier - as operator !>*/

                    if ($ident == 'r')
                    {
                        $op = $this->operatorList->getOp('\ro');
                        $this->processOperator($crude, $idx, $pos, $op);
                    }
                    else if ($ident == 's' || $ident == 'S')
                    {
                        $op = $this->operatorList->getOp('\in');
                        $this->processOperator($crude, $idx, $pos, $op);
                    }
                    else if ($ident == 'E')
                    {
                        $op = $this->operatorList->getOp('\qu');
                        $this->processOperator($crude, $idx, $pos, $op);
                    }
                    else if ($ident == 'sin')
                    {
                        $op = $this->operatorList->getOp('\si');
                        $this->processOperator($crude, $idx, $pos, $op);
                    }
                    else if ($ident == 'cos')
                    {
                        $op = $this->operatorList->getOp('\si');
                        $this->processOperator($crude, $idx, $pos, $op);
                    }
                    else if ($ident == 'tan')
                    {
                        $op = $this->operatorList->getOp('\ta');
                        $this->processOperator($crude, $idx, $pos, $op);
                    }
                    else if ($ident == 'log')
                    {
                        $op = $this->operatorList->getOp('\lo');
                        $this->processOperator($crude, $idx, $pos, $op);
                    }
                    /*!< exception of identifier - as constant !>*/
                    else if ($ident == 'e')
                    {
                        $this->processNumber($crude, $idx, $idx + 1, $pos);
                    }
                    // ===== exception point ===== //

                    else
                    {
                        // Identifier x_{0} == x0, allow only one number
                        // Normal line colud be x_{0}

                        if (!(($idx + 1) > $last)) {
                            if (ctype_digit($crude[$idx + 1]))
                                $ident .= $crude[++$idx];
                            else if ($pos == \Analyzer\TOKEN\POS::NOR &&
                                     $this->lookaround
                                     ($crude, $idx + 1, 0, 1, \Analyzer\TOKEN\POS::SUB, false, false, ' ') &&
                                     ctype_alnum($crude[$this->SUB_OFFSET]))
                                $ident .= $crude[$this->SUB_OFFSET++];

                            $this->symbolList[] =
                                                new \Analyzer\Symbol($pos,
                                                                     \Analyzer\TOKEN\TYPE::IDENTIFIER, $ident);

                            // check power
                            if ($pos == \Analyzer\TOKEN\POS::NOR)
                                $this->thread($crude, $idx, 1, 1, Lexer::NOR_LEFT_MOST, $pos, true);

                        }
                    }
                    $idx++;
                    if ($idx > $last) {
                        $offset = $idx;
                        return;
                    }

                } else if (strlen($crude[$idx]) > 1)
                    // Operator
                {
                    preg_match('/(?:\[)(\\\\[[:alnum:]]*)/', $crude[$idx], $match);

                    if ($match[1] == '\pi')
                    {
                        $this->processNumber($crude, $idx, $idx + 1, $pos);
                        $idx++;
                        continue;
                    }

                    $op = $this->operatorList->getOp($match[1]);
                    $this->processOperator($crude, $idx, $pos, $op);

                    if ((++$idx) > $last)
                    {
                        $offset = $idx;
                        return;
                    }

                } else if ($crude[$idx] == '=')
                    // Assign
                {
                    $this->symbolList[] = new \Analyzer\Symbol($pos, \Analyzer\TOKEN\TYPE::ASSIGN, '=');
                    if ((++$idx) > $last)
                    {
                        $offset = $idx;
                        return;
                    }

                } else if ($crude[$idx] == ' ')
                {
                    while (!(++$idx > $last) && $crude[$idx] == ' ' )
                        ;
                    if ($idx > $last)
                    {
                        $offset = $idx;
                    }
                    else
                    {
                        continue;
                    }
                } else
                    throw new \LexerException("Error: unknown {$crude[$idx]} ");
            }
        }

        private function processOperator(array $crude, int $idx, int $pos, \Analyzer\OP\OP $op)
        {
            if (!($pos == \Analyzer\TOKEN\POS::NOR ||
                  $pos == \Analyzer\TOKEN\POS::SUB ||
                  $pos == \Analyzer\TOKEN\POS::SUP))
                throw new \LexerException('line: ' . __LINE__);

            $this->symbolList[] = new \Analyzer\Symbol($pos, \Analyzer\TOKEN\TYPE::OPERATOR, $op);

            if ($pos == \Analyzer\TOKEN\POS::NOR)
            {
                if ($op->reqPos() & \Analyzer\OP\_ReqPos::SUB_AUX)
                {
                    if (!$this->lookaround
                        ($crude, $idx, 1, 2, \Analyzer\TOKEN\POS::SUB, false, false, ' ') &&
                        $op->reqPos() >> 1 & \Analyzer\OP\_ReqPos::SUP_PRI)
                        throw new \LexerException
                            ("Error: invalid operation require positions, {$op->name()}");
                    else
                    {
                        $save = $this->SUB_OFFSET;

                        $this->lookaround($crude, $idx,
                                          $idx + Lexer::SUB_LEFT_MOST - $save,
                                          20, \Analyzer\TOKEN\POS::SUB, false, true, ' ');
                        $this->scanHelper($crude,
                                          $save,
                                          $this->SUB_OFFSET,
                                          \Analyzer\TOKEN\POS::SUB);
                    }
                }

                if ($op->reqPos() & \Analyzer\OP\_ReqPos::NOR_AUX)
                {
                    if (!$this->lookaround
                        ($crude, $idx, 1, 2, \Analyzer\TOKEN\POS::NOR, false, false, ' ') &&
                        $op->reqPos() >> 1 & \Analyzer\OP\_ReqPos::NOR_PRI)
                        throw new \LexerException
                            ("Error: invalid operation require positions, {$op->name()}");
                    else
                    {
                        $save = $this->SUP_OFFSET;

                        $this->lookaround($crude, $idx,
                                          $idx + Lexer::NOR_LEFT_MOST - $save,
                                          20, \Analyzer\TOKEN\POS::NOR, false, true, ' ');
                        $this->scanHelper($crude,
                                          $save,
                                          $this->SUP_OFFSET,
                                          \Analyzer\TOKEN\POS::NOR);
                    }
                }

                if ($op->reqPos() & \Analyzer\OP\_ReqPos::SUP_AUX)
                {
                    if (!$this->lookaround
                        ($crude, $idx, 1, 2, \Analyzer\TOKEN\POS::SUP, false, false, ' ') &&
                        $op->reqPos() >> 1 & \Analyzer\OP\_ReqPos::SUP_PRI)
                        throw new \LexerException
                            ("Error: invalid operation require positions, {$op->name()}");
                    else
                    {
                        $save = $this->SUP_OFFSET;

                        $this->lookaround($crude, $idx,
                                          $idx + Lexer::SUP_LEFT_MOST - $save,
                                          20, \Analyzer\TOKEN\POS::SUP, false, true, ' ');
                        $this->scanHelper($crude,
                                          $save,
                                          $this->SUP_OFFSET,
                                          \Analyzer\TOKEN\POS::SUP);
                    }
                }
            }
        }

        private function processNumber(array $crude, int $idx, int $last, int $pos) {
            if (!($pos == \Analyzer\TOKEN\POS::NOR ||
                  $pos == \Analyzer\TOKEN\POS::SUB ||
                  $pos == \Analyzer\TOKEN\POS::SUP))
                throw new \LexerException('line: ' . __LINE__);

            if ($pos == \Analyzer\TOKEN\POS::NOR && $last > Lexer::NOR_RIGHT_MOST)
                throw new \LexerException('line: ' . __LINE__);
            else if ($pos == \Analyzer\TOKEN\POS::SUB && $last > Lexer::SUB_RIGHT_MOST)
                throw new \LexerException('line: ' . __LINE__);
            else if ($pos == \Analyzer\TOKEN\POS::SUP && $last > Lexer::SUP_RIGHT_MOST)
                throw new \LexerException('line: ' . __LINE__);

            $fst = $idx;
            $precision = 0;
            $number = null;
            $isFloat = false;

            // ===== exception point ===== //
            /*!< exception of identifier constants !>*/
            if ($crude[$idx] == 'e' || $crude[$idx] == 'pi' ||
                $crude[$idx] == '[\pi]')
            {
                if ($crude[$idx] == 'e') $number = M_E;
                if ($crude[$idx] == '[\pi]' ||
                    $crude[$idx] == 'pi')
                    $number = M_PI;

            }
            // ===== exception point ===== //

            else {
                while ((!($idx > $last)) &&
                       (ctype_digit($crude[$idx]) || ($crude[$idx] == '.')))
                {
                    if ($crude[$idx] == '.')
                    {
                        if(!ctype_digit($crude[$idx + 1]) || $isFloat)
                            throw new \LexerException();

                        $isFloat = true;
                        if ($fst == $idx) $number = 0;
                    }

                    if ($isFloat)
                        $number += (double) $crude[$idx++] / pow(10, $precision++);
                    else
                        $number = $number * 10 + (int) $crude[$idx++];
                }
                $idx--;
            }


            $this->symbolList[] = new \Analyzer\Symbol($pos, \Analyzer\TOKEN\TYPE::NUMBER, $number);
            if ($pos == \Analyzer\TOKEN\POS::NOR)
                $this->thread($crude, $idx, 1, 1, Lexer::NOR_LEFT_MOST, $pos, true);

            return ++$idx;
        }

        private function thread(array $crude,
                                int $idx,
                                int $left,
                                int $right,
                                int $base,
                                int $pos,
                                bool $isUpper)
        {
            if ($isUpper)
            {
                $shiftedPos = $pos << 1;
                $base += Lexer::MOD;
            }
            else
            {
                $shiftedPos = $pos >> 1;
                $base -= Lexer::MOD;
            }
            if (!$this->lookaround
                ($crude, $idx, $left, $right, $shiftedPos, false, false, ' '))
                return false;
            else
            {
                #$save = $this->SUP_OFFSET;
                $save = $this->getOffset($shiftedPos);
                $offset = &$this->getOffsetR($shiftedPos);
                $this->lookaround($crude, $idx,
                                  $idx + $base - $save,
                                  20, $shiftedPos, false, true, ' ');

                $this->scanHelper($crude,
                                  $save,
                                  $offset++,
                                  $shiftedPos);
                return true;
            }
        }

        /** @decaprecated */
        // private function isParen($crude, $idx, $pos, $number)
        // {
        //     if ($this->lookaround
        //         ($crude, $idx, 1, 0, \Analyzer\TOKEN\POS::SUP, false, true, '1') &&
        //         $this->lookaround
        //         ($crude, $idx, 1, 0, \Analyzer\TOKEN\POS::SUB, false, true, '1'))
        //     {
        //         $this->SUP_OFFSET++;
        //         $this->SUB_OFFSET++;

        //         $op = $this->operatorList->getOp('\pac');
        //         $this->processOperator($crude, $idx, \Analyzer\TOKEN\POS::NOR, $op);
        //         return $op;
        //     } else if ($this->lookaround
        //                ($crude, $idx, 0, 1, \Analyzer\TOKEN\POS::SUP, false, true, '1') &&
        //                $this->lookaround
        //                ($crude, $idx, 0, 1, \Analyzer\TOKEN\POS::SUB, false, true, '1'))
        //     {
        //         $this->SUP_OFFSET++;
        //         $this->SUB_OFFSET++;

        //         $op = $this->operatorList->getOp('\pao');
        //         $this->processOperator($crude, $idx, \Analyzer\TOKEN\POS::NOR, $op);
        //         return $op;
        //     } else return null;
        // }

        private function lookaround(array $crude,
                                    int $idx,
                                    int $left,
                                    int $right,
                                    int $pos,
                                    bool $move,
                                    bool $accept,
                                    ...$case)
        {
            $idx %= Lexer::MOD;
            if (!($pos == \Analyzer\TOKEN\POS::NOR ||
                  $pos == \Analyzer\TOKEN\POS::SUB ||
                  $pos == \Analyzer\TOKEN\POS::SUP))
                throw new \LexerException('line: ' . __LINE__);

            if ($pos == \Analyzer\TOKEN\POS::SUB)
            {
                $left  = $idx - $left + Lexer::SUB_LEFT_MOST > $this->SUB_OFFSET ?
                       $idx - $left + Lexer::SUB_LEFT_MOST :
                       $this->SUB_OFFSET;

                $right = $idx + $right + Lexer::SUB_LEFT_MOST < Lexer::SUB_RIGHT_MOST ?
                       $idx + $right + Lexer::SUB_LEFT_MOST :
                       Lexer::SUB_RIGHT_MOST;
            } else if ($pos == \Analyzer\TOKEN\POS::SUP)
            {
                $left  = $idx - $left + Lexer::SUP_LEFT_MOST > $this->SUP_OFFSET ?
                       $idx - $left + Lexer::SUP_LEFT_MOST :
                       $this->SUP_OFFSET;

                $right = $idx + $right + Lexer::SUP_LEFT_MOST < Lexer::SUP_RIGHT_MOST ?
                       $idx + $right + Lexer::SUP_LEFT_MOST :
                       Lexer::SUP_RIGHT_MOST;
            } else
            {
                $left  = $idx - $left + Lexer::NOR_LEFT_MOST > $this->NOR_OFFSET ?
                       $idx - $left + Lexer::NOR_LEFT_MOST :
                       $this->NOR_OFFSET;

                $right = $idx + $right + Lexer::NOR_LEFT_MOST < Lexer::NOR_RIGHT_MOST ?
                       $idx + $right + Lexer::NOR_LEFT_MOST :
                       Lexer::NOR_RIGHT_MOST;
            }

            $isTrue = false;
            for ($idx = $left;;)
            {
                if ($idx > $right)
                    break;

                foreach ($case as $it)
                {
                    if (($it == $crude[$idx]) == $accept) {
                        $isTrue = true;
                        goto outOfsearch;
                    }
                }

                $idx++;
            }
            outOfsearch:;

            if ($pos == \Analyzer\TOKEN\POS::SUB) $this->SUB_OFFSET = $idx + $move;
            else if ($pos == \Analyzer\TOKEN\POS::SUP) $this->SUP_OFFSET = $idx + $move;
            else $this->NOR_OFFSET = $idx + $move;

            return $isTrue;
        }
    }
}
?>
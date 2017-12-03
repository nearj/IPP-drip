<?php
namespace Analyzer\Parser
{
    include_once "analyzer.php";

    interface Prop
    {
        public function scan(array $symbolList);
        public function getPostfixExpr();
    }

    class Parser implements Prop
    {
        private $operatorList;

        private $stack = array();
        private $offset;
        private $offsetSave;
        private $numOfAssign = 0;
        private $idxOfAssign;
        private $symbolList;
        private $lhs;
        private $rhs;

        private $postfixExpr;

        public function __construct()
        {
            $this->operatorList = new \Analyzer\OP\OpList();
        }

        public function scan(array $symbolList)
        {

            $this->symbolList = &$symbolList;
            $this->preProcess($symbolList);

            $initSymbol = new \Analyzer\Symbol(
                \Analyzer\TOKEN\POS::NOR,
                \Analyzer\TOKEN\TYPE::OPERATOR,
                $this->operatorList->getOp('\pao'));
            $this->pushStck($initSymbol);

            $finalSymbol = new \Analyzer\Symbol(
                \Analyzer\TOKEN\POS::NOR,
                \Analyzer\TOKEN\TYPE::OPERATOR,
                $this->operatorList->getOp('\pac'));
            array_push($symbolList, $finalSymbol);

            $this->offsetSave = $this->offset = 0;
            // print "\nin total: ";
            // foreach($this->symbolList as $j)
            // {
            //     if ($j->type == \Analyzer\TOKEN\TYPE::OPERATOR)
            //         print $j->lexeme->name() . "@'{$j->pos}' ";
            //     else
            //         print $j->lexeme . "@'{$j->pos}' ";
            // }
            // TODO: to expr
            if ($this->numOfAssign == 0)
            {
                $this->scanHelper($symbolList, 0, count($symbolList) - 1);
                $this->lhs = $this->postfixExpr;
                $this->postfixExpr = array();

                // TODO: System synch with NULL
            }
            // print "\nin end: ";
            // foreach ($this->postfixExpr as $j){
            //     if ($j->type == \Analyzer\TOKEN\TYPE::OPERATOR)
            //         print $j->lexeme->name() . "@'{$j->pos}' ";
            //     else
            //         print $j->lexeme . "@'{$j->pos}' ";
            // }

            else if ($this->numOfAssign == 1)
            {
                // TODO: System synch

                $this->scanHelper($symbolList, 0, $i);
                $this->lhs = $this->postfixExpr;
                $this->postfixExpr = array(); // clear

                $this->scanHelper($symbolList, $i + 1, count($symbolList) - 1);
                $this->rhs = $this->postfixExpr;
            }
        }
         // TODO: delete return lhs
        public function getLHS()
        {
            return $this->lhs;
        }

        public function getPostfixExpr()
        {
            return $lhs;
        }

        private function infixToPostfix()
        {
            // multiplication handle need
            //
        }

        private function scanHelper(array $symbolList, int $fst, int $last)
        {
            // init
            $idx = $fst;

            // main: offset calculate needed
            for (;;)
            {
                if ($symbolList[$idx]->type & \Analyzer\TOKEN\TYPE::OPRERAND)
                {

                    $this->postfixExpr[] = $symbolList[$idx];

                    if ($symbolList[$idx]->pos == \Analyzer\TOKEN\POS::NOR &&
                        $symbolList[$idx + 1]->pos == \Analyzer\TOKEN\POS::SUP)
                    {
                        $idx = $this->delegateOperation(
                            new \Analyzer\Symbol(
                                \Analyzer\TOKEN\POS::NOR,
                                \Analyzer\TOKEN\TYPE::OPERATOR,
                                $this->operatorList->getOp('\po')), $idx);
                        continue;
                    }
                    else if ($symbolList[$idx]->pos == $symbolList[$idx + 1]->pos &&
                             $symbolList[$idx + 1]->type & \Analyzer\TOKEN\TYPE::OPRERAND)
                    {
                
                        $idx = $this->delegateOperation(
                            new \Analyzer\Symbol(
                                $symbolList[$idx]->pos,
                                \Analyzer\TOKEN\TYPE::OPERATOR,
                                $this->operatorList->getOp('\mu')), $idx);
                    } else $idx++;

                } else if ($symbolList[$idx]->type == \Analyzer\TOKEN\TYPE::OPERATOR)
                {
                    $idx = $this->delegateOperation($symbolList[$idx], $idx);
                } else $idx++;

                if($idx > $last) break;
            }

            // term
        }

        private function findOp(int $fst, int $last,int $pos, string $name)
        {
            for ($i = $fst; $i <= $last; $i++)
            {
                $symbol = $this->symbolList[$i];
                if ($symbol->pos == $pos && $symbol->lexeme->name() == $name)
                    return $i;
            }
            return -1;
        }

        private function delegateOperation(\Analyzer\Symbol $symbol, int $idx)
        {
            // init
            if (!($symbol->lexeme INSTANCEOF \Analyzer\OP\OP))
                throw new ParserException("line: " . __LINE__);
            // print "\n:: " . $symbol->lexeme->name() . "@{$symbol->pos}";
            if ($symbol->lexeme->name() == '\pao')
            {
                $this->pushStck($symbol);
            }
            else if ($symbol->lexeme->name() == '\pac')
            {
                try {
                while ($this->peekStck()->lexeme->order() <
                       \Analyzer\OP\OpList::COMMON_TYPE_GUIDE_LINE)
                    $this->postfixExpr[] = $this->popStck();
                } catch(\Exception $e) {
                    // TODO:
                    // print "\nin end**: ";
                    // foreach ($this->postfixExpr as $j){
                    //     if ($j->type == \Analyzer\TOKEN\TYPE::OPERATOR)
                    //         print $j->lexeme->name() . "@'{$j->pos}' ";
                    //     else
                    //         print $j->lexeme . "@'{$j->pos}' ";
                    // }
                }
                $this->popStck();
            }
            else {
                for (;;)
                {
                    try {
                    $peek = $this->peekStck();
                    } catch (\Exception $e) {
                        print $e;
                    }

                    if (($peek->lexeme->order() >=
                         $symbol->lexeme->order()) &&
                        ($peek->lexeme->order() < \Analyzer\OP\OpList::COMMON_TYPE_GUIDE_LINE))
                        $this->postfixExpr[] = $this->popStck();
                    else
                        break;
                }
                $this->pushStck($symbol);
                if ($symbol->lexeme->name() == '\di' || $symbol->lexeme->name() == '\de')
                {
                    $this->pushStck($this->symbolList[++$idx]);
                }
            }
            return ++$idx;
        }

        private function peekStck()
        {
            // TODO:
            // print "\nin peek: ";
            // foreach ($this->stack as $it)
            // {
            //     if ($it->type == \Analyzer\TOKEN\TYPE::OPERATOR)
            //         print $it->lexeme->name() . " ";
            //     else print $it->lexeme . " ";
            // }

            for ($i = count($this->stack) - 1; $i >= 0; $i--)
            {
                if ($this->stack[$i]->type == \Analyzer\TOKEN\TYPE::OPERATOR)
                    return $this->stack[$i];

            }
            throw new \Exception();
        }

        private function popStck()
        {
            // TODO:
            // print "\nin pop: ";
            // foreach ($this->stack as $it)
            // {
            //     if ($it->type == \Analyzer\TOKEN\TYPE::OPERATOR)
            //         print $it->lexeme->name() . " ";
            //     else print $it->lexeme . " ";
            // }
            return array_pop($this->stack);
        }

        private function pushStck(\Analyzer\Symbol $symbol)
        {
            if ($symbol == null)
                throw new \Exception();
            array_push($this->stack, $symbol);
            // TODO:
            // print "\nin push: ";
            // foreach ($this->stack as $it)
            // {
            //     if ($it->type == \Analyzer\TOKEN\TYPE::OPERATOR)
            //         print $it->lexeme->name() . " ";
            //     else print $it->lexeme . " ";
            // }
        }

        private function preProcess(array &$symbolList)
        {
            for ($i = 0; $i < count($symbolList); $i++)
            {
                $symbol = $symbolList[$i];
                if ($symbol->type == \Analyzer\TOKEN\TYPE::OPERATOR)
                {
                    if ($symbol->lexeme->hasPair())
                    {
                        array_splice($symbolList,
                                     $i,
                                     0,
                                     new \Analyzer\Symbol($symbol->pos,
                                                          \Analyzer\TOKEN\TYPE::OPERATOR,
                                                          $this->operatorList->getOp('\pao')));

                        $pairName = $symbol->lexeme->getPair()->name();
                        $j = count($symbolList) - 1;
                        for (; $j > $i; $j--)
                        {
                            $symbolTmp = $symbolList[$j];
                            if ($symbolTmp->type == \Analyzer\TOKEN\TYPE::OPERATOR &&
                                $symbolTmp->pos == $symbol->pos)
                            {
                                if ($symbolTmp->lexeme->name == $pairName)
                                {
                                    if ($pairName == '\di')
                                        array_splice
                                            ($symbolList,
                                             $j + 2,// TODO: dx adjust need.
                                                    // PairOp extensiont need.
                                             0,
                                             new \Analyzer\Symbol
                                             ($symbol->pos,
                                              \Analyzer\TOKEN\TYPE::OPERATOR,
                                              $this->operatorList->getOp('\pac')));
                                }
                            }
                        }
                        if ($i == $j)
                            throw new Exception("unmatched integral operation with dx: " .
                                                __LINE__);

                        $i += 2;
                    }
                }
                else if ($symbol->type == \Analyzer\TOKEN\TYPE::ASSIGN)
                {
                    if (++$this->numOfAssign > 1)
                        throw new Exception("number of assign is over allowed: " . __LINE__);
                    $this->idxOfAssign = $i;
                }

                if ($i < count($symbolList) - 1)
                {
                    if ($symbol->pos == \Analyzer\TOKEN\POS::NOR &&
                        $symbolList[$i + 1]->pos != \Analyzer\TOKEN\POS::NOR)
                    {
                        array_splice(
                            $symbolList,
                            $i + 1,
                            0,
                            [new \Analyzer\Symbol(
                                $symbolList[$i + 1]->pos,
                                \Analyzer\TOKEN\TYPE::OPERATOR,
                                $this->operatorList->getOp('\pao'))]);
                        $i++;
                    }
                    else if ($symbol->pos != \Analyzer\TOKEN\POS::NOR &&
                        $symbolList[$i + 1]->pos == \Analyzer\TOKEN\POS::NOR)
                    {
                        array_splice(
                            $symbolList,
                            $i + 1,
                            0,
                            [new \Analyzer\Symbol(
                                $symbolList[$i - 1]->pos,
                                \Analyzer\TOKEN\TYPE::OPERATOR,
                                $this->operatorList->getOp('\pac'))]);
                        $i++;
                    }
                    else if ($symbol->pos == \Analyzer\TOKEN\POS::SUB &&
                             $symbolList[$i + 1]->pos == \Analyzer\TOKEN\POS::SUP)
                    {
                        array_splice(
                            $symbolList,
                            ++$i,
                            0,
                            [new \Analyzer\Symbol(
                                $symbol->pos,
                                \Analyzer\TOKEN\TYPE::OPERATOR,
                                $this->operatorList->getOp('\pac'))]);
                        array_splice(
                            $symbolList,
                            ++$i,
                            0,
                            [new \Analyzer\Symbol(
                                $symbolList[$i]->pos,
                                \Analyzer\TOKEN\TYPE::OPERATOR,
                                $this->operatorList->getOp('\pao'))]);
                    }
                }
            }
        }
    }
}
?>
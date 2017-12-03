<?php
namespace Analyzer\TOKEN
{
    interface TYPE
    {
        const UNKNOWN    = 0;

        const NUMBER     = 0b000000000111;
        const IDENTIFIER = 0b000000111000;
        const OPRERAND   = TYPE::NUMBER | TYPE::IDENTIFIER;
        const OPERATOR   = 0b000111000000;
        const ASSIGN     = 0b111000000000;
    }

    interface POS
    {
        const SUB = 0b001001001001;
        const NOR = 0b010010010010;
        const SUP = 0b100100100100;
    }
}

namespace Analyzer\OP
{
    interface _Arity
    {
        const Nullary    = 0b00000000;
        const Unary      = 0b00000011;
        const UnaryPre   = 0b00000001;
        const UnaryPost  = 0b00000010;
        const Binary     = 0b00000100;
        const Ternary    = 0b00001000;
        const Quaternary = 0b00010000;
        const N_ary      = ~0b0;
        public function arity();
    }

    interface _ReqPos
    {
        const NONE     = 0;
        const SUB_PRI = 0b0000001100;
        const SUB_AUX = 0b0000000100;
        const NOR_PRI = 0b0001100000;
        const NOR_AUX = 0b0000100000;
        const SUP_PRI = 0b1100000000;
        const SUP_AUX = 0b0100000000;
        const SPECIAL = 0b0000000001;

        public function reqPos();
    }

    class Base implements _Arity, _ReqPos
    {
        private $name;
        private $alias;
        private $arity;
        private $order;
        private $reqPos;

        public function __construct(string $name,
                                    int $order,
                                    int $arity,
                                    int $reqPos,
                                    array $alias)
        {
            $this->name  = $name;
            $this->alias = $alias;
            $this->order  = $order;
            $this->arity  = $arity;
            $this->reqPos = $reqPos;
        }

        public function order()  { return $this->order; }
        public function arity()  { return $this->arity; }
        public function reqPos() { return $this->reqPos; }
        public function name() { return $this->name; }
        public function alias() { return $this->alias; }
    }

    class OP extends Base
    {
        private $isCommon               = true;
        private $inverse                = null;
        private $pair                   = null;

        public function __construct(string $name,
                                    int $order,
                                    int $arity,
                                    int $reqPos,
                                    bool $isCommon,
                                    array $alias)
        {
            parent::__construct($name,
                                $order,
                                $arity,
                                $reqPos,
                                $alias);
            $isCommon = $isCommon;
        }

        /** @decaprecated */
        public function isCommon()
        { return $this->isCommon; }

        public function hasInverse()
        { return $this->inverse != null; }

        public function hasPair()
        { return $this->pair != null;}

        public function setInverse(OP $op)
        {
            $this->inverse = $op;
            if (!$op->hasInverse())
                $op->setInverse($this);
        }

        public function setPair(OP $op)
        {
            $this->pair = $op;
        }

        public function getInverse()
        {
            if ($this->inverse == null)
                throw new \OpException();
            return $this->inverse;
        }

        public function getPair()
        {
            if ($this->pair == 0)
                throw new \OpException();
            return $this->pair;
        }
    }

    class OpList
    {
        const COMMON_TYPE_GUIDE_LINE = 7; // parenthesis handled
        private static $_list;

        public function __construct()
        {
            /* OP(name, order, arity, reqPos, alias) */
            $def_pl = new OP('\pl',
                             1,
                             _Arity::UnaryPre | _Arity::Binary,
                             _ReqPos::NONE,
                             true,
                             array('plus',
                                   '\plus'));
            $def_mi = new OP('\mi',
                             1,
                             _Arity::UnaryPre | _Arity::Binary,
                             _ReqPos::NONE,
                             true,
                             array('minus',
                                   '\minus'));	// minus

            // ===== exception point ===== //
            /* explain: Multiplication is interpolated in blank
             * 		   between operands
             */
            $def_mu = new OP('\mu',
                             2,
                             _Arity::Binary,
                             _ReqPos::NONE,
                             true,
                             array('mul',
                                   'multiple',
                                   '\mul'));
            // ===== \exception point ===== //

            $def_fr = new OP('\fr',
                             2,
                             _Arity::Binary,
                             _ReqPos::NONE,
                             true,
                             array('frac',
                                   '\frac',
                                   'div',
                                   'division',
                                   '\div'));		// division

            // ===== exception point ===== //
            /* explain: Power or Exponential is interpolated
             * 		   normal line to superscript
             */
            $def_po = new OP('\po',
                             3,
                             _Arity::Binary,
                             _ReqPos::NONE,
                             true,
                             array('pow',
                                   'exp',
                                   'exponential',
                                   'power',
                                   '\exp',
                                   '\power'));		// power
            /* explain: Root or sqrt is interpolated as 'r'
             */
            $def_ro = new OP('\ro',
                             3,
                             _Arity::UnaryPre,
                             _ReqPos::NONE,
                             true,
                             array('r',
                                   'root',
                                   '\root'));		// root
            /* explain: Logarithm is interpolated as 'log'
             */
            $def_lo = new OP('\lo',
                             3,
                             _Arity::UnaryPre,
                             _ReqPos::SUB_AUX,
                             true,
                             array('log',
                                   '\log'));

            // ===== /exception point ===== //

            $def_fa = new OP('\fa',
                             4,
                             _Arity::UnaryPost,
                             _ReqPos::NONE,
                             true,
                             array('fact',
                                   'factorial'));

            // ===== exception point ===== //
            /* explain: Sine is interpolated as 'sin'
             */
            $def_si = new OP('\si',
                             5,
                             _Arity::UnaryPre,
                             _ReqPos::SUP_AUX,
                             true,
                             array('sin',
                                   '\sin')); 		// sine
            /* explain: Cosine is interpolated as 'cos'
             */
            $def_co = new OP('\co',
                             5,
                             _Arity::UnaryPre,
                             _ReqPos::SUP_AUX,
                             true,
                             array('cos',
                                   '\cos')); 		// cosine
            /* explain: Tangent is interpolated as 'tan'
             */
            $def_ta = new OP('\ta',
                             5,
                             _Arity::UnaryPre,
                             _ReqPos::SUP_AUX,
                             true,
                             array('tan',
                                   '\tan')); 		// tangent
            // ===== \exception point ===== //

            /* common type: parenthesis open and close
             */
            $def_pao = new OP('\pao',
                              OpList::COMMON_TYPE_GUIDE_LINE,
                              _Arity::Nullary,
                              _ReqPos::NONE,
                              false,
                              array('1',
                                    'i',
                                    'parenthesis_open',
                                    '\paren_open')); 		// parenthesis
            $def_pac = new OP('\pac',
                              OpList::COMMON_TYPE_GUIDE_LINE,
                              _Arity::Nullary,
                              _ReqPos::SUP_AUX,
                              false,
                              array('1',
                                    'i',
                                    'parenthesis_open',
                                    '\paren_close')); 		// parenthesis

            /* explain: Derivate is interpolated as 'd\d?' at postprocess
             * 		   or 'D'
             */
            $def_de = new OP('\de',
                             6,
                             _Arity::Binary,
                             _ReqPos::NONE,
                             true,
                             array('D',
                                   'de',
                                   'der',
                                   '\derivative'));
            /* explain: Quadrature is interpolated as 'E'
             */
            $def_qu = new OP('\qu',
                             6,
                             _Arity::Ternary,
                             _ReqPos::SUB_PRI | _ReqPos::SUP_PRI,
                             true,
                             array('E',
                                   'quad',
                                   'quadrature',
                                   '\quad'));		// quadrature
            // ===== exception point ===== //

            /* explain: Integral is interpolated as 's'
             */
            $def_in = new OP('\in',
                              -1,
                             _Arity::Quaternary,
                             _ReqPos::SUB_PRI | _ReqPos::SUP_PRI,
                             false,
                             array('s',
                                   'int',
                                   'integral',
                                   '\int'));	 // integral

            /* explain: Differential is interpolated as 'd' or 'D'
             */
            $def_di = new OP('\di',
                             0,
                             _Arity::UnaryPre,
                             _ReqPos::NONE,
                             false,
                             array('d',
                                   'di',
                                   'diffrential',
                                   '\dif'));
            // ===== \exception point ===== //


            /*!< default >!*/
            $def_pl->setInverse($def_mi);
            $def_mu->setInverse($def_fr);
            $def_po->setInverse($def_lo);
            $def_in->setInverse($def_di);

            $def_in->setPair($def_di);

            OpList::$_list = array (
                $def_pl, $def_mi, $def_mu, $def_fr, $def_po,
                $def_ro, $def_lo, $def_fa, $def_si, $def_co,
                $def_ta, $def_pao, $def_pac, $def_in, $def_di,
                $def_de, $def_qu
            );

        }

        public static function getOp(string $nameOrAlias)
        {
            foreach (OpList::$_list as $it)
            {
                if ($it->name() == $nameOrAlias)
                    return $it;
                foreach ($it->alias() as $alias)
                {
                    if ($alias == $nameOrAlias)
                        return $it;
                }
            }
        }

        // TODO: revise need.
        public static function addOp(string $name, int $order)
        { array_push(OpList::$_list, new OP($name, $order)); }
    }
}

namespace Analyzer
{
    include_once "lexer.php";
    include_once "parser.php";

    class Symbol
    {
        public $pos;
        public $type;
        public $lexeme;

        public function __construct(int $pos,
                                    int $type,
                                    $lexeme)
        {
            $this->pos    = $pos;
            $this->type   = $type;
            $this->lexeme = $lexeme;
        }
    }
}

namespace
{
    class OpException extends Exception {}
    class LexerException extends Exception {}
    class ParserException extends Exception {}
}
?>
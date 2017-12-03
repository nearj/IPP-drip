<?php

namespace System\NumericalMethod
{

}

namespace System\Variable
{
    class Base
    {
        private $value;
        private $identifier;
        public function __construct($value, $identifier)
        {
            $this->value = $value;
            $this->identifier = $identifier;
        }

        public function getValue()
        {
            return $this->value;
        }

        public function getIdent()
        {
            return $this->identifier;
        }
    }

    class GlobalVariable extends Base
    {
        private $evaluablity;

        public function __construct($value, $identifier, $evaluablity)
        {
            parent::__construct($value, $identifier);
            $this->evaluablity = $evaluablity;
        }

        public function setEvaluablity(bool $evaluablity)
        {
            $this->evaluablity = $evaluablity;
        }
    }

    class GlobalVarList
    {
        private static $_list = array();

        public function __construct() {}

        public function add(GlobalVariable $gv)
        {
            foreach(GlobalVarList::$_list as $it)
            {
                if(!$gv->getIdent() == $it->getIdent())
                    $this->_list[] = $gv;
            }
        }

        public function get(string $ident)
        {
            foreach(GlobalVarList::$_list as $it)
            {
                if($it->getIdent() == $ident)
                    return $it;
            }
            throw new VarException('line: ' . __LINE__);
        }

        public function peek(string $ident)
        {
            foreach(GlobalVarList::$_list as $it)
            {
                if($it->getIdent() == $ident)
                    return true;
            }
            return false;
        }

        public function clear()
        {
            GlobalVarList::$_list = array();
        }
    }

    class LocalVariable extends Base
    {
        public function __construct($value, $identifier)
        {
            parent::__construct($value, $identifier);
        }
    }
}

namespace System\Equation\Expr
{
    interface Prop
    {
        const MAXLEN = 60;
    }

    class Base implements Prop
    {
        public function __construct() {}
    }

    class NULL_Expr extends Base
    {
        public function __construct()
        {
            parent::__construct();
        }
    }

    class CONST_Expr extends Base
    {
        private $value;

        public function __construct(int $val)
        {
            parent::__construct();
            $this->value = $value;
        }

        public function getVal()
        { return $this->value; }
    }

    class VAR_Expr extends Base
    {
        private $var;

        public function __construct()

        {
            parent::__construct();
            $this->$var = $var;
        }
    }

    class CLOSED_Expr extends Base
    {
        private $closer;
        private $varList;

    }
}

namespace System\Equation
{
    interface Prop
    {
        public function set(Expr\Base $lhs, Expr\Base $rhs);
    }

    class Base implements Prop
    {
        private $rhs;
        private $lhs;
        public function set(Expr\Base $lhs, Expr\Base $rhs)
        {

        }
    }

    class Assign extends Base
    {

        public function set(Expr\VAR_Expr $lhs, Expr\CONST_Expr $rhs)
        {
            $this->lhs = $lhs;
            $this->rhs = $rhs;
        }
    }

    class EvalX extends Base
    {

    }

    class EvalY extends Base
    {

    }

    class Equal extends Base
    {

    }
}

namespace System
{
    interface Prop
    {
        public function arrange();
        public function interpret();
    }
}

namespace
{
    class VarException extends Exception {}
    class EqException extends Exception {}
}


?>
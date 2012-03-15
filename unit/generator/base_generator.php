<? namespace ovasen\core\unit\generator;

abstract class BaseGenerator extends BaseUnit
{
    protected function connectTo() {
       throw new BaseGeneratorException("Can't connect to a generator: ", $this->getFullName());
    }
}

class BaseGeneratorException { };

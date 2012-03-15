<?php namespace ovasen\unit\processor\filter;

use ovasen\unit\processor\BaseProcessor;

abstract class BaseFilter extends BaseProcessor {
    protected function connectTo(BaseUnit $destination) {
        if (count($this->outputs) > 0 ) {
            throw new BaseFilterException("Only accepting one connection: ", $this->getFullName());
        }

        $destination->connectFrom($this);
        $this->outputs[$destination->getFullName] = $destination;
    }
}

class BaseFilterException { };

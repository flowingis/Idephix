<?php
namespace Idephix\Task\Parameter;

class UserDefinedCollection extends \FilterIterator
{
    public function accept()
    {
        return parent::current() instanceof UserDefined;
    }
}

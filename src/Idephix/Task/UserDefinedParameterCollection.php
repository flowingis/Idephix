<?php
namespace Idephix\Task;

class UserDefinedParameterCollection extends \FilterIterator
{
    public function accept()
    {
        return parent::current() instanceof UserDefinedParameter;
    }
}

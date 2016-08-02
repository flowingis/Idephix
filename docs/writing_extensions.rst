Extensions
==========

Extensions are meant to wrap reusable code into a class that you can wire to your next Idephix project. If you find
yourself writing the same task over and over again you may want to put it into an Extension so you can easily reuse it
in every projects.

An Extension are identified by a name, and are capable of:

- registering new Tasks, so they will be directly available from CLI
- registering helpers that will be hooked into the Idephix instance so you can use them within other tasks

Writing Extensions
------------------

An Extension is simply a class implementing `\Idephix\Extension` interface. This will require you do define a name
and, TasksCollection and a HelperCollection. If your extension don't need to register new tasks or methods, you can
simply return an empty collection (`\Idephix\Task\TaskCollection::dry()` or `\Idephix\Extension\HelperCollection::dry()`).

If you need an instance of the current `\Idephix\IdephixInterface` within your extension, simply implement also
the `\Idephix\Extension\IdephixAwareInterface` and you'll get one at runtime.

Only method registered by `\Idephix\Extension::helpers()` will be plugged into Idephix and will be available for other tasks to use:

.. code-block:: php

    class DummyExtension implements Extension
    {

        public function doStuff(\Idephix\IdephixInterface $idx, $foo, $go = false)
        {
            //do some stuff
        }

        /** @return array of callable */
        public function helpers()
        {
            return Extension\HelperCollection::ofCallables(
                array(
                    new Extension\CallableHelper('doStuff', array($this, 'doStuff'))
                )
            );
        }

    //cut
    }

.. code-block:: php

    //your idxfile.php

    function deploy(IdephixInterface $idx, $go = false)
    {
        //your deploy business logic here
        $idx->doStuff($foo, $go)
    }


If you want to expose some of your methods as CLI commands you need to define them as a task:

.. code-block:: php

    class DummyExtension implements Extension
    {

        /** @return TaskCollection */
        public function tasks()
        {
            return TaskCollection::ofTasks(array(
                new Task(
                'doStuff',
                'DummyExtension helps you doing stuff',
                array($this, 'doStuff'),
                Collection::createFromArray(array(
                    'foo' => array('description' => 'A nice description of foo')
                )),
            ));
        }

    //cut
    }

And the you'll also get to execute it directly from cli:

.. code-block:: bash

    $ idx doStuff bar --go


``Check out our `available extensions <https://github.com/ideatosrl/Idephix/tree/master/src/Idephix/Extension>`_
to see more complex examples ..``

Execution priority
------------------

Idephix will always try to execute code from the idxfile first, so if some function within the idxfile conflicts
with some registered method or task, the code from the idxfile will be executed and the extension code will be ignored.

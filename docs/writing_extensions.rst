Extensions
==========

Extensions are meant to wrap reusable code into a class that you can wire to your next Idephix project. If you find
yourself writing the same task over and over again you may want to put it into an Extension so you can easily reuse it
in every projects.

.. hint::

    Extensions should be used wisely, for most cases a bunch of tasks that you copy and paste across projects is
    the best solution. We in the first place dropped the Deploy solution to a standard recipe that we include in
    out idxfile for each project. This ease the readability and the hackability of the procedure. An Extension will
    allow you to define reusable code, but it will hide it a little bit, so take this in consideration before
    implementing one

An Extension is identified by a name, and is capable of:

- registering new Tasks, so they will be directly available from CLI
- registering methods that will be hooked into the Idephix instance so you can use them within other tasks

Writing Extensions
------------------

An Extension is simply a class implementing `\Idephix\Extension` interface. This will require you do define a name
and, TasksCollection and a MethodCollection. If your extension don't need to register new tasks or methods, you can
simply return an empty collection (`\Idephix\Task\TaskCollection::dry()` or `\Idephix\Extension\MethodCollection::dry()`).

If you need an instance of the current `\Idephix\Context` within your extension, simply implement also
the `\Idephix\Extension\IdephixAwareInterface` and you'll get one at runtime.

Only method registered by `::methods()` will be plugged into Idephix and will be available for other tasks to use:

.. code-block:: php

    class DummyExtension implements Extension
    {

        public function doStuff(IdephixInterface $idx, $foo, $go=false)
        {
            //do some stuff
        }

        /** @return array of callable */
        public function methods()
        {
            return Extension\MethodCollection::ofCallables(
                array(
                    new Extension\CallableMethod('doStuff', array($this, 'doStuff'))
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

    $ idx doStuff bar


``Check out our `available extensions <https://github.com/ideatosrl/Idephix/tree/master/src/Idephix/Extension>`_
to see more complex examples ..``

Execution priority
------------------

Idephix will always try to execute code from the idxfile first, so if some function within the idxfile conflicts
with some registered method or task, the code from the idxfile will be executed and the extension code will be ignored.

Extensions
==========

Extensions are meant to wrap reusable code into a class that you can wire to your next Idephix project. If you find
yourself writing the same task over and over again you may want to put it into an Extension so you can easily reuse it
in every projects.

An Extension are identified by a name, and are capable of:

- registering new Tasks, so they will be directly available from CLI
- add functionality to your `Idephix` instance so you can use them within other tasks

Writing Extensions
------------------

An Extension is simply a class implementing `\Idephix\Extension` interface. This will require you do define a name
and a TasksCollection. If your extension don't need to register new tasks (you may just want to add some logic to
Idephix so you can use it from other tasks), you can simply return an empty collection using
`\Idephix\Task\TaskCollection::dry()`.

If you need an instance of the current `\Idephix\IdephixInterface` within your extension, simply implement also
the `\Idephix\Extension\IdephixAwareInterface` and you'll get one at runtime.

Every public method will be plugged into Idephix and will be available for other tasks to use:

.. code-block:: php

    class DummyExtension implements Extension
    {

        public function doStuff($foo)
        {
            //do some stuff
        }

    //cut
    }

.. code-block:: php

    //your idxfile.php

    function deploy(IdephixInterface $idx)
    {
        //your deploy business logic here
        $idx->doStuff($foo)
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

Overriding Extensions
---------------------

Some times you want to wrap some boiler plate code within an extension but still be able to override some bits here and
there to customize them for a specific project. This is especially the case for deployments extensions where a large
bunch of code will be extremely reusable but you will still need to have some project specific business logic.

The good news is that Idephix will assign an higher priority to user defined code over extensions, which means that you
can use an extension and override some of its methods, just defining them in your idxfile as functions.


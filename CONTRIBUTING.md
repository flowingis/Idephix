# How to contribute

You can contribute to idephix in many ways: sending PR, requesting new functionality, fixing bugs. 
We want to make our development workflow public and to ease third party contributions like yours, 
so we created a public [kanban board for the project][waffle].

Feel free to use what you're more comfortable with, either the kanban board or github issues.

## Sending PR

Fork, then clone the repo:

    git clone git@github.com:your-username/factory_girl_rails.git
    
Make sure the tests pass:

    bin/idephix.phar build
    
This will install all vendors and run all the tests.
Push to your fork and [submit a pull request][pr].

We will get back to you as soon as possible to discuss your PR
and decide if accept it. 

Some things that will increase the chance that your pull request is accepted:

* Write tests for your code (no test no party)
* Follow PSR2 coding style guide
* Document any new features you add

## Coding Style Guide

idephix follows PSR2 coding standard, and you must respect that too if you 
want your contribution to be accepted. We don't want you to waste time fixing 
formatting though, you should add php-cs-fixer for that instead. 

    bin/php-cs-fixer fix {$fileName} --level=psr2 --fixers=long_array_syntax,single_quote,unused_use
    
You could also add a [pre-commit hook][hook] to ensure all committed files respect our 
coding standard

```php
#!/usr/bin/php
<?php
/**
 * .git/hooks/pre-commit
 *
 * This pre-commit hooks will check for PHP error (lint), and make sure the code
 * is PSR compliant.
 *
 * Dependecy: PHP-CS-Fixer (https://github.com/fabpot/PHP-CS-Fixer)
 *
 * @author  Mardix  http://github.com/mardix
 * @since   Sept 4 2012
 *
 */

/**
 * collect all files which have been added, copied or
 * modified and store them in an array called output
 */
exec('git diff --cached --name-only --diff-filter=ACM', $output);

foreach ($output as $file) {

    $fileName = trim($file);

    if (pathinfo($fileName,PATHINFO_EXTENSION) == "php") {

        $lint_output = array();
        exec("php -l " . escapeshellarg($fileName), $lint_output, $return);

        if ($return == 0) {

          exec("bin/php-cs-fixer fix {$fileName} --level=psr2 --fixers=long_array_syntax,single_quote,unused_use");
          exec("git add {$fileName}");

        } else {
           echo implode("\n", $lint_output), "\n";
           exit(1);

        }

    }

}

exit(0);
```

## Writing documentation

We use [read the docs][rtd] to document features, and we really encourage you to write documentation
for any new feature you want to submit. Read the docs is based on sphinx, so in order to be able to generate 
documentation locally you'll need to [install some deps][rtd-getting-started]. Once you have all up and running 
you can generate the docs using idephix 

    bin/idephix.phar buildDoc
    
and then open `docs/_build/html/index.html` with your preferred browser.

## Dogfooding

We use idephix as the build tool for idephix itself. Our [continuous integration][ci] uses `bin/idephix.phar`
to build the project. As a developer is your responsibility to update the phar when needed. Lucky enough the is
a task for that

    bin/idephix.phar updatePhar
    
[waffle]: https://waffle.io/ideatosrl/Idephix
[pr]: https://waffle.io/ideatosrl/Idephix
[hook]: https://git-scm.com/book/it/v2/Customizing-Git-Git-Hooks
[rtd]: http://idephix.readthedocs.io/
[rtd-getting-started]: http://read-the-docs.readthedocs.io/en/latest/getting_started.html
[ci]: https://travis-ci.org/ideatosrl/Idephix
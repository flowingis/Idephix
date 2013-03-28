<h2>Manage parameters</h2>

<p>Every anonymous function passed to the <code>add</code> method will be transformed in a idephix task. Function parameters will be used as the task arguments.</p>

<h3>Add a parameter</h3>

<pre><code>
/**
 * Execute the touch of a file named in input in a remote server
 * @param string $name the name of the file to be touch-ed
 */    
 add('myscript:remote-touch', 
   function ($name) use ($idx) {
     $idx->remote('touch /tmp/'.$name);
 });
</code></pre>

<p>The parameter $name will be a mandatory option to be specified in the command execution</p>

<pre><code>$ ./idephix.phar myscript:remote-touch myFile.txt</code></pre>

<h3>Add two or more parameters</h3>

<p>You can add two or more arguments as parameters of the anonimous function</p>

<pre><code>
/**
 * Execute the touch of a file named in input in a remote server
 * @param string $name the name of the file to be touch-ed
 * @param string $extension is the extension of the file to be touch-ed
 */    
 add('myscript:remote-touch', 
   function ($name, $extension) use ($idx) {
     $idx->remote('touch /tmp/'.$name.'.'.$extension);
 });
</code></pre>

<pre><code>$ ./idephix.phar myscript:remote-touch myFile txt</code></pre>

<h3>Add an optional parameter</h3>

<pre><code>
/**
 * Execute the touch of a file named in input in a remote server
 * @param string $name the name of the file to be touch-ed
 * @param string $extension is the extension of the file to be touch-ed
 */    
 add('myscript:remote-touch', 
   function ($name, $extension = 'txt') use ($idx) {
     $idx->remote('touch /tmp/'.$name.'.'.$extension);
 });
</code></pre>

<pre><code>$ ./idephix.phar myscript:remote-touch myFile</code></pre>

<h3>Add a flag</h3>

<p>A flag is a special parameter with default value <code>false</code>. Using flags should be useful to implement a dry-run approach in your script</p>

<pre><code>
/**
 * Delete the cache directory
 * @param bolean $go Use --go to execute the script
 */    
 add('myscript:remote-clearcache', 
   function ($go = false) use ($idx) {
     if ($go) {
     	$idx->remote('rm -rf ./var/www/project/cache/*');
     	return;
     }

     $idx->output->writeln('<info>use --go to delete the following files</info>')
     $idx->remote('ls -al ./var/www/project/cache/');   

 });
</code></pre>

<pre><code>$ ./idephix.phar myscript:remote-clearcache --go</code></pre>

# Commands

Commandy can be easily added by just creating a file inside `App\Console\Commands`.

# Quick start

Create a file `App\Console\Commands\Foo.php` and insert the following content:

```php
<?php
    
    namespace App\Console\Commands;
    
    class Foo extends \Xmgr\Command {
        
        public function bar(): void {
            echo 'Awesome, that works 👍';
        }
        
    }
    
```

In fact, that's it already. Now you can run the command in the console like

```shell
php index.php foo:bar
```

(assuming the current directory is your app root and there exists a
file `index.php` - adjust correspondingly if needed)

# How it works

The "command" in that example is `foo:bar`, consisting of

- the command handler (the part before the `:`, so `foo`) and
- the command action (the part after the `:`, which is `bar`)

⚠ The command *must* be entered in [snake_case](terminology.md#string-cases).

The system will check if there's a php file inside `App\Console\Commands` with the
[PascalCase](terminology.md#string-cases) version of the command handler. Next, it will search for
a method with the [snake_case](terminology.md#string-cases) version of the command action.

If both requirements are met, that method is executed.

# Another example

Create a file `App\Console\Commands\HelloWorld.php` and insert the following content:

```php
<?php
    
    namespace App\Console\Commands;
    
    class HelloWorld extends \Xmgr\Command {
        
        public function foo_bar(): void {
            echo 'Awesome, this also works 👍';
        }
        
    }
    
```

Now you can run the following command in the console to execute the `foo_bar` method
in the `HelloWorld` class:

```shell
php index.php hello_world:foo_bar
```

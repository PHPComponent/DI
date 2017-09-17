# PHPComponent\DI #

DI is Dependency Injection container. It will build whole dependency tree of your application.

## Configuration example ##
You can configure container using PHP.
For example you have class Translator, which have one constructor parameter `$default_language`.
```php
class Translator
{
    /** @var string */
    private $default_language;
    
    public function __construct($default_language)
    {
        $this->default_language = $default_langauge;
    }
}
```
Then you configure Container.
```php
$parameters = new \PHPComponent\DI\ParametersBag(array('default_language' => 'en'));
$container_builder = new \PHPComponent\DI\ContainerBuilder($parameters);
$container_builder->registerService('translator', Translator::class)
    ->setArguments(array('%default_language%'));
$translator = $container_builder->getService('translator');
```
When you call `getService('translator')` you will get Translator with default language en.
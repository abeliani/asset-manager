# AssetManager

Helps to manage your assets, with minimize, optimize and merge files.
The library is using two cache layers.

# Overview

```text
my_site/
    ├── css/
    │   ├── styles.css
    │   ├── reset.css
    │   └── main.css
    ├── js/
    │   ├── app.js
    │   ├── utils.js
    │   └── main.js
    └──MySiteBundle.php
```

```php
class MySiteBundle extends Bundle 
{
    public finction getTags(): TagInterface|\SplFixedArray|array
    {
        /* Separated files
        reurn [
            new Css('styles.css'),
            new Css('reset.css'),
            new Css('main.css'),
            // js...
        ]; */
        
         /* With attributes
        reurn [
            (new Css('styles.css')->addAttr('async'),
            (new Js('app.js'))->addAttr('media', 'print'),
            // ...
        ]; */
        
        /* Merge files
        return [
            new Css('styles.css', 'reset.css', 'main.css'),
            new Js('app.js', 'utils.js', 'main.js'),
        ]; */
        
        // With timestamp
        return [
            (new Css('styles.css', 'reset.css', 'main.css'))->withTimestamp(),
            (new Js('app.js', 'utils.js', 'main.js'))->withTimestamp(),
        ];
    }
}
```

By default, all styles or scripts will be optimized. If you want to do some minimizing you can use minimize() method

```php
    /*
     * Let's minimize? optimize and merge all scripts and all styles to two files style.css and app.js
     */
    public finction getTags(): array
    {
        reurn [
             (new Css('styles.css', 'reset.css', 'main.css'))->minimize()->withTimestamp(),
             (new Js('app.js', 'utils.js', 'main.js'))->minimize()->withTimestamp(),
        ]; 
    }
```

In result, we will have here html to include our files

```html
<link href="//localhost/assets/e30fdf4770/concrete/css/styles.css?ts=1717328901" rel="stylesheet">
<script src="//localhost/assets/e30fdf4770/concrete/js/app.js?ts=1717328901"></script>
```

## Installation

```bash
composer require abeliani/asset-manager
```

## More examples

### Configure asset manager

```php
$this->manager = new AssetManager(
    'https://mysite.cool',
    '/path/to/runtime/dir',
    '/path/to/asset/dir',
    $env === 'prod',
);
```

### Add our bundle to manager

```php
$this->manager->addBundle(new MySiteBundle());
$this->manager->addBundle(new MyAdminBundle(), AssetManagerInterface::CATEGORY_TOP);
$this->manager->addBundle(new MyEditorBundle(), AssetManagerInterface::CATEGORY_BOTTOM);
```

### Get some HTML to include our result

```php
print $this->manager->process();
```

It will display something like that
```html
<link href="//localhost/assets/e30fdf4770/concrete/css/styles.css" rel="stylesheet">
<script src="//localhost/assets/e30fdf4770/concrete/js/app.js"></script>
```

```php
print $this->manager->process(AssetManagerInterface::CATEGORY_TOP);
```

Or like that

```html
<link href="//localhost/assets/e30fdf4770/concrete/css/styles.css" rel="stylesheet" media="print">
<link href="//localhost/assets/e30fdf4770/concrete/css/reset.css" rel="stylesheet">
```

```php
print $this->manager->process(AssetManagerInterface::CATEGORY_BOTTOM);
```

Or like that

```html
<script src="//localhost/assets/e30fdf4770/concrete/js/editor.js" async></script>
```

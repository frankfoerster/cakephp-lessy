# Lessy = LessCSS + CSS-Minifier Plugin for CakePHP 2.3+
[![Build Status](https://travis-ci.org/frankfoerster/cakephp-lessy.png?branch=master)](https://travis-ci.org/frankfoerster/cakephp-lessy)

This plugin is based on:

* [LessCSS](http://leafo.net/lessphp), a Less2Css compiler adapted from [http://lesscss.org](http://lesscss.org) by [Leaf Corcoran](mailto://leafot@gmail.com)
* [YUI CSS compressor PHP port](https://github.com/tubalmartin/YUI-CSS-compressor-PHP-port), a Css compressor based on the popular YUI compressor.

**Table of Contents**

1. [What it does](#what-it-does)
2. [How to use](#how-to-use)
3. [Manage your Less Files](#manage-your-less-files)
4. [TODOs](#todos)
5. [License](#license)

<a name="what-it-does"></a>
## 1. What it does

Lessy lets you develop all of your styles with LessCss syntax, automatically compiles them to CSS and minifies them afterwards. Lessy not only handles your app styles, but can handle all of your custom plugins too.

On a typical CakeRequest cycle the plugin loads up via a custom DispatcherFilter and does all conversions and compressions automatically without polluting your beforeRender callbacks.

**Processing Flow**

1. Request is made
2. Lessy checks all loaded Plugins for a folder in `/app/LoadedPlugin/webroot/less` and compiles all `*.less` files in this folder to `/app/LoadedPlugin/webroot/css/LessFilename.css`
3. Lessy checks the app itself for a folder in `/app/webroot/less` and compiles all `*.less` files in this folder to `/app/webroot/css/LessFilename.css`

The overhead of running this process on every request is very small, because LessCss automatically checks if the `*.less` files have been modified and a new compilation is really neccessary. Furthermore the compiled css files are only compressed if they are newly compiled.

To minimize the workload even more, there is a config variable available that skips the whole Lessy processing on production.

<a name="how-to-use"></a>
## 2. How to use

1. Clone or [Download](http://github.com/frankfoerster/cakephp-lessy/zipball/master) the project and add it to your `/app/Plugin/` folder. (resulting in `/app/Plugin/Lessy`)

2. Enable Lessy by adding the following line to your `/app/Config/bootstrap.php` file:

        CakePlugin::load('Lessy');

3. Add the LessMinFilter to the DispatcherFilter configuration, again in `/app/Config/bootstrap.php`:

        Configure::write('Dispatcher.filters', array(
            'AssetDispatcher',
            'CacheDispatcher',
            'Lessy.LessMinFilter' // <-- add this line
        ));

4. Make sure all your `*.less` files reside in the folder `/app/webroot/less` or for Plugins in `/app/Plugin/YourPlugin/webroot/less`

<a name="manage-your-less-files"></a>
## 3. Manage your Less Files

Usually you want to maintain several less files for your project that compile down to one file. In LessCss, as you probably already know you can use `@import url(...);` statements to do that.

But since the plugin compiles all `*.less` files to their corresponding `*.css` file you can simply give your mixin or grid or whatever imported files another file ending to avoid that.

For example if you have several files that you want to import into one master file and compile only that you could use the following pattern:

    app.less <-- this is your master file
    mixins.less.ins <-- imported files get another file ending
    reset.less.ins  <--                -.-
    ....

<a name="todos"></a>
## 4. TODOs

* Add configuration options to allow merging of all / several minified css files into one resulting file.
* Add configuration options mapping to CssMin’s PHP settings overrides

<a name="license"></a>
## 5. License

Files in the `Vendor` folder are not part of this License Agreement and keep their original license as stated in their source.

Copyright (c) 2013 Frank Förster (http://frankfoerster.com)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

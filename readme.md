# Lessy = LessCSS + CSS-Minifier + JS Concatenator Plugin for CakePHP 2.3+
[![Build Status](https://travis-ci.org/frankfoerster/cakephp-lessy.png?branch=master)](https://travis-ci.org/frankfoerster/cakephp-lessy)

## 1. What it does

Lessy lets you develop all of your styles with LessCss syntax, automatically compiles them to CSS and minifies them afterwards. It also provides a simple JS concatenator similar to the Ruby on Rails asset pipeline. Lessy not only handles your app styles and js, but can handle all of your custom plugins too.

The processing is done via custom DispatcherFilters and runs automatically on each request.

To minimize the workload in production, there is a config variable available that skips the whole Lessy processing.

## 2. Install and load the plugin

1. Clone or [Download](http://github.com/frankfoerster/cakephp-lessy/zipball/master) the project and add it to your `/app/Plugin/` folder. (resulting in `/app/Plugin/Lessy`)
2. Enable Lessy by adding the following line to your `/app/Config/bootstrap.php` file:

        CakePlugin::load('Lessy');

## 2. Using LessMinFilter (LessCSS + CSS-Minifier)

**Directory Structure**

    app
    - Assets
      - less       <-- your app *.less files go here
    - Plugin
      - CustomPlugin
        - Assets
          - less   <-- your CustomPlugin *.less files go here
        - webroot
          - css    <-- the compiled CustomPlugin css files are automatically saved here
    - webroot
      - css        <-- the compiled app css files are automatically saved here

**Enable LessMinFilter**

Add LessMinFilter to the `Dispatcher.filters` configuration in `/app/Config/bootstrap.php`:

    Configure::write('Dispatcher.filters', array(
        'Lessy.LessMinFilter', // <-- add this line
        'AssetDispatcher',
        'CacheDispatcher'
    ));

## 3. Using JsConcatFilter (Javascript Concatenator)

**Directory Structure**

    app
    - Assets
      - js              <-- your app javascript libraries and manifests go here
        - library_one
          - build
          - src
        - library_two
          - dist
          - src
        - app.js        <-- this is an example manifest file (you can name it however you want)
    - Plugin
      - CustomPlugin
        - Assets
          - js          <-- your CustomPlugin javascript libraries and manifests go here
            - another_library
              - build
            - plugin.js <-- this is an example manifest file (you can name it however you want)
        - webroot
          - js
            - plugin.js <-- this is the concatenated version of your Plugin/CustomPlugin/Assets/js/plugin.js manifest
    - webroot
      - js
        - app.js        <-- this is the concatenated version of your Assets/js/app.js manifest

**Manifest Files**

Manifest files are `*.js` files that reside either in the `app/Assets/js` folder or in one of your plugin folders `app/Plugin/CustomPlugin/Assets/js`.
They use a similar syntax to the Ruby on Rails asset pipeline.

Following the directory structure from above, the `app.js` manifest may look like this:

    //= require library_one/build/lib-one.min.js
    //= require library_two/dist/lib-two.min.js

The JsConcatFilter will then try to fetch the contents of these two required files and produce the concatenated `app.js` file in `app/webroot/js`.

## 3. Manage your Less Files

Usually you maintain several less files for your project that compile down to one file. In LessCss, as you probably already know you can use `@import url(...);` statements to do that.

But since the plugin compiles all `*.less` files to their corresponding `*.css` file you have to use a different file extension for your secondary files, e.g. `*.less.inc` and adjust your import statements accordingly.

For example if you have several files that you want to import into one master file and compile only that you could use the following pattern:

    app.less <-- this is your master file
    grid.less.inc   <-- imported files get another file ending
    reset.less.inc  <--                 -.-
    ...

## 4. Used Third Party Tools

* [LessCSS](http://leafo.net/lessphp), a Less2Css compiler adapted from [http://lesscss.org](http://lesscss.org) by [Leaf Corcoran](mailto://leafot@gmail.com)
* [YUI CSS compressor PHP port](https://github.com/tubalmartin/YUI-CSS-compressor-PHP-port), a Css compressor based on the popular YUI compressor.

## 5. License

Files in the `Vendor` folder are not part of this License Agreement and keep their original license as stated in their source.

Copyright (c) 2013 Frank Förster (http://frankfoerster.com)

MIT License (http://www.opensource.org/licenses/mit-license.php)

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

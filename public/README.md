# public

These files are copied into public/packages/ddpro/admin in your application.

## bower_components

This contains the bower installed components for AdminLTE and its dependencies.  The necessary assets from
here are included in the main template in [resources](/resources/README.md).

There are some additional packages installed here using bower.  See the bower.json file in the root directory
for the things that we have brought in.

Eventually all of the plugins in css/js should be bowerized and brought in via this directory (e.g. knockout.js,
jquery, jquery plugins, etc).

## css

The CSS files that came across from the [Laravel-Administrator](https://github.com/FrozenNode/Laravel-Administrator)
package.

## img

The image files that came across from the [Laravel-Administrator](https://github.com/FrozenNode/Laravel-Administrator)
package.

## js

The JS files that came across from the [Laravel-Administrator](https://github.com/FrozenNode/Laravel-Administrator)
package.

This includes the following plugins that are used directly:

* [ckeditor](http://ckeditor.com/)
* [history](https://github.com/browserstate/history.js/)
* [jQuery](https://jquery.com/)
* [knockout.js](http://knockoutjs.com/)
* [plupload](http://www.plupload.com/)

Most of the above should eventually be updated to the latest versions and bower-ised for installation.

The markdown.js file here appears to be an old version of [evilstreak markdown-js](https://github.com/evilstreak/markdown-js)
which appears to be no longer maintained anyway.  We may be better switching to [markdown-it](https://github.com/markdown-it/markdown-it)

# CMS Media Management

This module allows you to upload media to the web server,
 then use that media in blog posts, or any other pages on the site.

## Supported MIME types

It currently supports the following extensions:

1. `jpg`
2. `png`
3. `gif`
4. `webp`

To add more extensions, it is critical that you update these files:

* `~~cms/media/ci/config/mimes.php` - ensure the desired extension is included
* `~~cms/media/ci/db/Mmedia` - append to both `$media_types` array at top of the file, *and* the `$config['allowed_types']` in the `function upload_to`
* `~~ubow/ci/assets/js/UBOW.js` - append the type to `const MTYPES` array
* `~~ubow/ci/helpers/general_helper.php` - append to the `$mtypes` array in `function imgSrc`

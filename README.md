# Facebook Photos Module

With this ExpressionEngine module you can include Facebook albums from your Facebook page in your website.
The module has a optional caching feature which will save the photos to your own website so the Facebook API doesn't have to be called each time.

You can buy this module at https://devot-ee.com/add-ons/facebook-photos

### 1. Installation
Upload the fb_photos directory to the third-party folder of your installation. Go to modules and install the module.

### 2. Configuration
Before you can use this module you have to register a Facebook app and obtain the App id, App secret and a Acces Token.
You can get this at http://www.facebook.com/developers
![alt text](https://github.com/TKuypers/expressionengine-facebook-photos-module/raw/master/src/images/settings.jpg "Settings")

In the Modules page of your control panel click on the Facebook albums module and enter these settings. When the settings are saved you will be redirected to setup the albums you would like to use. 
![alt text](https://github.com/TKuypers/expressionengine-facebook-photos-module/raw/master/src/images/albums.jpg "Albums")

To use a album you have to fill in a short name, with this name you can use the album in the templates of your website. If you want the module to download the photos to your own website you have to select the sync checkbox and select a folder you want to use.

## Module tags
The module has three different tags

#### Info tag
```
{exp:fb_photos:album_info name="short_name" item="item"}
```

With this tag you can retrieve information about the album that facebook provides. You can use this to display for example the title or description of a album.

*short_name - The short name of the album you've entered in the module cp
*item - This parameter lets you define what information you want to display. You can fill in 'all' to see the fields you can use for the album.

#### Pagination tag
```
{exp:fb_photos:album_pagination name="short_name" param="page" limit="25"}

    {if prev}
        <a href="/album/?page={prev_offset}">Back</a>
    {/if}

    {if next}
        <a href="/album/?page={next_offset}">Next</a>
    {/if}

{/exp:pb_photos:album_pagination}
```

*short_name - The short name of the album you've entered in the module cp
*param - The url parameter that will be used for pagination. Default: ?page=...
*limit - The maximum number of photos the module will show. Default: 25
This tag provides a simple form of pagination. The tag will determine if there is a next or previous page and let you set up some html according to that.

#### Album tag
```
{exp:fb_photos:show_album name="short_name" pagination="yes" param="page" limit="limit"}

    <a href="{img-3}" title="{name}">
        <img src="{img-3}" alt="{name}"
    </a>   
    
    Likes: {likes}, Comments: {comments}

{/exp:fb_photos:show_album}
```

*short_name - The short name of the album you've entered in the module cp
*pagination - Determines if you want to use pagination (yes/no). Default:yes
*param - The url parameter that will be used for pagination. Default: ?page=...
*limit - The maximum number of photos the module will show
This tag let's you display the photos from a facebook album. For every image in the album Facebook provides an array with different sizes. These sizes are available trough the {img-key} variable. To view the sizes that are available you can use the {sizes} variable.

If syncing is enabled the available images will be automaticly saved to your server. The vars in the tag will also automaticly show the local versions.

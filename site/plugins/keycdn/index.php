<?php

use Kirby\Cms\File;
use Kirby\Cms\FileVersion;
use Kirby\Http\Uri;
use Kirby\Toolkit\Str;

function keycdn($file, $params = [])
{
    $suffix = null;
    $name = Str::before($file->filename(), "." . $file->extension());


    // check the parameters passed to the function and set width and height
    if (empty($params) === false) {
        if( empty($params['crop']) === false ) {
            $suffix = '-' . $params['width'] . 'x' . $params['height'];
        }elseif (empty($params['height']) === false) {
            $suffix = '-x' . $params['height'];
        }elseif($params['width'] !== false) {
            $suffix = '-' . $params['width'] . 'x';
        }
    }
    $versionName = Str::replace($name . $suffix . '.' . $file->extension(), '_', '-');

    // strip site url and replace previous filename from old path with new version name
    $newFilename = Str::after(Str::before($file->mediaUrl(), $file->filename()), kirby()->site()->url()) . $versionName;

    // return final URL
    return option('keycdn.domain') . $newFilename;
}

Kirby::plugin('author/keycdn', [
    'components' => [
        'url' => function ($kirby, $path, $options, $original) {
            if (option('keycdn', false) !== false && Str::contains($path, 'assets')) {
                return option('keycdn.domain') . '/' . Cachebuster::path($path);
            }

            return $original($path, $options);
        },
        'file::version' => function (Kirby $kirby, File $file, array $options = []) {
            static $originalComponent;
            // if keycdn option is enabled
            if (option('keycdn', false) !== false) {
                $url = keycdn($file, $options);
                // return a new FileVersion object with the given settings
                return new FileVersion([
                    'modifications' => $options,
                    'original'      => $file,
                    'root'          => $file->root(),
                    'url'           => $url,
                ]);
            }
            // if $originalComponent is null, require the original component
            if ($originalComponent === null) {
                $originalComponent = (require $kirby->root('kirby') . '/config/components.php')['file::version'];
            }
            // and return it with the given options
            return $originalComponent($kirby, $file, $options);
        },
        'file::url' => function (Kirby $kirby, File $file): string {
            static $originalComponent;
            // if the file type is an image
            if ($file->type() === 'image' && option('keycdn', false) !== false) {
                // call the keycdn method
                return keycdn($file);
            }
            // if $originalComponent is null, require the original component
            if ($originalComponent === null) {
                $originalComponent = (require $kirby->root('kirby') . '/config/components.php')['file::url'];
            }
            // and return it
            return $originalComponent($kirby, $file);
        }
    ]
]);
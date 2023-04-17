<?php

namespace WaxFramework\Enqueue;

defined( 'ABSPATH' ) || exit;

use WaxFramework\App;

class Enqueue {
    public static function style( string $handle, string $src, array $deps = [], $media = 'all' ) {
        static::process_style( $handle, $src, $deps, $media, 'wp_enqueue_style' );
    }

    public static function register_style( string $handle, string $src, array $deps = [], $media = 'all' ) {
        static::process_style( $handle, $src, $deps, $media, 'wp_register_style' );
    }

    public static function script( string $handle, string $src, array $deps = [], bool $in_footer = false ) {
        static::process_script( $handle, $src, $deps, $in_footer, 'wp_enqueue_script' );
    }

    public static function register_script( string $handle, string $src, array $deps = [], bool $in_footer = false ) {
        static::process_script( $handle, $src, $deps, $in_footer, 'wp_register_script' );
    }

    protected static function process_style( string $handle, string $src, array $deps, $media, string $method ) {
        $src   = static::process_src( $src );
        $src   = "assets/css/{$src}";
        $asset = include App::get_dir( $src . '.asset.php' );

        $method( $handle, App::get_url( "{$src}.css" ), $deps, $asset['version'], $media );

        /**
         * Load css hot reload js script
         */
        if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG === true ) {
            wp_enqueue_script( "{$handle}-script", App::get_url( "{$src}.js" ), $asset['dependencies'], $asset['version'] );
        }
    }

    protected static function process_script( string $handle, string $src, array $deps, bool $in_footer, string $method ) {
        $src   = static::process_src( $src );
        $src   = "assets/js/{$src}";
        $asset = include App::get_dir( $src . '.asset.php' );

        $method( $handle, App::get_url( $src . '.js' ), array_merge( $asset['dependencies'], $deps ), $asset['version'], $in_footer );
    }

    protected static function process_src( string $src ) {
        $path_info = pathinfo( $src );
        $src       = $path_info['filename'];
        if ( '\\' !== $path_info['dirname'] ) {
            $src = $path_info['dirname'] . '/' . $path_info['filename'];
        }
        $src = ltrim( $src, '.' );
        return ltrim( $src, '/' );
    }
}
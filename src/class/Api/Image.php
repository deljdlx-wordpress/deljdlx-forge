<?php
namespace Deljdlx\WPForge\Api;

use Deljdlx\WPForge\BaseApi;
use Deljdlx\WPForge\Models\User;
use WP_REST_Request;

class Image extends BaseApi
{
    public function initialize()
    {
        register_rest_route($this->apiRoot, '/image', [
            'methods' => 'POST',
            'callback' => [$this, 'uploadImage'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($this->apiRoot, '/image', [
            'methods' => 'GET',
            'callback' => function () {
                return rest_ensure_response('ok');
            },
            'permission_callback' => '__return_true',
        ]);
    }

    public function checkPermission(WP_REST_Request $request)
    {
        $user = User::getCurrentByCookie();
        if (!$user) {
            return false;
        }

        return $user->can('edit_posts');
    }

    public function uploadImage(WP_REST_Request $request)
    {
        $file = $request->get_file_params();
        $file = $file['file'];

        // check file size > 0
        if ($file['size'] == 0) {
            return [
                'status' => 'error',
                'error' => 'empty file'
            ];
        }


        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'];
        $upload_url = $upload_dir['url'];

        $filename = $file['name'];
        $filename = sanitize_file_name($filename);
        $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $fileExtension;

        $file_path = $upload_path . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $url = $upload_url . '/' . $filename;

            return [
                'status' => 'success',
                'image_url' => $url,
                'size' => $file['size'],
            ];
        }

        return [
            'status' => 'error',
            'error' => 'move_uploaded_file error'
        ];
    }
}
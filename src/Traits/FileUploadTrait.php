<?php
namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\Image;
// use Intervention\Image\Facades\Image;

trait FileUploadTrait
{
    private $file_attribute_name = "";
    protected $upload_disk_name  = "";

    /*protected $file_meta_data =  [
    'file_type' => 'file_type',
    'file_size' => 'file_size',
    'file_name' => 'file_name'
    ];*/

    public function getFileUrl($file_path, $size = "full")
    {
        $files = $this->getFileUrls($file_path);
        
        if (isset($files[$size])) {
            return $files[$size];
        } else {
            return $this->getDiskUrl($file_path);
        }
    }

    public function getFileUrls($file_path)
    {
        $disk       = $this->upload_disk_name;
        $image_size = Config::get('site.image_size', []);
        $tmp        = explode('.', $file_path);
        $extension  = end($tmp);

        $name             = basename($file_path, "." . $extension);
        $destination_path = dirname($file_path) . "/";
        try {
            $file_urls = ['full' => $this->getDiskUrl($file_path)];
        } catch (\InvalidArgumentException $e) {
            $file_urls = ['full' => ""];
        }

        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
            case 'png':
                foreach ($image_size as $k => $size) {
                    try {
                        $path = $destination_path . $name . "-" . $k . "-" . $size[0] . "x" . $size[1] . "." . $extension;
                        if ($this->storageFileExist($path)) {
                            $file_urls[$k] = $this->getDiskUrl($path);
                        } else {
                            $file_urls[$k] = "";
                        }
                    } catch (\InvalidArgumentException $e) {
                        $file_urls[$k] = "";
                    }
                }
                return $file_urls;
                break;
        }

    }

    public function removeFile()
    {

        $file = $this->attributes[$this->file_attribute_name];

        if ($file) {
            if (File::isFile($file)) {
                File::delete($file);
            }
        }
        Storage::disk($this->upload_disk_name)->delete($file);
        $this->attributes[$this->file_attribute_name] = " ";
    }

    public function saveFile($value, $attribute_name = "image", $destination_path = "", $disk = "")
    {

        if (isset($this->attributes[$attribute_name]) && ends_with($value, $this->attributes[$attribute_name])) {
            return true;
        }

        $this->file_attribute_name = $attribute_name;
        if (empty($disk)) {
            $disk = $this->upload_disk_name = Config::get('filesystems.default');
        } else {
            $this->upload_disk_name = $disk;
        }

        $image_size = Config::get('site.image_size', []);

        if (isset($this->attributes[$attribute_name])) {
            $this->removeFile($this->attributes[$attribute_name]);
            if ($value == null) {
                return true;
            }
        }
        
        $this->saveFileData($value);

        // if a base64 was sent, store it in the db
        if (starts_with($value, 'data:')) {
            $filename = config('app.name') . "-" . md5($value . time());
            $fileext  = "." . $this->getFileType($value);
            if (starts_with($value, 'data:image')) {
                $image       = Image::make($value);
                $file_stream = $image->stream()->getContents();
            } else {
                $base64_str  = substr($value, strpos($value, ",") + 1);
                $file_stream = base64_decode($base64_str);
            }

            $this->storeFile($value, $destination_path . '/' . $filename . $fileext);
            $this->attributes[$attribute_name] = $destination_path . '/' . $filename . $fileext;
            return true;
        } else if (starts_with($value, 'http')) {

            if (strpos($value, '?') !== false) {
                $url = explode('?', $value);
                $url = $url[0];
            } else {
                $url = $value;
            }
            $url_arr  = explode('.', $url);
            $ct       = count($url_arr);
            $filename = config('app.name') . "-" . md5($value . time());
            try {
                $contents = file_get_contents($value);
            } catch (\Exception $e) {
                $this->attributes[$attribute_name] = "";
                return false;
            }
            $finfo   = new \finfo(FILEINFO_MIME_TYPE);
            $mime    = $finfo->buffer($contents);
            $fileext = $this->mime_to_ext($mime);

            if (!$fileext) {
                $fileext = "." . $url_arr[$ct - 1];
            } else {
                $fileext = "." . $fileext;
            }

            $this->storeFile($value, $destination_path . '/' . $filename . $fileext);
            $this->attributes[$attribute_name] = $destination_path . '/' . $filename . $fileext;
            return true;
        } elseif (is_object($value)) {
            $name      = $value->getClientOriginalName();
            $temp_name = $value->getPathName();

            $filename = config('app.name') . "-" . md5($name . time());
            $fileext  = '.' . $value->getClientOriginalExtension();

            $this->storeFile($value, $destination_path . '/' . $filename . $fileext);
            $this->attributes[$attribute_name] = $destination_path . '/' . $filename . $fileext;

            return true;
        } else if (!empty($value) && is_string($value)) {
            $this->attributes[$attribute_name] = $value;

        } else {
            return false;
        }
    }

    private function storeFile($value, $destination_path)
    {
        $disk       = $this->upload_disk_name;
        $path_parts = pathinfo($destination_path);

        if (starts_with($value, 'data:')) {

            if (starts_with($value, 'data:image')) {
                $image       = Image::make($value);
                $file_stream = $image->stream()->getContents();
            } else {
                $base64_str  = substr($value, strpos($value, ",") + 1);
                $file_stream = base64_decode($base64_str);
            }
            Storage::disk($disk)->put($destination_path, $file_stream);
            $this->createThumb($file_stream, $path_parts['dirname'], $path_parts['filename'], "." . $path_parts['extension']);
            return true;
        } else if (starts_with($value, 'http')) {
            $contents = file_get_contents($value);
            Storage::disk($disk)->put($destination_path, $contents);

            $this->createThumb($contents, $path_parts['dirname'], $path_parts['filename'], "." . $path_parts['extension']);
            return true;
        } elseif (is_object($value)) {

            $value->storeAs($path_parts['dirname'], $path_parts['filename'] . "." . $path_parts['extension'], $disk);
            //try {
            $this->createThumb($value, $path_parts['dirname'], $path_parts['filename'], "." . $path_parts['extension']);
            //} catch (Exception $e) {

            //}
            return true;
        } else if (!empty($value) && is_string($value)) {
            $this->attributes[$this->file_attribute_name] = $value;
        } else {
            return false;
        }
    }

    private function saveFileData($file)
    {
        $default = [
            'file_type' => 'file_type',
            'file_size' => 'file_size',
            'file_name' => 'file_name',
        ];
        $attrs = $default;
        if (isset($this->file_meta_data) && !empty($this->file_meta_data) && is_array($this->file_meta_data)) {
            $attrs = array_merge($default, $this->file_meta_data);
        }

        if (Schema::hasColumn($this->getTable(), $attrs['file_type'])) {
            if (starts_with($file, 'data:')) {
                $type                                  = explode(';', $file);
                $type                                  = explode(':', $type[0]);
                $this->attributes[$attrs['file_type']] = $type[0];
            } else if (starts_with($file, 'http')) {
                $this->attributes[$attrs['file_type']] = $this->getRemoteMimeType($file);
            } else if (is_object($file)) {
                $this->attributes[$attrs['file_type']] = $file->getMimeType();
            }
        }

        if (Schema::hasColumn($this->getTable(), $attrs['file_size'])) {
            if (starts_with($file, 'data:')) {
                $size                                  = (int) (strlen(rtrim($data, '=')) * 3 / 4);
                $this->attributes[$attrs['file_size']] = $size;
            } else if (starts_with($file, 'http')) {
                $this->attributes[$attrs['file_size']] = $this->remotefileSize($file);
            } else if (is_object($file)) {
                $this->attributes[$attrs['file_size']] = $file->getSize();
            }
        }

        if (Schema::hasColumn($this->getTable(), $attrs['file_name'])) {
            if (starts_with($file, 'data:')) {
                if (starts_with($file, 'data:image')) {
                    $orig_file_name = config('app.name') . " Image";
                } else {
                    $orig_file_name = config('app.name') . " File";
                }
            } else if (starts_with($file, 'http')) {
                $name           = basename($file);
                $orig_file_name = explode('.', $name);
                $orig_file_name = ucfirst(rtrim($orig_file_name[0]));
                $orig_file_name = preg_replace('/[^A-Za-z0-9 \-\']/', '-', $orig_file_name);
                $orig_file_name = preg_replace('/[-]{2,}/', '-', $orig_file_name);
                $orig_file_name = preg_replace('/[-]/', ' ', $orig_file_name);
            } else if (is_object($file)) {
                $name           = $file->getClientOriginalName();
                $orig_file_name = explode('.', $name);
                $orig_file_name = ucfirst(rtrim($orig_file_name[0]));
                $orig_file_name = preg_replace('/[^A-Za-z0-9 \-\']/', '-', $orig_file_name);
                $orig_file_name = preg_replace('/[-]{2,}/', '-', $orig_file_name);
                $orig_file_name = preg_replace('/[-]/', ' ', $orig_file_name);
            } else {
                $orig_file_name = "File";
            }
            $this->attributes[$attrs['file_name']] = $orig_file_name;
        }
    }

    private function createThumb($value, $destination_path, $filename, $ext)
    {

        $image_size = Config::get('site.image_size', []);
        $disk       = $this->upload_disk_name;

        switch (strtolower($ext)) {
            case '.jpg':
            case '.jpeg':
            case '.gif':
            case '.png':
                foreach ($image_size as $name => $size) {
                    if (is_object($value)) {
                        $image_thumb = Image::make($value->getRealPath())->fit($size[0], $size[1], function ($constraint) {$constraint->upsize();});
                        $image_thumb = $image_thumb->stream()->__toString();

                        $file = $filename . "-" . $name . "-" . $size[0] . "x" . $size[1] . $ext;
                        Storage::disk($disk)->put($destination_path . "/" . $file, $image_thumb);
                    } else {
                        $tempImage = tempnam(sys_get_temp_dir(), 'temp-image.jpg');

                        $handle = fopen($tempImage, "w");
                        fwrite($handle, $value);
                        fclose($handle);

                        $image_thumb = Image::make($tempImage)->fit($size[0], $size[1], function ($constraint) {$constraint->upsize();});
                        $image_thumb = $image_thumb->stream()->__toString();

                        $file = $filename . "-" . $name . "-" . $size[0] . "x" . $size[1] . $ext;
                        Storage::disk($disk)->put($destination_path . "/" . $file, $image_thumb);

                        unlink($tempImage);
                    }
                }
                break;
        }

    }

    private function createDocumentThumb($content, $destination_path, $filename, $ext)
    {
        $image_size = Config::get('site.image_size', []);
        $disk       = $this->upload_disk_name;

        foreach ($image_size as $name => $size) {
            $tempImage = tempnam(sys_get_temp_dir(), 'temp-image.jpg');
            $handle    = fopen($tempImage, "w");
            fwrite($handle, $content);
            fclose($handle);

            $image_thumb = Image::make($tempImage)->resize($size[0], $size[1], function ($constraint) {
                $constraint->upsize();
            });

            $file        = $filename . "-" . $name . "-" . $size[0] . "x" . $size[1] . '.jpg';
            $image_thumb = $image_thumb->stream()->__toString();

            Storage::disk($disk)->put($destination_path . "/" . $file, $image_thumb);
            unlink($tempImage);
        }
    }

    private function getDiskUrl($file_path)
    {
        $disk = $this->upload_disk_name;
        if ($disk == 's3') {
            $base_url = Config::get("filesystems.disks.s3.cloudfront_url");
            if (!empty($base_url)) {
                return $base_url . "/" . $file_path;
            } else {
                return Storage::disk($disk)->url($file_path);
            }
        } else {
            return Storage::disk($disk)->url($file_path);
        }
    }

    private function storageFileExist($file_path)
    {
        $disk = $this->upload_disk_name;
        if ($disk == 's3') {
            $base_url = Config::get("filesystems.disks.s3.cloudfront_url");
            if (!empty($base_url)) {
                return $base_url . "/" . $file_path;
            } else {
                return Storage::disk($disk)->url($file_path);
            }
        } else {
            return Storage::disk($disk)->url($file_path);
        }
    }

    private function mime_to_ext($mime)
    {
        $mime_types = array(

            'txt'  => 'text/plain',
            'htm'  => 'text/html',
            'html' => 'text/html',
            'php'  => 'text/html',
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'xml'  => 'application/xml',
            'swf'  => 'application/x-shockwave-flash',
            'flv'  => 'video/x-flv',

            // images
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'ico'  => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif'  => 'image/tiff',
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip'  => 'application/zip',
            'rar'  => 'application/x-rar-compressed',
            'exe'  => 'application/x-msdownload',
            'msi'  => 'application/x-msdownload',
            'cab'  => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3'  => 'application/octet-stream',
            'mp3'  => 'audio/mpeg',
            'qt'   => 'video/quicktime',
            'mov'  => 'video/quicktime',

            // adobe
            'pdf'  => 'application/pdf',
            'psd'  => 'image/vnd.adobe.photoshop',
            'ai'   => 'application/postscript',
            'eps'  => 'application/postscript',
            'ps'   => 'application/postscript',

            // ms office
            'doc'  => 'application/msword',
            'rtf'  => 'application/rtf',
            'xls'  => 'application/vnd.ms-excel',
            'ppt'  => 'application/vnd.ms-powerpoint',

            // open office
            'odt'  => 'application/vnd.oasis.opendocument.text',
            'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        if (in_array($mime, $mime_types)) {

            return array_search($mime, $mime_types);
        } else {
            return $mime;
        }

    }
    private function getFileType($base64_image_string = '')
    {
        $splited = explode(',', substr($base64_image_string, 5), 2);
        $mime    = $splited[0];

        $mime_split_without_base64 = explode(';', $mime, 2);
        $mime_split                = explode('/', $mime_split_without_base64[0], 2);

        return $mime_split[1];
    }

    private function getRemoteMimeType($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        return curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    }

    private function remotefileSize($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_exec($ch);
        $filesize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        if ($filesize) {
            return $filesize;
        } else {
            return 0;
        }

    }

}

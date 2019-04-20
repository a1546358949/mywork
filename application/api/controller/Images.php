<?php


namespace app\api\controller;


use think\Controller;
use think\Image;

class Images extends Controller
{
    public function images(){
        $images = request()->file('img');
        $img = Image::open($images);
        $width = $img->width();
        $wid = $width * 0.2;
        $height = $img->height();
        $hei = $height * 0.2;
        $image = $img->crop(108,108,$wid,$hei)->save('static/img/image'.time().'.jpg');
        var_dump($wid);
    }
}
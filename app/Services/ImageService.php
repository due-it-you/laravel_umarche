<?php 

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use InterventionImage;

//画像ファイル名の作成、画像のリサイズの処理

class ImageService {
    public static function upload($imageFile, $folderName) {

         //ランダムなファイル名の作成
         $fileName = uniqid(rand().'_');
         //拡張子の取得
         $extension = $imageFile->extension();
         $fileNameToStore = $fileName. '.' . $extension;

         //取得した画像をリサイズ
         $resizedImage = InterventionImage::make($imageFile)
                             ->resize(1980,1080)
                             ->encode();

        Storage::put('public/' . $folderName . '/' . $fileNameToStore, $resizedImage);

        return $fileNameToStore;
    }
}

?>
<?php

namespace app\core\services;

use Yii;
use yii\base\BaseObject;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use yiier\graylog\Log;

class UploadService extends BaseObject
{
    /**
     * @param UploadedFile $uploadedFile
     * @param string $filename
     * @return bool|string
     * @throws \Exception
     */
    public function uploadRecord(UploadedFile $uploadedFile, string $filename)
    {
        try {
            if (!$this->saveFile($uploadedFile, $filename)) {
                throw new \Exception(Yii::t('app', 'Upload file failed'));
            }
            return $this->getFullFilename($filename, params('uploadWebPath'));
        } catch (\Exception $e) {
            Log::error('upload record error', [$uploadedFile, (string)$e]);
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $uploadedFile UploadedFile
     * @param string $filename
     * @return bool
     * @throws \yii\base\Exception
     */
    protected function saveFile(UploadedFile $uploadedFile, string $filename)
    {
        $filename = $this->getFullFilename($filename);
        $this->deleteLocalFile($filename);
        FileHelper::createDirectory(dirname($filename));
        return $uploadedFile->saveAs($filename);
    }


    /**
     * @param $filename
     * @param $path
     * @return bool|string
     */
    public function getFullFilename($filename, string $path = '')
    {
        $path = $path ?: params('uploadSavePath');
        $filename = Yii::getAlias(rtrim($path, '/') . '/' . $filename);
        return $filename;
    }

    /**
     * @param string $filename
     */
    public function deleteLocalFile(string $filename)
    {
        $fileAbsoluteName = $this->getFullFilename($filename);
        @unlink($fileAbsoluteName);
    }
}

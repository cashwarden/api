<?php

namespace app\core\services;

use app\core\exceptions\ErrorCodes;
use app\core\exceptions\FileException;
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
            $this->checkEncoding($filename);
            return $this->getFullFilename($filename, params('uploadWebPath'));
        } catch (\Exception $e) {
            Log::error('upload record error', [$uploadedFile, (string)$e]);
            throw new \Exception($e->getMessage(), $e->getCode());
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

    /**
     * @param string $filename
     * @param string $encoding
     * @throws FileException
     */
    public function checkEncoding(string $filename, $encoding = 'UTF-8')
    {
        $fileAbsoluteName = $this->getFullFilename($filename);
        if (!mb_check_encoding(file_get_contents($fileAbsoluteName), $encoding)) {
            @unlink($fileAbsoluteName);
            throw new FileException(Yii::t('app/error', ErrorCodes::FILE_ENCODING_ERROR));
        }
    }
}

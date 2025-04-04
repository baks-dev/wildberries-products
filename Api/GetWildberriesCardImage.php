<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Wildberries\Products\Api;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class GetWildberriesCardImage
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $project,
        private HttpClientInterface $client,
        private Filesystem $filesystem,
    ) {}

    /**
     * @param string $url - url фото загрузки
     * @param mixed $Image - DTO для присвоения значений
     * @param string $nameDir - Директория загрузки файла
     */
    public function get(string $url, object $Image, string $nameDir, $reload = false): mixed
    {
        /** Вычисляем хеш ссылки и присваиваем его к названию файла */
        $originalFilename = pathinfo($url);
        $newFilename = 'image.'.$originalFilename['extension'];

        $dir = md5($url);

        $arrUpload = [
            $this->project,
            'public',
            'upload',
            $nameDir,
            $dir
        ];

        /** Полный путь к директории загрузки */
        $uploadDir = implode(DIRECTORY_SEPARATOR, $arrUpload); // $this->project.'/public/upload/'.$nameDir.'/'.$dir;
        $path = $uploadDir.DIRECTORY_SEPARATOR.$newFilename;

        /**
         * Если файла не существует - скачиваем
         */
        if($reload || false === $this->filesystem->exists($path))
        {
            /* Создаем директорию для загрузки */
            $this->filesystem->exists($uploadDir) ?: $this->filesystem->mkdir($uploadDir);

            $response = $this->client->request('GET', $url);

            // Responses are lazy: this code is executed as soon as headers are received
            if(200 !== $response->getStatusCode())
            {
                return false;
            }

            /**
             * Если файл перезагружается, и его актуальность больше 1 суток - удаляем директорию
             */
            if($reload && filemtime($path) < (time() - 86400))
            {
                $this->filesystem->remove($uploadDir);

                /* Создаем директорию для новой загрузки */
                $this->filesystem->mkdir($uploadDir);
            }

            if(!file_exists($path))
            {
                // получить содержимое ответа и сохранить их в файл
                $fileHandler = fopen($path, 'w');

                foreach($this->client->stream($response) as $chunk)
                {
                    fwrite($fileHandler, $chunk->getContent());
                }
            }
        }

        /* Размер файла */
        $fileSize = filesize($path);

        $Image->setName($dir);
        //$Image->setDir($dir);
        $Image->setExt($originalFilename['extension']);
        $Image->setSize($fileSize);

        return $Image;
    }


    public static function removeDir($dir): bool
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach($files as $file)
        {
            (is_dir($dir.'/'.$file)) ? self::removeDir($dir.'/'.$file) : unlink($dir.'/'.$file);
        }

        return rmdir($dir);
    }


}